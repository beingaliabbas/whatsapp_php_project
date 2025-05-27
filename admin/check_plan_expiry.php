<?php
require_once '../db.php';

$conn->query("UPDATE users SET plan_activated = 0 WHERE plan_activated = 1 AND plan_end_date IS NOT NULL AND plan_end_date < NOW()");
echo "Expired plans deactivated.";
