<?php
// config/JWT.php
use \Firebase\JWT\JWT;

class JWTUtility
{
    private static $secretKey; // Will be initialized from environment variable

    public function __construct()
    {
        // Loading environment variable (make sure you use dotenv to load .env file)
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
        self::$secretKey = getenv('JWT_SECRET_KEY'); // Get the secret key from .env
    }

    // Function to generate a JWT token
    public static function encode($data)
    {
        $issuedAt = time();
        $expirationTime = $issuedAt + 3600;  // JWT valid for 1 hour from the issued time
        $payload = [
            "iat" => $issuedAt, // Issued At
            "exp" => $expirationTime, // Expiration Time
            "data" => $data // Custom data (e.g., user info)
        ];

        return JWT::encode($payload, self::$secretKey, 'HS256');
    }

    // Function to decode the JWT token
    public static function decode($jwt)
    {
        try {
            $decoded = JWT::decode($jwt, new \Firebase\JWT\Key(self::$secretKey, 'HS256'));
            return (array) $decoded->data; // Return decoded data as an array
        } catch (Exception $e) {
            return null; // Token is invalid or expired
        }
    }
}
