<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserStreak;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register', ['timezones' => $this->timezones()]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $password = (string) $value;
                    $variety = (int) preg_match('/[a-z]/', $password)
                        + (int) preg_match('/[A-Z]/', $password)
                        + (int) preg_match('/\d/', $password)
                        + (int) preg_match('/[^A-Za-z0-9]/', $password);
                    $lengthScore = strlen($password) >= 12 ? 2 : (strlen($password) >= 8 ? 1 : 0);

                    if (strlen($password) < 8 || $lengthScore + $variety < 4) {
                        $fail('The password is too weak. Use at least 8 characters with a mix of uppercase, lowercase, numbers, and symbols.');
                    }
                },
            ],
            'timezone' => ['required', Rule::in($this->timezones())],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'USER',
            'status' => 'ACTIVE',
            'timezone' => $validated['timezone'],
        ]);

        UserStreak::firstOrCreate(['user_id' => $user->id]);
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials + ['status' => 'ACTIVE'], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'The provided credentials do not match an active account.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route($request->user()->isAdmin() ? 'admin.dashboard' : 'dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    /**
     * @return array<int, string>
     */
    private function timezones(): array
    {
        return [
            'UTC',
            'Asia/Manila',
            'America/New_York',
            'America/Chicago',
            'America/Denver',
            'America/Los_Angeles',
            'Europe/London',
        ];
    }
}
