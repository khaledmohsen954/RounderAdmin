<?php
namespace App\Modules\Access\Enums;
/** أقسام الداشبورد؛ لا تُقبل مسارات أو أكواد صلاحية عشوائية من العميل. */
enum DashboardSection: string
{
    case Dashboard = 'dashboard';
    case About = 'about';
    case Users = 'users';
    case Providers = 'providers';
    case Realestate = 'realestate';
    case Regions = 'regions';
    case Cities = 'cities';
    case Services = 'services';
    case SubServices = 'sub_services';   // جديد
    case ServiceCategories = 'service_categories'; // جديد
    case Prices = 'prices';

    case Packages = 'packages';
    case Features = 'features';
    case Subscriptions = 'subscriptions';
    case Content = 'content';
    case Orders = 'orders';
    case Emergencies = 'emergencies';
    case Permissions = 'permissions';
    case Pages = 'pages';
    /** @return string */
    public function label(): string
    {
        return match ($this) {
            self::Dashboard => __('Dashboard'),
            self::About => __('About Rounder'),
            self::Users => __('Users'),
            self::Providers => __('Providers and contracts'),
            self::Realestate => __('Homes'),
            self::Regions => __('Regions'),
            self::Cities => __('Cities'),
            self::Services => __('Available services'),
            self::SubServices => __('Sub services'),   // جديد
            self::ServiceCategories => __('Service categories'), // جديد
            self::Prices => __('Prices'),
            self::Packages => __('Customer packages'),
            self::Features => __('Package comparison features'),
            self::Subscriptions => __('Customer subscriptions'),
            self::Content => __('Resident operations'),
            self::Orders => __('Service orders'),
            self::Emergencies => __('Emergency requests'),
            self::Permissions => __('Permissions'),
            self::Pages => __('Pages and policies'),
        };
    }
}
