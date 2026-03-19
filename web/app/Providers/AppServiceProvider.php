<?php

namespace App\Providers;

use App\DAO\CategoryDAO;
use App\DAO\Interfaces\CategoryDAOInterface;
use App\DAO\Interfaces\OrderDAOInterface;
use App\DAO\Interfaces\ProductDAOInterface;
use App\DAO\OrderDAO;
use App\DAO\ProductDAO;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CategoryDAOInterface::class, CategoryDAO::class);
        $this->app->bind(ProductDAOInterface::class, ProductDAO::class);
        $this->app->bind(OrderDAOInterface::class, OrderDAO::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
