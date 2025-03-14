<?php

class Faucet {
    private $db;
    private $settings;
    private $ip;

    public function __construct() {
        $this->db = new Database();
        $this->settings = $this->db->fetchSettings(); // Most már a helyes metódust használjuk
        $this->ip = $_SERVER['REMOTE_ADDR'];
    }

    public function getSetting($key, $default = null) {
        return isset($this->settings[$key]) ? $this->settings[$key] : $default;
    }

    public function getClaimInterval() {
        return (int) $this->getSetting('claim_interval', 0);
    }

    public function getDailyLimit() {
        return (int) $this->getSetting('daily_limit', 0);
    }

    public function getRemainingClaims() {
        $claims_today = $this->db->fetchOne(
            "SELECT COUNT(*) FROM withdraw_history WHERE ip = ? AND DATE(FROM_UNIXTIME(timestamp)) = CURDATE()",
            ["s", $this->ip]
        );
        return max(0, $this->getDailyLimit() - $claims_today);
    }

public function getLastClaimTime() {
    $lastClaim = $this->db->fetchOne(
        "SELECT timestamp FROM withdraw_history WHERE ip = ? ORDER BY timestamp DESC LIMIT 1",
        ["s", $this->ip]
    );
    return $lastClaim ? time() - (int) $lastClaim : PHP_INT_MAX; // Ha nincs claim, nagyon nagy számot ad vissza.
}

    public function processClaim($userAddress, $captcha) {
        $userAddress = filter_var($userAddress, FILTER_SANITIZE_STRING);
        if (!Captcha::validate($captcha)) {
            Captcha::generate();
            $_SESSION['message'] = "Invalid captcha! Please try again.";
            return false;
        }

        Captcha::generate();
        $balance = random_int($this->getSetting('min_payout'), $this->getSetting('max_payout')) / 100000000;
        $apiKey = $this->getSetting('zerochain_api');
        $privateKey = $this->getSetting('zerochain_privatekey');

        $result = file_get_contents("https://zerochain.info/api/rawtxbuild/{$privateKey}/{$userAddress}/{$balance}/0/1/{$apiKey}");
        $txID = $this->extractTxID($result);

        if ($txID) {
            $this->db->query(
                "INSERT INTO withdraw_history (ip, address, amount, txid, status, timestamp) VALUES (?, ?, ?, ?, ?, UNIX_TIMESTAMP(NOW()))",
                ["ssdss", $this->ip, $userAddress, $balance, $txID, "Paid"]
            );
            $_SESSION['message'] = "Successful payment: " . number_format($balance, 8) . " ZER to " . htmlspecialchars($userAddress);
            return true;
        } else {
        $_SESSION['message'] = "Payment error! The ZeroChain API responded, but the transaction could not be completed. This might be due to network congestion. Please try again later.";
        return false;
        }
    }

    private function extractTxID($result) {
        if (strpos($result, '"txid":"') !== false) {
            $pieces = explode('"txid":"', $result);
            return explode('"', $pieces[1])[0];
        }
        return "";
    }

public function getStatusMessage() {
    $dailyLimit = $this->getDailyLimit();
    $remainingClaims = $this->getRemainingClaims();
    $claimInterval = $this->getClaimInterval();
    $remainingTime = max(0, $claimInterval - $this->getLastClaimTime());

    // Ha a napi limit elérve, jelezzük a felhasználónak
    if ($dailyLimit > 0 && $remainingClaims == 0) {
        return "Daily limit reached. You cannot claim more today.";
    }

    // Ha nincs napi limit és nincs időkorlát
    if ($dailyLimit == 0 && $claimInterval == 0) {
        return "No daily limit and no timer, claim anytime!";
    }

    // Ha van napi limit, de nincs időkorlát
    if ($dailyLimit > 0 && $claimInterval == 0) {
        return "$remainingClaims claims remaining out of $dailyLimit per day, no timer.";
    }

    // Ha nincs napi limit, de van időkorlát
    if ($dailyLimit == 0 && $claimInterval > 0) {
        return "No daily limit, claim every $claimInterval seconds.";
    }

    // Ha mindkettő van
    return "$remainingClaims claims remaining out of $dailyLimit per day, claim every $claimInterval seconds.";
}


    public function getLastClaims($limit = 10) {
        $claims = $this->db->fetchAllAssoc(
            "SELECT address, amount, txid, timestamp FROM withdraw_history ORDER BY timestamp DESC LIMIT ?",
            ["i", $limit]
        );

        foreach ($claims as &$claim) {
            $claim['address'] = substr($claim['address'], 0, 10) . '...';
            $claim['amount'] = number_format($claim['amount'], 8) . " ZER";
            $claim['txid'] = '<a href="https://zerochain.info/tx/' . htmlspecialchars($claim['txid']) . '" target="_blank" style="color:#369cf6;">' . substr($claim['txid'], 0, 10) . '...</a>';
            
            // Timestamp ellenőrzése és konvertálása
            $timestamp = is_numeric($claim['timestamp']) ? (int) $claim['timestamp'] : time();
            $claim['timestamp'] = $this->timeElapsedString($timestamp);
        }
        return $claims;
    }

    private function timeElapsedString($timestamp) {
        if (!is_numeric($timestamp)) {
            return "Invalid timestamp";
        }

        $now = new DateTime();
        $ago = new DateTime();
        $ago->setTimestamp((int) $timestamp);

        $diff = $now->diff($ago);

        $string = ['y' => 'year', 'm' => 'month', 'd' => 'day', 'h' => 'hour', 'i' => 'minute', 's' => 'second'];
        foreach ($string as $key => &$value) {
            if ($diff->$key) {
                $value = $diff->$key . ' ' . $value . ($diff->$key > 1 ? 's' : '');
            } else {
                unset($string[$key]);
            }
        }
        return $string ? implode(', ', array_slice($string, 0, 1)) . ' ago' : 'Just now';
    }
}


?>

