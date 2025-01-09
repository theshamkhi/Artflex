<?php
require_once '../config/db.php';
require_once '../models/classes.php';

session_start();

$user = new User();

$artID = $_GET['artID'];

$article = $user->getArtByID($artID);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $photoURL = $_POST['photoURL'];

    $user->updateArt($artID, $title, $content, $photoURL);
    header("Location: authorDashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Article</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100">
    <div class="max-w-lg mx-auto mt-10 bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Edit Article</h2>
        <form method="POST">
            <!-- Title -->
            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($article['Title']); ?>" 
                       class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
            </div>

            <!-- Content -->
            <div class="mb-4">
                <label for="content" class="block text-sm font-medium text-gray-700">Content</label>
                <textarea id="content" name="content" rows="5" 
                          class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500" required><?php echo htmlspecialchars($article['Content']); ?></textarea>
            </div>

            <!-- Photo URL -->
            <div class="mb-4">
                <label for="photoURL" class="block text-sm font-medium text-gray-700">Photo URL</label>
                <input type="url" id="photoURL" name="photoURL" value="<?php echo htmlspecialchars($article['PhotoURL']); ?>" 
                       class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:ring-blue-500 focus:border-blue-500">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    Update Article
                </button>
            </div>
        </form>
    </div>
</body>
</html>
