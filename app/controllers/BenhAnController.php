<?php
require_once "app/helpers/SessionHelper.php";
require_once "app/models/BenhAnModel.php";
require_once "app/services/AIService.php";
class BenhAnController
{
    private $model;

    public function __construct()
    {
        SessionHelper::start();
        $this->model = new BenhAnModel();
    }

    private function uploadMedicalRecordImage(): ?string
    {
        if (empty($_FILES['hinh_anh']) || ($_FILES['hinh_anh']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        $file = $_FILES['hinh_anh'];
        if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            error_log("BenhAnController::uploadMedicalRecordImage upload error: " . $file['error']);
            return null;
        }

        $mimeType = function_exists('mime_content_type') ? (mime_content_type($file['tmp_name']) ?: '') : '';
        if ($mimeType !== '' && stripos($mimeType, 'image/') !== 0) {
            error_log("BenhAnController::uploadMedicalRecordImage invalid mime: " . $mimeType);
            return null;
        }

        $uploadDir = "public/uploads/avatar/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $extension = 'jpg';
        }

        $fileName = time() . "_medical_" . uniqid() . "." . $extension;
        $uploadPath = $uploadDir . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
            error_log("BenhAnController::uploadMedicalRecordImage cannot move file to: " . $uploadPath);
            return null;
        }

        return $fileName;
    }

    // =========================
    // DANH SÁCH HỒ SƠ BỆNH ÁN
    // =========================
    public function index()
    {
        $user = SessionHelper::user();
        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $user_id = $user['id'];
        $benhAn = $this->model->getAll($user_id);
        $totalCount = count($benhAn);
        $lastUpdate = null;
        if ($totalCount > 0) {
            $lastUpdate = date('d/m/Y', strtotime($benhAn[0]['ngay_cap_nhat']));
        }

        $stats = [
            [
                'loai' => 'Tổng hồ sơ bệnh án',
                'count' => $totalCount,
                'last_update' => $lastUpdate,
            ]
        ];

        $grouped = [
            'Hồ sơ bệnh án' => $benhAn,
        ];

        ob_start();
        require "app/views/benh_an/history.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    // =========================
    // XEM CHI TIẾT HỒ SƠ BỆNH ÁN
    // =========================
    public function view()
    {
        $user = SessionHelper::user();
        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header("Location: index.php?controller=benh_an&action=index");
            exit;
        }

        $benhAn = $this->model->getAll($user['id']);
        $record = null;
        
        foreach ($benhAn as $item) {
            if ($item['id'] == $id) {
                $record = $item;
                break;
            }
        }

        if (!$record) {
            die("❌ Không tìm thấy hồ sơ bệnh án");
        }

        ob_start();
        require "app/views/benh_an/detail.php";
        $content = ob_get_clean();
        require "app/views/layout.php";
    }

    // =========================
    // FORM THÊM HỒ SƠ BỆNH ÁN
    // =========================
   

    // =========================
    // LƯU HỒ SƠ BỆNH ÁN
    // =========================
    public function save()
    {
        $user = SessionHelper::user();
        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $loai = $_POST['loai'] ?? '';
        $ten_muc = $_POST['ten_muc'] ?? '';
        $gia_tri = $_POST['gia_tri'] ?? '';

        if (empty($loai) || empty($ten_muc)) {
            echo json_encode(['success' => false, 'message' => 'Vui lòng điền đầy đủ thông tin']);
            return;
        }

        $hinh_anh = $this->uploadMedicalRecordImage();
        $id = $this->model->add($user['id'], $loai, $ten_muc, $gia_tri, $hinh_anh);

        if ($id) {
            header("Location: index.php?controller=benh_an&action=index");
        } else {
            echo "❌ Lỗi lưu hồ sơ bệnh án";
        }
    }

    // =========================
    // FORM CHỈNH SỬA HỒ SƠ BỆNH ÁN
    // =========================
    public function edit()
    {
        $user = SessionHelper::user();
        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header("Location: index.php?controller=benh_an&action=index");
            exit;
        }

        $benhAn = $this->model->getAll($user['id']);
        $record = null;
        
        foreach ($benhAn as $item) {
            if ($item['id'] == $id) {
                $record = $item;
                break;
            }
        }

        if (!$record) {
            die("❌ Không tìm thấy hồ sơ bệnh án");
        }

        header("Location: index.php?controller=benh_an&action=view&id=" . $id . "&mode=edit");
        exit;
    }

    // =========================
    // CẬP NHẬT HỒ SƠ BỆNH ÁN
    // =========================
    public function update()
    {
        $user = SessionHelper::user();
        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $id = intval($_POST['id'] ?? 0);
        $ten_muc = $_POST['ten_muc'] ?? '';
        $gia_tri = $_POST['gia_tri'] ?? '';

        if (!$id || empty($ten_muc)) {
            echo "❌ Dữ liệu không hợp lệ";
            return;
        }

        $hinh_anh = $this->uploadMedicalRecordImage();
        $result = $this->model->update($id, $user['id'], $ten_muc, $gia_tri, $hinh_anh);

        if ($result) {
            header("Location: index.php?controller=benh_an&action=view&id=" . $id);
        } else {
            echo "❌ Lỗi cập nhật hồ sơ bệnh án";
        }
    }

    // =========================
    // XÓA HỒ SƠ BỆNH ÁN
    // =========================
    public function delete()
    {
        $user = SessionHelper::user();
        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $id = intval($_GET['id'] ?? 0);
        if (!$id) {
            header("Location: index.php?controller=benh_an&action=index");
            exit;
        }

        $result = $this->model->delete($id, $user['id']);

        if ($result) {
            header("Location: index.php?controller=benh_an&action=index");
        } else {
            echo "❌ Lỗi xóa hồ sơ bệnh án";
        }
    }

    // =========================
    // NHẬP BATCH DỮ LIỆU (AJAX)
    // =========================
    public function importBatch()
    {
        header('Content-Type: application/json');

        $user = SessionHelper::user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $data = json_decode($_POST['data'] ?? '[]', true);
        
        if (!is_array($data) || empty($data)) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        $inserted = $this->model->importBatch($user['id'], $data);

        echo json_encode([
            'success' => true,
            'message' => "Đã nhập $inserted mục hồ sơ bệnh án",
            'count' => $inserted
        ]);
    }

    // =========================
    // LẤY THỐNG KÊ (AJAX)
    // =========================
    public function getStats()
    {
        header('Content-Type: application/json');

        $user = SessionHelper::user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $stats = $this->model->getStats($user['id']);

        echo json_encode([
            'success' => true,
            'stats' => $stats
        ]);
    }
}
