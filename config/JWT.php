<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

class JWTUtility
{
    private static $secretKey = null;
    private static $algorithm = 'HS256';
    private static $tokenExpiration = 3600; // 1 hour in seconds

    public static function initialize()
    {
        if (self::$secretKey === null) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
            $dotenv->load();
            self::$secretKey = $_ENV['JWT_SECRET_KEY'];
            self::$algorithm = $_ENV['JWT_ALGORITHM'];
        }
    }

    public static function getSecretKey()
    {
        self::initialize();
        return self::$secretKey;
    }

    public static function getAlgorithm()
    {
        return self::$algorithm;
    }

    public static function encode($payload)
    {
        self::initialize();
        $issuedAt = time();
        $expire = $issuedAt + self::$tokenExpiration;

        $tokenPayload = array(
            "iat" => $issuedAt,
            "exp" => $expire,
            "data" => $payload
        );

        return JWT::encode($tokenPayload, self::$secretKey, self::$algorithm);
    }

    public static function decode($token)
    {
        self::initialize();
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            if ($decoded->exp < time()) {
                return null; // Token has expired
            }
            return $decoded->data;
        } catch (Exception $e) {
            return null;
        }
    }

    public static function getBearerToken()
    {
        $headers = apache_request_headers();
        if (isset($headers['Authorization']) && preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
        return null;
    }

    public static function isTokenExpired($token)
    {
        try {
            $decoded = JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
            return $decoded->exp < time();
        } catch (Exception $e) {
            return true;
        }
    }
}
