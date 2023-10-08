<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Rules\RandomMathValidation;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function validateLogin(Request $request) {
        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
            'answer' => ['required', new RandomMathValidation],
        ];
        $customMessages = [
            'answer.required' => 'Please answer the math question.',
            'answer.random_math_validation' => 'The math question was not answered correctly.',
        ];
        $this->validate($request, $rules, $customMessages);
    }

    public function showLoginForm() {
        // Generate a random math challenge
        $firstNumber = random_int(1, 10);
        $secondNumber = random_int(1, 10);
        $correctAnswer = $firstNumber + $secondNumber;

        // Store the correct answer in the session
        session(['correct_math_answer' => $correctAnswer]);

        return view('auth.login', compact('firstNumber', 'secondNumber'));
    }

    protected function attemptLogin(Request $request) {
        $credentials = $this->credentials($request);

        if ($this->guard()->attempt($credentials, $request->filled('remember'))) {
            $this->clearLoginAttempts($request);

            return true;
        }
        $this->incrementLoginAttempts($request);
        return false;
    }

    protected function incrementLoginAttempts(Request $request) {
            $maxAttempts = 3; // Jumlah maksimum percobaan gagal

            $user = User::where('email', $request->email)->first();

            if ($user)  {
                $login_attempts = $user->login_attempts;
                if ($login_attempts > $maxAttempts) {
                    $this->fireLockoutEvent($request);
                    $this->sendLockoutResponse($request);
                } else {
                    $user->login_attempts = $login_attempts + 1;
                    $user->last_failed_login = now();
                    $user->save();
                    error_log($user->login_attempts);
                }
            }
        }
    protected function sendLockoutResponse(Request $request) {
        $seconds = 30; // Jangka waktu dalam detik
    
        $message = trans('auth.throttle', ['seconds' => $seconds]);
    
        throw ValidationException::withMessages([
            $this->username() => [$message],
        ])->status(429);
    }

    protected function clearLoginAttempts(Request $request){
        $user = User::where('email', $request->email)->first();

        if ($user) {
            $user->login_attempts = 0;
            $user->last_failed_login = null;
            $user->save();
        }
    }
}
