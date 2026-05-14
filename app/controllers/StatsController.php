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

        $overview = $this->model->getOverview((int)$user['id']);
        $last7Days = $this->model->getLast7Days((int)$user['id']);
        $mealBreakdown = $this->model->getMealBreakdown((int)$user['id']);
        $topFoods = $this->model->getTopFoods((int)$user['id']);

        ob_start();
        require "app/views/stats/index.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }
}
