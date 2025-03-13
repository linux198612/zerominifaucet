<?php

class Captcha {
    public static function generate() {
        $_SESSION['captcha'] = rand(1000, 9999);
    }

    public static function validate($input) {
        return isset($_SESSION['captcha']) && $input == $_SESSION['captcha'];
    }
}

?>