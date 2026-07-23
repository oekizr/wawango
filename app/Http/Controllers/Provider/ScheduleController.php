<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Http\Requests\Provider\UpdateScheduleRequest;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class ScheduleController extends Controller
{
    public function __construct(private readonly ScheduleService $scheduleService) {}

    public function edit(): Response
    {
        $provider = auth()->user()->provider->load('schedules');

        $days = collect(range(0, 6))->map(function (int $day) use ($provider) {
            $schedule = $provider->schedules->firstWhere('day_of_week', $day);

            return [
                'day_of_week' => $day,
                'is_active' => (bool) ($schedule?->is_active ?? false),
                'open_time' => $schedule ? substr($schedule->open_time, 0, 5) : '08:00',
                'close_time' => $schedule ? substr($schedule->close_time, 0, 5) : '09:00',
            ];
        });

        return Inertia::render('Provider/Schedule/Edit', [
            'schedules' => $days,
        ]);
    }

    public function update(UpdateScheduleRequest $request): RedirectResponse
    {
        $this->scheduleService->sync(auth()->user()->provider, $request->validated('schedules'));

        return redirect()->route('provider.schedule.edit')->with('success', 'Jadwal layanan berhasil diperbarui.');
    }
}
