@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    @include('layouts._nav')

    <main class="max-w-6xl mx-auto p-4 sm:p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Pengaturan Notifikasi</h2>

        @if (session('status'))
            <div class="bg-green-50 text-green-700 border border-green-200 rounded-md px-4 py-3 mb-6">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}" class="bg-white rounded-lg shadow p-4 sm:p-6 max-w-2xl">
            @csrf
            @method('PUT')

            <div class="space-y-6">
                <div class="flex justify-between items-center gap-4">
                    <div class="min-w-0">
                        <label class="font-medium text-gray-800">Email Notification</label>
                        <p class="text-sm text-gray-500">Kirim pengingat deadline melalui email.</p>
                    </div>
                    <input type="checkbox" name="email_enabled" value="1" @checked(old('email_enabled', $user->email_enabled)) class="h-5 w-5 shrink-0">
                </div>

                <div class="flex justify-between items-center gap-4">
                    <div class="min-w-0">
                        <label class="font-medium text-gray-800">Reminder 24 Jam</label>
                        <p class="text-sm text-gray-500">Ingatkan 24 jam sebelum deadline.</p>
                    </div>
                    <input type="checkbox" name="reminder_24h" value="1" @checked(old('reminder_24h', $user->reminder_24h)) class="h-5 w-5 shrink-0">
                </div>

                <div class="flex justify-between items-center gap-4">
                    <div class="min-w-0">
                        <label class="font-medium text-gray-800">Reminder 1 Jam</label>
                        <p class="text-sm text-gray-500">Ingatkan 1 jam sebelum deadline.</p>
                    </div>
                    <input type="checkbox" name="reminder_1h" value="1" @checked(old('reminder_1h', $user->reminder_1h)) class="h-5 w-5 shrink-0">
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </main>
</div>
@endsection