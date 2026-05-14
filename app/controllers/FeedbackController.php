<?php
require_once "app/models/FeedbackModel.php";
require_once "app/helpers/SessionHelper.php";

class FeedbackController
{
    private $model;

    public function __construct()
    {
        $this->model = new FeedbackModel();
        SessionHelper::start();
    }

    public function index()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();
        $feedbacks = $this->model->getByUser((int)$user['id']);
        $success = $_GET['success'] ?? '';
        $error = $_GET['error'] ?? '';

        ob_start();
        require "app/views/feedback/index.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function submit()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=feedback&action=index");
            exit;
        }

        $rating = intval($_POST['rating'] ?? 5);
        $category = trim($_POST['category'] ?? 'general');
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');

        if ($title === '' || $message === '') {
            header("Location: index.php?controller=feedback&action=index&error=missing");
            exit;
        }

        $ok = $this->model->create((int)$user['id'], $rating, $category, $title, $message);
        header("Location: index.php?controller=feedback&action=index&" . ($ok ? "success=1" : "error=save"));
        exit;
    }
}
