<?php
session_start();
require_once 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hard-coded admin credentials
    if ($email === "admin@admin.com" && $password === "admin123") {
        $_SESSION['user'] = [
            'id_user' => 0,
            'nom_user' => 'Admin',
            'email' => 'admin@admin.com',
            'role' => 'admin'
        ];
        header("Location: dashboard.php");
        exit();
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            $_SESSION['user'] = $user;
            // Redirect based on role
            if ($user['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: dashboarduser.php");
            }
            exit();
        } else {
            $error_message = "Invalid email or password";
        }
    } catch(PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#1e2936]">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full space-y-8 p-10 bg-white rounded-xl shadow-2xl">
            <div class="flex items-center justify-center">
                <span class="text-2xl font-bold text-green-500">IT</span>
                <span class="text-2xl font-bold">THINK</span>
            </div>
            <div>
                <h2 class="mt-2 text-center text-3xl font-bold text-gray-900">
                    Sign in 
                </h2>
            </div>
            
            <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <?php endif; ?>

            <form class="mt-8 space-y-6" action="" method="POST">
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                        <input id="email" name="email" type="email" required 
                            class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" 
                            placeholder="Enter your email">
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" name="password" type="password" required 
                            class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" 
                            placeholder="Enter your password">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Sign in
                    </button>
                    <div class="text-center pt-6">
                        <a href="signup.php" class="text-sm text-green-900 hover:text-green-500">
                            Don't have an account? Sign up
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</body>
</html>