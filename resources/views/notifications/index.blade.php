@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    @include('layouts._nav')

    <main class="max-w-6xl mx-auto p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Riwayat Notifikasi</h2>
            <div class="flex flex-wrap items-center gap-4">
                @if ($unreadCount > 0)
                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-sm">{{ $unreadCount }} belum dibaca</span>
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="text-sm text-blue-600 hover:underline">Tandai Semua Dibaca</button>
                    </form>
                @endif
                <span class="text-sm text-gray-500">{{ $notifications->total() }} notifikasi</span>
            </div>
        </div>

        @if ($notifications->isEmpty())
            <div class="bg-white rounded-lg shadow p-6 text-center text-gray-500">
                <p>Belum ada notifikasi. Buat task dengan deadline lalu jalankan scheduler untuk melihat reminder.</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs uppercase text-gray-500">
                            <th class="px-4 sm:px-6 py-3">Task</th>
                            <th class="px-4 sm:px-6 py-3">Jenis</th>
                            <th class="px-4 sm:px-6 py-3">Channel</th>
                            <th class="px-4 sm:px-6 py-3">Dikirim</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($notifications as $notif)
                            <tr class="{{ $notif->read_at ? '' : 'bg-blue-50/50' }}">
                                <td class="px-4 sm:px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @unless ($notif->read_at)
                                            <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                                        @endunless
                                        @if ($notif->task)
                                            <a href="{{ route('tasks.show', $notif->task) }}" class="text-gray-800 hover:text-blue-600">{{ $notif->task->title }}</a>
                                        @else
                                            <span class="text-gray-400">Task dihapus</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 sm:px-6 py-4">
                                    <span class="capitalize">{{ $notif->type }}</span>
                                </td>
                                <td class="px-4 sm:px-6 py-4">
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">
                                        {{ $notif->channel }}
                                    </span>
                                </td>
                                <td class="px-4 sm:px-6 py-4 text-sm text-gray-600">
                                    @if ($notif->sent_at)
                                        <time datetime="{{ $notif->sent_at->utc()->toIso8601String() }}"></time>
                                    @else
                                        -
                                    @endif
                                    @unless ($notif->read_at)
                                        <form method="POST" action="{{ route('notifications.read', $notif) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="ml-2 text-xs text-blue-600 hover:underline whitespace-nowrap">Tandai dibaca</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                {{ $notifications->links() }}
            </div>
        @endif
    </main>
</div>
@endsection