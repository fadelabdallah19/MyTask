@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    @include('layouts._nav')

    <main class="max-w-6xl mx-auto p-4 sm:p-6">
        <div class="flex flex-col gap-4 mb-6">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-800">Daftar Tugas</h2>
                <p class="text-sm text-gray-500 mt-1">Kelola dan pantau semua tugasmu.</p>
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-3">
                <a href="{{ route('tasks.trash') }}" class="inline-flex items-center justify-center gap-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 px-3 py-2.5 rounded-lg transition-colors whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Tempat Sampah
                </a>
                <a href="{{ route('tasks.create') }}" class="inline-flex items-center justify-center gap-2 bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors whitespace-nowrap shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Buat Task
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <form id="task-filter-form" method="GET" action="{{ route('tasks.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 sm:p-5 mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
            <div class="sm:col-span-2 lg:col-span-1">
                <label for="search" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Cari Judul</label>
                <input
                    type="text"
                    id="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Cari task..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition"
                >
            </div>
            <div class="min-w-0">
                <label for="status" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Status</label>
                <select id="status" name="status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Semua Status</option>
                    <option value="todo" {{ request('status') == 'todo' ? 'selected' : '' }}>To Do</option>
                    <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="min-w-0">
                <label for="priority" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Prioritas</label>
                <select id="priority" name="priority" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="">Semua Prioritas</option>
                    <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Low</option>
                    <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>High</option>
                </select>
            </div>
            <div class="min-w-0">
                <label for="sort" class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-1.5">Urutkan</label>
                <select id="sort" name="sort" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                    <option value="latest" {{ request('sort') == 'latest' || !request('sort') ? 'selected' : '' }}>Terbaru</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>Terlama</option>
                    <option value="deadline" {{ request('sort') == 'deadline' ? 'selected' : '' }}>Deadline</option>
                    <option value="priority" {{ request('sort') == 'priority' ? 'selected' : '' }}>Prioritas</option>
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-1 flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors">Terapkan</button>
                <a href="{{ route('tasks.index') }}" class="flex-1 text-center bg-gray-100 text-gray-700 px-4 py-2.5 rounded-lg hover:bg-gray-200 transition-colors">Reset</a>
            </div>
        </form>

        <div id="task-results">
            @include('tasks._table', ['tasks' => $tasks])
        </div>
    </main>
</div>
@vite(['resources/js/tasks-filter.js'])
@endsection