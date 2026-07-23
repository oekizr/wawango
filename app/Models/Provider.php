<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'divisi',
        'lantai',
        'no_hp',
        'foto_profil',
        'qris_image',
        'nama_bank',
        'no_rekening',
        'nama_pemilik_rekening',
        'is_active',
        'manual_close_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'manual_close_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ProviderSchedule::class);
    }

    public function location(): HasOne
    {
        return $this->hasOne(ProviderLocation::class);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isOpenNow(): bool
    {
        if ($this->isManuallyClosedToday()) {
            return false;
        }

        return $this->isWithinScheduleNow();
    }

    public function isManuallyClosedToday(): bool
    {
        return $this->manual_close_date !== null && $this->manual_close_date->isToday();
    }

    public function isWithinScheduleNow(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();

        return $this->schedules()
            ->where('day_of_week', $now->dayOfWeek)
            ->where('is_active', true)
            ->where('open_time', '<=', $now->format('H:i:s'))
            ->where('close_time', '>=', $now->format('H:i:s'))
            ->exists();
    }
}
