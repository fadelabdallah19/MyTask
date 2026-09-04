@extends('layouts.app')

@section('title', 'Daftar User')

@section('content')
    <div class="max-w-6xl mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Semua User</h2>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Role</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Task</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-900">{{ $user->name }}</td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600">{{ $user->email }}</td>
                            <td class="px-4 sm:px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full text-xs {{ $user->isAdmin() ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $user->isAdmin() ? 'admin' : 'user' }}
                                </span>
                            </td>
                            <td class="px-4 sm:px-6 py-4 text-sm text-gray-600 whitespace-nowrap">{{ $user->tasks_count }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 sm:px-6 py-4 text-sm text-gray-500">Belum ada user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>

        <div class="mt-4 overflow-x-auto">
            {{ $users->links() }}
        </div>
    </div>
@endsection