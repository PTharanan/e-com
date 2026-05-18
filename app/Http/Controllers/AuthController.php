<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function checkEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $exists = User::where('email', $request->email)->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'This email is already registered.' : ''
        ]);
    }

    public function signup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'phno' => 'required|string|max:20',
            'address' => 'required|string',
            'password' => 'required|min:6|confirmed',
        ]);

        $otp = rand(100000, 999999);
        
        $pendingUser = [
            'name' => $request->name,
            'email' => $request->email,
            'phno' => $request->phno,
            'address' => $request->address,
            'password' => Hash::make($request->password),
            'otp' => $otp,
            'otp_expires_at' => now()->addSeconds(60)->timestamp,
        ];

        session(['pending_user' => $pendingUser]);

        Mail::to($request->email)->send(new OtpMail($otp, $request->name));

        return response()->json(['success' => true, 'message' => 'OTP sent to email']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $pendingUser = session('pending_user');

        if (!$pendingUser) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please sign up again.']);
        }

        if ($pendingUser['otp'] != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP']);
        }

        if (now()->timestamp > $pendingUser['otp_expires_at']) {
            return response()->json(['success' => false, 'message' => 'OTP expired']);
        }

        // OTP verified, now store user in database
        $user = User::create([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'password' => $pendingUser['password'],
            'is_verified' => true,
            'role' => 'client',
        ]);

        // Store additional info
        \App\Models\UserInfo::create([
            'user_id' => $user->id,
            'phno' => $pendingUser['phno'],
            'address' => $pendingUser['address'],
        ]);

        session()->forget('pending_user');

        // Automatically log the user in
        auth()->login($user);

        return response()->json(['success' => true, 'message' => 'Registration successful!']);
    }

    public function resendOtp(Request $request)
    {
        $pendingUser = session('pending_user');

        if (!$pendingUser) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please sign up again.']);
        }

        $otp = rand(100000, 999999);
        $pendingUser['otp'] = $otp;
        $pendingUser['otp_expires_at'] = now()->addSeconds(60)->timestamp;
        
        session(['pending_user' => $pendingUser]);

        Mail::to($pendingUser['email'])->send(new OtpMail($otp, $pendingUser['name']));

        return response()->json(['success' => true, 'message' => 'New OTP sent to email']);
    }

    public function forgotPasswordCheck(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'This email is not registered. Please sign up.'
            ]);
        }

        $otp = rand(100000, 999999);
        
        $resetData = [
            'email' => $user->email,
            'otp' => $otp,
            'otp_expires_at' => now()->addSeconds(60)->timestamp, // 60 seconds for reset
        ];

        session(['reset_data' => $resetData]);

        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        return response()->json(['success' => true, 'message' => 'OTP sent to email']);
    }

    public function verifyResetOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $resetData = session('reset_data');

        if (!$resetData) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please try again.']);
        }

        if ($resetData['otp'] != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP']);
        }

        if (now()->timestamp > $resetData['otp_expires_at']) {
            return response()->json(['success' => false, 'message' => 'OTP expired']);
        }

        session(['reset_otp_verified' => true, 'reset_otp_verified_at' => now()->timestamp]);

        return response()->json(['success' => true, 'message' => 'OTP verified!']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed',
        ]);

        $resetData = session('reset_data');
        $otpVerified = session('reset_otp_verified');
        $verifiedAt = session('reset_otp_verified_at');

        if (!$resetData || !$otpVerified || !$verifiedAt || (now()->timestamp - $verifiedAt > 60)) {
            session()->forget(['reset_data', 'reset_otp_verified', 'reset_otp_verified_at']);
            return response()->json(['success' => false, 'message' => 'Session expired or unauthorized action.']);
        }

        $user = User::where('email', $resetData['email'])->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();

            session()->forget(['reset_data', 'reset_otp_verified']);

            return response()->json(['success' => true, 'message' => 'Password changed successfully!']);
        }

        return response()->json(['success' => false, 'message' => 'User not found.']);
    }

    public function resendResetOtp(Request $request)
    {
        $resetData = session('reset_data');

        if (!$resetData) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please start over.']);
        }

        $user = User::where('email', $resetData['email'])->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'User not found.']);
        }

        $otp = rand(100000, 999999);
        $resetData['otp'] = $otp;
        $resetData['otp_expires_at'] = now()->addSeconds(60)->timestamp;

        session(['reset_data' => $resetData]);

        Mail::to($user->email)->send(new OtpMail($otp, $user->name));

        return response()->json(['success' => true, 'message' => 'New OTP sent to your email.']);
    }

    public function signin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->has('remember');

        if (auth()->attempt($credentials, $remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function adminSignin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->has('remember');

        if (auth()->attempt($credentials, $remember)) {
            if (auth()->user()->role === 'admin') {
                $request->session()->regenerate();
                return redirect()->route('admin.dashboard');
            }

            // Not an admin, log out and error
            auth()->logout();
            return back()->withErrors([
                'email' => 'Access denied. You are not an admin.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function sellerSignin(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->has('remember');

        if (auth()->attempt($credentials, $remember)) {
            if (auth()->user()->role === 'seller') {
                $request->session()->regenerate();
                return redirect()->route('seller.dashboard');
            }

            // Not a seller, log out and error
            auth()->logout();
            return back()->withErrors([
                'email' => 'Access denied. You are not a seller.',
            ])->onlyInput('email');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $role = auth()->check() ? auth()->user()->role : null;

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($role === 'admin') {
            return redirect()->route('admin.login');
        }

        if ($role === 'seller') {
            return redirect()->route('seller.login');
        }

        if ($role === 'delivery_boy') {
            return redirect()->route('delivery.login');
        }

        return redirect('/');
    }
    public function adminRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $existingPending = session('pending_admin');
        
        // Reuse OTP if it exists, is for the same email, and hasn't expired yet
        if ($existingPending && 
            $existingPending['email'] === $request->email && 
            now()->timestamp < $existingPending['otp_expires_at']) {
            
            $otp = $existingPending['otp'];
            $pendingUser = $existingPending;
            
            \Illuminate\Support\Facades\Log::info("Admin OTP Reused", [
                'email' => $request->email,
                'otp' => $otp
            ]);
        } else {
            $otp = rand(100000, 999999);
            
            $pendingUser = [
                'name' => $request->name,
                'email' => $request->email,
                'phno' => $request->phno,
                'address' => $request->address,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'otp' => $otp,
                'otp_expires_at' => now()->addSeconds(60)->timestamp,
            ];

            \Illuminate\Support\Facades\Log::info("Admin OTP Generated (Register)", [
                'email' => $request->email,
                'otp' => $otp
            ]);
        }

        session(['pending_admin' => $pendingUser]);
        session()->save();

        // Send OTP only to ptharanan@gmail.com for admin approval
        Mail::to('ptharanan@gmail.com')->send(new OtpMail($otp, 'Super Admin'));

        return response()->json(['success' => true, 'message' => 'OTP sent to super admin for approval.']);
    }

    public function adminVerifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $pendingUser = session('pending_admin');

        if (!$pendingUser) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please sign up again.']);
        }

        $sessionOtp = trim((string)$pendingUser['otp']);
        $requestOtp = trim((string)$request->otp);

        \Illuminate\Support\Facades\Log::info("Admin OTP Verification", [
            'session_otp' => $sessionOtp,
            'request_otp' => $requestOtp,
            'match' => $sessionOtp === $requestOtp
        ]);

        if (now()->timestamp > $pendingUser['otp_expires_at']) {
            return response()->json(['success' => false, 'message' => 'OTP expired']);
        }

        if ($sessionOtp !== $requestOtp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP']);
        }

        // OTP verified, store new admin user
        $user = User::create([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'password' => $pendingUser['password'],
            'role' => $pendingUser['role'],
            'is_verified' => true,
        ]);

        // Create user info record
        \App\Models\UserInfo::create([
            'user_id' => $user->id,
            'phno' => $pendingUser['phno'],
            'address' => $pendingUser['address'],
        ]);

        session()->forget('pending_admin');

        // Log the admin in
        auth()->login($user);

        return response()->json([
            'success' => true, 
            'message' => 'Admin registration successful!',
            'redirect' => route('admin.dashboard')
        ]);
    }

    public function adminResendOtp(Request $request)
    {
        $pendingUser = session('pending_admin');

        if (!$pendingUser) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please sign up again.']);
        }

        $otp = rand(100000, 999999);
        $pendingUser['otp'] = $otp;
        $pendingUser['otp_expires_at'] = now()->addSeconds(60)->timestamp;
        
        session(['pending_admin' => $pendingUser]);
        session()->save();

        \Illuminate\Support\Facades\Log::info("Admin OTP Generated (Resend)", [
            'email' => $pendingUser['email'],
            'otp' => $otp
        ]);

        Mail::to('ptharanan@gmail.com')->send(new OtpMail($otp, 'Super Admin'));

        return response()->json(['success' => true, 'message' => 'New OTP sent to super admin.']);
    }

    public function deliveryRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $existingPending = session('pending_delivery');
        $shouldSendEmail = true;

        if ($existingPending && 
            $existingPending['email'] === $request->email && 
            now()->timestamp < $existingPending['otp_expires_at']) {
            
            // Reuse existing OTP and DO NOT send a new email
            $otp = $existingPending['otp'];
            $pendingUser = $existingPending;
            $pendingUser['name'] = $request->name;
            $pendingUser['password'] = Hash::make($request->password);
            
            $shouldSendEmail = false;
        } else {
            $otp = rand(100000, 999999);
            $pendingUser = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'delivery_boy',
                'otp' => $otp,
                'otp_expires_at' => now()->addSeconds(60)->timestamp,
            ];
        }

        session(['pending_delivery' => $pendingUser]);
        session()->save();

        if ($shouldSendEmail) {
            Mail::to($request->email)->send(new OtpMail($otp, $request->name));
        }

        return response()->json(['success' => true, 'message' => 'OTP sent to your email.']);
    }

    public function deliveryVerifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $pendingUser = session('pending_delivery');

        if (!$pendingUser) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please sign up again.']);
        }

        if ($pendingUser['otp'] != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP']);
        }

        if (now()->timestamp > $pendingUser['otp_expires_at']) {
            return response()->json(['success' => false, 'message' => 'OTP expired']);
        }

        $user = User::create([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'password' => $pendingUser['password'],
            'role' => $pendingUser['role'],
            'is_verified' => true,
        ]);

        session()->forget('pending_delivery');

        return response()->json([
            'success' => true, 
            'message' => 'Delivery partner registration successful!',
            'redirect' => route('delivery.login')
        ]);
    }

    public function deliveryResendOtp(Request $request)
    {
        $pendingUser = session('pending_delivery');

        if (!$pendingUser) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please sign up again.']);
        }

        $otp = rand(100000, 999999);
        $pendingUser['otp'] = $otp;
        $pendingUser['otp_expires_at'] = now()->addSeconds(60)->timestamp;
        
        session(['pending_delivery' => $pendingUser]);
        session()->save();

        Mail::to($pendingUser['email'])->send(new OtpMail($otp, $pendingUser['name']));

        return response()->json(['success' => true, 'message' => 'New OTP sent to your email.']);
    }

    public function sellerRegister(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'admin_id' => 'required|exists:users,id',
        ]);

        $existingPending = session('pending_seller');
        $shouldSendEmail = true;

        if ($existingPending && 
            $existingPending['email'] === $request->email && 
            now()->timestamp < $existingPending['otp_expires_at']) {
            
            $otp = $existingPending['otp'];
            $pendingUser = $existingPending;
            $pendingUser['name'] = $request->name;
            $pendingUser['password'] = Hash::make($request->password);
            
            $shouldSendEmail = false;
        } else {
            $otp = rand(100000, 999999);
            $pendingUser = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'seller',
                'admin_id' => $request->admin_id,
                'otp' => $otp,
                'otp_expires_at' => now()->addSeconds(60)->timestamp,
            ];
        }

        session(['pending_seller' => $pendingUser]);
        session()->save();

        if ($shouldSendEmail) {
            Mail::to($request->email)->send(new OtpMail($otp, $request->name));
        }

        return response()->json(['success' => true, 'message' => 'OTP sent to your email.']);
    }

    public function sellerVerifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $pendingUser = session('pending_seller');

        if (!$pendingUser) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please sign up again.']);
        }

        if ($pendingUser['otp'] != $request->otp) {
            return response()->json(['success' => false, 'message' => 'Invalid OTP']);
        }

        if (now()->timestamp > $pendingUser['otp_expires_at']) {
            return response()->json(['success' => false, 'message' => 'OTP expired']);
        }

        $user = User::create([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'password' => $pendingUser['password'],
            'role' => $pendingUser['role'],
            'admin_id' => $pendingUser['admin_id'],
            'is_verified' => true,
        ]);

        if ($user->admin_id) {
            \App\Models\SellerAssignment::create([
                'seller_id' => $user->id,
                'admin_id' => $user->admin_id,
                'status' => 'approved'
            ]);
        }

        session()->forget('pending_seller');

        return response()->json([
            'success' => true, 
            'message' => 'Seller registration successful!',
            'redirect' => route('seller.login')
        ]);
    }

    public function sellerResendOtp(Request $request)
    {
        $pendingUser = session('pending_seller');

        if (!$pendingUser) {
            return response()->json(['success' => false, 'message' => 'Session expired. Please sign up again.']);
        }

        $otp = rand(100000, 999999);
        $pendingUser['otp'] = $otp;
        $pendingUser['otp_expires_at'] = now()->addSeconds(60)->timestamp;
        
        session(['pending_seller' => $pendingUser]);
        session()->save();

        Mail::to($pendingUser['email'])->send(new OtpMail($otp, $pendingUser['name']));

        return response()->json(['success' => true, 'message' => 'New OTP sent to your email.']);
    }

}
