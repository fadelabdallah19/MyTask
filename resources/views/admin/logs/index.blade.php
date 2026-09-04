@extends('layouts.app')

@section('title', 'Log Aktivitas')

@section('content')
    <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Log Aktivitas</h2>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Detail</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-900">{{ $log->user?->name }} <span class="text-gray-500">({{ $log->user?->email }})</span></td>
                            <td class="px-4 sm:px-6 py-4"><span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700 whitespace-nowrap">{{ $log->action }}</span></td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600">{{ $log->details ? json_encode($log->details, JSON_UNESCAPED_UNICODE) : '-' }}</td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $log->created_at?->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-6 py-4 text-sm text-gray-500">Belum ada aktivitas.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            {{ $logs->links() }}
        </div>
    </div>
@endsection