<?php
session_start();
require_once './includes/db_connection.php';
require_once './includes/functions.php';

$conn = getDBConnection();
$error = '';
$success = '';

if (!isset($_SESSION['reset_user'])) {
    header("Location: login.php");
    exit();
}

$userName = $_SESSION['reset_user'];

$checkSql = "SELECT UserName FROM User WHERE UserName = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($checkStmt, "s", $userName);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if (!mysqli_fetch_assoc($checkResult)) {
    unset($_SESSION['reset_user']);
    header("Location: login.php?error=no_user");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword     = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';

    if (empty($newPassword) || empty($confirmPassword)) {
        $error = "Please fill in both fields.";
    } elseif (strlen($newPassword) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $sql = "UPDATE User SET Password = ? WHERE UserName = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $newPassword, $userName);

        if (mysqli_stmt_execute($stmt)) {
            unset($_SESSION['reset_user']);
            unset($_SESSION['recovery_user']);

            $success = "Password reset successfully! Redirecting to login...";
            header("Refresh: 2; url=login.php");
        } else {
            $error = "Error updating password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - WorkNest</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 20px;
        }
        .resetContainer {
            background: #fff;
            border-radius: 20px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .resetIcon { text-align: center; font-size: 48px; margin-bottom: 16px; }
        .resetLogo {
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .resetSubtitle {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 32px;
        }
        .resetError {
            background: #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .resetSuccess {
            background: #d1fae5;
            color: #059669;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .resetGroup { margin-bottom: 20px; }
        .resetLabel {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }
        .resetInput {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        .resetInput:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .resetBtn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            margin-top: 8px;
        }
        .resetBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .resetBack {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }
        .resetBack a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .resetBack a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="resetContainer">
        <div class="resetIcon">🔑</div>
        <h1 class="resetLogo">Reset Password</h1>
        <p class="resetSubtitle">Set a new password for <strong><?php echo htmlspecialchars($userName); ?></strong></p>

        <?php if ($error): ?>
            <div class="resetError"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="resetSuccess"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="POST" action="">
            <div class="resetGroup">
                <label class="resetLabel">New Password</label>
                <input type="password" name="newPassword" class="resetInput" placeholder="At least 6 characters" required minlength="6">
            </div>
            <div class="resetGroup">
                <label class="resetLabel">Confirm New Password</label>
                <input type="password" name="confirmPassword" class="resetInput" placeholder="Re-enter new password" required minlength="6">
            </div>
            <button type="submit" class="resetBtn">Reset Password</button>
        </form>
        <?php endif; ?>

        <div class="resetBack">
            <a href="login.php">← Back to Sign In</a>
        </div>
    </div>
</body>
</html>