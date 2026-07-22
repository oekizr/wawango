<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository
{
    /**
     * @param  array{search?: ?string, status?: ?string, divisi?: ?string, provider_id?: ?string, date_from?: ?string, date_to?: ?string}  $filters
     */
    public function paginateForAdmin(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Order::query()
            ->with(['user', 'store', 'provider.user'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('kode_order', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['divisi'] ?? null, fn ($query, $divisi) => $query->where('divisi_snapshot', $divisi))
            ->when($filters['provider_id'] ?? null, fn ($query, $providerId) => $query->where('provider_id', $providerId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('ordered_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('ordered_at', '<=', $date))
            ->latest('ordered_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Distinct divisi values found on orders, for filter dropdown options.
     *
     * @return array<int, string>
     */
    public function distinctDivisions(): array
    {
        return Order::query()
            ->whereNotNull('divisi_snapshot')
            ->distinct()
            ->orderBy('divisi_snapshot')
            ->pluck('divisi_snapshot')
            ->all();
    }
}
