<?php
// Start session if you want to show login info later
session_start();

// Include database connection
require 'db.php';

// Fetch destinations from database
$stmt = $pdo->query("SELECT * FROM destinations ORDER BY created_at DESC");
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Destinations</title>
  <link rel="stylesheet" href="destinationsstyles.css">
</head>

<body>

<h1>Our Destinations</h1>

<div class="destinations-container">

<?php if (count($destinations) > 0): ?>
  
  <?php foreach ($destinations as $dest): ?> <!--foreach loop to iterate through destinations-->
      
    <div class="destination-card">
      
      <!-- Destination Image -->
      <img src="uploads/<?= htmlspecialchars($dest['image']) ?>" alt="<?= htmlspecialchars($dest['name']) ?>">

      <!-- Destination Name -->
      <h3><?= htmlspecialchars($dest['name']) ?></h3>

      <!-- Location -->
      <p><strong>Location:</strong> <?= htmlspecialchars($dest['location']) ?></p>

      <!-- Duration -->
      <p><strong>Duration:</strong> <?= htmlspecialchars($dest['duration']) ?></p>

      <!-- Price -->
      <p class="price">$<?= number_format($dest['price'], 2) ?></p>

      <!-- Description -->
      <p><?= htmlspecialchars($dest['description']) ?></p>

      <!-- View More -->
      <a href="destination_details.php?id=<?= $dest['id'] ?>" class="btn">
        View Details
      </a>

    </div>

  <?php endforeach; ?>

<?php else: ?>
  <p>No destinations available at the moment.</p>
<?php endif; ?>

</div>

</body>
</html>