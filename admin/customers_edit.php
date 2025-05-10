<?php
session_start();
if (!isset($_SESSION['user_id']) || !$_SESSION['is_admin']) {
  header("Location: ../login.php");
  exit();
}
include_once '../db.php';

if (isset($_GET['id'])) {
  $id = $_GET['id'];

  // Fetch the user
  $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
  $stmt->execute([$id]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$user) {
    header("Location: customers.php");
    exit();
  }
} else {
  header("Location: customers.php");
  exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = $_POST['name'];
  $username = $_POST['username'];
  $email = $_POST['email'];
  $contact_number = $_POST['contact_number'];
  $is_admin = isset($_POST['is_admin']) ? 1 : 0;
  
  // Handle profile picture upload
  $profilePicture = $user['profile_picture'];
  
  if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
    // Create uploads directory if it doesn't exist
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/uploads/";
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    
    // Generate unique filename
    $fileExtension = strtolower(pathinfo($_FILES["profile_picture"]["name"], PATHINFO_EXTENSION));
    $uniqueFilename = uniqid('profile_') . '.' . $fileExtension;
    $targetFile = $uploadDir . $uniqueFilename;
    
    // Delete old profile picture if exists
    if (!empty($user['profile_picture'])) {
      $oldFile = $_SERVER['DOCUMENT_ROOT'] . parse_url($user['profile_picture'], PHP_URL_PATH);
      if (file_exists($oldFile)) {
        unlink($oldFile);
      }
    }
    
    // Move uploaded file
    if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
      $profilePicture = "/uploads/" . $uniqueFilename; // Store relative path in database
    }
  }

  $stmt = $conn->prepare("UPDATE users SET name = ?, username = ?, email = ?, contact_number = ?, is_admin = ?, profile_picture = ? WHERE id = ?");
  $stmt->execute([$name, $username, $email, $contact_number, $is_admin, $profilePicture, $id]);

  header("Location: customers.php");
  exit();
}

$pageTitle = 'Edit Customer';

ob_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Customer</title>
  <script>
    function previewImage(event) {
      const reader = new FileReader();
      reader.onload = function() {
        const output = document.getElementById('profilePicturePreview');
        output.src = reader.result;
        output.classList.remove('hidden');
        document.getElementById('profileIcon').classList.add('hidden');
      };
      reader.readAsDataURL(event.target.files[0]);
    }
  </script>
</head>

<body class="bg-gray-50">
  <div class="container mx-auto p-4 rounded-lg shadow-xl">
    <h1 class="text-2xl font-bold mb-4">Edit Customer</h1>
    <form method="post" class="p-6" enctype="multipart/form-data">
      <!-- Profile Picture Section -->
      <div class="mb-6 text-center">
        <label class="block text-sm font-medium text-gray-700 mb-3">Profile Picture</label>
        <div class="mb-4">
          <?php if (!empty($user['profile_picture'])): ?>
            <img id="profilePicturePreview" src="<?= $user['profile_picture'] ?>" alt="Profile Picture" class="mx-auto rounded-full w-32 h-32 object-cover border-2 border-gray-200">
            <div id="profileIcon" class="hidden"></div>
          <?php else: ?>
            <div id="profileIcon" class="mx-auto rounded-full w-32 h-32 bg-gray-200 flex items-center justify-center">
              <i class="fas fa-user-circle text-5xl text-gray-400"></i>
            </div>
            <img id="profilePicturePreview" class="hidden mx-auto rounded-full w-32 h-32 object-cover border-2 border-gray-200">
          <?php endif; ?>
        </div>
        <div class="flex justify-center">
          <label for="profile_picture" class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-md transition">
            <i class="fas fa-camera mr-2"></i>Change Photo
          </label>
          <input type="file" name="profile_picture" id="profile_picture" class="hidden" onchange="previewImage(event)" accept="image/*">
        </div>
      </div>

      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700" for="name">Name</label>
        <input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" type="text" name="name" id="name" value="<?= $user['name'] ?>" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700" for="username">Username</label>
        <input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" type="text" name="username" id="username" value="<?= $user['username'] ?>" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700" for="email">Email</label>
        <input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" type="email" name="email" id="email" value="<?= $user['email'] ?>" required>
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700" for="contact_number">Contact Number</label>
        <input class="shadow-sm bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" type="text" name="contact_number" id="contact_number" value="<?= $user['contact_number'] ?>">
      </div>
      <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700">Role</label>
        <input type="checkbox" name="is_admin" id="is_admin" <?= $user['is_admin'] ? 'checked' : '' ?>>
        <label class="ml-2 text-sm font-medium text-gray-900" for="is_admin">Admin</label>
      </div>
      <button type="submit" class="bg-teal-600 hover:bg-teal-800 focus:ring-4 focus:ring-teal-300 text-white font-bold py-2 px-4 rounded focus:outline-none">Update</button>
    </form>
  </div>
</body>

</html>

<?php
$content = ob_get_clean();
include 'includes/dashboard_layout.php';
?>