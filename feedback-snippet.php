<?php
require 'db.php';

$feedbacks = $conn->query("SELECT f.rating, f.comments, f.submitted_at, u.username 
                           FROM feedback f 
                           JOIN appointments a ON f.appointment_id = a.id 
                           JOIN users u ON a.user_id = u.id 
                           WHERE f.visible_to_tech = 1 
                           ORDER BY f.submitted_at DESC 
                           LIMIT 5");

while ($f = $feedbacks->fetch_assoc()):
?>
  <div class="testimonial">
    <strong><?= htmlspecialchars($f['username']) ?></strong>
    <div>⭐ <?= $f['rating'] ?>/5</div>
    <p class="mt-2"><?= htmlspecialchars($f['comments']) ?></p>
    <small class="text-muted"><?= date("F j, Y", strtotime($f['submitted_at'])) ?></small>
  </div>
<?php endwhile; ?>
