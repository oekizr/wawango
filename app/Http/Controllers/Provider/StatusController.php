<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StatusController extends Controller
{
    /**
     * Toggle the provider's manual close-early override for today.
     * Providers cannot force themselves "open" outside their own schedule.
     */
    public function toggle(): RedirectResponse
    {
        $provider = auth()->user()->provider;

        if ($provider->isManuallyClosedToday()) {
            $provider->update(['manual_close_date' => null]);

            return back()->with('success', 'Layanan dibuka kembali.');
        }

        if (! $provider->isWithinScheduleNow()) {
            return back()->withErrors(['status' => 'Tidak bisa membuka layanan di luar jadwal.']);
        }

        $provider->update(['manual_close_date' => today()]);

        return back()->with('success', 'Layanan ditutup untuk hari ini.');
    }
}
