<?php
// Kan7tajo database connection
require_once 'database.php';
session_start();

// Kan checkyiw wach l'user logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

// Kan connectiw l database
$database = new Database();
$conn = $database->getConnection();

// Kan jibo statistics
try {
    // Total Projects
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Projects");
    $totalProjects = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Freelancers
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Freelancers");
    $totalFreelancers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Offers
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Offers");
    $totalOffers = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Total Reviews
    $stmt = $conn->query("SELECT COUNT(*) as total FROM Reviews");
    $totalReviews = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Latest Projects
    $stmt = $conn->query("SELECT p.*, c.category_name, s.subcategory_name, u.nom_user 
                         FROM Projects p 
                         LEFT JOIN Categories c ON p.category_id = c.category_id
                         LEFT JOIN SubCategories s ON p.subcategory_id = s.subcategory_id
                         LEFT JOIN Users u ON p.id_user = u.id_user
                         ORDER BY p.created_at DESC LIMIT 5");
    $latestProjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error = "Error f database: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ITTHInkk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'custom-dark': '#1e2532',
                        'custom-sidebar': '#262d3d',
                        'custom-green': '#22c55e',
                    }
                }
            }
        }
    </script>
    <!-- Kan zido Chart.js bach nkhdmo b graphs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-custom-dark min-h-screen">
    <!-- Sidebar -->
    <div class="fixed left-0 top-0 w-64 h-full bg-custom-sidebar text-white p-4">
        <div class="flex items-center gap-2 mb-8">
            <span class="text-2xl font-bold"><span class="text-custom-green">IT</span>THInkk</span>
        </div>
        
        <nav class="space-y-2">
            <a href="#" class="flex items-center gap-2 p-2 bg-custom-green/10 text-custom-green rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                Dashboard
            </a>
            <a href="#" class="flex items-center gap-2 p-2 text-gray-400 hover:bg-custom-green/10 
                              hover:text-custom-green rounded-lg transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                    </path>
                </svg>
                Freelancers
            </a>
            <a href="#" class="flex items-center gap-2 p-2 text-gray-400 hover:bg-custom-green/10 
                              hover:text-custom-green rounded-lg transition-all duration-300">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                    </path>
                </svg>
                Projects
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            <div class="flex items-center gap-4">
                <span class="text-gray-400">
                    <?php echo $_SESSION['email'] ?? 'User'; ?>
                </span>
                <a href="logout.php" class="text-custom-green hover:text-custom-green/80">Logout</a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Projects Card -->
            <div class="bg-custom-sidebar p-6 rounded-xl border border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-500/10 text-blue-500 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Projects</p>
                        <p class="text-2xl font-bold text-white"><?php echo $totalProjects; ?></p>
                    </div>
                </div>
            </div>

            <!-- Freelancers Card -->
            <div class="bg-custom-sidebar p-6 rounded-xl border border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-custom-green/10 text-custom-green rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Freelancers</p>
                        <p class="text-2xl font-bold text-white"><?php echo $totalFreelancers; ?></p>
                    </div>
                </div>
            </div>

            <!-- Offers Card -->
            <div class="bg-custom-sidebar p-6 rounded-xl border border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-yellow-500/10 text-yellow-500 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Total Offers</p>
                        <p class="text-2xl font-bold text-white"><?php echo $totalOffers; ?></p>
                    </div>
                </div>
            </div>

            <!-- Reviews Card -->
            <div class="bg-custom-sidebar p-6 rounded-xl border border-gray-700">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-purple-500/10 text-purple-500 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm">Reviews</p>
                        <p class="text-2xl font-bold text-white"><?php echo $totalReviews; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Latest Projects Table -->
        <div class="bg-custom-sidebar rounded-xl border border-gray-700 overflow-hidden">
            <div class="p-6">
                <h2 class="text-xl font-bold text-white mb-4">Latest Projects</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-custom-dark/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Title
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Category
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Subcategory
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Created By
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                                Date
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php foreach ($latestProjects as $project): ?>
                        <tr class="hover:bg-custom-dark/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-white">
                                <?php echo htmlspecialchars($project['title']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                <?php echo htmlspecialchars($project['category_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                <?php echo htmlspecialchars($project['subcategory_name']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                <?php echo htmlspecialchars($project['nom_user']); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">
                                <?php echo date('Y-m-d', strtotime($project['created_at'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
