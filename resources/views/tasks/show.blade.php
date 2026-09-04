@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    @include('layouts._nav')

    <main class="max-w-3xl mx-auto p-4 sm:p-6">
        <a href="{{ route('tasks.index') }}" class="text-sm text-gray-600 hover:underline mb-4 inline-block">&larr; Kembali</a>

        <div class="bg-white rounded-lg shadow p-4 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
                <h2 class="text-2xl font-bold text-gray-800 break-words">{{ $task->title }}</h2>
                <div class="flex gap-3 text-sm">
                    <a href="{{ route('tasks.edit', $task) }}" class="text-blue-600 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Hapus task ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>

            <div class="flex gap-2 mb-6">
                <span class="{{ $task->priority === 'high' ? 'bg-red-100 text-red-700' : ($task->priority === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }} px-3 py-1 rounded-full text-xs capitalize">{{ $task->priority }}</span>
                <span class="{{ $task->status === 'completed' ? 'bg-green-100 text-green-700' : ($task->status === 'in_progress' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700') }} px-3 py-1 rounded-full text-xs capitalize">{{ str_replace('_', ' ', $task->status) }}</span>
            </div>

            <div class="mb-6">
                <p class="text-sm text-gray-600">
                    @if ($task->deadline)
                        Deadline: <time datetime="{{ $task->deadline->utc()->toIso8601String() }}"></time>
                    @else
                        Deadline: Tanpa batas waktu
                    @endif
                </p>
            </div>

            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Deskripsi</h3>
                <p class="text-gray-600 whitespace-pre-line">{{ $task->description ?? 'Tidak ada deskripsi.' }}</p>
            </div>
        </div>
    </main>
</div>
@endsection