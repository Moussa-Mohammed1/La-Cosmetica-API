<?php

namespace App\DAO\Interfaces;

use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface AnalyticsDAOInterface
{
    public function getSalesSummary(
        CarbonInterface $dateFrom,
        CarbonInterface $dateTo,
        string $status
    ): array;

    public function getTopProducts(
        CarbonInterface $dateFrom,
        CarbonInterface $dateTo,
        string $status,
        int $limit
    ): Collection;

    public function getCategoryDistribution(
        CarbonInterface $dateFrom,
        CarbonInterface $dateTo,
        string $status
    ): Collection;
}