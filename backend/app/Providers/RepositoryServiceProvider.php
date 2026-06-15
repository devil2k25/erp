<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Repositories will be bound here as they are created per-module
        // Example:
        // $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
    }
}
