<?php
use \Firebase\JWT\JWT;
use Dotenv\Dotenv;


class JWTUtility
{
    private static $secretKey;

    public static function initialize()
    {
        try {
            // Load environment variables correctly
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();

            // Fetch the secret key
            self::$secretKey = $_ENV['JWT_SECRET_KEY'] ?? getenv('JWT_SECRET_KEY');

            if (!self::$secretKey) {
                throw new Exception("JWT_SECRET_KEY is missing in the environment variables.");
            }
        } catch (Exception $e) {
            die("❌ " . $e->getMessage());
        }
    }

    public static function encode($data)
    {
        self::initialize(); // Ensure key is loaded

        try {
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600;

            $payload = [
                "iat" => $issuedAt,
                "exp" => $expirationTime,
                "data" => $data
            ];

            return JWT::encode($payload, self::$secretKey, 'HS256');
        } catch (Exception $e) {
            return "❌ Error generating JWT: " . $e->getMessage();
        }
    }

    public static function decode($jwt)
    {
        self::initialize(); // Ensure key is loaded

        try {
            return JWT::decode($jwt, new \Firebase\JWT\Key(self::$secretKey, 'HS256'));
        } catch (Exception $e) {
            return "❌ Error decoding JWT: " . $e->getMessage();
        }
    }
}
