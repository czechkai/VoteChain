<?php
require_once '../includes/config.php';

logoutUser();

header('Location: ../index.php?logout=1');
exit;
?>
