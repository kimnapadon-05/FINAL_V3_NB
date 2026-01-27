<?php
// Redirect legacy URL manage_repair.php -> manage_repairs.php
// This helps when links or bookmarks use the singular filename.
header("Location: manage_repairs.php", true, 301);
exit();
?>
