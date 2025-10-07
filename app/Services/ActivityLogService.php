<?php
// app/Services/ActivityLogService.php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class ActivityLogService
{
    public static function log(
        string $action,
        ?string $description = null,
        ?Model $model = null,
        ?array $oldData = null,
        ?array $newData = null
    ): ActivityLog {
        $user = Auth::user();
        
        return ActivityLog::create([
            'action' => $action,
            'description' => $description,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'old_data' => $oldData,
            'new_data' => $newData,
            'user_id' => $user ? $user->id : null,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    // Common log methods
    public static function login($user)
    {
        return self::log('user_login', "User {$user->email} logged in", $user);
    }

    public static function logout($user)
    {
        return self::log('user_logout', "User {$user->email} logged out", $user);
    }

    public static function orderUpdated($order, $oldData, $newData)
    {
        return self::log(
            'order_updated',
            "Order #{$order->id} updated",
            $order,
            $oldData,
            $newData
        );
    }

    public static function invoiceCreated($invoice)
    {
        return self::log(
            'invoice_created',
            "Invoice #{$invoice->id} created for order #{$invoice->order_id}",
            $invoice
        );
    }

    public static function pickupCompleted($order, $user)
    {
        return self::log(
            'pickup_completed',
            "Order #{$order->id} marked as picked up by {$user->name}",
            $order
        );
    }

    public static function orderApproved($order, $user, $oldStatus)
    {
        return self::log(
            'order_approved',
            "Order #{$order->id} status changed from {$oldStatus} to Approved by {$user->name}",
            $order,
            ['status' => $oldStatus],
            ['status' => 'Approved', 'approved_by' => $user->id]
        );
    }
}