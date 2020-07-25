<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\JWTAuth;
use Tymon\JWTAuth\Manager;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * @var JWTAuth
     */
    protected $jwt;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * AuthController constructor.
     * 
     * @param JWTAuth $jwt
     * @param Manager $manager
     */
    public function __construct(JWTAuth $jwt, Manager $manager)
    {
        $this->jwt = $jwt;
        $this->manager = $manager;
    }

    /**
     * @param Request $request
     * @throws ValidationException
     */
    public function login(Request $request): JsonResponse
    {
        $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required',
        ]);

        try {
            if (!$token = $this->jwt->attempt($request->only('email', 'password'))) {
                return response()->json([
                    'error' => 'Invalid credentials',
                ], 404);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not create a token',
            ], 500);
        }

        return $this->respondWithToken($token);
    }

    /**
     * @throws ValidationException
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = $this->jwt->getToken();
            return $this->respondWithToken($this->jwt->refresh($token));
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Could not refresh the token',
            ], 500);
        }
    }

    /**
     * @param string $token
     */
    protected function respondWithToken($token): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => 60 * 60,
        ], 200);
    }
}
