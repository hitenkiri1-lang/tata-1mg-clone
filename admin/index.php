<?php
/**
 * Admin Index - Redirect to appropriate page
 */
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';

if (isAdminLoggedIn()) {
    redirect(SITE_URL . '/admin/dashboard.php');
} else {
    redirect(SITE_URL . '/admin/login.php');
}
?>
