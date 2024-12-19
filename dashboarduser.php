<?php
session_start();
require_once 'database.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Handle project creation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];

    try {
        $stmt = $conn->prepare("INSERT INTO Projects (title, description, category_id, id_user) VALUES (:title, :description, :category_id, :id_user)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':id_user', $user['id_user']);
        $stmt->execute();
        header("Location: dashboarduser.php");
        exit();
    } catch(PDOException $e) {
        $error_message = "Error creating project: " . $e->getMessage();
    }
}

// Get categories
$cat_stmt = $conn->prepare("SELECT * FROM Categories");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's projects
$proj_stmt = $conn->prepare("SELECT p.*, c.category_name FROM Projects p LEFT JOIN Categories c ON p.category_id = c.category_id WHERE p.id_user = :id_user");
$proj_stmt->bindParam(':id_user', $user['id_user']);
$proj_stmt->execute();
$projects = $proj_stmt->fetchAll(PDO::FETCH_ASSOC);

// Count total projects
$total_projects = count($projects);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITThink Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-[#1e2936]">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-[#1e2936] border-r border-gray-700">
            <div class="flex items-center p-4">
                <span class="text-2xl font-bold text-green-500">IT</span>
                <span class="text-2xl font-bold text-white">THINK</span>
            </div>
            <div class="px-4 py-2">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                        <?php echo strtoupper(substr($user['nom_user'], 0, 1)); ?>
                    </div>
                    <div>
                        <p class="text-white font-medium"><?php echo htmlspecialchars($user['nom_user']); ?></p>
                        <p class="text-gray-400 text-sm"><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                </div>
            </div>
            <nav class="mt-8">
                <a href="#" class="flex items-center px-6 py-3 text-white bg-gray-800">
                    <i class="fas fa-home mr-3"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-800">
                    <i class="fas fa-project-diagram mr-3"></i>
                    <span>Projects</span>
                </a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-800">
                    <i class="fas fa-laptop-code mr-3"></i>
                    <span>Freelancers</span>
                </a>
                <a href="logout.php" class="flex items-center px-6 py-3 text-red-400 hover:bg-gray-800">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
            <!-- Top Navigation -->
            <div class="bg-[#1e2936] border-b border-gray-700 p-4">
                <div class="flex justify-between items-center">
                    <h1 class="text-xl font-bold text-white">Dashboard</h1>
                    <div class="flex items-center space-x-4">
                        <div class="relative">
                            <input type="text" placeholder="Search..." 
                                class="bg-gray-800 text-white px-4 py-2 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                            <i class="fas fa-search absolute right-3 top-3 text-gray-400"></i>
                        </div>
                        <button onclick="document.getElementById('createProjectModal').classList.remove('hidden')"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg flex items-center">
                            <i class="fas fa-plus mr-2"></i> New Project
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-gray-500 text-sm">Total Projects</p>
                                <p class="text-2xl font-bold"><?php echo $total_projects; ?></p>
                            </div>
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">30%</span>
                        </div>
                    </div>
                    <!-- Add more stat cards as needed -->
                </div>

                <!-- Projects Table -->
                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-bold">Recent Projects</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php foreach ($projects as $project): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-900"><?php echo htmlspecialchars($project['title']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($project['description']); ?></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                            <?php echo htmlspecialchars($project['category_name']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">Active</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex space-x-3">
                                            <button class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Project Modal -->
    <div id="createProjectModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-8 max-w-md w-full">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold">Create New Project</h3>
                <button onclick="document.getElementById('createProjectModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project Title</label>
                        <input type="text" name="title" required
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" required rows="3"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" required
                            class="mt-1 w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('createProjectModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                        Create Project
                    </button>
      </div>
            </form>
        </div>
    </div>
    project.style.display = '';
            } else {
                project.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html><?php
session_start();
require_once 'database.php';

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user = $_SESSION['user'];

// Handle project creation
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $category_id = $_POST['category_id'];

    try {
        $stmt = $conn->prepare("INSERT INTO Projects (title, description, category_id, id_user) VALUES (:title, :description, :category_id, :id_user)");
        $stmt->bindParam(':title', $title);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category_id', $category_id);
        $stmt->bindParam(':id_user', $user['id_user']);
        $stmt->execute();
        header("Location: dashboarduser.php");
        exit();
    } catch(PDOException $e) {
        $error_message = "Error creating project: " . $e->getMessage();
    }
}

// Get categories
$cat_stmt = $conn->prepare("SELECT * FROM Categories");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user's projects
$proj_stmt = $conn->prepare("SELECT p.*, c.category_name FROM Projects p LEFT JOIN Categories c ON p.category_id = c.category_id WHERE p.id_user = :id_user");
$proj_stmt->bindParam(':id_user', $user['id_user']);
$proj_stmt->execute();
$projects = $proj_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo htmlspecialchars($user['nom_user']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-green-500">IT</span>
                    <span class="text-2xl font-bold">THINK</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['nom_user']); ?></span>
                    <a href="logout.php" class="text-red-500 hover:text-red-700">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-gray-900">My Projects</h1>
            <button onclick="document.getElementById('createProjectModal').classList.remove('hidden')" 
                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg flex items-center">
                <i class="fas fa-plus mr-2"></i> Create Project
            </button>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Projects Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($projects as $project): ?>
            <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition duration-300">
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($project['title']); ?></h3>
                    <span class="bg-green-100 text-green-800 text-sm px-3 py-1 rounded-full">
                        <?php echo htmlspecialchars($project['category_name']); ?>
                    </span>
                </div>
                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($project['description']); ?></p>
                <div class="flex justify-end space-x-2">
                    <button class="text-blue-500 hover:text-blue-700">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="text-red-500 hover:text-red-700">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Create Project Modal -->
    <div id="createProjectModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold">Create New Project</h3>
                <button onclick="document.getElementById('createProjectModal').classList.add('hidden')" 
                        class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" action="">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Project Title</label>
                        <input type="text" name="title" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" required rows="3"
                                  class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category_id" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200">
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>">
                                    <?php echo htmlspecialchars($category['category_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" 
                            onclick="document.getElementById('createProjectModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                    <button type="submit"
                            class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">Create</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>