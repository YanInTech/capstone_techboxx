<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Exception;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $now = Carbon::now('Asia/Manila');

        // ---------------------
        // Date range for period
        // ---------------------
        switch ($period) {
            case 'daily':
                $startDate = $now->copy()->startOfDay();
                $endDate   = $now->copy()->endOfDay();
                break;
        
            case 'weekly':
                $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
                $endDate   = $now->copy()->endOfWeek(Carbon::SUNDAY);
                break;
        
            case 'annually':
                $startDate = $now->copy()->startOfYear();
                $endDate   = $now->copy()->endOfYear();
                break;
        
            case 'monthly':
            default:
                $startDate = $now->copy()->startOfMonth();
                $endDate   = $now->copy()->endOfMonth();
                break;
        }
        

        // ---------------------
        // Orders & Revenue (only these change with period)
        // ---------------------
        $filteredOrders = Order::whereIn('status', ['paid', 'completed'])
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $ordersCount  = $filteredOrders->count();
        $totalRevenue = $filteredOrders->sum('total');

        // Static values
        $costOfGoods = 205000;
        $profit      = 100000;
        $activeUsers = 1000000;

        // ---------------------
        // Top selling products (unchanged - global)
        // ---------------------
        $topProducts = CartItem::with([
                'case', 'cpu', 'gpu', 'motherboard', 'ram', 'storage', 'psu', 'cooler'
            ])
            ->select('product_id', 'product_type')
            ->selectRaw('SUM(quantity) as total_sold, SUM(total_price) as earnings')
            ->groupBy('product_id', 'product_type')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get()
            ->map(function ($cartItem) {
                // Get the component based on product_type
                $component = match($cartItem->product_type) {
                    'case' => $cartItem->case,
                    'cpu' => $cartItem->cpu,
                    'gpu' => $cartItem->gpu,
                    'motherboard' => $cartItem->motherboard,
                    'ram' => $cartItem->ram,
                    'storage' => $cartItem->storage,
                    'psu' => $cartItem->psu,
                    'cooler' => $cartItem->cooler,
                    default => null
                };
                
                $productName = $component 
                    ? $component->brand . ' ' . $component->model 
                    : ucfirst($cartItem->product_type);
                
                return (object) [
                    'name' => $productName,
                    'type' => $cartItem->product_type,
                    'total_sold' => $cartItem->total_sold,
                    'earnings' => $cartItem->earnings
                ];
            });
        // ---------------------
        // Product Orders by component TYPE (new logic)
        // - We group order_items by name (product display name)
        // - For each distinct product name we try to detect which hardware model/table it belongs to
        //   by checking hardware model columns (brand+model / model) across classes in config('components').
        // - Then we aggregate counts per detected component type.
        // ---------------------
        $componentMap = Config::get('components', []); // e.g. ['cpu' => Cpu::class, ...]
        $productNames = CartItem::select('product_id')
            ->selectRaw('SUM(quantity) as total_qty')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(100) // safety
            ->get();

        $typeCounts = [];

        foreach ($productNames as $pn) {
            $name = trim($pn->name);
            $qty  = (int) $pn->total_qty;
            $detectedType = null;

            // Normalize search value
            $search = mb_strtolower($name);

            // Try multiple matching strategies for robustness.
            // For each component class, attempt to find a record where model or brand+model matches.
            foreach ($componentMap as $typeKey => $className) {
                // ensure class exists
                if (!is_string($className) || !class_exists($className)) {
                    continue;
                }

                try {
                    // exact match model
                    $q1 = $className::whereRaw('LOWER(model) = ?', [$search])->exists();
                    if ($q1) {
                        $detectedType = $typeKey;
                        break;
                    }

                    // exact match brand + model (e.g. "ASUS ROG Strix")
                    $q2 = $className::whereRaw('LOWER(CONCAT_WS(" ", brand, model)) = ?', [$search])->exists();
                    if ($q2) {
                        $detectedType = $typeKey;
                        break;
                    }

                    // model appears inside order item name or vice versa (partial match)
                    // find any model that is contained in the product name
                    $q3 = $className::whereRaw('LOWER(?) LIKE CONCAT("%", LOWER(model), "%")', [$search])->exists();
                    if ($q3) {
                        $detectedType = $typeKey;
                        break;
                    }

                    // the model contains the product name (reverse)
                    $q4 = $className::whereRaw('LOWER(model) LIKE CONCAT("%", LOWER(?), "%")', [$search])->exists();
                    if ($q4) {
                        $detectedType = $typeKey;
                        break;
                    }
                } catch (Exception $e) {
                    // skip any errors for a class (e.g. missing column), continue trying others
                    continue;
                }
            }

            $typeLabel = $detectedType ? ucfirst($detectedType) : 'Unknown';

            if (!isset($typeCounts[$typeLabel])) {
                $typeCounts[$typeLabel] = 0;
            }
            $typeCounts[$typeLabel] += $qty;
        }

        // Convert to collection sorted by count desc
        // Product Orders - Fixed version
        $productOrders = CartItem::select('product_type')
            ->selectRaw('SUM(quantity) as total_orders')
            ->groupBy('product_type')
            ->orderByDesc('total_orders')
            ->get()
            ->map(function ($item) {
                return [
                    'type' => ucfirst($item->product_type),
                    'orders' => (int)$item->total_orders
                ];
            });

        // ---------------------
        // Frequent pairs (unchanged)
        // ---------------------
        $frequentPairs = DB::table('order_items as a')
            ->join('order_items as b', function ($join) {
                $join->on('a.order_id', '=', 'b.order_id')
                     ->whereColumn('a.id', '<', 'b.id');
            })
            ->select(
                'a.name as product_a',
                'b.name as product_b',
                DB::raw('COUNT(*) as times_bought_together'),
                DB::raw('SUM(a.subtotal + b.subtotal) as total_price')
            )
            ->groupBy('a.name', 'b.name')
            ->orderByDesc('times_bought_together')
            ->take(5)
            ->get();

        // ---------------------
        // Cart Analysis (keep original approach; compute orders by product_id)
        // ---------------------
        // Cart Analysis
        $modelMap = config('components', []);
        $cartAggregates = DB::table('cart_items')
            ->select('product_type', 'product_id', DB::raw('SUM(quantity) as added_to_cart'))
            ->groupBy('product_type', 'product_id')
            ->orderByDesc('added_to_cart')
            ->limit(10)
            ->get();

        $cartAnalysis = collect();

        foreach ($cartAggregates as $row) {
            $displayName = "{$row->product_type} #{$row->product_id}";
            $modelClass = $modelMap[$row->product_type]
                    ?? ($modelMap[Str::plural($row->product_type)] ?? null)
                    ?? ($modelMap[Str::singular($row->product_type)] ?? null);

        if ($modelClass && is_string($modelClass) && class_exists($modelClass)) {
                try {
                    $product = $modelClass::find($row->product_id);
                    if ($product) {
                        $brand = $product->brand ?? null;
                        $model = $product->model ?? null;
                        $title = $product->name ?? $product->title ?? null;

                        $candidate = trim(($brand ? $brand . ' ' : '') . ($model ?? ''));
                        $displayName = $candidate ?: ($title ?: $displayName);
                    }
                } catch (Exception $e) {
                    // ignore error
                }			
            }

            $ordersCountForProduct = (int) CartItem::whereRaw('LOWER(product_id) LIKE ?', ['%' . strtolower($displayName) . '%'])
                ->sum('quantity');

            $cartAnalysis->push([
                'type' => $row->product_type,
                'product' => $displayName,
                'added_to_cart' => (int) $row->added_to_cart,
                'orders' => $ordersCountForProduct,
            ]);
        }

                // return view
                return view('admin.sales', [
                    'period' => $period,
                    'ordersCount' => $ordersCount,
                    'totalRevenue' => $totalRevenue,
                    'costOfGoods' => $costOfGoods,
                    'profit' => $profit,
                    'topProducts' => $topProducts,
                    'productOrders' => $productOrders,   // collection of ['type','orders']
                    'cartAnalysis' => $cartAnalysis,
                    'frequentPairs' => $frequentPairs,
                    'activeUsers' => $activeUsers,
                ]);
            }
}