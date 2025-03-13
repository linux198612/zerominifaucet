<?php
session_start();
include 'config.php'; // Database connection
include 'classes/Database.php'; 
include 'classes/Captcha.php'; 
include 'classes/Faucet.php'; 


// Kezeli a request-et
$faucet = new Faucet();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userAddress = $_POST['zero_address'] ?? '';
    $captcha = $_POST['captcha'] ?? '';
    $faucet->processClaim($userAddress, $captcha);
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

Captcha::generate();
$statusMessage = $faucet->getStatusMessage();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $faucet->getSetting('site_name') ?></title>
<style>
    /* Alap világos mód */
    html {
        background: #f4f4f4;
        color: #333;
    }

    /* Ha sötét mód be van állítva, azonnal alkalmazzuk */
    html.dark-mode {
        background: #121212;
        color: #e0e0e0;
    }
</style>

<script>
    (function() {
        const darkMode = localStorage.getItem("darkMode");
        if (darkMode === "enabled") {
            document.documentElement.classList.add("dark-mode");
        }
    })();
</script>


    <link rel="stylesheet" href="style.css?v=1.1"> <!-- Verziószámmal -->

</head>
<body>
    <div class="container">


        <div class="card">
            <div class="title-container">
                <h2 class="title"><?= $faucet->getSetting('site_name') ?></h2>
                <button id="theme-toggle" class="theme-btn">
                    <span id="theme-icon">🌙</span>
                </button>
            </div>
            <div class="status-message"><?= $statusMessage ?></div>
            
            <?php if (!empty($_SESSION['message'])): ?>
                <div class="alert"><?= $_SESSION['message'] ?></div>
                <?php unset($_SESSION['message']); ?>
            <?php endif; ?>

            <?php 
            $remainingTime = max(0, $faucet->getClaimInterval() - $faucet->getLastClaimTime());
            $remainingClaims = $faucet->getRemainingClaims();
            ?>

            <?php if ($remainingTime > 0): ?>
                <div class="timer">
                    Please wait <span id="countdown"><?= $remainingTime ?></span> seconds before claiming again.
                </div>
                <script>
                    var countdown = document.getElementById("countdown");
                    var seconds = parseInt(countdown.innerText);
                    var interval = setInterval(function () {
                        seconds--;
                        countdown.innerText = seconds;
                        if (seconds <= 0) {
                            clearInterval(interval);
                            location.reload();
                        }
                    }, 1000);
                </script>
<?php elseif ($remainingClaims <= 0 && $dailyLimit > 0): ?>
    <div class="limit-reached">Daily limit reached. No more claims available today.</div>
<?php else: ?>
    <form method="POST" class="faucet-form">
        <label for="zero_address">Zero Address</label>
        <input type="text" id="zero_address" name="zero_address" required>

        <label for="captcha">Enter Captcha: <strong><?= $_SESSION['captcha'] ?></strong></label>
        <input type="text" id="captcha" name="captcha" required>

        <button type="submit" class="claim-btn">Claim</button>
    </form>
<?php endif; ?>

        </div>

        <div class="card">
            <h4>Last 10 Claims</h4>
            <table>
                <thead>
                    <tr>
                        <th>Address</th>
                        <th>Amount</th>
                        <th>Transaction ID</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faucet->getLastClaims() as $claim): ?>
                        <tr>
                            <td><?= $claim['address']; ?></td>
                            <td><?= $claim['amount']; ?></td>
                            <td><?= $claim['txid']; ?></td>
                            <td><?= $claim['timestamp']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <footer>
            <p>&copy; <?php echo date('Y'); ?> <a href="./"><?= $faucet->getSetting('site_name') ?></a>. All Rights Reserved. Version: 0.02<br>Powered by <a href="https://coolscript.hu">CoolScript</a></p>
        </footer>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const themeToggle = document.getElementById("theme-toggle");
    const themeIcon = document.getElementById("theme-icon");
    const html = document.documentElement;

    // Ellenőrizzük a localStorage értékét és állítsuk be a megfelelő ikont
    if (localStorage.getItem("darkMode") === "enabled") {
        html.classList.add("dark-mode");
        themeIcon.textContent = "☀️"; // Világos mód ikon
    } else {
        themeIcon.textContent = "🌙"; // Sötét mód ikon
    }

    // Téma váltás eseménykezelő
    themeToggle.addEventListener("click", function () {
        html.classList.toggle("dark-mode");

        if (html.classList.contains("dark-mode")) {
            localStorage.setItem("darkMode", "enabled");
            themeIcon.textContent = "☀️"; // Világos mód ikon
        } else {
            localStorage.setItem("darkMode", "disabled");
            themeIcon.textContent = "🌙"; // Sötét mód ikon
        }
    });
});
</script>

</body>
</html>

