<?php

namespace App\Enums;

enum OrderStatus: string
{
    case Menunggu = 'menunggu';
    case Diproses = 'diproses';
    case Dibelikan = 'dibelikan';
    case Diantar = 'diantar';
    case Selesai = 'selesai';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu',
            self::Diproses => 'Diproses',
            self::Dibelikan => 'Dibelikan',
            self::Diantar => 'Dalam Pengantaran',
            self::Selesai => 'Selesai',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Selesai || $this === self::Dibatalkan;
    }

    /**
     * The next status in the normal forward sequence, or null if already terminal.
     * Used by the provider's "advance one step" action (admin may jump freely instead).
     */
    public function next(): ?self
    {
        return match ($this) {
            self::Menunggu => self::Diproses,
            self::Diproses => self::Dibelikan,
            self::Dibelikan => self::Diantar,
            self::Diantar => self::Selesai,
            self::Selesai, self::Dibatalkan => null,
        };
    }

    /**
     * @return array<int, self>
     */
    public static function activeStatuses(): array
    {
        return [self::Menunggu, self::Diproses, self::Dibelikan, self::Diantar];
    }
}
