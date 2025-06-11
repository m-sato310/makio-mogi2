<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                if ($request->input('is_admin_logout')) {
                    return redirect('/admin/login');
                }
                return redirect('/login');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::registerView(function () {
            return view('auth.register');
        });

        Fortify::loginView(function (Request $request) {
            if ($request->is('admin/login')) {
                $pageTitle = '管理者ログイン';
                $heading = '管理者ログイン';
                $loginRoute = route('admin.login.post');
                $buttonLabel = '管理者ログインする';
                $hideRegisterLink = true;

                return view('auth.login', compact(
                    'pageTitle',
                    'heading',
                    'loginRoute',
                    'buttonLabel',
                    'hideRegisterLink'
                ));
            }

            $pageTitle = 'ログイン';
            $heading = 'ログイン';
            $loginRoute = route('login.post');
            $buttonLabel = 'ログインする';

            return view('auth.login', compact(
                'pageTitle',
                'heading',
                'loginRoute',
                'buttonLabel'
            ));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(10)->by($throttleKey);
        });
    }
}
