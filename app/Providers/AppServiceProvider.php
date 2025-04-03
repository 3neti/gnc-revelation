<?php

namespace App\Providers;

use App\Classes\Buyer;
use App\Services\AgeService;
use App\Services\BorrowingRulesService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
//        app()->bind(Buyer::class, function () {
//            return new Buyer(new BorrowingRulesService(new AgeService()));
//        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
//        Carbon::macro('floatDiffInYears', function () {
//            return $this->diffInDays(now()) / 365.25;
//        });
    }
}
