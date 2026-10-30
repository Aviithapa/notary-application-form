<?php

namespace App\Providers;

use App\Client\FileUpload\FileUploaderInterface;
use App\Client\FileUpload\LocalFileUploader;
use App\Repositories\Role\RoleRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Support\ServiceProvider;

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
        //
        $this->app->bind(
            UserRepository::class
        );

        $this->app->bind(
            RoleRepository::class
        );

        $this->app->bind(FileUploaderInterface::class, LocalFileUploader::class);

        // log viewer is for admins only, in every environment
        \Opcodes\LogViewer\Facades\LogViewer::auth(fn ($request) => $request->user()
            && in_array(optional($request->user()->mainRole())->name, ['admin', 'super_admin'], true));
    }
}
