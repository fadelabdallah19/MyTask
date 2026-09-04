@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    @include('layouts._nav')

    <main class="max-w-6xl mx-auto p-4 sm:p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Tempat Sampah (Trash)</h2>
            <a href="{{ route('tasks.index') }}" class="text-sm text-gray-600 hover:underline">← Kembali ke Task</a>
        </div>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if ($tasks->isEmpty())
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-gray-500">Tempat sampah kosong.</p>
            </div>
        @else
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr class="text-left text-sm text-gray-600">
                            <th class="px-4 sm:px-6 py-3">Judul</th>
                            <th class="px-4 sm:px-6 py-3">Dihapus</th>
                            <th class="px-4 sm:px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($tasks as $task)
                            <tr>
                                <td class="px-4 sm:px-6 py-4 text-gray-800">{{ $task->title }}</td>
                                <td class="px-4 sm:px-6 py-4 text-sm text-gray-600">
                                    <time datetime="{{ $task->deleted_at->utc()->toIso8601String() }}"></time>
                                </td>
                                <td class="px-4 sm:px-6 py-4 flex gap-3 text-sm">
                                    <form method="POST" action="{{ route('tasks.restore', $task) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="text-green-600 hover:underline whitespace-nowrap">Pulihkan</button>
                                    </form>
                                    <form method="POST" action="{{ route('tasks.force-delete', $task) }}" onsubmit="return confirm('Hapus permanen task ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline whitespace-nowrap">Hapus Permanen</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>

            @if ($tasks->hasPages())
                <div class="mt-6 overflow-x-auto">
                    {{ $tasks->links() }}
                </div>
            @endif
        @endif
    </main>
</div>
@endsection