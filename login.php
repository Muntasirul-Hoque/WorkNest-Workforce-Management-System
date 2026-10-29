<?php
session_start();
require_once './includes/db_connection.php';
require_once './includes/functions.php';

$conn = getDBConnection();
$error = '';
$showForgotLink = false;
$recoveryUser = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username'] ?? '');
    $password = sanitizeInput($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = "Please enter username and password";
    } else {
        $sql = "SELECT * FROM User WHERE UserName = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($user = mysqli_fetch_assoc($result)) {
            if ($user['Status'] === 'Inactive') {
                // Blocked — deactivated account
                $error = "Your account has been deactivated. Please contact HR.";
            } elseif ($password === $user['Password']) {
                // Successful login
                $_SESSION['user_name']  = $user['UserName'];
                $_SESSION['user_role']  = $user['Role'];
                $_SESSION['first_name'] = $user['FirstName'] ?? 'User';
                $_SESSION['last_name']  = $user['LastName'] ?? '';

                unset($_SESSION['recovery_user']);
                unset($_SESSION['reset_user']);

                if ($user['Role'] === 'HR') {
                    header("Location: hr/hr_dashboard.php");
                    exit();
                } elseif (strpos($user['Role'], 'Manager') !== false) {
                    header("Location: manager/manager_dashboard.php");
                    exit();
                } else {
                    header("Location: employee/employee_dashboard.php");
                    exit();
                }
            } else {
                // Valid username, wrong password
                $error = "Invalid password.";
                $showForgotLink = true;
                $recoveryUser = $user['UserName'];
            }
        } else {
            // Username does not exist
            $error = "User not found.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WorkNest</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }
        .loginContainer {
            background: #fff;
            border-radius: 20px;
            padding: 48px 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .loginLogo {
            font-size: 32px;
            font-weight: 700;
            text-align: center;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .loginSubtitle {
            text-align: center;
            color: #64748b;
            font-size: 14px;
            margin-bottom: 32px;
        }
        .loginError {
            background: #fecaca;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .loginGroup { margin-bottom: 20px; }
        .loginLabel {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }
        .loginInput {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: border-color 0.2s ease;
        }
        .loginInput:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .loginForgot {
            text-align: right;
            margin-top: -12px;
            margin-bottom: 20px;
        }
        .loginForgot a {
            color: #667eea;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
        }
        .loginForgot a:hover { text-decoration: underline; }
        .loginBtn {
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
        .loginBtn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }
        .loginDemo {
            margin-top: 20px;
            padding: 16px;
            background: #f8fafc;
            border-radius: 10px;
            font-size: 13px;
            color: #64748b;
        }
        .loginDemo strong { color: #1a1a2e; }
    </style>
</head>
<body>
    <div class="loginContainer">
        <h1 class="loginLogo">WorkNest</h1>
        <p class="loginSubtitle">Real-Time Workforce Analytics & Management</p>

        <?php if (!empty($error)): ?>
            <div class="loginError"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="loginGroup">
                <label class="loginLabel">Username</label>
                <input type="text" name="username" class="loginInput" placeholder="Enter your username" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required>
            </div>
            <div class="loginGroup">
                <label class="loginLabel">Password</label>
                <input type="password" name="password" class="loginInput" placeholder="Enter your password" required>
            </div>

            <?php if ($showForgotLink): ?>
                <div class="loginForgot">
                    <a href="forgot_password.php?user=<?php echo urlencode($recoveryUser); ?>">Forgot Password?</a>
                </div>
            <?php endif; ?>

            <button type="submit" class="loginBtn">Sign In</button>
        </form>

        <div class="loginDemo">
            <strong>Demo Credentials:</strong><br>
            HR: hira_islam / 12345<br>
            Manager: manik_roy / 12345<br>
            Employee: limon_ahmed / 12345
        </div>
    </div>
</body>
</html>