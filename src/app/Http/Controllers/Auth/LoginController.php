<?php 
namespace App\Http\Controllers\Auth;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends BaseController
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function login(Request $request)
    {
        $this->validator($request->all())->validate();      
        
        if ($this->attemptLogin($request)) {
            return $this->sendSuccessLoginResponse();      
        }
        
        return $this->sendFailedLoginResponse();
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return JsonResponse(['message' => 'Successfully logged out']);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }

    protected function credentials(Request $request)
    {
        return $request->only('email', 'password');
    }
    
    protected function guard()
    {
        return Auth::guard();
    }    

    protected function sendSuccessLoginResponse()
    {
        $user = $this->guard()->user();
        $token = $user->createToken('authToken');

        $response = [
            'user' => $user,
            'token' => $token->plainTextToken
        ];
        return new JsonResponse([$response], 200);
    }

    protected function sendFailedLoginResponse()
    {
        throw ValidationException::withMessages([
           'email' => 'Invalid Credentials',
        ]);
    }
}