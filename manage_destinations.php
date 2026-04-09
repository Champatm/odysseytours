<?php
session_start();
require 'db.php';

// Only allow logged-in users to manage destinations
if (!isset($_SESSION['user_id'])) {
    header('Location: loginhtml.php');
    exit;
}

$message = '';

// Delete destination
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $stmt = $pdo->prepare('SELECT image FROM destinations WHERE id = ?');
    $stmt->execute([$id]);
    $dest = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dest) {
        if (!empty($dest['image']) && file_exists('uploads/' . $dest['image'])) {
            unlink('uploads/' . $dest['image']);
        }

        $deleteStmt = $pdo->prepare('DELETE FROM destinations WHERE id = ?');
        $deleteStmt->execute([$id]);
        $message = 'Destination deleted successfully.';
    }
}

// Add new destination
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_destination'])) {
    $name = trim($_POST['name']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $duration = trim($_POST['duration']);
    $price = floatval($_POST['price']);

    $imageName = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetPath = 'uploads/' . $imageName;
        move_uploaded_file($_FILES['image']['tmp_name'], $targetPath);
    }

    $stmt = $pdo->prepare('INSERT INTO destinations (name, location, description, price, duration, image) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$name, $location, $description, $price, $duration, $imageName]);

    $message = 'Destination added successfully.';
}

$destinations = $pdo->query('SELECT * FROM destinations ORDER BY created_at DESC')->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Destinations</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f4f4f8; color: #222; }
        h1 { margin-bottom: 16px; }
        .message { margin-bottom: 20px; color: #105c10; }
        .admin-nav { margin-bottom: 20px; }
        .admin-nav a { margin-right: 12px; color: #004a99; text-decoration: none; }
        .admin-nav a:hover { text-decoration: underline; }
        .panel { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.05); margin-bottom: 30px; }
        label { display: block; margin-top: 12px; font-weight: bold; }
        input, textarea { width: 100%; padding: 10px; margin-top: 6px; border: 1px solid #ccc; border-radius: 8px; }
        textarea { min-height: 100px; resize: vertical; }
        button { margin-top: 16px; padding: 12px 18px; border: none; border-radius: 8px; background: #333; color: #fff; cursor: pointer; }
        button:hover { background: #222; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 12px 10px; border-bottom: 1px solid #ddd; text-align: left; }
        th { background: #f9f9fb; }
        td img { width: 100px; height: 60px; object-fit: cover; border-radius: 8px; }
        .actions a, .actions form { display: inline-block; margin-right: 10px; }
        .actions a { padding: 8px 12px; border-radius: 8px; background: #0073e6; color: white; text-decoration: none; }
        .actions a.delete { background: #d64545; }
        .actions button { padding: 8px 12px; border-radius: 8px; border: none; background: #d64545; color: white; cursor: pointer; }
    </style>
</head>
<body>

<div class="admin-nav">
    <a href="dashboard.php">Dashboard</a>
    <a href="manage_destinations.php">Manage Destinations</a>
    <a href="logout.php">Logout</a>
</div>

<h1>Manage Destinations</h1>

<?php if ($message): ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<div class="panel">
    <h2>Add New Destination</h2>
    <form method="POST" enctype="multipart/form-data">
        <label for="name">Destination Name</label>
        <input type="text" id="name" name="name" required>

        <label for="location">Location</label>
        <input type="text" id="location" name="location" required>

        <label for="description">Description</label>
        <textarea id="description" name="description" required></textarea>

        <label for="duration">Duration</label>
        <input type="text" id="duration" name="duration" placeholder="7 Days / 6 Nights">

        <label for="price">Price</label>
        <input type="number" id="price" name="price" step="0.01" required>

        <label for="image">Image</label>
        <input type="file" id="image" name="image" accept="image/*" required>

        <button type="submit" name="add_destination">Add Destination</button>
    </form>
</div>

<div class="panel">
    <h2>Existing Destinations</h2>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Image</th>
                <th>Name</th>
                <th>Location</th>
                <th>Price</th>
                <th>Duration</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($destinations as $index => $dest): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><img src="uploads/<?= htmlspecialchars($dest['image']) ?>" alt="<?= htmlspecialchars($dest['name']) ?>"></td>
                    <td><?= htmlspecialchars($dest['name']) ?></td>
                    <td><?= htmlspecialchars($dest['location']) ?></td>
                    <td>$<?= number_format($dest['price'], 2) ?></td>
                    <td><?= htmlspecialchars($dest['duration']) ?></td>
                    <td class="actions">
                        <a href="editdestination.php?id=<?= $dest['id'] ?>">Edit</a>
                        <a class="delete" href="manage_destinations.php?action=delete&id=<?= $dest['id'] ?>" onclick="return confirm('Delete this destination?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
