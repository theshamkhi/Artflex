<?php
require_once '../config/db.php';
require_once '../models/classes.php';

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] == 'Admin') {
    header("Location: login.php");
    exit;
}

$user = new User();
$user->setUserID($_SESSION['user_id']);

$userr = $user->getUserData();

$favorites = $user->getFavoris();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'toggleLike') {
        $artID = intval($_POST['artID']);
        $result = $user->toggleLike($artID);
  
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
  
    if (isset($_POST['action']) && $_POST['action'] === 'deleteArt') {
        $artID = $_POST['artID'];
        $user->deleteArt($artID);
  
        header("Location: readerDashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Favorites</title>
    <link rel="icon" href="../assets/art.png"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <!-- AOS Animation CDN -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</head>
<body class="bg-gray-100">

<!-- Main -->
<button data-drawer-target="default-sidebar" data-drawer-toggle="default-sidebar" aria-controls="default-sidebar" type="button" class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
   <span class="sr-only">Open sidebar</span>
   <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
        <path clip-rule="evenodd" fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z"></path>
   </svg>
</button>

<aside id="default-sidebar" class="fixed top-0 left-0 z-40 w-80 h-screen transition-transform -translate-x-full sm:translate-x-0" aria-label="Sidebar">
    <div class="h-full overflow-y-auto bg-black">
    <!-- Sidebar Menu -->
    <?php if ($userr): ?>
        <div class="flex flex-col space-y-2">
            <img src="<?php echo $userr['PhotoURL']; ?>" alt="Lawyer Photo" class="object-cover">
            <div class="px-3 pt-4">
                <h2 class="text-xl font-semibold text-white text-center uppercase mb-4"><?php echo $userr['Name']; ?></h2>
                <p class="text-base text-gray-400">&#128231;  <?php echo $userr['Email']; ?></p>
            </div>
        </div>
    <?php endif; ?>
        <ul class="space-y-2 font-medium px-3 pb-4">
            <?php
            if ($_SESSION['role'] == 'Reader') {
                echo '
                <li>
                    <a href="readerDashboard.php" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-100 hover:text-black group">
                        <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                            <path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
                        </svg>
                    <span class="ms-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="userProfile.php" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-100 hover:text-black group">
                        <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                        <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                        </svg>
                    <span class="ms-3">Profile</span>
                    </a>
                </li>
                <li>
                    <a href="userFav.php" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-100 hover:text-black group">
                        <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                        <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                        </svg>
                    <span class="ms-3">Favorites</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-100 hover:text-black group">
                    <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
                    </svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Log Out</span>
                    </a>
                </li>';
            } elseif ($_SESSION['role'] == 'Author') {
                echo '
                <li>
                    <a href="authorDashboard.php" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-100 hover:text-black group">
                        <svg class="w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 22 21">
                            <path d="M16.975 11H10V4.025a1 1 0 0 0-1.066-.998 8.5 8.5 0 1 0 9.039 9.039.999.999 0 0 0-1-1.066h.002Z"/>
                            <path d="M12.5 0c-.157 0-.311.01-.565.027A1 1 0 0 0 11 1.02V10h8.975a1 1 0 0 0 1-.935c.013-.188.028-.374.028-.565A8.51 8.51 0 0 0 12.5 0Z"/>
                        </svg>
                    <span class="ms-3">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="userProfile.php" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-100 hover:text-black group">
                        <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                        <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                        </svg>
                    <span class="ms-3">Profile</span>
                    </a>
                </li>
                <li>
                    <a href="userFav.php" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-100 hover:text-black group">
                        <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 18">
                        <path d="M14 2a3.963 3.963 0 0 0-1.4.267 6.439 6.439 0 0 1-1.331 6.638A4 4 0 1 0 14 2Zm1 9h-1.264A6.957 6.957 0 0 1 15 15v2a2.97 2.97 0 0 1-.184 1H19a1 1 0 0 0 1-1v-1a5.006 5.006 0 0 0-5-5ZM6.5 9a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9ZM8 10H5a5.006 5.006 0 0 0-5 5v2a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-2a5.006 5.006 0 0 0-5-5Z"/>
                        </svg>
                    <span class="ms-3">Favorites</span>
                    </a>
                </li>
                <li>
                    <a href="authorArticles.php" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-100 hover:text-black group">
                        <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 18 18">
                            <path d="M6.143 0H1.857A1.857 1.857 0 0 0 0 1.857v4.286C0 7.169.831 8 1.857 8h4.286A1.857 1.857 0 0 0 8 6.143V1.857A1.857 1.857 0 0 0 6.143 0Zm10 0h-4.286A1.857 1.857 0 0 0 10 1.857v4.286C10 7.169 10.831 8 11.857 8h4.286A1.857 1.857 0 0 0 18 6.143V1.857A1.857 1.857 0 0 0 16.143 0Zm-10 10H1.857A1.857 1.857 0 0 0 0 11.857v4.286C0 17.169.831 18 1.857 18h4.286A1.857 1.857 0 0 0 8 16.143v-4.286A1.857 1.857 0 0 0 6.143 10Zm10 0h-4.286A1.857 1.857 0 0 0 10 11.857v4.286c0 1.026.831 1.857 1.857 1.857h4.286A1.857 1.857 0 0 0 18 16.143v-4.286A1.857 1.857 0 0 0 16.143 10Z"/>
                        </svg>
                    <span class="ms-3">Articles</span>
                    </a>
                </li>
                <li>
                    <a href="logout.php" class="flex items-center p-2 text-white rounded-lg hover:bg-gray-100 hover:text-black group">
                    <svg class="flex-shrink-0 w-5 h-5 text-gray-500 transition duration-75 dark:text-gray-400 group-hover:text-gray-900 dark:group-hover:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 16">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 8h11m0 0L8 4m4 4-4 4m4-11h3a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-3"/>
                    </svg>
                    <span class="flex-1 ms-3 whitespace-nowrap">Log Out</span>
                    </a>
                </li>';
            } 
            ?>
        </ul>
   </div>
</aside>

<div class="flex-1 ml-0 sm:ml-80 p-8">
    <h1 class="text-5xl font-semibold text-black mb-10">My Favorites</h1>

    <div class="grid grid-cols-1 sm:px-12 lg:px-24 gap-8" style="align-items: start;">
      <?php foreach ($favorites as $favorite): ?>
        <article class="overflow-hidden shadow transition hover:shadow-lg" data-aos="fade-up" data-aos-anchor-placement="top-bottom">
          <img src="<?php echo htmlspecialchars($favorite['PhotoURL']); ?>" alt="Article Image" class="h-56 w-full object-cover"/>
          <div class="bg-white p-4 sm:p-6">
              <a href="#">
                  <h3 class="mt-0.5 text-lg text-gray-900">
                      <?php echo htmlspecialchars($favorite['Title']); ?>
                  </h3>
              </a>
              <hr class="mt-3">
              <p class="mt-2 text-sm text-gray-500 break-words">
                  <?php echo htmlspecialchars($favorite['Content']); ?>
              </p>
              <hr class="mt-3">
              <time datetime="<?php echo htmlspecialchars($favorite['PubDate']); ?>" class="block mt-2 text-xs text-gray-600 italic">
                  <?php echo "<strong>Created by </strong>" . htmlspecialchars($favorite['AuthorName']) . "<br><strong>On </strong>" . htmlspecialchars($favorite['PubDate']); ?>
              </time>

              <div class="mt-4 flex items-center justify-between">
                  <form method="POST">
                      <input type="hidden" name="action" value="toggleLike">
                      <input type="hidden" name="artID" value="<?php echo $favorite['ArtID']; ?>">
                      <div class="flex items-center gap-3">
                          <span><?php echo $user->getLikeCount($favorite['ArtID']); ?></span>
                          <button type="submit" class="like-btn">
                              <?php
                              $liked = $user->hasLiked($favorite['ArtID']);
                              echo $liked ? 'Unlike' : 'Like';
                              ?>
                          </button>
                      </div>
                  </form>
              </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
</div>



<script>
  AOS.init();
</script>

</body>
</html>