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
    case Catalog = 'catalog';
    case Content = 'content';
    case Orders = 'orders';
    case Emergencies = 'emergencies';
    case Permissions = 'permissions';
    /** @return string */
    public function label(): string
    {
        return match ($this) {
            self::Dashboard => __('Dashboard'),
            self::About => __('About Rounder'),
            self::Users => __('Users'),
            self::Providers => __('Providers and contracts'),
            self::Realestate => __('Homes'),
            self::Catalog => __('Packages and services'),
            self::Content => __('Resident operations'),
            self::Orders => __('Service orders'),
            self::Emergencies => __('Emergency requests'),
            self::Permissions => __('Permissions'),
        };
    }
}
