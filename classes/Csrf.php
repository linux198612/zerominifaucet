<?php
class Csrf {
    public static function generateToken() {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(); // Biztosítjuk, hogy a session elinduljon
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public static function getToken() {
        return $_SESSION['csrf_token'] ?? '';
    }

    public static function validateToken($token) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(); // Biztosítjuk, hogy a session elinduljon
        }
        if (empty($_SESSION['csrf_token'])) {
            throw new Exception('CSRF token is missing!');
        }
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            throw new Exception('Invalid CSRF token!');
        }
        return true;
    }
}
?>
