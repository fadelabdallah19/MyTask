@auth
<aside class="fixed inset-y-0 left-0 w-64 bg-white border-r border-gray-100 shadow-sm hidden lg:flex flex-col">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center gap-2">
        <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-gray-800 hover:text-blue-600">MyTask</a>
    </div>

    <nav class="flex-1 flex flex-col gap-1 px-3 py-4">
        @php
            $main = [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
                ['label' => 'Tugas', 'route' => 'tasks.index'],
                ['label' => 'Report', 'route' => 'report.index'],
            ];
        @endphp
        @foreach ($main as $item)
            <a href="{{ route($item['route']) }}"
               class="px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs($item['route']) ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                {{ $item['label'] }}
            </a>
        @endforeach

        <a href="{{ route('notifications.index') }}"
           class="flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('notifications.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            <span>Notifikasi</span>
            @if ($unreadCount > 0)
                <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs">{{ $unreadCount }}</span>
            @endif
        </a>

        @can('viewAny', App\Models\User::class)
            <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase">Admin</p>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">Pengguna</a>
            <a href="{{ route('admin.logs.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">Log Aktivitas</a>
        @endcan

        <a href="{{ route('settings.edit') }}" class="mt-2 px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">Settings</a>
        <a href="{{ route('profile.edit') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-900">Profil</a>
    </nav>

    <div class="px-3 py-4 border-t border-gray-100">
        <div class="flex items-center justify-between px-4 py-2.5 rounded-lg bg-gray-50">
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-red-600 hover:underline">Logout</button>
            </form>
        </div>
    </div>
</aside>

<nav class="lg:hidden bg-white border-b border-gray-200 fixed top-0 inset-x-0 z-50">
    <div class="flex items-center justify-between px-4 py-3">
        <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-800">MyTask</a>
        <button id="mobile-menu-btn" type="button" class="p-2 rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none" aria-controls="mobile-menu" aria-expanded="false" aria-label="Buka menu">
            <svg id="mobile-menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
            <svg id="mobile-menu-icon-close" class="w-6 h-6 hidden" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <div id="mobile-menu-backdrop" class="hidden fixed inset-0 z-40 bg-gray-900/40 backdrop-blur-[2px]"></div>

    <div id="mobile-menu" class="hidden absolute top-full right-4 z-50 w-[min(16rem,calc(100vw-2rem))] mt-2 p-2 bg-white rounded-2xl shadow-xl ring-1 ring-gray-900/5 flex-col gap-1">
        <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">Dashboard</a>
        <a href="{{ route('tasks.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('tasks.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">Tugas</a>
        <a href="{{ route('report.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('report.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">Report</a>
        <a href="{{ route('notifications.index') }}" class="flex items-center justify-between px-4 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('notifications.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50' }}">
            <span>Notifikasi</span>
            @if ($unreadCount > 0)
                <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full text-xs">{{ $unreadCount }}</span>
            @endif
        </a>

        @can('viewAny', App\Models\User::class)
            <p class="px-4 pt-3 pb-1 text-xs font-semibold text-gray-400 uppercase">Admin</p>
            <a href="{{ route('admin.users.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Pengguna</a>
            <a href="{{ route('admin.logs.index') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Log Aktivitas</a>
        @endcan

        <a href="{{ route('settings.edit') }}" class="mt-2 px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Settings</a>
        <a href="{{ route('profile.edit') }}" class="px-4 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-50">Profil</a>

        <div class="border-t border-gray-100 mt-3 pt-3 flex items-center justify-between px-4">
            <div class="min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-xs text-red-600 hover:underline">Logout</button>
            </form>
        </div>
    </div>
</nav>

<script>
(function () {
    const btn = document.getElementById('mobile-menu-btn');
    const menu = document.getElementById('mobile-menu');
    const backdrop = document.getElementById('mobile-menu-backdrop');
    const iconOpen = document.getElementById('mobile-menu-icon-open');
    const iconClose = document.getElementById('mobile-menu-icon-close');
    if (!btn || !menu) return;

    function toggle(force) {
        const willOpen = typeof force === 'boolean' ? force : menu.classList.contains('hidden');
        menu.classList.toggle('hidden', !willOpen);
        menu.classList.toggle('flex', willOpen);
        backdrop.classList.toggle('hidden', !willOpen);
        iconOpen.classList.toggle('hidden', willOpen);
        iconClose.classList.toggle('hidden', !willOpen);
        btn.setAttribute('aria-expanded', String(willOpen));
    }

    btn.addEventListener('click', function (e) {
        e.stopPropagation();
        toggle();
    });

    menu.addEventListener('click', function (e) {
        const link = e.target.closest('a');
        if (link) toggle(false);
    });

    backdrop.addEventListener('click', function () {
        toggle(false);
    });

    document.addEventListener('click', function (e) {
        if (!menu.contains(e.target) && !btn.contains(e.target)) toggle(false);
    });
})();
</script>
@endauth