<?php
session_start();
require_once 'database.php';
require_once 'auth.php';

requireAdmin();

$user = $_SESSION['user'];

// Handle delete operations
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id_user = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
}

if (isset($_POST['delete_project'])) {
    $project_id = $_POST['project_id'];
    $stmt = $conn->prepare("DELETE FROM Projects WHERE project_id = :id");
    $stmt->bindParam(':id', $project_id);
    $stmt->execute();
}

if (isset($_POST['delete_freelancer'])) {
    $freelancer_id = $_POST['freelancer_id'];
    $stmt = $conn->prepare("DELETE FROM freelancers WHERE id = :id");
    $stmt->bindParam(':id', $freelancer_id);
    $stmt->execute();
}

if (isset($_POST['delete_category'])) {
    $category_id = $_POST['category_id'];
    try {
        $stmt = $conn->prepare("DELETE FROM Categories WHERE category_id = :id");
        $stmt->bindParam(':id', $category_id);
        $stmt->execute();
        header("Location: dashboard.php#categories");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

if (isset($_POST['add_category'])) {
    $new_category_name = trim($_POST['new_category_name']);
    if (!empty($new_category_name)) {
        try {
            $stmt = $conn->prepare("INSERT INTO Categories (category_name) VALUES (:name)");
            $stmt->bindParam(':name', $new_category_name);
            $stmt->execute();
            header("Location: dashboard.php#categories");
            exit();
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

// Fetch data
$users = $conn->query("SELECT * FROM users WHERE role != 'admin'")->fetchAll(PDO::FETCH_ASSOC);
$projects = $conn->query("SELECT p.*, u.nom_user, c.category_name FROM Projects p LEFT JOIN users u ON p.id_user = u.id_user LEFT JOIN Categories c ON p.category_id = c.category_id")->fetchAll(PDO::FETCH_ASSOC);
$freelancers = $conn->query("SELECT * FROM freelancers")->fetchAll(PDO::FETCH_ASSOC);
$categories = $conn->query("SELECT * FROM Categories")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); }
        .modal-content { background-color: #1f2937; margin: 15% auto; padding: 20px; border-radius: 8px; width: 80%; max-width: 500px; }
    </style>
</head>
<body class="bg-gray-900">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <div class="w-64 bg-gray-800">
            <div class="p-4 border-b border-gray-700">
                <span class="text-2xl font-bold text-green-500">IT</span>
                <span class="text-2xl font-bold text-white">THINK</span>
            </div>
            <nav class="mt-4">
                <a href="#users" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-users mr-3"></i>
                    <span>Users</span>
                </a>
                <a href="#projects" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-project-diagram mr-3"></i>
                    <span>Projects</span>
                </a>
                <a href="#freelancers" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-laptop-code mr-3"></i>
                    <span>Freelancers</span>
                </a>
                <a href="#categories" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700">
                    <i class="fas fa-tags mr-3"></i>
                    <span>Categories</span>
                </a>
                <a href="logout.php" class="flex items-center px-4 py-2 text-red-400 hover:bg-gray-700">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-8">
            <!-- Users Section -->
            <div id="users" class="bg-gray-800 rounded-lg shadow-lg mb-8">
                <div class="p-4 border-b border-gray-700">
                    <h2 class="text-xl font-bold text-white">Users</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-400">Name</th>
                                <th class="px-4 py-2 text-left text-gray-400">Email</th>
                                <th class="px-4 py-2 text-left text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr class="border-t border-gray-700">
                                <td class="px-4 py-2 text-gray-300"><?php echo htmlspecialchars($user['nom_user']); ?></td>
                                <td class="px-4 py-2 text-gray-300"><?php echo htmlspecialchars($user['email']); ?></td>
                                <td class="px-4 py-2">
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id_user']; ?>">
                                        <button type="submit" name="delete_user" class="text-red-400 hover:text-red-300">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Projects Section -->
            <div id="projects" class="bg-gray-800 rounded-lg shadow-lg mb-8">
                <div class="p-4 border-b border-gray-700">
                    <h2 class="text-xl font-bold text-white">Projects</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-400">Project</th>
                                <th class="px-4 py-2 text-left text-gray-400">User</th>
                                <th class="px-4 py-2 text-left text-gray-400">Category</th>
                                <th class="px-4 py-2 text-left text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                            <tr class="border-t border-gray-700">
                                <td class="px-4 py-2">
                                    <div class="text-gray-300"><?php echo htmlspecialchars($project['title']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars($project['description']); ?></div>
                                </td>
                                <td class="px-4 py-2 text-gray-300"><?php echo htmlspecialchars($project['nom_user']); ?></td>
                                <td class="px-4 py-2">
                                    <span class="px-2 py-1 text-sm text-green-400 bg-green-900 rounded">
                                        <?php echo htmlspecialchars($project['category_name']); ?>
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <button onclick="openEditModal('project', '<?php echo $project['project_id']; ?>', '<?php echo htmlspecialchars($project['title']); ?>', '<?php echo htmlspecialchars($project['description']); ?>', '<?php echo $project['category_id']; ?>')" class="text-blue-400 hover:text-blue-300 mr-2">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="project_id" value="<?php echo $project['project_id']; ?>">
                                        <button type="submit" name="delete_project" class="text-red-400 hover:text-red-300">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Freelancers Section -->
            <div id="freelancers" class="bg-gray-800 rounded-lg shadow-lg">
                <div class="p-4 border-b border-gray-700">
                    <h2 class="text-xl font-bold text-white">Freelancers</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-400">Name</th>
                                <th class="px-4 py-2 text-left text-gray-400">Skills</th>
                                <th class="px-4 py-2 text-left text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($freelancers as $freelancer): ?>
                            <tr class="border-t border-gray-700">
                                <td class="px-4 py-2 text-gray-300"><?php echo htmlspecialchars($freelancer['name']); ?></td>
                                <td class="px-4 py-2">
                                    <?php foreach (explode(',', $freelancer['skills']) as $skill): ?>
                                        <span class="inline-block px-2 py-1 text-sm text-blue-400 bg-blue-900 rounded mr-1">
                                            <?php echo htmlspecialchars(trim($skill)); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </td>
                                <td class="px-4 py-2">
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                        <input type="hidden" name="freelancer_id" value="<?php echo $freelancer['id']; ?>">
                                        <button type="submit" name="delete_freelancer" class="text-red-400 hover:text-red-300">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Categories Section -->
            <div id="categories" class="bg-gray-800 rounded-lg shadow-lg mb-8 gap-10">
                <div class="p-4 border-b border-gray-700 flex justify-between items-center">
                    <h2 class="text-xl font-bold text-white">Categories</h2>
                    <button onclick="showAddCategoryModal()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        <i class="fas fa-plus mr-2"></i>Add Category
                    </button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left text-gray-400">ID</th>
                                <th class="px-4 py-2 text-left text-gray-400">Category Name</th>
                                <th class="px-4 py-2 text-left text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category): ?>
                            <tr class="border-t border-gray-700">
                                <td class="px-4 py-2 text-gray-300"><?php echo htmlspecialchars($category['category_id']); ?></td>
                                <td class="px-4 py-2 text-gray-300"><?php echo htmlspecialchars($category['category_name']); ?></td>
                                <td class="px-4 py-2">
                                    <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        <input type="hidden" name="category_id" value="<?php echo $category['category_id']; ?>">
                                        <button type="submit" name="delete_category" class="text-red-500 hover:text-red-700">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modals -->
    <div id="editProjectModal" class="modal">
        <div class="modal-content">
            <h2 class="text-xl font-bold text-white mb-4">Edit Project</h2>
            <form action="edit_operations.php" method="POST">
                <input type="hidden" name="project_id" id="editProjectId">
                <div class="mb-4">
                    <label class="block text-gray-300 mb-2">Title</label>
                    <input type="text" id="editProjectTitle" name="title" class="w-full bg-gray-700 text-white px-3 py-2 rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 mb-2">Description</label>
                    <textarea id="editProjectDescription" name="description" class="w-full bg-gray-700 text-white px-3 py-2 rounded"></textarea>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-300 mb-2">Category</label>
                    <select id="editProjectCategory" name="category_id" class="w-full bg-gray-700 text-white px-3 py-2 rounded">
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['category_id']; ?>">
                                <?php echo htmlspecialchars($category['category_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="closeModal('editProjectModal')" class="bg-gray-700 text-gray-300 px-4 py-2 rounded mr-2">Cancel</button>
                    <button type="submit" name="edit_project" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-white">Add New Category</h3>
                <button onclick="closeModal('addCategoryModal')" class="text-gray-500 hover:text-gray-400">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-400 mb-2">Category Name</label>
                    <input type="text" name="new_category_name" required 
                           class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded text-white">
                </div>
                <div class="flex justify-end">
                    <button type="submit" name="add_category" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                        Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(type, id, ...args) {
            const modal = document.getElementById(`edit${type.charAt(0).toUpperCase() + type.slice(1)}Modal`);
            document.getElementById(`edit${type.charAt(0).toUpperCase() + type.slice(1)}Id`).value = id;
            
            if (type === 'project') {
                document.getElementById('editProjectTitle').value = args[0];
                document.getElementById('editProjectDescription').value = args[1];
                document.getElementById('editProjectCategory').value = args[2];
            }
            modal.style.display = 'block';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function showAddCategoryModal() {
            document.getElementById('addCategoryModal').style.display = 'block';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        };
    </script>
</body>
</html>