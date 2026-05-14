<?php
class SessionHelper
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    public static function requireLogin()
    {
        self::start();
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }
    }
}