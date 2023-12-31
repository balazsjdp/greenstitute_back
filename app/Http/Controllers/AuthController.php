<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Validator;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
    	$validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Az email cím vagy jelszó nem megfelelő!'], 422);
        }
        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['message' => 'Az email cím vagy jelszó nem megfelelő!'], 401);
        }
        return $this->createNewToken($token);
    }
    /**
     * Register a User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {

        // Check if an admin is trying to register (if the company name is equals to the 'ADMIN_SECRET')
        $registrationData = $request->all();
        $isAdmin = false;
        if($registrationData['company_name'] == env('ADMIN_SECRET'))
        {
            $isAdmin = true;
        }

        //return response()->json(['data' => $registrationData, 'secret' => env('ADMIN_SECRET')]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
            'password_confirmation' => 'confirmed',
            'company_name' => 'required|string|between:2,200',
            'company_address' => 'required|string|between:2,300',
            'company_id' => 'required|string|unique:users|size:12',
            'company_tax_number' => 'required|string|unique:users|between:10,13',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }



        $user = User::create(array_merge(
                    $validator->validated(),
                    ['is_admin' => $isAdmin],
                    ['password' => bcrypt($request->password)]
                ));
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user
        ], 201);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout() {
        auth()->logout();
        return response()->json(['message' => 'User successfully signed out']);
    }
    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh() {
        return $this->createNewToken(auth()->refresh());
    }
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json(auth()->user()->load('certificationRequest'));
    }

    public function userProfileById($id)
    {
        $this->authorize('viewAny', User::class);

        $user = User::find($id);

        return response()->json($user->load('certificationRequest'));
    }
    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'authToken' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }

    protected function checkToken()
    {
        return response()->json(['valid' => true]);
    }
}