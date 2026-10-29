<?php
session_start();
require_once './includes/db_connection.php';
require_once './includes/functions.php';

$conn = getDBConnection();
$error = '';

// Read username from URL
$userName = isset($_GET['user']) ? sanitizeInput($_GET['user']) : '';

// If no username, bounce back to login
if (empty($userName)) {
    header("Location: login.php");
    exit();
}

// Verify the username exists
$checkSql = "SELECT UserName FROM User WHERE UserName = ?";
$checkStmt = mysqli_prepare($conn, $checkSql);
mysqli_stmt_bind_param($checkStmt, "s", $userName);
mysqli_stmt_execute($checkStmt);
$checkResult = mysqli_stmt_get_result($checkStmt);

if (!mysqli_fetch_assoc($checkResult)) {
    header("Location: login.php?error=no_user");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $phone = sanitizeInput($_POST['phone'] ?? '');

    if (empty($email) || empty($phone)) {
        $error = "Please enter both email and phone number.";
    } else {
        $sql = "SELECT UserName FROM User WHERE UserName = ? AND Email = ? AND Phone = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $userName, $email, $phone);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            $_SESSION['reset_user'] = $user['UserName'];
            header("Location: reset_password.php");
            exit();
        } else {
            $error = "Recovery of account failed. The email and phone number do not match our records.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WorkNest</title>
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
        .forgotContainer {
            background: #fff;
            border-radius: 20px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .forgotIcon { text-align: center; font-size: 48px; margin-bottom: 16px; }
        .forgotLogo {
            font-size: 28px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .forgotSubtitle {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 24px;
        }
        .forgotInfo {
            text-align: center;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 24px;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
        }
        .forgotError {
            background: #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .forgotGroup { margin-bottom: 20px; }
        .forgotLabel {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }
        .forgotInput {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        .forgotInput:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .forgotBtn {
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
        .forgotBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .forgotBack {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }
        .forgotBack a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .forgotBack a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="forgotContainer">
        <div class="forgotIcon">🔐</div>
        <h1 class="forgotLogo">Forgot Password?</h1>
        <p class="forgotSubtitle">
            Verify your identity to reset your password for
            <strong><?php echo htmlspecialchars($userName); ?></strong>
        </p>

        <div class="forgotInfo">
            Enter the <strong>email</strong> and <strong>phone number</strong> registered with your account.
        </div>

        <?php if ($error): ?>
            <div class="forgotError"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="forgotGroup">
                <label class="forgotLabel">Email Address</label>
                <input type="email" name="email" class="forgotInput"  required>
            </div>
            <div class="forgotGroup">
                <label class="forgotLabel">Phone Number</label>
                <input type="text" name="phone" class="forgotInput" required>
            </div>
            <button type="submit" class="forgotBtn">Verify &amp; Continue</button>
        </form>

        <div class="forgotBack">
            <a href="login.php">← Back to Sign In</a>
        </div>
    </div>
</body>
</html>