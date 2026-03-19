<?php

namespace App\DAO;

use App\DAO\Interfaces\AnalyticsDAOInterface;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AnalyticsDAO implements AnalyticsDAOInterface
{
    public function getSalesSummary(
        CarbonInterface $dateFrom,
        CarbonInterface $dateTo,
        string $status
    ): array {
        $summary = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', $status)
            ->whereBetween('orders.created_at', [
                $dateFrom->toDateTimeString(),
                $dateTo->toDateTimeString(),
            ])
            ->selectRaw('COALESCE(SUM(order_items.quantity * COALESCE(order_items.ItemPrice, 0)), 0) as total_revenue')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_items')
            ->selectRaw('COUNT(DISTINCT orders.id) as total_orders')
            ->first();

        $totalRevenue = (float) ($summary?->total_revenue ?? 0);
        $totalOrders = (int) ($summary?->total_orders ?? 0);

        return [
            'total_revenue' => round($totalRevenue, 2),
            'total_items' => (int) ($summary?->total_items ?? 0),
            'total_orders' => $totalOrders,
            'average_order_value' => $totalOrders > 0
                ? round($totalRevenue / $totalOrders, 2)
                : 0.0,
        ];
    }

    public function getTopProducts(
        CarbonInterface $dateFrom,
        CarbonInterface $dateTo,
        string $status,
        int $limit
    ): Collection {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', $status)
            ->whereBetween('orders.created_at', [
                $dateFrom->toDateTimeString(),
                $dateTo->toDateTimeString(),
            ])
            ->groupBy(
                'products.id',
                'products.name',
                'products.slug',
                'categories.id',
                'categories.name'
            )
            ->select([
                'products.id as product_id',
                'products.name as product_name',
                'products.slug as product_slug',
                'categories.id as category_id',
                'categories.name as category_name',
            ])
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as sold_quantity')
            ->selectRaw('COALESCE(SUM(order_items.quantity * COALESCE(order_items.ItemPrice, 0)), 0) as revenue')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get()
            ->map(function (object $row): array {
                return [
                    'product_id' => (int) $row->product_id,
                    'product_name' => $row->product_name,
                    'product_slug' => $row->product_slug,
                    'category_id' => $row->category_id ? (int) $row->category_id : null,
                    'category_name' => $row->category_name,
                    'sold_quantity' => (int) $row->sold_quantity,
                    'revenue' => round((float) $row->revenue, 2),
                ];
            })
            ->values();
    }

    public function getCategoryDistribution(
        CarbonInterface $dateFrom,
        CarbonInterface $dateTo,
        string $status
    ): Collection {
        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('orders.status', $status)
            ->whereBetween('orders.created_at', [
                $dateFrom->toDateTimeString(),
                $dateTo->toDateTimeString(),
            ])
            ->groupBy('categories.id', 'categories.name')
            ->select([
                'categories.id as category_id',
                'categories.name as category_name',
            ])
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as sold_quantity')
            ->selectRaw('COALESCE(SUM(order_items.quantity * COALESCE(order_items.ItemPrice, 0)), 0) as revenue')
            ->orderByDesc('revenue')
            ->get();

        $totalRevenue = (float) $rows->sum(function (object $row): float {
            return (float) $row->revenue;
        });

        $totalItems = (int) $rows->sum(function (object $row): int {
            return (int) $row->sold_quantity;
        });

        return $rows
            ->map(function (object $row) use ($totalRevenue, $totalItems): array {
                $rowRevenue = (float) $row->revenue;
                $rowItems = (int) $row->sold_quantity;

                return [
                    'category_id' => $row->category_id ? (int) $row->category_id : null,
                    'category_name' => $row->category_name,
                    'sold_quantity' => $rowItems,
                    'revenue' => round($rowRevenue, 2),
                    'revenue_share_percent' => $totalRevenue > 0
                        ? round(($rowRevenue / $totalRevenue) * 100, 2)
                        : 0.0,
                    'item_share_percent' => $totalItems > 0
                        ? round(($rowItems / $totalItems) * 100, 2)
                        : 0.0,
                ];
            })
            ->values();
    }
}