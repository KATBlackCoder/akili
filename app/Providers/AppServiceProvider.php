<?php

namespace App\Providers;

use App\Models\UserPrivilege;
use App\Observers\UserPrivilegeObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        UserPrivilege::observe(UserPrivilegeObserver::class);
    }
}
