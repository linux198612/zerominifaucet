<?php

class Database {
    private $mysqli;

    public function __construct() {
        $this->mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->mysqli->connect_error) {
            die("Database connection failed: " . $this->mysqli->connect_error);
        }
    }

    public function query($query, $params = []) {
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            die("SQL Error: " . $this->mysqli->error);
        }

        if (!empty($params)) {
            $stmt->bind_param(...$params);
        }

        $stmt->execute();
        return $stmt; 
    }

    public function fetchOne($query, $params = []) {
        $stmt = $this->query($query, $params);
        $result = null;
        $stmt->bind_result($result);
        $stmt->fetch();
        $stmt->close();
        return $result;
    }

    // Ezt használjuk a `settings` táblánál
    public function fetchSettings() {
        $stmt = $this->query("SELECT name, value FROM settings");
        $result = [];
        $stmt->bind_result($name, $value);
        while ($stmt->fetch()) {
            $result[$name] = $value;
        }
        $stmt->close();
        return $result;
    }

    // Ezt használjuk a `withdraw_history` és más normál táblákhoz
    public function fetchAllAssoc($query, $params = []) {
        $stmt = $this->query($query, $params);
        $result = $stmt->get_result();
        $data = [];

        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $stmt->close();
        return $data;
    }
}


?>