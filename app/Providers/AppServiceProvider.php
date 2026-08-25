<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Interfaces\AuthServiceInterface;
use App\Services\AuthService;
use App\Services\Interfaces\UserServiceInterface;
use App\Services\UserService;
use App\Services\Interfaces\ImportServiceInterface;
use App\Services\ImportService;
use App\Services\Interfaces\DashboardServiceInterface;
use App\Services\DashboardService;
use App\Services\Interfaces\FileServiceInterface;
use App\Services\FileService;
use App\Services\Interfaces\LevelServiceInterface;
use App\Services\LevelService;
use App\Services\Interfaces\ProdiServiceInterface;
use App\Services\ProdiService;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\EloquentUserRepository;
use App\Repositories\Interfaces\JadwalRepositoryInterface;
use App\Repositories\EloquentJadwalRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Service Layer Bindings
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(ImportServiceInterface::class, ImportService::class);
        $this->app->bind(DashboardServiceInterface::class, DashboardService::class);
        $this->app->bind(FileServiceInterface::class, FileService::class);
        $this->app->bind(LevelServiceInterface::class, LevelService::class);
        $this->app->bind(ProdiServiceInterface::class, ProdiService::class);

        // Repository Layer Bindings
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);
        $this->app->bind(JadwalRepositoryInterface::class, EloquentJadwalRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
