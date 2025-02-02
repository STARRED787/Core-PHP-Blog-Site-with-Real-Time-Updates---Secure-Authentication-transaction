<?php

if ($loginSuccessful) {
    session_start();
    $_SESSION['user_role'] = $userRole;
    $_SESSION['last_activity'] = time();
    // ... rest of your login code ...
} 