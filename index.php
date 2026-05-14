<?php
session_start();

require_once "app/helpers/SessionHelper.php";

// =========================
// DEFAULT ROUTE
// =========================
$controller = $_GET['controller'] ?? null;
$action = $_GET['action'] ?? null;

if (!$controller) {
    $controller = "default";  // 🔥 QUAN TRỌNG
    $action = "index";
}

// default action
if (!$action) {
    $action = "index";
}

// =========================
// LOAD CONTROLLER
// =========================
$controllerName = str_replace(' ', '', ucwords(str_replace('_', ' ', $controller))) . "Controller";
$controllerFile = "app/controllers/" . $controllerName . ".php";

if (!file_exists($controllerFile)) {
    die("❌ Controller không tồn tại: $controllerName");
}

require_once $controllerFile;

if (!class_exists($controllerName)) {
    die("❌ Không tìm thấy class: $controllerName");
}

// =========================
// INIT
// =========================
$obj = new $controllerName();

// =========================
// AUTO FIX ACTION (🔥 QUAN TRỌNG)
// =========================
if (!method_exists($obj, $action)) {

    // fallback về index nếu có
    if (method_exists($obj, "index")) {
        $action = "index";
    } else {
        die("❌ Action không tồn tại và không có index(): $action");
    }
}

// =========================
// CALL
// =========================
$obj->$action();
