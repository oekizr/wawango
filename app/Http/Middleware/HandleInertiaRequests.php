<?php

namespace App\Http\Middleware;

use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $role = $user?->getRoleNames()->first();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'role' => $role,
                'provider_id' => $user?->provider?->id,
            ],
            'notifications' => $user ? [
                'unread_count' => $user->unreadNotifications()->count(),
                'items' => $user->notifications()->latest()->limit(10)->get()->map(fn (DatabaseNotification $n) => [
                    'id' => $n->id,
                    'message' => $n->data['message'] ?? '',
                    'created_at' => $n->created_at,
                    'is_read' => $n->read_at !== null,
                    'url' => $this->resolveNotificationUrl($n, $role),
                ]),
            ] : null,
        ];
    }

    private function resolveNotificationUrl(DatabaseNotification $notification, ?string $role): ?string
    {
        $orderId = $notification->data['order_id'] ?? null;

        if (! $orderId) {
            return null;
        }

        return match ($role) {
            'admin' => route('admin.orders.show', $orderId),
            'penyedia_jasa' => route('provider.orders.show', $orderId),
            'pemesan' => route('pemesan.orders.show', $orderId),
            default => null,
        };
    }
}
