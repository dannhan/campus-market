<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\University;
use App\Models\User;
use App\Services\Verification\StudentVerificationService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the landing / welcome page.
     */
    public function welcome(): View
    {
        return view('welcome');
    }

    /**
     * Show the login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('profile');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('profile'));
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors([
                'email' => 'Kombinasi email universitas dan password tidak sesuai.',
            ]);
    }

    /**
     * Show the registration form.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('profile');
        }

        $universities = University::orderBy('name')->get();

        return view('auth.register', compact('universities'));
    }

    /**
     * Handle student registration and verification.
     */
    public function register(
        RegisterRequest $request,
        StudentVerificationService $verificationService
    ): RedirectResponse {
        // Run student verification service against university registry
        $verificationResult = $verificationService->verifyByUniversityId(
            universityId: (int) $request->input('university_id'),
            studentNumber: (string) $request->input('student_number'),
            email: (string) $request->input('university_email'),
            name: (string) $request->input('name'),
            major: $request->input('major')
        );

        if ($verificationResult->isFailed()) {
            return back()
                ->withInput()
                ->withErrors($verificationResult->getErrors() ?: [
                    'student_number' => [$verificationResult->getMessage()],
                ]);
        }

        $student = $verificationResult->getStudent();

        // Create the CampusMarket user account linked to the verified student record
        $user = User::create([
            'name' => $student->full_name,
            'email' => strtolower($student->university_email),
            'password' => Hash::make($request->input('password')),
            'student_id' => $student->id,
        ]);

        // Dispatch standard Laravel registration event (triggers verification email)
        event(new Registered($user));

        // Authenticate new user session
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')->with(
            'status',
            'Akun berhasil dibuat dan data mahasiswa terverifikasi. Silakan cek email kampus Anda untuk menyelesaikan verifikasi.'
        );
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
