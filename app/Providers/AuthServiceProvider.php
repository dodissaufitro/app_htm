<?php

namespace App\Providers;

use App\Models\User;
use App\Models\DataPemohon;
use App\Models\AppVerifikator;
use App\Models\AppBank;
use App\Models\AppDeveloper;
use App\Models\AppPenetapan;
use App\Models\AppBast;
use App\Models\AppAkad;
use App\Policies\UserPolicy;
use App\Policies\DataPemohonPolicy;
use App\Policies\AppVerifikatorPolicy;
use App\Policies\AppBankPolicy;
use App\Policies\AppDeveloperPolicy;
use App\Policies\AppPenetapanPolicy;
use App\Policies\AppBastPolicy;
use App\Policies\AppAkadPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        DataPemohon::class => DataPemohonPolicy::class,
        AppVerifikator::class => AppVerifikatorPolicy::class,
        AppBank::class => AppBankPolicy::class,
        AppDeveloper::class => AppDeveloperPolicy::class,
        AppPenetapan::class => AppPenetapanPolicy::class,
        AppBast::class => AppBastPolicy::class,
        AppAkad::class => AppAkadPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
