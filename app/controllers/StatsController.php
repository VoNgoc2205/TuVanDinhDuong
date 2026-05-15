<?php
require_once "app/models/UserStatsModel.php";
require_once "app/helpers/SessionHelper.php";

class StatsController
{
    private $model;

    public function __construct()
    {
        $this->model = new UserStatsModel();
        SessionHelper::start();
    }

    public function index()
    {
        SessionHelper::requireLogin();
        $user = SessionHelper::user();
        $selectedMonth = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $overview = $this->model->getOverview((int)$user['id'], $selectedMonth);
        $last7Days = $this->model->getLast7Days((int)$user['id']);
        $mealBreakdown = $this->model->getMealBreakdown((int)$user['id']);
        $topFoods = $this->model->getTopFoods((int)$user['id']);
        $availableMonths = $this->model->getAvailableMonths((int)$user['id']);

        ob_start();
        require "app/views/stats/index.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }
}
