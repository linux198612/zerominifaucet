<?php
session_start();
include 'config.php';
include 'classes/Database.php';
include 'classes/Admin.php';

$admin = new Admin();

// Ha az admin kijelentkezik
if (isset($_GET['logout'])) {
    $admin->logout();
}

// Ha az admin még nincs bejelentkezve, kezeljük a belépést
if (!$admin->isLoggedIn()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
        if ($admin->login($_POST['username'], $_POST['password'])) {
            header("Location: admin.php");
            exit;
        } else {
            $loginError = "Invalid username or password!";
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="container mt-5">
            <div class="card p-4 shadow">
                <h2 class="text-center">Admin Login</h2>
                <?php if (isset($loginError)): ?>
                    <div class="alert alert-danger text-center"><?= $loginError; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Ha az admin be van jelentkezve, akkor jöhet a fő admin felület

// Admin beállítások módosítása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    $settings = [
        'site_name' => $_POST['site_name'],
        'zerochain_api' => $_POST['zc_api_key'],
        'zerochain_privatekey' => $_POST['pk'],
        'min_payout' => $_POST['min_payout'],
        'max_payout' => $_POST['max_payout'],
        'daily_limit' => $_POST['daily_limit'],
        'claim_interval' => $_POST['claim_interval']
    ];
    $successMessage = $admin->updateSettings($settings);
}

// Admin jelszó módosítása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password']) && !empty($_POST['new_password'])) {
    $successMessage = $admin->changePassword($_POST['new_password']);
}

// Betöltjük a beállításokat
$settings = $admin->getSettings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4 shadow">
            <h2 class="text-center">Admin Panel</h2>
            <hr>
            <div class="text-center">
                <a href="admin.php?logout=true" class="btn btn-danger">Logout</a>
            </div>
            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success text-center"><?= $successMessage; ?></div>
            <?php endif; ?>

            <form method="POST">
                <h4>API Settings</h4>
                <div class="mb-3">
                    <label class="form-label">ZeroChain API Key</label>
                    <input type="text" class="form-control" name="zc_api_key" value="<?= htmlspecialchars($settings['zerochain_api']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">ZeroChain Private Key</label>
                    <input type="text" class="form-control" name="pk" value="<?= htmlspecialchars($settings['zerochain_privatekey']); ?>" required>
                </div>

                <h4>Faucet Settings</h4>
                <div class="mb-3">
                    <label class="form-label">Site Name</label>
                    <input type="text" class="form-control" name="site_name" value="<?= htmlspecialchars($settings['site_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Min reward (zatoshi)</label>
                    <input type="number" class="form-control" name="min_payout" value="<?= $settings['min_payout']; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Max reward (zatoshi)</label>
                    <input type="number" class="form-control" name="max_payout" value="<?= $settings['max_payout']; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Daily Limit (0 = no daily limit)</label>
                    <input type="number" class="form-control" name="daily_limit" value="<?= $settings['daily_limit']; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Claim Interval (seconds) (0 = no timer)</label>
                    <input type="number" class="form-control" name="claim_interval" value="<?= $settings['claim_interval']; ?>" required>
                </div>

                <button type="submit" name="update_settings" class="btn btn-primary w-100">Update Settings</button>
            </form>

            <hr>

            <form method="POST">
                <h4>Change Admin Password</h4>
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" class="form-control" name="new_password" required>
                </div>
                <button type="submit" name="update_password" class="btn btn-danger w-100">Change Password</button>
            </form>
        </div>
    </div>
</body>
</html>
