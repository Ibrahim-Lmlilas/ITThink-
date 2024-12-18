<?php
require_once 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom_user = $_POST['nom_user'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $check_stmt->bindParam(':email', $email);
        $check_stmt->execute();

        if ($check_stmt->rowCount() > 0) {
            $error_message = "Email already exists!";
        } else {
            // Insert new user
            $stmt = $conn->prepare("INSERT INTO users (nom_user, email, password) VALUES (:nom_user, :email, :password)");
            $stmt->bindParam(':nom_user', $nom_user);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':password', $password);
            $stmt->execute();

            // Get the user data and start session
            $user_stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $user_stmt->bindParam(':email', $email);
            $user_stmt->execute();
            $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

            session_start();
            $_SESSION['user'] = $user;
            
            // Redirect to dashboard directly
            header("Location: dashboarduser.php");
            exit();
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
    <title>ITThink - Sign Up</title>
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
                    Create your account
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
                        <label for="nom_user" class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input id="nom_user" name="nom_user" type="text" required 
                            class="mt-1 block w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500" 
                            placeholder="Enter your name">
                    </div>
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
                            placeholder="Create a password">
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Sign up
                    </button>
                </div>

                <div class="text-center">
                    <a href="login.php" class="text-sm text-gray-900 hover:text-gray-500">
                        Already have an account? Sign in
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>