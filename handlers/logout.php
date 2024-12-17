<?php
// logout.php
require_once 'session_handler.php';

SessionManager::destroy();
header('Location: ../login.php');
exit;
?>