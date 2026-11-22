<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

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
        // 一般的な処理（1分間に60回）
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)
                ->by($request->user()?->id ?? $request->ip());
        });

        // ログイン処理（1分間に5回）
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)
                ->by($request->ip());
        });

        // ユーザー登録（1分間に3回）
        RateLimiter::for('account-register', function (Request $request) {
            return Limit::perMinute(3)
                ->by($request->ip());
        });

        // シミュレーション処理（1分間に10回）
        RateLimiter::for('simulate', function (Request $request) {
            return Limit::perMinute(10)
                ->by($request->ip());
        });
    }
}
