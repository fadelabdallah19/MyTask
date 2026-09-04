@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    @include('layouts._nav')

    <main class="max-w-6xl mx-auto p-4 sm:p-6">
        <div class="flex items-center justify-between gap-3 mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
            <a href="{{ route('tasks.create') }}" class="inline-flex shrink-0 items-center gap-1.5 bg-blue-600 text-white px-3 py-2.5 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors shadow-sm">
                <span aria-hidden="true">+</span> Buat Task
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-500">Total Tasks</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ $totalTasks }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-500">To Do</p>
                <p class="text-3xl font-bold text-yellow-500 mt-1">{{ $todoTasks }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-500">In Progress</p>
                <p class="text-3xl font-bold text-blue-500 mt-1">{{ $inProgressTasks }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-500">Completed</p>
                <p class="text-3xl font-bold text-green-500 mt-1">{{ $completedTasks }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-500">Overdue</p>
                <p class="text-3xl font-bold text-red-500 mt-1">{{ $overdueTasks }}</p>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <p class="text-sm text-gray-500">Completion</p>
                <div class="flex items-center mt-1">
                    <p class="text-3xl font-bold text-gray-800">{{ $completionPercentage }}%</p>
                    <div class="flex-1 min-w-0 ml-4 h-2 bg-gray-200 rounded-full overflow-hidden">
                        <div class="h-full bg-green-500 transition-all" style="width: {{ $completionPercentage }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Upcoming Deadlines</h3>
                @if ($upcomingTasks->isEmpty())
                    <p class="text-gray-500 text-sm">Tidak ada task dalam 3 hari ke depan.</p>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach ($upcomingTasks as $task)
                            <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                                <a href="{{ route('tasks.show', $task) }}" class="text-gray-800 hover:text-blue-600 truncate">{{ $task->title }}</a>
                                <span class="text-sm text-gray-500 shrink-0"><time datetime="{{ $task->deadline->utc()->toIso8601String() }}">{{ $task->deadline->format('d M Y, H:i') }}</time></span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Recent Tasks</h3>
                @if ($recentTasks->isEmpty())
                    <p class="text-gray-500 text-sm">Belum ada task.</p>
                @else
                    <ul class="divide-y divide-gray-200">
                        @foreach ($recentTasks as $task)
                            <li class="py-3 flex items-center justify-between gap-3">
                                <a href="{{ route('tasks.show', $task) }}" class="text-gray-800 hover:text-blue-600 truncate">{{ $task->title }}</a>
                                <span class="text-sm text-gray-500 capitalize shrink-0">{{ str_replace('_', ' ', $task->status) }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-6 mt-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Recent Notifications</h3>
                <a href="{{ route('notifications.index') }}" class="text-sm text-blue-600 hover:underline">
                    Lihat semua
                    @if ($unreadNotifications > 0)
                        <span class="ml-1 bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs">{{ $unreadNotifications }} baru</span>
                    @endif
                </a>
            </div>
            @if ($recentNotifications->isEmpty())
                <p class="text-gray-500 text-sm">Belum ada notifikasi. Jadwalkan reminder untuk melihatnya.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach ($recentNotifications as $notif)
                        <li class="py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 {{ $notif->read_at ? '' : 'bg-blue-50/40' }}">
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
                            <div class="flex flex-wrap items-center gap-3 pl-4 sm:pl-0">
                                <span class="text-sm text-gray-500 capitalize">{{ $notif->type }}</span>
                                <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700">{{ $notif->channel }}</span>
                                <span class="text-sm text-gray-400">{{ $notif->sent_at?->diffForHumans() }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </main>
</div>
@endsection