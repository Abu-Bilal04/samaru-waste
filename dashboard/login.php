<?php
// dashboard/login.php
session_start();
require_once '../config/db.php';
require_once '../config/roles.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = sanitize($_POST['user_id']);
    $password = $_POST['password'];
    
    // Check for user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE user_unique_id = ? AND role_level = 1");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($user) {
        // Verify Password
        // For legacy support (if password is null/empty during dev), we might allow simple access or force update. 
        // But for "no bugs", we enforce password check.
        if (isset($user['password']) && password_verify($password, $user['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['user_unique_id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid Password";
        }
    } else {
        $error = "Invalid Admin ID";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Samaru Waste</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center text-success mb-4">Samaru Admin</h3>
        <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Admin ID</label>
                <input type="text" name="user_id" class="form-control" placeholder="Enter ADMIN001" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Login</button>
        </form>
        
    </div>
</body>
</html>
