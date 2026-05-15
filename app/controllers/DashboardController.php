<?php
require_once "app/models/DashboardModel.php";
require_once "app/helpers/SessionHelper.php";
require_once "app/helpers/NutritionTargetHelper.php";

class DashboardController
{
    private $model;

    public function __construct()
    {
        $this->model = new DashboardModel();
        SessionHelper::start();
    }

    public function index()
    {
        $user = SessionHelper::user();

        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $user_id = $user['id'];

        // ===== DATA =====
        $calo_today = $this->model->getCaloToday($user_id);
        $meal_today = $this->model->getMealCountToday($user_id);

        $macro = $this->model->getMacrosToday($user_id);
        $proteinToday = $macro['protein'] ?? 0;
        $carbsToday   = $macro['carbs'] ?? 0;
        $fatToday     = $macro['fat'] ?? 0;

        $week = $this->model->getWeekData($user_id);

        $recentMeals = $this->model->getRecentMeals($user_id);

        $ai_count = $this->model->getAICount($user_id);

        // ===== TARGET =====
        $profile = $this->model->getProfile($user_id);
        $target_calo = intval($profile['kcal_target'] ?? 2000);
        $macroTargets = NutritionTargetHelper::calculateMacroTargets($profile ?? []);
        $proteinTarget = $macroTargets['protein'];
        $carbsTarget = $macroTargets['carbs'];
        $fatTarget = $macroTargets['fat'];
        $macroTargetReason = $macroTargets['reason'];
        $percent_completed = ($target_calo > 0) 
            ? ($calo_today / $target_calo) * 100 
            : 0;

        // ===== VIEW =====
        ob_start();
        require "app/views/dashboard/dashboard_user.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }
}
