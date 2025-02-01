<?php
require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JWTUtility
{
    private static $secretKey;
    private static $algorithm = 'HS256';

    /**
     * Initialize the secret key from the environment variable
     */
    private static function init()
    {
        if (!self::$secretKey) {
            $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
            self::$secretKey = $_ENV['JWT_SECRET'];
        }
    }

    /**
     * Generate a JWT token.
     * @param array $payload
     * @param int $expiry (default: 1 hour)
     * @return string
     */
    public static function generateToken($payload, $expiry = 3600)
    {
        self::init();
        $payload['exp'] = time() + $expiry;
        return JWT::encode($payload, self::$secretKey, self::$algorithm);
    }

    /**
     * Verify and decode a JWT token.
     * @param string $token
     * @return object|false
     */
    public static function verifyToken($token)
    {
        self::init();
        try {
            return JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Extract token from Authorization header.
     * @return string|false
     */
    public static function getBearerToken()
    {
        $headers = getallheaders();
        if (isset($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
        return false;
    }

    /**
     * Validate user authentication based on the token in the Authorization header.
     * @return array|false
     */
    public static function authenticate()
    {
        $token = self::getBearerToken();
        if (!$token) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(["error" => "Access Denied. No token provided."]);
            exit;
        }
        $decoded = self::verifyToken($token);
        if (!$decoded) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(["error" => "Invalid or expired token."]);
            exit;
        }
        return (array) $decoded;
    }

    /**
     * Get the secret key for JWT encoding/decoding
     */
    public static function getSecretKey()
    {
        self::init();
        return self::$secretKey;
    }

    /**
     * Get the algorithm used for JWT encoding/decoding
     */
    public static function getAlgorithm()
    {
        return self::$algorithm;
    }

    /**
     * Validate a JWT token
     */
    public static function validateToken($token)
    {
        try {
            return self::verifyToken($token);
        } catch (Exception $e) {
            return false;
        }
    }
}
