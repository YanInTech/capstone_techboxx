<?php
// app/Http/Controllers/ActivityLogController.php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')
            ->latest();

        // Default to last 7 days if no date filters are applied
        if (!$request->has('date_from') && !$request->has('date_to') && !$request->has('month')) {
            $oneWeekAgo = now()->subDays(7)->format('Y-m-d');
            $today = now()->format('Y-m-d');
            
            $query->whereDate('created_at', '>=', $oneWeekAgo)
                ->whereDate('created_at', '<=', $today);
            
            // Set default values for the filter inputs
            $request->merge([
                'date_from' => $oneWeekAgo,
                'date_to' => $today
            ]);
        }

        // Filter by action
        if ($request->has('action') && $request->action) {
            $query->where('action', $request->action);
        }

        // Filter by user
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter by month
        if ($request->has('month') && $request->month) {
            $monthYear = explode('-', $request->month);
            if (count($monthYear) === 2) {
                $year = $monthYear[0];
                $month = $monthYear[1];
                
                $query->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month);
            }
        }

        $logs = $query->paginate(10);

        // Get available months for filter dropdown
        $months = $this->getAvailableMonths();
        
        // Get users for filter dropdown
        $users = User::whereHas('activityLogs')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'email']);

        return view('admin.activitylogs', compact('logs', 'months', 'users'));
    }

    public function show(ActivityLog $activityLog)
    {
        $activityLog->load('user');
        
        return view('activity-logs.show', compact('activityLog'));
    }

    /**
     * Get distinct months from activity logs for filter dropdown
     */
    private function getAvailableMonths()
    {
        $months = ActivityLog::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                $date = Carbon::create($item->year, $item->month, 1);
                $value = $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                return [$value => $date->format('F Y')];
            })
            ->toArray();

        return $months;
    }
}