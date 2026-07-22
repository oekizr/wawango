<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $role = $request->user()->getRoleNames()->first();
        $role = $role ? \App\Enums\RoleName::from($role) : null;

        return redirect()->route($role?->dashboardRoute() ?? 'profile.edit');
    }
}
