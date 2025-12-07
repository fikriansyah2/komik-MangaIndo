<!-- Account Summary Sidebar -->
<div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
    <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">👤 Akun</h3>

    <div class="space-y-3 text-sm">
        <div>
            <div class="text-gray-600 dark:text-gray-400">Nama</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->name }}</div>
        </div>

        <div>
            <div class="text-gray-600 dark:text-gray-400">Email</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100 break-all">{{ $user->email }}</div>
        </div>

        <div>
            <div class="text-gray-600 dark:text-gray-400">Member Sejak</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $user->created_at->format('d M Y') }}</div>
        </div>

        <div>
            <div class="text-gray-600 dark:text-gray-400">Terakhir Login</div>
            <div class="font-semibold text-gray-900 dark:text-gray-100">
                @if($user->last_login_at)
                    {{ $user->last_login_at->diffForHumans() }}
                @else
                    Baru pertama kali
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 space-y-2">
        <a href="{{ route('profile.edit') }}" 
           class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg text-sm font-semibold transition-colors">
            Edit Profil
        </a>
        <form method="POST" action="{{ route('logout') }}" class="block">
            @csrf
            <button type="submit" 
                    class="w-full text-center bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-900 dark:text-gray-100 py-2 rounded-lg text-sm font-semibold transition-colors">
                Logout
            </button>
        </form>
    </div>
</div>
