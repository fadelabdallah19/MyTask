@if ($tasks->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-dashed border-gray-300 p-12 text-center">
        <div class="mx-auto mb-4 h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
            </svg>
        </div>
        <p class="text-gray-500 text-lg mb-4">Belum ada task.</p>
        <a href="{{ route('tasks.create') }}" class="inline-block bg-blue-600 text-white px-5 py-2.5 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            Buat Task Pertama
        </a>
    </div>
@else
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" id="task-table">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr class="hidden sm:table-row text-left text-xs font-medium text-gray-500 uppercase tracking-wide">
                    <th class="px-4 sm:px-6 py-3">Judul</th>
                    <th class="px-4 sm:px-6 py-3">Prioritas</th>
                    <th class="px-4 sm:px-6 py-3">Status</th>
                    <th class="px-4 sm:px-6 py-3">Deadline</th>
                    <th class="px-4 sm:px-6 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="block space-y-3 sm:table-row-group sm:divide-y sm:divide-gray-100 sm:space-y-0">
                @foreach ($tasks as $task)
                    <tr class="block sm:table-row p-4 sm:p-0 border border-gray-100 rounded-lg sm:border-0 sm:rounded-none hover:bg-gray-50 transition-colors">
                        <td data-label="Judul" class="flex sm:table-cell items-center justify-between gap-4 px-0 sm:px-6 py-2 sm:py-4">
                            <a href="{{ route('tasks.show', $task) }}" class="text-gray-800 font-medium hover:text-blue-600">{{ $task->title }}</a>
                        </td>
                        <td data-label="Prioritas" class="flex sm:table-cell items-center justify-between gap-4 px-0 sm:px-6 py-2 sm:py-4">
                            <span class="sm:hidden text-xs font-medium uppercase tracking-wide text-gray-500">Prioritas</span>
                            @if ($task->priority === 'high')
                                <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-medium">High</span>
                            @elseif ($task->priority === 'medium')
                                <span class="bg-yellow-100 text-yellow-700 px-2.5 py-1 rounded-full text-xs font-medium">Medium</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full text-xs font-medium">Low</span>
                            @endif
                        </td>
                        <td data-label="Status" class="flex sm:table-cell items-center justify-between gap-4 px-0 sm:px-6 py-2 sm:py-4">
                            <span class="sm:hidden text-xs font-medium uppercase tracking-wide text-gray-500">Status</span>
                            @if ($task->status === 'completed')
                                <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-medium">Selesai</span>
                            @elseif ($task->status === 'in_progress')
                                <span class="bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full text-xs font-medium">Sedang Dikerjakan</span>
                            @else
                                <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-full text-xs font-medium">Belum</span>
                            @endif
                        </td>
                        <td data-label="Deadline" class="flex sm:table-cell items-center justify-between gap-4 px-0 sm:px-6 py-2 sm:py-4 text-sm text-gray-600">
                            <span class="sm:hidden text-xs font-medium uppercase tracking-wide text-gray-500">Deadline</span>
                            <div>
                                @if ($task->deadline)
                                    <time datetime="{{ $task->deadline->utc()->toIso8601String() }}"></time>
                                @else
                                    -
                                @endif
                            </div>
                            @php
                                $deadlineStatus = $task->deadlineStatus();
                            @endphp
                            @if ($deadlineStatus === 'overdue')
                                <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs mt-1 inline-block font-medium">Overdue</span>
                            @elseif ($deadlineStatus === 'due_today')
                                <span class="bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full text-xs mt-1 inline-block font-medium">Hari Ini</span>
                            @elseif ($deadlineStatus === 'due_tomorrow')
                                <span class="bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full text-xs mt-1 inline-block font-medium">Besok</span>
                            @elseif ($deadlineStatus === 'upcoming')
                                <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-xs mt-1 inline-block font-medium">Akan Datang</span>
                            @endif
                        </td>
                        <td data-label="Aksi" class="flex sm:table-cell items-center justify-between gap-4 px-0 sm:px-6 py-2 sm:py-4">
                            <span class="sm:hidden text-xs font-medium uppercase tracking-wide text-gray-500">Aksi</span>
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('tasks.edit', $task) }}" class="inline-flex items-center text-blue-600 hover:bg-blue-50 px-2.5 py-1.5 rounded-md text-sm transition-colors">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('tasks.destroy', $task) }}" onsubmit="return confirm('Hapus task ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:bg-red-50 px-2.5 py-1.5 rounded-md text-sm transition-colors">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

    @if ($tasks->hasPages())
        <div class="mt-6 overflow-x-auto" id="task-pagination">
            {{ $tasks->links() }}
        </div>
    @endif
@endif