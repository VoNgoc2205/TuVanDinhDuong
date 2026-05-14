<?php
require_once "app/models/UserModel.php";

class UserController
{
    private $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    // =========================
    // LOGIN
    // =========================
    public function login()
    {

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $account = trim($_POST["email"] ?? "");
            $password = $_POST["password"] ?? "";
            $remember = isset($_POST["remember"]);

            if (empty($account) || empty($password)) {
                $error = "Vui lòng nhập đầy đủ thông tin!";
                require "app/views/auth/login.php";
                return;
            }

            $user = $this->model->getUserByEmailOrPhone($account);

            if ($user && password_verify($password, $user["password"])) {

                $_SESSION["user"] = [
                    "id" => $user["id"],
                    "name" => $user["name"],
                    "email" => $user["email"],
                    "phone" => $user["phone"],
                    "role" => $user["role"],
                    "avatar" => $user["avatar"] ?? "",
                ];

                if ($remember) {
                    setcookie("user_id", $user["id"], time() + (86400 * 30), "/");
                }

                if ($user["role"] === 'admin') {
                    header("Location: index.php?controller=admin&action=dashboard");
                } else {
                    header("Location: index.php?controller=dashboard&action=index");
                }
                exit;

                exit;
            } else {
                $error = "Sai tài khoản hoặc mật khẩu!";
            }
        }

        require "app/views/auth/login.php";
    }
    // =========================
    // REGISTER
    // =========================
    public function register()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $name = $_POST["name"];
            $phone = $_POST["phone"];
            $email = $_POST["email"];
            $password = $_POST["password"];
            $confirm = $_POST["confirm_password"];

            // ===== VALIDATE =====
            if ($password !== $confirm) {
                $error = "Mật khẩu không khớp!";
                require "app/views/auth/register.php";
                return;
            }

            if ($this->model->checkEmail($email)) {
                $error = "Email đã tồn tại!";
                require "app/views/auth/register.php";
                return;
            }

            // ===== HASH =====
            $hash = password_hash($password, PASSWORD_BCRYPT);

            // ===== CREATE USER =====
            $user_id = $this->model->createUser($name, $phone, $email, $hash);

            if ($user_id) {

                // 🔥 TẠO PROFILE SAU KHI CÓ ID
                $this->model->createProfile($user_id);

                header("Location: index.php?controller=user&action=login");
                exit;
            } else {
                $error = "Đăng ký thất bại!";
            }
        }

        require "app/views/auth/register.php";
    }

    // =========================
    // FORGOT PASSWORD
    // =========================
    public function forgotPassword()
    {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $account = trim($_POST["account"] ?? "");

            if (empty($account)) {
                $error = "Vui lòng nhập email hoặc số điện thoại!";
                require "app/views/auth/forgot.php";
                return;
            }

            // 🔥 Tìm user
            $user = $this->model->getUserByEmailOrPhone($account);

            if (!$user) {
                $error = "Tài khoản không tồn tại!";
                require "app/views/auth/forgot.php";
                return;
            }

            // 🔥 Reset password = 123456
            $newPassword = "123456";
            $hash = password_hash($newPassword, PASSWORD_BCRYPT);

            if ($this->model->updatePasswordById($user["id"], $hash)) {
                $success = "Mật khẩu mới của bạn là: 123456";
            } else {
                $error = "Không thể đặt lại mật khẩu!";
            }
        }

        require "app/views/auth/forgot.php";
    }

    // =========================
    // LOGOUT
    // =========================
    public function logout()
    {
        session_destroy();
        setcookie("user_id", "", time() - 3600, "/");

        header("Location: index.php");
        exit;
    }

    public function setting()
    {
        if (!isset($_SESSION["user"])) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $user = $_SESSION["user"];

        // lấy data mới nhất từ DB
        $userData = $this->model->getById($user["id"]);
        ob_start();
        require "app/views/setting/setting.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    public function update()
    {
        if (!isset($_SESSION["user"])) return;

        $user = $_SESSION["user"];

        $name = $_POST["name"] ?? "";
        $email = $_POST["email"] ?? "";

        $avatar = $user["avatar"] ?? "";

        // upload avatar
        if (!empty($_FILES["avatar"]["name"])) {
            $path = "public/uploads/" . time() . "_" . $_FILES["avatar"]["name"];
            move_uploaded_file($_FILES["avatar"]["tmp_name"], $path);
            $avatar = $path;
        }

        $this->model->updateUser($user["id"], $name, $email, $avatar);

        // update session
        $_SESSION["user"]["name"] = $name;
        $_SESSION["user"]["avatar"] = $avatar;

        header("Location: index.php?controller=user&action=setting");
    }

    public function changePassword()
    {
        if (!isset($_SESSION["user"])) return;

        $user = $_SESSION["user"];

        $old = $_POST["old_password"] ?? "";
        $new = $_POST["new_password"] ?? "";

        $userData = $this->model->getById($user["id"]);

        if (!password_verify($old, $userData["password"])) {
            die("❌ Sai mật khẩu cũ");
        }

        $hash = password_hash($new, PASSWORD_BCRYPT);

        $this->model->updatePassword($user["id"], $hash);

        header("Location: index.php?controller=user&action=setting");
    }

    public function updateProfile()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: index.php?controller=user&action=setting");
            exit;
        }

        $user = $_SESSION['user'];

        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (!$name) {
            die("Tên không được để trống");
        }

        // 👉 update DB
        $updated = $this->model->updateProfile($user['id'], $name, $phone);

        if ($updated) {
            // 🔥 cập nhật lại session luôn (quan trọng)
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['phone'] = $phone;

            header("Location: index.php?controller=user&action=setting&success=1");
            exit;
        } else {
            die("Update thất bại");
        }
    }

    public function updatePassword()
    {
        if (!isset($_SESSION['user'])) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        if ($_SERVER["REQUEST_METHOD"] === "POST") {

            $user_id = $_SESSION['user']['id'];

            $old = $_POST['old_password'] ?? '';
            $new = $_POST['new_password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';

            // ===== VALIDATE =====
            if (empty($old) || empty($new) || empty($confirm)) {
                header("Location: index.php?controller=user&action=setting&error=Vui lòng nhập đủ mật khẩu");
                exit;
            }

            if ($new !== $confirm) {
                header("Location: index.php?controller=user&action=setting&error=Mật khẩu không khớp");
                exit;
            }

            // ===== LẤY USER =====
            $user = $this->model->getById($user_id);

            if (!$user || !password_verify($old, $user['password'])) {
                header("Location: index.php?controller=user&action=setting&error=Mật khẩu cũ sai");
                exit;
            }

            // ===== UPDATE =====
            $hash = password_hash($new, PASSWORD_BCRYPT);

            if ($this->model->updatePasswordById($user_id, $hash)) {
                header("Location: index.php?controller=user&action=setting&success=1");
                exit;
            } else {
                header("Location: index.php?controller=user&action=setting&error=Lỗi cập nhật");
                exit;
            }
        }
    }
}
