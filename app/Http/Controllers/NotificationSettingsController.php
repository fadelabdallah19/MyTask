<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationSettingsController extends Controller
{
    public function edit(): View
    {
        return view('settings.edit', ['user' => request()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email_enabled' => 'sometimes|boolean',
            'reminder_24h' => 'sometimes|boolean',
            'reminder_1h' => 'sometimes|boolean',
        ]);

        $user = app(User::class)->findOrFail($request->user()->id);

        $user->update([
            'email_enabled' => $request->boolean('email_enabled', $user->email_enabled),
            'reminder_24h' => $request->boolean('reminder_24h', $user->reminder_24h),
            'reminder_1h' => $request->boolean('reminder_1h', $user->reminder_1h),
        ]);

        ActivityLog::record($user, 'settings.updated');

        return back()->with('status', 'Pengaturan notifikasi disimpan.');
    }
}
