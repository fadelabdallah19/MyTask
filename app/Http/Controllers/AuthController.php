<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Laravel\Firebase\Facades\Firebase;

class AuthController extends Controller
{
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create($validated);

        Auth::login($user);

        ActivityLog::record($user, 'auth.register');

        return redirect()->route('dashboard');
    }

    public function ShowForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::broker()->sendResetLink($validated);

        return back()->with('status', trans($status === Password::RESET_LINK_SENT
            ? 'passwords.sent'
            : 'passwords.user'));
    }

    public function showResetPasswordForm(string $token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => $password,
                ])->save();
                ActivityLog::record($user, 'auth.password_reset');
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'Password berhasil diatur ulang. Silahkan login.');
        }

        return back()->withErrors(['email' => 'Gagal mengatur ulang password. Coba lagi.'])->onlyInput('email');
    }

    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            ActivityLog::record(Auth::user(), 'auth.login');

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function dashboard(): View
    {
        $user = Auth::user();

        $stats = Cache::remember("dashboard:{$user->id}", now()->addSeconds(60), function () use ($user): array {
            $totalTasks = $user->tasks()->count();
            $completedTasks = $user->tasks()->where('status', 'completed')->count();

            return [
                'totalTasks' => $totalTasks,
                'todoTasks' => $user->tasks()->where('status', 'todo')->count(),
                'inProgressTasks' => $user->tasks()->where('status', 'in_progress')->count(),
                'completedTasks' => $completedTasks,
                'completionPercentage' => $totalTasks > 0
                    ? round(($completedTasks / $totalTasks) * 100)
                    : 0,
                'overdueTasks' => $user->tasks()
                    ->where('status', '!=', 'completed')
                    ->whereNotNull('deadline')
                    ->where('deadline', '<', now())
                    ->count(),
            ];
        });

        $upcomingTasks = $user->tasks()
            ->where('status', '!=', 'completed')
            ->whereNotNull('deadline')
            ->whereBetween('deadline', [now(), now()->addDays(3)])
            ->orderBy('deadline')
            ->take(5)
            ->get();

        $recentTasks = $user->tasks()->latest()->take(5)->get();

        $recentNotifications = Notification::query()
            ->where('user_id', $user->id)
            ->with('task:id,title')
            ->orderBy('sent_at', 'desc')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        $unreadNotifications = Notification::forUser($user->id)->unread()->count();

        return view('dashboard', array_merge($stats, [
            'upcomingTasks' => $upcomingTasks,
            'recentTasks' => $recentTasks,
            'recentNotifications' => $recentNotifications,
            'unreadNotifications' => $unreadNotifications,
        ]));
    }

    public function firebaseLogin(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_token' => ['required', 'string'],
        ]);

        try {
            $token = Firebase::auth()->verifyIdToken($validated['id_token']);
        } catch (FailedToVerifyToken) {
            return back()->withErrors([
                'google' => 'Gagal memverifikasi akun Google.',
            ]);
        }

        $claims = $token->claims()->all();

        $user = User::query()
            ->where('google_id', $claims['sub'])
            ->orWhere('email', $claims['email'])
            ->first();

        if ($user === null) {
            $user = User::create([
                'name' => $claims['name'],
                'email' => $claims['email'],
                'google_id' => $claims['sub'],
                'email_verified_at' => now(),
            ]);
        } else {
            $user->forceFill([
                'google_id' => $claims['sub'],
                'email_verified_at' => $user->email_verified_at ?? now(),
            ])->save();
        }

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLog::record($user, 'auth.login_google');

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        ActivityLog::record(Auth::user(), 'auth.logout');

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
