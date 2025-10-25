<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // forcefully registering sidebar icons components to render
        Blade::component('components.icons.dashboard', 'x-icons.dashboard');
        Blade::component('components.icons.user', 'x-icons.user');
        Blade::component('components.icons.order', 'x-icons.order');
        Blade::component('components.icons.component', 'x-icons.component');
        Blade::component('components.icons.bargraph', 'x-icons.bargraph');
        Blade::component('components.icons.inventory', 'x-icons.inventory');
        Blade::component('components.icons.software', 'x-icons.software');
        Blade::component('components.icons.logs', 'x-icons.logs');
        Blade::component('components.icons.build', 'x-icons.build');
        Blade::component('components.icons.checkout', 'x-icons.checkout');
        Blade::component('components.icons.purchase', 'x-icons.purchase');
        Blade::component('components.icons.supplier', 'x-icons.supplier');
        Blade::component('components.icons.manage', 'x-icons.manage');

        View::composer('*', function ($view) {
            $cartCount = 0;
            
            if(Auth::check()) {
                // If user is logged in, get from database - only unprocessed items
                $user = Auth::user();
                if ($user->shoppingCart) {
                    $cartCount = $user->shoppingCart->cartItem()
                        ->where('processed', false)
                        ->sum('quantity');
                }
            } elseif(session('cart')) {
                // If guest, get from session
                $cartCount = array_sum(array_column(session('cart'), 'quantity'));
            }
            
            $view->with('cartCount', $cartCount);
        });
    }
}
