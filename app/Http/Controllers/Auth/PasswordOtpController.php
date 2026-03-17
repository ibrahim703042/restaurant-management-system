<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetOtpMail;
use App\Models\PasswordResetOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PasswordOtpController extends Controller
{
    protected function otpExpireMinutes(): int
    {
        return max(5, min(60, (int) config('auth.password_otp_expire_minutes', 15)));
    }

    /**
     * Send OTP to email (shared by initial request and resend).
     */
    protected function issueOtpAndMail(string $email): void
    {
        $key = 'password-otp-send:'.sha1(strtolower($email));
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => __('Too many attempts. Try again in :seconds seconds.', ['seconds' => $seconds]),
            ]);
        }
        RateLimiter::hit($key, 60);

        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        PasswordResetOtp::where('email', $email)->delete();
        PasswordResetOtp::create([
            'email' => $email,
            'code_hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes($this->otpExpireMinutes()),
        ]);

        try {
            Mail::to($email)->send(new PasswordResetOtpMail($otp, $this->otpExpireMinutes()));
        } catch (\Throwable $e) {
            report($e);
            throw ValidationException::withMessages([
                'email' => __('Could not send email. Configure SMTP in .env (MAIL_*) or try again later.'),
            ]);
        }
    }

    public function showRequestForm()
    {
        return view('auth.passwords.email');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $email = $request->input('email');
        try {
            $this->issueOtpAndMail($email);
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        }

        $request->session()->put('password_reset_email', $email);

        return redirect()->route('password.otp.show')
            ->with('status', __('We sent a 6-digit code to your email. Enter it below with your new password.'));
    }

    public function showOtpForm(Request $request)
    {
        if (! $request->session()->has('password_reset_email')) {
            return redirect()->route('password.request')
                ->withErrors(['email' => __('Start by entering your email on the previous page.')]);
        }

        return view('auth.passwords.otp-reset', [
            'email' => $request->session()->get('password_reset_email'),
        ]);
    }

    public function resendOtp(Request $request)
    {
        if (! $request->session()->has('password_reset_email')) {
            return redirect()->route('password.request');
        }

        $email = $request->session()->get('password_reset_email');
        try {
            $this->issueOtpAndMail($email);
        } catch (ValidationException $e) {
            $msg = $e->errors()['email'][0] ?? __('Could not resend. Try again later.');

            return redirect()->route('password.otp.show')->withErrors(['otp' => $msg]);
        }

        return redirect()->route('password.otp.show')
            ->with('status', __('A new code was sent to your email.'));
    }

    public function resetWithOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6', 'regex:/^[0-9]+$/'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = $request->session()->get('password_reset_email');
        if (! $email) {
            return redirect()->route('password.request');
        }

        $row = PasswordResetOtp::where('email', $email)
            ->where('expires_at', '>', now())
            ->first();

        if (! $row) {
            return back()->withErrors(['otp' => __('Code expired. Request a new code from the email step.')]);
        }

        if ($row->attempts >= 10) {
            $row->delete();

            return redirect()->route('password.request')
                ->withErrors(['email' => __('Too many failed attempts. Request a new code.')]);
        }

        if (! Hash::check($request->otp, $row->code_hash)) {
            $row->increment('attempts');

            return back()->withErrors(['otp' => __('Invalid code.')]);
        }

        $user = User::where('email', $email)->firstOrFail();
        $user->password = Hash::make($request->password);
        $user->setRememberToken(Str::random(60));
        $user->save();

        $row->delete();
        $request->session()->forget('password_reset_email');

        return redirect()->route('login')
            ->with('status', __('Your password has been reset. You can sign in.'));
    }

    public function cancel(Request $request)
    {
        $request->session()->forget('password_reset_email');

        return redirect()->route('password.request');
    }
}
