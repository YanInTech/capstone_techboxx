<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Checkout;
use App\Models\OrderedBuild;
use App\Models\UserBuild;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $filterType = $request->get('filter_type');
        
        // Use the reusable method
        $data = $this->getSalesData($period, $filterType);
        
        // Paginate the filtered sales data
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 9;
        $pagedData = new LengthAwarePaginator(
            $data['groupedSalesWithDetails']->forPage($currentPage, $perPage),
            $data['groupedSalesWithDetails']->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.sales', [
            'period' => $period,
            'groupedSalesWithDetails' => $pagedData,
            'summary' => $data['summary'],
            'filterType' => $filterType,
            'salesLabels' => $data['salesLabels'],
            'salesTotals' => $data['salesTotals'],
            'xAxisLabel' => $data['xAxisLabel'],
        ]);
    }

    private function getSalesData($period, $filterType = null)
    {
        // Set date range based on period
        $now = Carbon::now('Asia/Manila');
        switch ($period) {
            case 'daily':
                $startDate = $now->copy()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
            case 'weekly':
                $startDate = $now->copy()->startOfWeek(Carbon::MONDAY);
                $endDate = $now->copy()->endOfWeek(Carbon::SUNDAY);
                break;
            case 'annually':
                $startDate = $now->copy()->startOfYear();
                $endDate = $now->copy()->endOfYear();
                break;
            default: // monthly
                $startDate = $now->copy()->startOfMonth();
                $endDate = $now->copy()->endOfMonth();
                break;
        }

        // Get sales data from checkouts and ordered builds
        $componentSales = collect();

        // 1. From CHECKOUTS (cart_items)
        $paidCartItems = Checkout::where('payment_status', 'paid')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->pluck('cart_item_id');

        foreach ($paidCartItems as $cartItemId) {
            $cartItem = CartItem::find($cartItemId);
            if ($cartItem) {
                $componentSales->push([
                    'product_type' => $cartItem->product_type,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                ]);
            }
        }

        // 2. From ORDERED BUILDS (user_builds)
        $orderedBuilds = OrderedBuild::where('payment_status', 'paid')
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->pluck('user_build_id');

        foreach ($orderedBuilds as $userBuildId) {
            $userBuild = UserBuild::find($userBuildId);
            if ($userBuild) {
                $components = [
                    'case' => $userBuild->pc_case_id,
                    'motherboard' => $userBuild->motherboard_id,
                    'cpu' => $userBuild->cpu_id,
                    'gpu' => $userBuild->gpu_id,
                    'storage' => $userBuild->storage_id,
                    'ram' => $userBuild->ram_id,
                    'psu' => $userBuild->psu_id,
                    'cooler' => $userBuild->cooler_id,
                ];

                foreach ($components as $type => $id) {
                    if ($id) {
                        $componentSales->push([
                            'product_type' => $type,
                            'product_id' => $id,
                            'quantity' => 1,
                        ]);
                    }
                }
            }
        }

        // 3. Combine and group all results
        $groupedSales = $componentSales
            ->groupBy(fn($item) => $item['product_type'] . '-' . $item['product_id'])
            ->map(fn($items) => [
                'product_type' => $items->first()['product_type'],
                'product_id' => $items->first()['product_id'],
                'total_sold' => $items->sum('quantity'),
            ])
            ->sortByDesc('total_sold')
            ->values();

        // 4. Enrich grouped results with component details
        $groupedSalesWithDetails = $groupedSales->map(function ($item) {
            $model = match ($item['product_type']) {
                'case' => \App\Models\Hardware\PcCase::class,
                'motherboard' => \App\Models\Hardware\Motherboard::class,
                'cpu' => \App\Models\Hardware\Cpu::class,
                'gpu' => \App\Models\Hardware\Gpu::class,
                'storage' => \App\Models\Hardware\Storage::class,
                'ram' => \App\Models\Hardware\Ram::class,
                'psu' => \App\Models\Hardware\Psu::class,
                'cooler' => \App\Models\Hardware\Cooler::class,
                default => null,
            };

            $component = $model ? $model::find($item['product_id']) : null;

            return [
                'product_type' => ucfirst($item['product_type']),
                'product_id' => $item['product_id'],
                'total_sold' => $item['total_sold'],
                'product_name' => $component ? ($component->brand . ' ' . $component->model) : 'Unknown',
                'base_price' => $component->base_price ?? 0,
                'selling_price' => $component->price ?? 0,
            ];
        });

        // 5. Compute summary
        $totalSold = $groupedSalesWithDetails->sum('total_sold');
        $totalCostOfGoods = $groupedSalesWithDetails->sum(function ($item) {
            return $item['base_price'] * $item['total_sold'];
        });
        $totalRevenue = $groupedSalesWithDetails->sum(function ($item) {
            return $item['selling_price'] * $item['total_sold'];
        });
        $totalProfit = $totalRevenue - $totalCostOfGoods;

        $summary = [
            'total_sold' => $totalSold,
            'cost_of_goods' => $totalCostOfGoods,
            'revenue' => $totalRevenue,
            'profit' => $totalProfit,
        ];

        // 6. Apply product type filter
        $filteredSales = $groupedSalesWithDetails;
        if (!empty($filterType)) {
            $filteredSales = $filteredSales->filter(function ($item) use ($filterType) {
                return strtolower($item['product_type']) === strtolower($filterType);
            });
        }
        $filteredSales = $filteredSales->sortByDesc('total_sold')->values();

        // 7. Get chart data
        $chartData = $this->getChartData($period, $startDate, $endDate);

        return [
            'period' => $period,
            'summary' => $summary,
            'groupedSalesWithDetails' => $filteredSales,
            'salesLabels' => $chartData['labels'],
            'salesTotals' => $chartData['totals'],
            'xAxisLabel' => $chartData['xAxisLabel']
        ];
    }

    private function getChartData($period, $startDate, $endDate)
    {
        // Get checkouts data
        $checkouts = Checkout::whereIn('payment_status', ['paid', 'pending'])  
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get();
        
        // Get ordered builds data
        $orderedBuilds = OrderedBuild::with('userBuild')
            ->whereIn('payment_status', ['paid', 'pending'])  
            ->whereBetween('updated_at', [$startDate, $endDate])
            ->get();
        
        // Combine all sales into a collection
        $allSales = collect();
        
        // Add checkouts
        foreach ($checkouts as $checkout) {
            $allSales->push([
                'date' => $checkout->updated_at,
                'amount' => $checkout->total_cost
            ]);
        }
        
        // Add ordered builds
        foreach ($orderedBuilds as $order) {
            $allSales->push([
                'date' => $order->updated_at,
                'amount' => $order->userBuild->total_price
            ]);
        }
        
        // Group by period
        if ($period === 'daily') {
            $grouped = $allSales->groupBy(function ($sale) {
                return $sale['date']->format('H');
            })->sortKeys();
            
            $labels = range(0, 23);
            $completeLabels = array_map(fn($h) => sprintf('%02d:00', $h), $labels);
            $xAxisLabel = 'Time of Day';
            
        } elseif ($period === 'weekly') {
            $grouped = $allSales->groupBy(function ($sale) {
                return $sale['date']->format('l'); // Full day name
            });
            
            $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            $labels = $dayOrder;
            $xAxisLabel = 'Days of Week';
            
        } elseif ($period === 'annually') {
            $grouped = $allSales->groupBy(function ($sale) {
                return $sale['date']->format('F'); // Full month name
            });
            
            $monthOrder = [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ];
            $labels = $monthOrder;
            $xAxisLabel = 'Months';
            
        } else { // monthly
            $grouped = $allSales->groupBy(function ($sale) {
                return $sale['date']->format('j'); // Day without leading zeros
            })->sortKeys();
            
            $daysInMonth = $startDate->daysInMonth;
            $labels = range(1, $daysInMonth);
            $completeLabels = array_map(fn($d) => 'Day ' . $d, $labels);
            $xAxisLabel = 'Days of Month';
        }
        
        // Sum amounts for each group
        $totals = collect($labels)->map(function ($label) use ($grouped) {
            return $grouped->has($label) ? $grouped[$label]->sum('amount') : 0;
        });
        
        return [
            'labels' => $period === 'monthly' ? $completeLabels : $labels,
            'totals' => $totals,
            'xAxisLabel' => $xAxisLabel
        ];
    }

    public function downloadSalesReport(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $filterType = $request->get('filter_type');
        
        // Use the reusable method
        $data = $this->getSalesData($period, $filterType);

        // Generate PDF
        $pdf = Pdf::loadView('admin.sales-pdf', [
            'period' => $period,
            'summary' => $data['summary'],
            'products' => $data['groupedSalesWithDetails'],
            'generatedDate' => now()->format('F j, Y g:i A')
        ]);

        // Download PDF
        return $pdf->download('sales-report-' . $period . '-' . now()->format('Y-m-d') . '.pdf');
    }
}