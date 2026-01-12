<?php

namespace App\Filament\Widgets;

use App\Models\Meal;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Users', User::count())
                ->description('Registered users')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('Total Meals', Meal::count())
                ->description('Available meals')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('info'),
            Stat::make('Total Orders', Order::count())
                ->description('All time orders')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
            Stat::make('Total Reviews', Review::count())
                ->description('Customer reviews')
                ->descriptionIcon('heroicon-m-star')
                ->color('primary'),
            Stat::make('Pending Orders', Order::where('status', 'pending')->count())
                ->description('Orders awaiting confirmation')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
            Stat::make('Active Users', User::where('is_active', true)->count())
                ->description('Active user accounts')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}
