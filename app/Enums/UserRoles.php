<?php

namespace App\Enums;

enum UserRoles: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case CASHIER = 'cashier';
    case SUPPLIER = 'supplier';
    case CUSTOMER = 'customer';

    public function label(): string
    {
        return match($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN => 'Admin',
            self::CASHIER => 'Cashier',
            self::SUPPLIER => 'Supplier',
            self::CUSTOMER => 'Customer',
        };
    }

    public static function labels():array
    {
        $labels = [];

        foreach(self::cases() as $role) {
            $labels[$role->value] = $role->label();
        }

        return $labels;
    }

    public static function adminLabels(): array
    {
        return [
            self::CUSTOMER->value => self::CUSTOMER->label(),
            self::ADMIN->value => self::ADMIN->label(),
        ];
    }

    public static function superAdminLabels(): array
    {
        return [
            self::SUPER_ADMIN->value => self::SUPER_ADMIN->label(),
            self::ADMIN->value => self::ADMIN->label(),
            self::CUSTOMER->value => self::CUSTOMER->label(),
        ];
    }
}
