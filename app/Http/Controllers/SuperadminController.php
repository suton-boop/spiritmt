<?php

namespace App\Http\Controllers;

use App\Models\Presentation;
use App\Models\Response;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SuperadminController extends Controller
{
    /**
     * Show dedicated superadmin login page.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if ($request->session()->get('superadmin_logged_in') === true && Auth::check() && Auth::user()->isSuperadmin()) {
            return redirect()->route('superadmin.index');
        }

        return view('superadmin.login');
    }

    /**
     * Authenticate superadmin credentials and establish superadmin session.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return back()->withInput($request->only('email'))->with('error', 'Email atau password yang Anda masukkan salah.');
        }

        if (! $user->isSuperadmin()) {
            return back()->withInput($request->only('email'))->with('error', 'Akun ini bukan akun Superadmin dan tidak memiliki izin akses ke area ini.');
        }

        // Log in to normal Auth session
        Auth::login($user);

        // Mark superadmin session
        $request->session()->put('superadmin_logged_in', true);
        $request->session()->put('superadmin_user_id', $user->id);
        $request->session()->regenerate();

        return redirect()->route('superadmin.index')->with('success', 'Autentikasi Superadmin berhasil. Selamat datang!');
    }

    /**
     * End superadmin session and return to login.
     */
    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['superadmin_logged_in', 'superadmin_user_id']);

        return redirect()->route('superadmin.login')->with('success', 'Sesi Superadmin telah berhasil diakhiri.');
    }

    /**
     * Display the main superadmin command center.
     */
    public function index(Request $request): View
    {
        SystemSetting::seedDefaultFeatures();

        $search = trim((string) $request->query('search', ''));
        $roleFilter = trim((string) $request->query('role', 'all'));

        $usersQuery = User::withCount(['presentations', 'folders'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter === 'superadmin', function ($query) {
                $query->where('is_superadmin', true);
            })
            ->when($roleFilter === 'presenter', function ($query) {
                $query->where('is_superadmin', false);
            })
            ->orderByDesc('is_superadmin')
            ->latest('id');

        $users = $usersQuery->paginate(15)->withQueryString();

        $features = SystemSetting::orderBy('group')->orderBy('name')->get();

        $stats = [
            'total_users' => User::count(),
            'total_superadmins' => User::where('is_superadmin', true)->count(),
            'total_presentations' => Presentation::count(),
            'total_responses' => Response::count(),
            'active_features' => SystemSetting::where('is_enabled', true)->count(),
            'total_features' => SystemSetting::count(),
        ];

        return view('superadmin.index', [
            'users' => $users,
            'features' => $features,
            'stats' => $stats,
            'search' => $search,
            'roleFilter' => $roleFilter,
        ]);
    }

    /**
     * Store a newly created user account by Superadmin.
     */
    public function storeUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'is_superadmin' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_superadmin' => $request->boolean('is_superadmin'),
        ]);

        return redirect()->route('superadmin.index')
            ->with('success', "Akun pengguna '{$user->name}' ({$user->email}) berhasil dibuat.");
    }

    /**
     * Reset password for a user account.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $autoGenerate = $request->boolean('auto_generate');

        if ($autoGenerate) {
            $newPassword = 'Spirit'.Str::random(6).'!8';
        } else {
            $validated = $request->validate([
                'password' => ['required', 'string', 'min:6'],
            ]);
            $newPassword = $validated['password'];
        }

        $user->forceFill([
            'password' => Hash::make($newPassword),
            'remember_token' => Str::random(60),
        ])->save();

        $message = "Password untuk akun '{$user->name}' ({$user->email}) berhasil direset.";
        if ($autoGenerate) {
            $message .= " Password baru: {$newPassword}";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'new_password' => $autoGenerate ? $newPassword : null,
            ]);
        }

        return redirect()->route('superadmin.index')->with('success', $message);
    }

    /**
     * Toggle superadmin role for an account.
     */
    public function toggleRole(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($user->id === Auth::id()) {
            $msg = 'Anda tidak dapat mengubah peran akun Anda sendiri demi keamanan.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $msg], 422);
            }

            return redirect()->route('superadmin.index')->with('error', $msg);
        }

        $user->is_superadmin = ! $user->is_superadmin;
        $user->save();

        $roleName = $user->is_superadmin ? 'Superadmin' : 'Presenter Biasa';
        $message = "Peran akun '{$user->name}' berhasil diubah menjadi {$roleName}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_superadmin' => $user->is_superadmin,
            ]);
        }

        return redirect()->route('superadmin.index')->with('success', $message);
    }

    /**
     * Delete a user account and clean up their associated data.
     */
    public function destroyUser(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($user->id === Auth::id()) {
            $msg = 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login.';
            if ($request->wantsJson()) {
                return response()->json(['error' => $msg], 422);
            }

            return redirect()->route('superadmin.index')->with('error', $msg);
        }

        $userName = $user->name;
        $userEmail = $user->email;

        // Cascade delete presentations and folders
        $user->presentations()->each(function ($presentation) {
            $presentation->slides()->delete();
            $presentation->responses()->delete();
            $presentation->audienceQuestions()->delete();
            $presentation->quizParticipants()->delete();
            $presentation->delete();
        });

        $user->folders()->delete();
        $user->delete();

        $message = "Akun '{$userName}' ({$userEmail}) dan seluruh data presentasinya berhasil dihapus.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()->route('superadmin.index')->with('success', $message);
    }

    /**
     * Toggle a feature flag ON or OFF.
     */
    public function toggleFeature(Request $request, SystemSetting $setting): RedirectResponse|JsonResponse
    {
        $setting->is_enabled = ! $setting->is_enabled;
        $setting->save();

        $statusText = $setting->is_enabled ? 'diaktifkan' : 'dinonaktifkan';
        $message = "Fitur '{$setting->name}' berhasil {$statusText}.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'is_enabled' => $setting->is_enabled,
                'key' => $setting->key,
            ]);
        }

        return redirect()->route('superadmin.index')->with('success', $message);
    }
}
