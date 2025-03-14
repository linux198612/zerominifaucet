<?php

class Admin {
    private $db;
    private $username;
    private $password;

    public function __construct() {
        $this->db = new Database();
        $this->loadAdminCredentials();
    }

    private function loadAdminCredentials() {
        $this->username = $this->db->fetchOne("SELECT value FROM settings WHERE name = 'admin_username'");
        $this->password = $this->db->fetchOne("SELECT value FROM settings WHERE name = 'admin_password'");
    }

    public function isLoggedIn() {
        return isset($_SESSION['admin_logged_in']);
    }

    public function login($inputUser, $inputPass) {
        if ($inputUser === $this->username && password_verify($inputPass, $this->password)) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
        header("Location: admin.php");
        exit;
    }

    public function updateSettings($settings) {
        foreach ($settings as $key => $value) {
            $this->db->query("UPDATE settings SET value = ? WHERE name = ?", ["ss", $value, $key]);
        }
        return "Settings updated successfully!";
    }

    public function changePassword($newPassword) {
        $newPasswordHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $this->db->query("UPDATE settings SET value = ? WHERE name = 'admin_password'", ["s", $newPasswordHash]);
        return "Admin password updated successfully!";
    }

public function getSettings() {
    return $this->db->fetchSettings(); // Az új fetchSettings() metódust használja
}


}

