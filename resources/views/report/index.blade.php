@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    @include('layouts._nav')

    <main class="max-w-4xl mx-auto p-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Report Statistik</h2>

        <p class="text-sm text-gray-500 mb-8">Total <span class="font-semibold">{{ $totalTasks }}</span> task.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Berdasarkan Status</h3>
                @php $maxStatus = max(array_merge($byStatus, [1])); @endphp
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="w-24 text-sm text-gray-600">To Do</span>
                        <div class="flex-1 h-6 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-gray-500" style="width: {{ ($byStatus['todo'] ?? 0) / $maxStatus * 100 }}%"></div>
                        </div>
                        <span class="w-8 text-right text-sm font-semibold">{{ $byStatus['todo'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-24 text-sm text-gray-600">In Progress</span>
                        <div class="flex-1 h-6 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-blue-500" style="width: {{ ($byStatus['in_progress'] ?? 0) / $maxStatus * 100 }}%"></div>
                        </div>
                        <span class="w-8 text-right text-sm font-semibold">{{ $byStatus['in_progress'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-24 text-sm text-gray-600">Completed</span>
                        <div class="flex-1 h-6 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-green-500" style="width: {{ ($byStatus['completed'] ?? 0) / $maxStatus * 100 }}%"></div>
                        </div>
                        <span class="w-8 text-right text-sm font-semibold">{{ $byStatus['completed'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Berdasarkan Prioritas</h3>
                @php $maxPriority = max(array_merge($byPriority, [1])); @endphp
                <div class="space-y-4">
                    <div class="flex items-center gap-3">
                        <span class="w-24 text-sm text-gray-600">Low</span>
                        <div class="flex-1 h-6 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-gray-400" style="width: {{ ($byPriority['low'] ?? 0) / $maxPriority * 100 }}%"></div>
                        </div>
                        <span class="w-8 text-right text-sm font-semibold">{{ $byPriority['low'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-24 text-sm text-gray-600">Medium</span>
                        <div class="flex-1 h-6 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-500" style="width: {{ ($byPriority['medium'] ?? 0) / $maxPriority * 100 }}%"></div>
                        </div>
                        <span class="w-8 text-right text-sm font-semibold">{{ $byPriority['medium'] ?? 0 }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="w-24 text-sm text-gray-600">High</span>
                        <div class="flex-1 h-6 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-red-500" style="width: {{ ($byPriority['high'] ?? 0) / $maxPriority * 100 }}%"></div>
                        </div>
                        <span class="w-8 text-right text-sm font-semibold">{{ $byPriority['high'] ?? 0 }}</span>
                    </div>
                </div>
            </div>

        </div>
    </main>
</div>
@endsection