<?php
class Csrf {
    public static function generateToken() {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    public static function getToken() {
        return $_SESSION['csrf_token'] ?? '';
    }

    public static function validateToken($token) {
        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }
}
