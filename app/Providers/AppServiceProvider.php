<?php

namespace App\Providers;

use App\Models\Creator;
use App\Models\Order;
use App\Models\Outlet;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        \Illuminate\Support\Facades\View::composer(['layouts.app', 'about'], function (View $view): void {
            $view->with('creators', Creator::orderBy('position')->get());
        });
        \Illuminate\Support\Facades\View::composer(['home', 'about'], function (View $view): void {
            $view->with('impactStats', [
                'portions' => Order::where('status', 'completed')->sum('quantity'),
                'orders' => Order::where('status', 'completed')->count(),
                'outlets' => Outlet::where('is_active', true)->count(),
            ]);
        });
        Model::preventLazyLoading(! app()->isProduction());
        Paginator::defaultView('components.pagination');
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by(Str::lower($request->string('email')).'|'.$request->ip()));
    }
}
