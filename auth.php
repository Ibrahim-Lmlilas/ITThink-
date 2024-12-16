<?php
// ========== INITIALIZATION ==========
// Kan7tajo database.php bach nconnectiw m3a database dyalna
require_once 'database.php';

// Kanbdaw session bach n9dro n7tafdo b les informations dial user
session_start();

// Kan initialisiw les variables li ghadin n7tajo
// Variable dial error ila kan chi mochkil
$error_message = '';
// Variable dial message successful ila dar chi 7aja mziana
$success_message = '';

// Kan créeyiw instance dial database bach nconnectiw
$database = new Database();
// Kan jibo connection m3a database
$conn = $database->getConnection();

// Kan chofo wach l'user baghi y login wla y signup (par défaut kandiro login)
$form_type = isset($_GET['type']) ? $_GET['type'] : 'login';

// ========== FORM PROCESSING ==========
// Kan checkyiw wach l'utilisateur صيفط chi form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kan n9iw email mn ay 7aja dangerous
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    // Kanjibo password kima howa
    $password = $_POST['password'];

    // ========== LOGIN LOGIC ==========
    if ($form_type === 'login') {
        // Direct check for admin credentials
        if ($email === 'admin@admin.com' && $password === 'admin-123') {
            $_SESSION['user_id'] = 'admin';
            $_SESSION['email'] = $email;
            header("Location: dashboard.php");
            exit();
        }
        
        try {
            // Kan prepariw query bach nverifiw email
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            // Kan checkyiw wach l9ina user
            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Kan verifiw password
                if (password_verify($password, $user['password'])) {
                    // Kan stockiw info dial user f session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    
                    // For regular users, redirect to index
                    header("Location: index.php");
                    exit();
                } else {
                    $error_message = "Password machi s7i7";
                }
            } else {
                $error_message = "Email makaynch";
            }
        } catch(PDOException $e) {
            $error_message = "Error f connection: " . $e->getMessage();
        }
    } 
    // ========== SIGNUP LOGIC ==========
    else {
        // Kanjibo password dial confirmation
        $confirm_password = $_POST['confirm_password'];
        
        // Kan checkyiw wach passwords b7al b7al
        if ($password !== $confirm_password) {
            $error_message = "Passwords machi b7al b7al";
        } else {
            try {
                // Kan checkyiw wach email déjà kayn
                $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
                $stmt->bindParam(":email", $email);
                $stmt->execute();

                if ($stmt->fetch()) {
                    $error_message = "Had email déjà kayn";
                } else {
                    // Kan hashhiw password bach mayt7fatc clear f database
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Kan prepariw query bach n créeyiw user jdid
                    $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
                    $stmt->bindParam(":email", $email);
                    $stmt->bindParam(":password", $hashed_password);
                    
                    if ($stmt->execute()) {
                        $success_message = "Compte tcrya! Dkhl daba";
                        header("refresh:2;url=auth.php?type=login");
                    } else {
                        $error_message = "Error f creation dial compte";
                    }
                }
            } catch(PDOException $e) {
                // Ila table users makaynach, kan créeyiwha automatiquement
                if ($e->getCode() == '42S02') {
                    try {
                        // Query bach n créeyiw table users
                        $sql = "CREATE TABLE users (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            email VARCHAR(255) NOT NULL UNIQUE,
                            password VARCHAR(255) NOT NULL,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        )";
                        $conn->exec($sql);
                        // Kan3awdo n7awlo n créeyiw compte
                        header("Location: " . $_SERVER['REQUEST_URI']);
                        exit();
                    } catch(PDOException $e2) {
                        $error_message = "Error f creation dial table: " . $e2->getMessage();
                    }
                } else {
                    $error_message = "Error f connection: " . $e->getMessage();
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($form_type); ?> - ITTHInkk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Kan configiw colors dial Tailwind
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-dark': '#1e2532',
                        'custom-sidebar': '#262d3d',
                        'custom-green': '#22c55e',
                        'custom-card': '#ffffff',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-custom-dark min-h-screen flex items-center justify-center p-4">
    <!-- Container Principal -->
    <div class="w-full max-w-md">
        <!-- Logo o Title -->
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-white flex items-center justify-center gap-2">
                <span class="text-custom-green">IT</span>THInkk
            </h1>
        </div>

        <!-- Card dial Login/Signup -->
        <div class="bg-custom-sidebar rounded-xl shadow-2xl p-8 border border-gray-700">
            <!-- Tabs -->
            <div class="flex mb-8 bg-custom-dark/50 rounded-lg p-1">
                <a href="?type=login" 
                   class="flex-1 text-center py-2 rounded-md transition-all duration-300 <?php echo $form_type === 'login' 
                   ? 'bg-custom-green text-white' 
                   : 'text-gray-400 hover:text-white'; ?>">
                    Login
                </a>
                <a href="?type=signup" 
                   class="flex-1 text-center py-2 rounded-md transition-all duration-300 <?php echo $form_type === 'signup' 
                   ? 'bg-custom-green text-white' 
                   : 'text-gray-400 hover:text-white'; ?>">
                    Inscription
                </a>
            </div>

            <!-- Messages -->
            <?php if ($error_message): ?>
                <div class="mb-4 p-3 bg-red-500/10 text-red-500 rounded-md text-sm">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="mb-4 p-3 bg-custom-green/10 text-custom-green rounded-md text-sm">
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="?type=<?php echo $form_type; ?>" class="space-y-6">
                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-300 mb-1">
                        Email
                    </label>
                    <input type="email" id="email" name="email" required
                           class="w-full px-4 py-2 bg-custom-dark border border-gray-700 rounded-lg 
                                  text-white placeholder-gray-500 focus:outline-none focus:border-custom-green
                                  focus:ring-1 focus:ring-custom-green transition-all duration-300">
                </div>

                <!-- Password Input -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">
                        Password
                    </label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-2 bg-custom-dark border border-gray-700 rounded-lg 
                                  text-white placeholder-gray-500 focus:outline-none focus:border-custom-green
                                  focus:ring-1 focus:ring-custom-green transition-all duration-300">
                </div>

                <!-- Confirm Password (ghir f signup) -->
                <?php if ($form_type === 'signup'): ?>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1">
                            Confirmer Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" required
                               class="w-full px-4 py-2 bg-custom-dark border border-gray-700 rounded-lg 
                                      text-white placeholder-gray-500 focus:outline-none focus:border-custom-green
                                      focus:ring-1 focus:ring-custom-green transition-all duration-300">
                    </div>
                <?php endif; ?>

                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full py-2 px-4 bg-custom-green text-white rounded-lg 
                               hover:bg-custom-green/90 focus:outline-none focus:ring-2 
                               focus:ring-custom-green focus:ring-offset-2 focus:ring-offset-custom-dark
                               transition-all duration-300 font-medium">
                    <?php echo $form_type === 'login' ? 'login' : 'Inscription'; ?>
                </button>
            </form>

            
        </div>
    </div>
</body>
</html>
