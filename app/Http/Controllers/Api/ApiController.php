<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;
use Lcobucci\JWT\Configuration;

class ApiController extends Controller
{
    // POST [name, email, password]
    public function register(Request $request)
    {
        // Validation
        $request->validate([
            "name" => "required|string",
            "email" => "required|string|email|unique:users",
            "password" => "required|confirmed"
        ]);

        // Create User
        User::create([
            "name" => $request->name,
            "email" => $request->email,
            "password" => bcrypt($request->password)
        ]);

        return response()->json([
            "status" => true,
            "message" => "User registered successfully",
            "data" => []
        ]);
    }

    // POST [email, password]
    public function login(Request $request)
    {
        $request->validate([
            "email" => "required|email|string",
            "password" => "required|string"
        ]);

        // User object
        $user = User::where("email", $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            // Password matched
            $token = $user->createToken("mytoken")->accessToken;
            // Read existing tokens from the file
            $filePath = storage_path('app/tokens.json');
            $tokens = [];
            if (File::exists($filePath)) {
                $tokens = json_decode(File::get($filePath), true);
            }

            // Add new token to the list
            $tokens[] = [
                'token' => $token,
                'client_id' => $user->id, // or 'client_name' => $user->name
                'expiry' => now()->addMinutes(1)->toDateTimeString(), // Example: 1 minute expiry
            ];

            // Store updated tokens list in the file
            File::put($filePath, json_encode($tokens));

            return response()->json([
                "status" => true,
                "message" => "Login successful",
                "token" => $token,
                "data" => []
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Invalid email or password",
                "data" => []
            ]);
        }
    }

    // GET [Auth: Token]
    public function profile()
    {
        $user = Auth::user();

        return response()->json([
            "status" => true,
            "message" => "User profile retrieved successfully",
            "data" => $user
        ]);
    }

    // POST [Auth: Token]
    public function logout()
    {
        $token = Auth::user()->token();
        $token->revoke();

        return response()->json([
            "status" => true,
            "message" => "User logged out successfully"
        ]);
    }

    // GET [Auth: Token]
    public function verifyToken(Request $request)
    {
        $token = $request->bearerToken();
        $config = Configuration::forAsymmetricSigner(
            new \Lcobucci\JWT\Signer\Rsa\Sha256(),
            \Lcobucci\JWT\Signer\Key\InMemory::file(storage_path('oauth-private.key')),
            \Lcobucci\JWT\Signer\Key\InMemory::file(storage_path('oauth-public.key'))
        );
        $parsedToken = $config->parser()->parse($token);
        $tokenId = $parsedToken->claims()->get('jti');
        $token = Token::find($tokenId);

        if ($token && !$token->revoked) {
            $user = $token->user;
            return response()->json(['status' => true, 'user' => $user]);
        } else {
            return response()->json(['status' => false, 'message' => 'Token is invalid or expired']);
        }
    }
    public function introspectToken(Request $request)
    {
        $token = $request->input('token');
        $tokenRepository = app(TokenRepository::class);
        $token = $tokenRepository->find($token);

        if ($token && !$token->revoked) {
            return response()->json([
                'active' => true,
                'scope' => $token->scopes,
                'client_id' => $token->client_id,
                'username' => $token->user_id,
                'exp' => $token->expires_at->timestamp,
            ]);
        }

        return response()->json(['active' => false]);
    }

}
