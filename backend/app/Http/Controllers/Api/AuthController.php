<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\AdminLoginOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate(['email' => ['required','email'], 'password' => ['required','string']]);
        $user = User::where('email', $data['email'])->where('status', 'active')->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) return response()->json(['message' => 'Invalid credentials.'], 422);
        return $this->sendChallenge($request, $user);
    }

    public function resendOtp(Request $request)
    {
        $data = $request->validate(['challenge_id' => ['required', 'uuid']]);
        $previous = DB::table('admin_login_otps')->where('id', $data['challenge_id'])->whereNull('consumed_at')->first();
        if (! $previous || now()->diffInSeconds(\Illuminate\Support\Carbon::parse($previous->created_at)) < 60) {
            return response()->json(['message' => 'Please wait before requesting another code.'], 429);
        }
        $user = User::whereKey($previous->user_id)->where('status', 'active')->first();
        if (! $user) return response()->json(['message' => 'This login request is no longer valid.'], 422);
        return $this->sendChallenge($request, $user);
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate(['challenge_id' => ['required','uuid'], 'code' => ['required','digits:6']]);
        return DB::transaction(function () use ($data, $request) {
            $challenge = DB::table('admin_login_otps')->where('id', $data['challenge_id'])->lockForUpdate()->first();
            if (! $challenge || $challenge->consumed_at || now()->greaterThan($challenge->expires_at) || $challenge->attempts >= 5) {
                return response()->json(['message' => 'This verification code is invalid or has expired.'], 422);
            }
            if (! hash_equals((string) $challenge->ip_address, (string) $request->ip())) {
                return response()->json(['message' => 'This verification code is invalid or has expired.'], 422);
            }
            if (! hash_equals($challenge->otp_hash, $this->hashOtp($data['code']))) {
                DB::table('admin_login_otps')->where('id', $challenge->id)->increment('attempts');
                return response()->json(['message' => 'This verification code is invalid or has expired.'], 422);
            }
            $user = User::whereKey($challenge->user_id)->where('status', 'active')->first();
            if (! $user) return response()->json(['message' => 'This verification code is invalid or has expired.'], 422);
            $token = Str::random(80); $sessionId = (string) Str::uuid();
            DB::table('admin_login_otps')->where('id', $challenge->id)->update(['consumed_at' => now(), 'updated_at' => now()]);
            DB::table('admin_sessions')->insert([
                'id'=>$sessionId, 'user_id'=>$user->id, 'token_hash'=>hash('sha256', $token),
                'ip_address'=>$request->ip(), 'user_agent'=>Str::limit((string)$request->userAgent(), 1000, ''),
                'last_used_at'=>now(), 'expires_at'=>now()->addHours((int) env('ADMIN_SESSION_HOURS', 8)),
                'created_at'=>now(), 'updated_at'=>now(),
            ]);
            $user->update(['api_token_hash' => null, 'last_login_at' => now()]);
            return response()->json(['token' => $token, 'expires_in' => (int) env('ADMIN_SESSION_HOURS', 8) * 3600, 'user' => $user->only('id','name','email','role')]);
        });
    }

    private function hashOtp(string $code): string { return hash_hmac('sha256', $code, (string) config('app.key')); }

    public function me(Request $request) { return $request->user(); }
    public function logout(Request $request) { DB::table('admin_sessions')->where('id', $request->attributes->get('admin_session_id'))->delete(); return response()->noContent(); }

    public function changePassword(Request $request)
    {
        $data=$request->validate(['current_password'=>['required','string'],'password'=>['required','confirmed',Password::min(12)->letters()->mixedCase()->numbers()->symbols()]]);
        if(!Hash::check($data['current_password'],$request->user()->password)) return response()->json(['message'=>'The current password is incorrect.'],422);
        $request->user()->update(['password'=>Hash::make($data['password'])]);
        DB::table('admin_sessions')->where('user_id',$request->user()->id)->where('id','!=',$request->attributes->get('admin_session_id'))->delete();
        return response()->json(['message'=>'Password updated. Other administrator sessions were signed out.']);
    }

    private function sendChallenge(Request $request, User $user)
    {
        DB::table('admin_login_otps')->where('user_id', $user->id)->whereNull('consumed_at')->delete();
        $challengeId = (string) Str::uuid(); $code = (string) random_int(100000, 999999);
        DB::table('admin_login_otps')->insert(['id'=>$challengeId,'user_id'=>$user->id,'otp_hash'=>$this->hashOtp($code),'attempts'=>0,'ip_address'=>$request->ip(),'expires_at'=>now()->addMinutes(10),'created_at'=>now(),'updated_at'=>now()]);
        try { Mail::to($user->email)->send(new AdminLoginOtp($code)); }
        catch (\Throwable $exception) { DB::table('admin_login_otps')->where('id', $challengeId)->delete(); report($exception); return response()->json(['message'=>'The verification email could not be sent. Please try again later.'],503); }
        return response()->json(['otp_required'=>true,'challenge_id'=>$challengeId,'expires_in'=>600,'resend_after'=>60]);
    }
}
