<?php
session_start();
require 'db.php'; //is used Database connection

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: loginhtml.php');
    exit;
}

// Ensure session security with cookies
if (!isset($_COOKIE['auth_session']) || $_COOKIE['auth_session'] !== session_id()) {
    header('Location: loginhtml.php');
    exit;
}

// Define upload directory
$targetDir = "uploads/";   
if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

// Allowed image formats
$allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp', 'tiff', 'svg'];
$maxFileSize = 2 * 1024 * 1024; // 2MB limit

// Handle profile photo upload
if (isset($_POST['upload_photo']) && isset($_FILES['profile_photo'])) {
    $file = $_FILES["profile_photo"];

    if ($file["error"] !== UPLOAD_ERR_OK) {
        die("<p>Error uploading file. Code: {$file['error']}</p>");
    }

    $imageFileType = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    if (!in_array($imageFileType, $allowedTypes)) {
        die("<p>Only allowed image formats: JPG, PNG, GIF, BMP, SVG, WEBP.</p>");
    }

    if ($file["size"] > $maxFileSize) {
        die("<p>File exceeds 2MB limit.</p>");
    }

    $fileName = uniqid("profile_", true) . "." . $imageFileType;
    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($file["tmp_name"], $targetFile)) {
        $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE id = ?");
        $stmt->execute([$fileName, $_SESSION['user_id']]);
        header("Refresh:0");
        exit;
    } else {
        die("<p>Error moving uploaded file.</p>");
    }
}

// Handle profile photo deletion
if (isset($_POST['delete_photo'])) {
    $stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user['profile_photo'] !== 'blankprofile.png') {
        unlink("uploads/" . $user['profile_photo']);
    }

    $stmt = $pdo->prepare("UPDATE users SET profile_photo = 'blankprofile.png' WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);

    header("Refresh:0");
    exit;
}

// Retrieve profile photo
$stmt = $pdo->prepare("SELECT profile_photo FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['profile_photo'] && file_exists("uploads/" . $user['profile_photo']) && $user['profile_photo'] !== 'blankprofile.png') {
    $profilePhoto = "uploads/" . $user['profile_photo'];
} else {
    $profilePhoto = "blankprofile.png";
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="dash.css">
    <title>User Dashboard</title>
    <link rel="icon" type="image/jpeg" href="bat.jpg">
</head>

    <style>
        
.profile-photo {
    width: 120px;        /* or any size you want */
    height: 120px;
    border-radius: 50%;  /* makes it round */
    object-fit: cover;   /* keeps proportions */
    border: 3px solid #fff; /* optional, looks cleaner */
    box-shadow: 0 0 10px rgba(0,0,0,0.2); /* optional */
}



        /* Profile Modal Styles */
        .profile-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.8);
            justify-content: center;
            align-items: center;
        }
        .profile-modal-content {
            position: relative;
            background: #fff;
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: zoomIn 0.3s ease-in-out;
        }
        .profile-modal-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
        }
        .profile-options {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .option-btn {
            padding: 10px;
            background: #007bff;
            border: none;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            transition: 0.3s;
        }
        .option-btn:hover { background: #0056b3; }
        .option-btn.delete { background: #dc3545; }
        .option-btn.delete:hover { background: #a71d2a; }
        .option-btn:disabled { background: #999; cursor: not-allowed; }
        .close-btn {
            position: absolute;
            top: 10px; right: 15px;
            font-size: 24px;
            cursor: pointer;
        }
        @keyframes zoomIn {
            from { transform: scale(0.7); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
    </style>

<body>

<div id="time-container">
  <span id="clock"></span>
</div>

<script>
  function updateTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString();
    document.getElementById('clock').textContent = timeString;
  }
  updateTime();
  setInterval(updateTime, 1000);
</script>

<div class="container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>Dashboard</h2>
        <a href="dashboard.php">Profile</a>
        <a href="settings.php">Settings</a>
        <a href="logout.php">Logout</a>
        <a href="keepnotes.php">Notes</a>
   
    <!-- Profile Section -->
    <div class="profile-section">
        <div class="profile-photo-container" onclick="openProfileModal()">
            <img src="<?= $profilePhoto ?>" alt="Profile Photo" class="profile-photo">
        </div>
    </div>

    <!-- Profile Modal -->
    <div id="profileModal" class="profile-modal">
        <div class="profile-modal-content">
            <span class="close-btn" onclick="closeProfileModal()">&times;</span>
            
            <!-- Preview Image -->
            <img id="profilePreview" src="<?= $profilePhoto ?>" alt="Profile Photo" class="profile-modal-photo">

            <div class="profile-options">
                <!-- Upload Form -->
                <form action="" method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="file" name="profile_photo" accept="image/*" onchange="previewPhoto(event)" required>
                    <button type="submit" name="upload_photo" class="option-btn" id="uploadBtn" disabled>Upload</button>
                </form>

                <!-- Delete Form -->
                <form action="" method="POST">
                    <button type="submit" name="delete_photo" class="option-btn delete">Delete Photo</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="content">
        <h2>Welcome, <?= $_SESSION['email'] ?> </h2>
        <p>Here’s your personalized dashboard.</p>
    </main>
</div>

<!-- Footer -->
<footer>
    <p>&copy; <?= date("Y/M") ?> Alex's Coms. All rights reserved.</p>
</footer>

<script>
let originalPhoto = "<?= $profilePhoto ?>";

function openProfileModal() {
    document.getElementById("profileModal").style.display = "flex";
}

function closeProfileModal() {
    document.getElementById("profileModal").style.display = "none";
    // reset preview & disable button
    document.getElementById("profilePreview").src = originalPhoto;
    document.getElementById("uploadBtn").disabled = true;
}

function previewPhoto(event) {
    const file = event.target.files[0];
    const preview = document.getElementById("profilePreview");
    const uploadBtn = document.getElementById("uploadBtn");

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(file);
        uploadBtn.disabled = false;
    } else {
        preview.src = originalPhoto;
        uploadBtn.disabled = true;
    }
}

// Close modal if click outside
window.onclick = function(event) {
    let modal = document.getElementById("profileModal");
    if (event.target === modal) {
        closeProfileModal();
    }
}
</script>

</body>
</html>
