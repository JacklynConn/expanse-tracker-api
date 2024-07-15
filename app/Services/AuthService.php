<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthService
{
    public function register(object $request): User
    {
        $user = User::create([
            'uuid' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);

        // Send Verification Code
        $this->otp($user);

        return $user;
    }

    public function login(object $request): ?User
    {
       $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            return $user;
        }

        return null;
    }

    public function otp(User $user): Otp
    {
        $code = rand(100000, 999999);
        
        $otp = Otp::create([
            'user_id' => $user->id,
            'type' => 'verification',
            'code' => $code,
            'active' => 1
        ]);

        // Send Mail
        Mail::to($user)->send(new OtpMail($user, $code));

        return $otp;
    }

    public function verify(User $user, object $request): User
    {
        $otp = Otp::where([
            'user_id' => $user->id,
            'code' => $request->otp,
            'active' => 1
        ])->first();

        if (!$otp) {
            abort(422, __('app.invalid_otp'));
        }

        // Update Otp
        $user->email_verified_at = Carbon::now();
        $user->update();

        // Deactivate Otp
        $otp->active = 0;
        $otp->updated_at = Carbon::now();
        $otp->update();

        return $user;
    }
}