<?php

namespace App\Enums;

enum RoleName: string
{
    case Admin = 'admin';
    case PenyediaJasa = 'penyedia_jasa';
    case Pemesan = 'pemesan';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::PenyediaJasa => 'Penyedia Jasa',
            self::Pemesan => 'Pemesan',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::PenyediaJasa => 'provider.dashboard',
            self::Pemesan => 'pemesan.dashboard',
        };
    }
}
