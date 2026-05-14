<?php
require_once "app/helpers/SessionHelper.php";
require_once "app/models/NutritionModel.php";
require_once "app/models/DashboardModel.php";
require_once "app/models/BenhAnModel.php";

class NutritionController
{
    private $model;
    private $dashboardModel;
    private $benhAnModel;

    public function __construct()
    {
        SessionHelper::start();
        $this->model = new NutritionModel();
        $this->dashboardModel = new DashboardModel();
        $this->benhAnModel = new BenhAnModel();
    }

    // =========================
    // VIEW PROFILE
    // =========================
    public function list()
{
    $user = SessionHelper::user();

    if (!$user) {
        header("Location: index.php?controller=user&action=login");
        exit;
    }

    $user_id = $user['id'];

    // ===== GET DATA =====
    $profile = $this->model->getProfile($user_id) ?? [];
    $weights = $this->model->getWeightLogs($user_id) ?? [];

    $currentWeight = !empty($weights)
        ? floatval($weights[0]['can_nang'])
        : floatval($profile['cannang'] ?? 0);

    $height = floatval($profile['chieucao'] ?? 0);
    $age    = intval($profile['tuoi'] ?? 0);
    $gender = $profile['gioitinh'] ?? 'Nam';

    // =========================
    // BMI
    // =========================
    $bmi = 0;

    if ($height > 0 && $currentWeight > 0) {
        $h = $height / 100;
        $bmi = $currentWeight / ($h * $h);
        $bmi = round($bmi, 1);
    }

    // =========================
    // BODY FAT (AI)
    // =========================
    $tilemo = 0;

    if ($bmi > 0 && $age > 0) {

        if ($gender == 'Nam') {
            $tilemo = (1.2 * $bmi) + (0.23 * $age) - 5.4;
        } else {
            $tilemo = (1.2 * $bmi) + (0.23 * $age) - 5.4 + 10.8;
        }

        $tilemo = round($tilemo, 1);
    }

    // =========================
    // TDEE
    // =========================
    $tdee = 0;

    if ($height > 0 && $currentWeight > 0 && $age > 0) {

        if ($gender == 'Nam') {
            $bmr = 10 * $currentWeight + 6.25 * $height - 5 * $age + 5;
        } else {
            $bmr = 10 * $currentWeight + 6.25 * $height - 5 * $age - 161;
        }

        $tdee = $bmr * 1.4;
    }

    // =========================
    // ACTUAL DAILY MACROS
    // =========================
    $macrosToday = $this->dashboardModel->getMacrosToday($user_id) ?? ['protein' => 0, 'carbs' => 0, 'fat' => 0];
    $protein = intval($macrosToday['protein'] ?? 0);
    $carbs   = intval($macrosToday['carbs'] ?? 0);
    $fat     = intval($macrosToday['fat'] ?? 0);

    // kcal_target từ user hoặc mặc định
    $kcal_target = intval($profile['kcal_target'] ?? 2000);

    // kcal consumed today
    $kcal_today = intval($this->dashboardModel->getCaloToday($user_id) ?? 0);

    // =========================
    // RENDER VIEW
    // =========================
    ob_start();
    require "app/views/profile/ho_so_dinh_duong.php";
    $content = ob_get_clean();

    require "app/views/layout.php";
}

    // =========================
    // FORM EDIT
    // =========================
    public function edit()
    {
        $user = SessionHelper::user();

        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $user_id = $user['id'];

        $profile = $this->model->getProfile($user_id) ?? [];
        $weights = $this->model->getWeightLogs($user_id) ?? [];

        $currentWeight = !empty($weights)
            ? $weights[0]['can_nang']
            : 0;

    
        ob_start();

 require "app/views/profile/edit_ho_so.php";

$content = ob_get_clean();

require "app/views/layout.php";
    }

    // =========================
    // UPDATE PROFILE
    // =========================
    public function update()
    {
        $user = SessionHelper::user();

        if (!$user) {
            header("Location: index.php?controller=user&action=login");
            exit;
        }

        $user_id = $user['id'];

        $data = [
            'chieucao' => $_POST['chieucao'] ?? 0,
            'tuoi' => $_POST['tuoi'] ?? 0,
            'gioitinh' => $_POST['gioitinh'] ?? '',
            'tinhtrang_suckhoe' => $_POST['tinhtrang_suckhoe'] ?? '',
            'chedo_an' => $_POST['chedo_an'] ?? '',
            'muctieu' => $_POST['muctieu'] ?? '',
            'muctieu_cannang' => $_POST['muctieu_cannang'] ?? 0,
            'kcal_target' => intval($_POST['kcal_target'] ?? 2000)
        ];

        $weightGoalValidation = $this->model->validateProfileWeightGoal(
            $user_id,
            $_POST['cannang'] ?? 0,
            $data['muctieu_cannang'],
            $data['muctieu']
        );

        if (!$weightGoalValidation['valid']) {
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => $weightGoalValidation['message']
            ];
            header("Location: index.php?controller=nutrition&action=edit&id=" . $user_id);
            exit;
        }

        $this->model->updateProfile($user_id, $data);

        // Lưu phân tích hồ sơ bệnh án nếu có
        if (!empty($_POST['medical_analysis_data'])) {
            $analysisData = json_decode($_POST['medical_analysis_data'], true);

            if (is_array($analysisData)) {
                $healthMetrics = $analysisData['health_metrics'] ?? [];
                $imageName = $healthMetrics['medical_record_image'] ?? null;

                $analysisSummary = [
                    'findings' => $analysisData['findings'] ?? '',
                    'recommendations' => $analysisData['recommendations'] ?? '',
                    'diagnosis' => $analysisData['diagnosis'] ?? '',
                    'treatment' => $analysisData['treatment'] ?? '',
                    'vitals' => $analysisData['vitals'] ?? '',
                    'chan_doan' => $analysisData['chan_doan'] ?? [],
                    'huong_dieu_tri' => $analysisData['huong_dieu_tri'] ?? '',
                    'chi_so_sinh_hieu' => $analysisData['chi_so_sinh_hieu'] ?? [],
                    'loi_khuyen_dinh_duong' => $analysisData['loi_khuyen_dinh_duong'] ?? '',
                    'ket_qua_xet_nghiem' => $analysisData['ket_qua_xet_nghiem'] ?? '',
                    'xquang' => $analysisData['xquang'] ?? '',
                    'lam_sang' => $analysisData['lam_sang'] ?? '',
                    'health_metrics' => $healthMetrics,
                ];
                $analysisJson = json_encode($analysisSummary, JSON_UNESCAPED_UNICODE);

                $hasAIAnalysis = !empty($analysisSummary['findings'])
                    || !empty($analysisSummary['recommendations'])
                    || !empty($analysisSummary['diagnosis'])
                    || !empty($analysisSummary['treatment'])
                    || !empty($analysisSummary['vitals'])
                    || !empty($analysisSummary['chan_doan'])
                    || !empty($analysisSummary['huong_dieu_tri'])
                    || !empty($analysisSummary['chi_so_sinh_hieu'])
                    || !empty($analysisSummary['loi_khuyen_dinh_duong'])
                    || !empty($analysisSummary['ket_qua_xet_nghiem'])
                    || !empty($analysisSummary['xquang'])
                    || !empty($analysisSummary['lam_sang'])
                    || !empty($imageName);

                if ($hasAIAnalysis) {
                    if ($imageName) {
                        $this->model->saveMedicalRecord($user_id, $imageName, $healthMetrics, $analysisJson);
                    } else {
                        $this->model->saveMedicalAnalysis($user_id, $analysisJson);
                    }
                }
            }
        }

        // lưu cân nặng
        if (!empty($_POST['cannang'])) {
            $weight = floatval($_POST['cannang']);
            $validation = $this->model->validateWeightChange($user_id, $weight);
            if (!$validation['valid']) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => $validation['message']
                ];
                header("Location: index.php?controller=nutrition&action=edit&id=" . $user_id);
                exit;
            }

            if (!$this->model->saveWeight($user_id, $weight)) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Không thể lưu cân nặng. Vui lòng thử lại.'
                ];
                header("Location: index.php?controller=nutrition&action=edit&id=" . $user_id);
                exit;
            }
        }

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Đã cập nhật hồ sơ dinh dưỡng.'
        ];
        header("Location: index.php?controller=nutrition&action=list");
    }

    // =========================
    // SAVE WEIGHT (AJAX)
    // =========================
    public function saveWeight()
    {
        header('Content-Type: application/json; charset=utf-8');
        $user = SessionHelper::user();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Bạn cần đăng nhập lại.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $user_id = $user['id'];
        $weight = floatval($_POST['cannang'] ?? 0);

        if ($weight <= 0) {
            echo json_encode(['success' => false, 'message' => 'Nhập cân nặng hợp lệ.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $validation = $this->model->validateWeightChange($user_id, $weight);
        if (!$validation['valid']) {
            echo json_encode(['success' => false, 'message' => $validation['message']], JSON_UNESCAPED_UNICODE);
            return;
        }

        $saved = $this->model->saveWeight($user_id, $weight);

        echo json_encode([
            'success' => $saved,
            'message' => $saved ? 'Đã lưu cân nặng.' : 'Lưu thất bại.'
        ], JSON_UNESCAPED_UNICODE);
    }

    // =========================
    // ANALYZE MEDICAL RECORD (AJAX)
    // =========================
    public function analyzeMedicalRecord()
    {
        header('Content-Type: application/json');

        try {
            $user = SessionHelper::user();
            if (!$user) {
                echo json_encode(['success' => false, 'message' => 'Unauthorized']);
                return;
            }

            if (!isset($_FILES['image'])) {
                echo json_encode(['success' => false, 'message' => 'Không có tệp ảnh']);
                return;
            }

            require_once "app/services/AIService.php";
            $aiService = new AIService();

            $file = $_FILES['image'];
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                error_log("NutritionController::analyzeMedicalRecord upload error: " . $file['error']);
                echo json_encode(['success' => false, 'message' => 'Lỗi tải file']);
                return;
            }

            $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid() . '_' . basename($file['name']);
            if (!move_uploaded_file($file['tmp_name'], $tempPath)) {
                error_log("NutritionController::analyzeMedicalRecord cannot move uploaded file: " . $file['tmp_name']);
                echo json_encode(['success' => false, 'message' => 'Không thể lưu file']);
                return;
            }

            $extractedData = [];
            $response = null;
            $analysisResult = [];
            $mimeType = mime_content_type($tempPath) ?: 'application/octet-stream';
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $isPdf = ($ext === 'pdf') || (stripos($mimeType, 'pdf') !== false);
            $isImage = stripos($mimeType, 'image/') === 0;

            $medicalJsonSchema = [
                "type" => "json_schema",
                "json_schema" => [
                    "name" => "medical_record_analysis",
                    "strict" => true,
                    "schema" => [
                        "type" => "object",
                        "additionalProperties" => false,
                        "properties" => [
                            "chan_doan" => [
                                "type" => "array",
                                "items" => ["type" => "string"]
                            ],
                            "huong_dieu_tri" => ["type" => "string"],
                            "chi_so_sinh_hieu" => [
                                "type" => "object",
                                "additionalProperties" => false,
                                "properties" => [
                                    "can_nang" => ["type" => "string"],
                                    "bmi" => ["type" => "string"],
                                    "huyet_ap" => ["type" => "string"]
                                ],
                                "required" => ["can_nang", "bmi", "huyet_ap"]
                            ],
                            "loi_khuyen_dinh_duong" => ["type" => "string"],
                            "ket_qua_xet_nghiem" => ["type" => "string"],
                            "xquang" => ["type" => "string"],
                            "lam_sang" => ["type" => "string"]
                        ],
                        "required" => [
                            "chan_doan",
                            "huong_dieu_tri",
                            "chi_so_sinh_hieu",
                            "loi_khuyen_dinh_duong",
                            "ket_qua_xet_nghiem",
                            "xquang",
                            "lam_sang"
                        ]
                    ]
                ]
            ];

            $buildMedicalPrompt = function (?string $ocrText = null): string {
                $base = "Ban la bo trich xuat du lieu y khoa than trong cao. "
                    . "Chi tra ve DUY NHAT JSON hop le theo schema. "
                    . "Khong markdown, khong giai thich.\n\n"
                    . "Quy tac:\n"
                    . "1) Chi dung thong tin co trong OCR/anh, khong bia dat.\n"
                    . "2) chan_doan uu tien muc 'IV. CHAN DOAN' hoac 'III. KHAM BENH'.\n"
                    . "3) Neu khong co chan doan ro, cho phep chan doan muc trieu chung tu 'LY DO VAO VIEN HOAC LY DO KHAM'.\n"
                    . "4) lam_sang lay tu 'B. THONG TIN KHAM BENH', gom ly do vao vien va hoi benh.\n"
                    . "5) huong_dieu_tri chi dien khi co y lenh/ke hoach dieu tri/toa thuoc.\n"
                    . "6) chi_so_sinh_hieu chi dien khi thay so lieu ro, neu khong de rong.\n"
                    . "7) loi_khuyen_dinh_duong ghi day du theo bac si nhung rut gon y ngan, lien quan rang-ham-mat, khong ke thuoc.\n"
                    . "8) ket_qua_xet_nghiem, xquang chi dien khi co bang chung ro trong noi dung.\n\n"
                    . "Schema can tra ve:\n"
                    . "{\n"
                    . "  \"chan_doan\": [],\n"
                    . "  \"huong_dieu_tri\": \"\",\n"
                    . "  \"chi_so_sinh_hieu\": {\"can_nang\":\"\", \"bmi\":\"\", \"huyet_ap\":\"\"},\n"
                    . "  \"loi_khuyen_dinh_duong\": \"\",\n"
                    . "  \"ket_qua_xet_nghiem\": \"\",\n"
                    . "  \"xquang\": \"\",\n"
                    . "  \"lam_sang\": \"\"\n"
                    . "}\n";

                if ($ocrText !== null && trim($ocrText) !== '') {
                    $base .= "\nVAN BAN OCR:\n" . $ocrText;
                }

                return $base;
            };

            if (!$isPdf && !$isImage) {
                @unlink($tempPath);
                echo json_encode([
                    'success' => false,
                    'message' => 'Chi ho tro file anh (JPG/PNG/WebP) hoac PDF.'
                ]);
                return;
            }

            if ($isPdf) {
                $pdfText = $this->extractTextFromPdf($tempPath);
                if ($this->isLowQualityOcrText($pdfText)) {
                    @unlink($tempPath);
                    echo json_encode([
                        'success' => false,
                        'message' => "Khong the phan tich ro tu file PDF.\n\nGoi y de thu lai:\n1. Dung PDF text ro rang (khong phai scan mo).\n2. Neu la ban scan, hay tang do net/anh sang.\n3. Thu tai tep khac ro hon."
                    ]);
                    return;
                }

                $extractedData = ['raw_text' => $pdfText];
                $retryPrompt = $buildMedicalPrompt($pdfText);

                $response = $aiService->callOpenAI([
                    "model" => "gpt-4o",
                    "response_format" => $medicalJsonSchema,
                    "contents" => [[
                        "parts" => [["text" => $retryPrompt]]
                    ]]
                ]);
                $analysisResult = $this->extractJsonObjectFromText((string)$response);
            }

            if (!$isPdf) {
                $imageData = file_get_contents($tempPath);
                $base64Image = base64_encode($imageData);
            }

            $prompt = $buildMedicalPrompt();

            if (!$isPdf) {
                $data = [
                    "model" => "gpt-4o",
                    "response_format" => $medicalJsonSchema,
                    "contents" => [[
                        "parts" => [
                            ["text" => $prompt],
                            [
                                "inline_data" => [
                                    "mime_type" => $mimeType,
                                    "data" => $base64Image
                                ]
                            ]
                        ]
                    ]]
                ];

                $response = $aiService->callOpenAI($data);
                $analysisResult = $this->extractJsonObjectFromText((string)$response);
            }

            if (empty($analysisResult) && !$isPdf) {
                $extractedData = $aiService->extractMedicalData($tempPath);
                $rawTextForRetry = trim((string)($extractedData['raw_text'] ?? ''));
                if ($rawTextForRetry !== '') {
                    $retryPrompt = $buildMedicalPrompt($rawTextForRetry);

                    $retryResponse = $aiService->callOpenAI([
                        "model" => "gpt-4o",
                        "response_format" => $medicalJsonSchema,
                        "contents" => [[
                            "parts" => [["text" => $retryPrompt]]
                        ]]
                    ]);
                    $analysisResult = $this->extractJsonObjectFromText((string)$retryResponse);
                }
            }

            if ($this->isLowQualityOcrText((string)($extractedData['raw_text'] ?? '')) && empty($analysisResult)) {
                @unlink($tempPath);
                echo json_encode([
                    'success' => false,
                    'message' => "Khong the phan tich ro tu anh.\n\nGoi y de thu lai:\n1. Chup thang mat giay, du sang, khong rung tay.\n2. Chup gan hon de chu to, khong cat mat noi dung.\n3. Uu tien anh/PDF ro net."
                ]);
                return;
            }

            $analysisResult = $this->normalizeMedicalAnalysis($analysisResult);
            $hasStructuredAnalysis = !empty($analysisResult['chan_doan'])
                || !empty($analysisResult['huong_dieu_tri'])
                || $this->stringifyMedicalValue($analysisResult['chi_so_sinh_hieu'] ?? []) !== ''
                || !empty($analysisResult['loi_khuyen_dinh_duong'])
                || !empty($analysisResult['ket_qua_xet_nghiem'])
                || !empty($analysisResult['xquang'])
                || !empty($analysisResult['lam_sang']);

            if (empty($analysisResult['findings']) && !$hasStructuredAnalysis) {
                $analysisResult['findings'] = $response ?: 'Không thể phân tích ảnh';
            }
            if (empty($analysisResult['recommendations']) && !$hasStructuredAnalysis) {
                $analysisResult['recommendations'] = 'Vui lòng tham khảo ý kiến bác sĩ để có hướng dẫn chi tiết';
            }

            if ($response === null || trim((string)$response) === '') {
                $analysisResult['findings'] = '';
                $analysisResult['recommendations'] = '';
            }

            $hasMainAnalysis = !empty($analysisResult['diagnosis'])
                || !empty($analysisResult['treatment'])
                || !empty($analysisResult['vitals'])
                || !empty($analysisResult['findings'])
                || !empty($analysisResult['recommendations'])
                || !empty($analysisResult['chan_doan'])
                || !empty($analysisResult['huong_dieu_tri'])
                || $this->stringifyMedicalValue($analysisResult['chi_so_sinh_hieu'] ?? []) !== ''
                || !empty($analysisResult['loi_khuyen_dinh_duong'])
                || !empty($analysisResult['ket_qua_xet_nghiem'])
                || !empty($analysisResult['xquang'])
                || !empty($analysisResult['lam_sang']);

            if (!$hasMainAnalysis) {
                $extractedData = $aiService->extractMedicalData($tempPath);
            } elseif (empty($analysisResult['health_metrics']['raw_text'])) {
                // Keep OCR text as a fallback display even when top-level analysis exists.
                $fallbackExtracted = $aiService->extractMedicalData($tempPath);
                if (!empty($fallbackExtracted['raw_text'])) {
                    $extractedData = array_merge($extractedData, ['raw_text' => $fallbackExtracted['raw_text']]);
                }
            }

            $analysisResult['health_metrics'] = array_merge($analysisResult['health_metrics'] ?? [], $extractedData);
            foreach (['diagnosis', 'treatment', 'vitals', 'findings', 'recommendations'] as $field) {
                if (empty($analysisResult[$field]) && !empty($extractedData[$field])) {
                    $analysisResult[$field] = $extractedData[$field];
                }
            }
            if (empty($analysisResult['diagnosis']) && !empty($extractedData['tinhtrang_suckhoe'])) {
                $analysisResult['diagnosis'] = $extractedData['tinhtrang_suckhoe'];
            }

            if (
                empty($analysisResult['treatment'])
                && empty($analysisResult['huong_dieu_tri'])
                && !empty($analysisResult['health_metrics']['raw_text'])
            ) {
                $rxFallback = $this->extractPrescriptionFromRawText($analysisResult['health_metrics']['raw_text']);
                if (!empty($rxFallback['treatment'])) {
                    $analysisResult['treatment'] = $rxFallback['treatment'];
                    $analysisResult['huong_dieu_tri'] = $analysisResult['huong_dieu_tri'] ?: $rxFallback['treatment'];
                    $analysisResult['health_metrics']['dieu_tri'] = $rxFallback['treatment'];
                }
                if (empty($analysisResult['findings']) && !empty($rxFallback['findings'])) {
                    $analysisResult['findings'] = $rxFallback['findings'];
                }
                if (empty($analysisResult['recommendations']) && !empty($rxFallback['recommendations'])) {
                    $analysisResult['recommendations'] = $rxFallback['recommendations'];
                    $analysisResult['loi_khuyen_dinh_duong'] = $analysisResult['loi_khuyen_dinh_duong'] ?: $rxFallback['recommendations'];
                }
            }

            if (
                empty($analysisResult['findings'])
                && empty($analysisResult['diagnosis'])
                && empty($analysisResult['treatment'])
                && empty($analysisResult['vitals'])
                && !empty($analysisResult['health_metrics']['raw_text'])
            ) {
                $analysisResult['health_metrics']['needs_manual_review'] = 1;
            }

            $uploadDir = 'public/uploads/avatar/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileName = time() . '_medical_' . uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $uploadPath = $uploadDir . $fileName;

            if (copy($tempPath, $uploadPath)) {
                $analysisResult['health_metrics']['medical_record_image'] = $fileName;
            } else {
                error_log("NutritionController::analyzeMedicalRecord cannot copy file to upload dir: " . $uploadPath);
            }

            $analysisJson = json_encode([
                'findings' => $analysisResult['findings'] ?? '',
                'recommendations' => $analysisResult['recommendations'] ?? '',
                'diagnosis' => $analysisResult['diagnosis'] ?? '',
                'treatment' => $analysisResult['treatment'] ?? '',
                'vitals' => $analysisResult['vitals'] ?? '',
                'chan_doan' => $analysisResult['chan_doan'] ?? [],
                'huong_dieu_tri' => $analysisResult['huong_dieu_tri'] ?? '',
                'chi_so_sinh_hieu' => $analysisResult['chi_so_sinh_hieu'] ?? [],
                'loi_khuyen_dinh_duong' => $analysisResult['loi_khuyen_dinh_duong'] ?? '',
                'ket_qua_xet_nghiem' => $analysisResult['ket_qua_xet_nghiem'] ?? '',
                'xquang' => $analysisResult['xquang'] ?? '',
                'lam_sang' => $analysisResult['lam_sang'] ?? '',
                'health_metrics' => $analysisResult['health_metrics'] ?? [],
                'raw_text' => $analysisResult['health_metrics']['raw_text'] ?? ''
            ], JSON_UNESCAPED_UNICODE);

            $this->model->saveMedicalRecord($user['id'], $analysisResult['health_metrics']['medical_record_image'] ?? null, $analysisResult['health_metrics'], $analysisJson);

            // 🔥 LƯU DỮ LIỆU VÀO BẢNG benh_an
            $this->saveMedicalDataToBenhAn($user['id'], $analysisResult);

            @unlink($tempPath);

            $finalHasContent = !empty($analysisResult['diagnosis'])
                || !empty($analysisResult['treatment'])
                || !empty($analysisResult['vitals'])
                || !empty($analysisResult['findings'])
                || !empty($analysisResult['recommendations'])
                || !empty($analysisResult['chan_doan'])
                || !empty($analysisResult['huong_dieu_tri'])
                || $this->stringifyMedicalValue($analysisResult['chi_so_sinh_hieu'] ?? []) !== ''
                || !empty($analysisResult['loi_khuyen_dinh_duong'])
                || !empty($analysisResult['ket_qua_xet_nghiem'])
                || !empty($analysisResult['xquang'])
                || !empty($analysisResult['lam_sang'])
                || !empty($analysisResult['health_metrics']['raw_text']);

            if (!$finalHasContent) {
                echo json_encode([
                    'success' => false,
                    'message' => 'AI chua trich xuat duoc du lieu tu anh nay. Vui long thu anh ro hon hoac doi dinh dang.'
                ]);
                return;
            }

            echo json_encode([
                'success' => true,
                'analysis' => $analysisResult
            ]);
        } catch (Throwable $ex) {
            error_log('NutritionController::analyzeMedicalRecord exception: ' . $ex->getMessage() . '\n' . $ex->getTraceAsString());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Lỗi nội bộ server']);
        }
    }

    // =========================
    // SAVE MEDICAL ANALYSIS (AJAX)
    // =========================
    public function saveMedicalAnalysis()
    {
        header('Content-Type: application/json');

        $user = SessionHelper::user();
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized']);
            return;
        }

        $analysis = json_decode($_POST['analysis'] ?? '{}', true);
        
        if (!$analysis) {
            echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
            return;
        }

        // Save analysis to user profile or create medical records table
        $user_id = $user['id'];
        $analysisJson = json_encode($analysis);
        
        // For now, save to a custom field or create a new medical_records entry
        // This would require a database table like: medical_records (id, user_id, analysis, created_at)
        
        // Update profile with latest analysis
        $this->model->saveMedicalAnalysis($user_id, $analysisJson);

        echo json_encode(['success' => true, 'message' => 'Đã lưu phân tích']);
    }

    // =========================
    // LƯU DỮ LIỆU BỆNH ÁN VÀO BẢNG benh_an
    // =========================
    private function normalizeMedicalAnalysis(array $analysis): array
    {
        $analysis['chan_doan'] = $analysis['chan_doan'] ?? ($analysis['diagnosis'] ?? []);
        if (is_string($analysis['chan_doan'])) {
            $analysis['chan_doan'] = array_values(array_filter(array_map('trim', preg_split('/[,;\n]+/', $analysis['chan_doan']))));
        }
        if (!is_array($analysis['chan_doan'])) {
            $analysis['chan_doan'] = [];
        }

        $analysis['huong_dieu_tri'] = $analysis['huong_dieu_tri'] ?? ($analysis['treatment'] ?? '');
        $analysis['loi_khuyen_dinh_duong'] = $analysis['loi_khuyen_dinh_duong'] ?? ($analysis['recommendations'] ?? '');
        $analysis['ket_qua_xet_nghiem'] = $analysis['ket_qua_xet_nghiem'] ?? '';
        $analysis['xquang'] = $analysis['xquang'] ?? '';
        $analysis['lam_sang'] = $analysis['lam_sang'] ?? '';

        $vitals = $analysis['chi_so_sinh_hieu'] ?? ($analysis['vitals'] ?? []);
        if (is_string($vitals)) {
            $vitals = ['mo_ta' => $vitals];
        }
        if (!is_array($vitals)) {
            $vitals = [];
        }

        $analysis['chi_so_sinh_hieu'] = [
            'can_nang' => $vitals['can_nang'] ?? ($vitals['cannang'] ?? ''),
            'bmi' => $vitals['bmi'] ?? '',
            'huyet_ap' => $vitals['huyet_ap'] ?? '',
        ];
        if (!empty($vitals['mo_ta'])) {
            $analysis['chi_so_sinh_hieu']['mo_ta'] = $vitals['mo_ta'];
        }

        $analysis['diagnosis'] = $analysis['diagnosis'] ?? implode(', ', $analysis['chan_doan']);
        $analysis['treatment'] = $analysis['treatment'] ?? $analysis['huong_dieu_tri'];
        $analysis['vitals'] = $analysis['vitals'] ?? $this->stringifyMedicalValue($analysis['chi_so_sinh_hieu']);
        $analysis['findings'] = $analysis['findings'] ?? trim(implode("\n", array_filter([
            $analysis['lam_sang'],
            $analysis['ket_qua_xet_nghiem'],
            $analysis['xquang'],
        ])));
        $analysis['recommendations'] = $analysis['recommendations'] ?? $analysis['loi_khuyen_dinh_duong'];

        $analysis['health_metrics'] = $analysis['health_metrics'] ?? [];
        foreach ($analysis['chi_so_sinh_hieu'] as $key => $value) {
            if ($value !== '') {
                $analysis['health_metrics'][$key] = $value;
            }
        }

        if (!empty($analysis['chan_doan'])) {
            $analysis['health_metrics']['chan_doan'] = implode(', ', $analysis['chan_doan']);
        }
        if (!empty($analysis['huong_dieu_tri'])) {
            $analysis['health_metrics']['dieu_tri'] = $analysis['huong_dieu_tri'];
        }

        return $analysis;
    }

    private function extractJsonObjectFromText(string $text): array
    {
        $text = trim($text);
        if ($text === '') {
            return [];
        }

        $decoded = json_decode($text, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        if (preg_match('/\{[\s\S]*\}/', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return [];
    }

    private function isLowQualityOcrText(string $text): bool
    {
        $normalized = trim(preg_replace('/\s+/u', ' ', $text));
        if ($normalized === '') {
            return true;
        }

        $len = mb_strlen($normalized, 'UTF-8');
        $alphaNumCount = preg_match_all('/[\p{L}\p{N}]/u', $normalized);
        if ($len < 60 || $alphaNumCount < 40) {
            return true;
        }

        return false;
    }

    private function extractTextFromPdf(string $pdfPath): string
    {
        if (!$this->commandExists('pdftotext')) {
            return '';
        }

        $outputFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'pdf_ocr_' . uniqid();
        $command = sprintf(
            'pdftotext -layout -f 1 -l 3 %s %s 2>&1',
            escapeshellarg($pdfPath),
            escapeshellarg($outputFile)
        );
        exec($command, $output, $returnVar);

        if ($returnVar !== 0) {
            return '';
        }

        $txtPath = $outputFile . '.txt';
        if (!file_exists($txtPath)) {
            return '';
        }

        $text = file_get_contents($txtPath) ?: '';
        @unlink($txtPath);
        return trim($text);
    }

    private function commandExists(string $command): bool
    {
        if (stripos(PHP_OS, 'WIN') !== false) {
            $check = shell_exec("where {$command} 2>NUL");
            return !empty(trim((string)$check));
        }

        $check = shell_exec("command -v {$command} 2>/dev/null");
        return !empty(trim((string)$check));
    }

    private function stringifyMedicalValue($value): string
    {
        if (is_array($value)) {
            $parts = [];
            foreach ($value as $key => $item) {
                if ($item === '' || $item === null || $item === []) {
                    continue;
                }
                $parts[] = is_int($key)
                    ? $this->stringifyMedicalValue($item)
                    : $key . ': ' . $this->stringifyMedicalValue($item);
            }
            return implode("\n", $parts);
        }

        return trim((string)$value);
    }

    private function extractPrescriptionFromRawText(string $rawText): array
    {
        $text = trim($rawText);
        if ($text === '') {
            return [];
        }

        $lines = preg_split('/\r?\n/u', $text);
        $items = [];
        $take = false;

        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '') {
                continue;
            }

            if (preg_match('/\b(thuoc|don thuoc|chi dinh|toa thuoc)\b/iu', $line)) {
                $take = true;
                continue;
            }

            if ($take && preg_match('/^(?:\d+[\.\)]\s*)?(.{3,120})$/u', $line, $m)) {
                $candidate = trim($m[1]);
                if (preg_match('/\b(ngay|bac si|ky ten|sdt|dien thoai|benh vien|dia chi|qr)\b/iu', $candidate)) {
                    continue;
                }
                if (preg_match('/\b(mg|mcg|ml|vien|goi|lan|sang|trua|chieu|toi|uong)\b/iu', $candidate) || mb_strlen($candidate) >= 12) {
                    $items[] = $candidate;
                }
            }
        }

        $items = array_values(array_unique(array_slice($items, 0, 8)));
        if (empty($items)) {
            return [];
        }

        $treatment = "Don thuoc OCR:\n- " . implode("\n- ", $items);
        return [
            'treatment' => $treatment,
            'findings' => 'Trich xuat duoc danh sach thuoc va huong dan tu don thuoc.',
            'recommendations' => 'Tuan thu don thuoc cua bac si. An nhat, han che ruou bia, uong du nuoc va theo doi trieu chung bat thuong de tai kham.',
        ];
    }

    private function saveMedicalDataToBenhAn($user_id, $analysisResult)
    {
        try {
            $summary = [];
            $addSummary = function (string $label, $value) use (&$summary) {
                $value = $this->stringifyMedicalValue($value);
                if ($value === '') {
                    return;
                }

                $line = $label . ': ' . $value;
                if (!in_array($line, $summary, true)) {
                    $summary[] = $line;
                }
            };

            $addSummary('Chẩn đoán', $analysisResult['diagnosis'] ?? '');
            $addSummary('Chẩn đoán chi tiết', $analysisResult['chan_doan'] ?? []);
            $addSummary('Lâm sàng', $analysisResult['lam_sang'] ?? '');
            $addSummary('Kết luận y tế', $analysisResult['findings'] ?? '');
            $addSummary('Điều trị / Đơn thuốc', $analysisResult['treatment'] ?? '');
            $addSummary('Hướng điều trị', $analysisResult['huong_dieu_tri'] ?? '');
            $addSummary('Chỉ số sinh hiệu', $analysisResult['chi_so_sinh_hieu'] ?? []);
            $addSummary('Dữ liệu sinh hiệu', $analysisResult['vitals'] ?? '');
            $addSummary('Kết quả xét nghiệm', $analysisResult['ket_qua_xet_nghiem'] ?? '');
            $addSummary('X-quang / Chẩn đoán hình ảnh', $analysisResult['xquang'] ?? '');
            $addSummary('Khuyến nghị', $analysisResult['recommendations'] ?? '');
            $addSummary('Lời khuyên dinh dưỡng', $analysisResult['loi_khuyen_dinh_duong'] ?? '');

            if (!empty($analysisResult['findings'])) {
                $summary[] = 'Kết luận y tế: ' . $analysisResult['findings'];
            }
            if (!empty($analysisResult['recommendations'])) {
                $summary[] = 'Khuyến nghị: ' . $analysisResult['recommendations'];
            }
            if (!empty($analysisResult['diagnosis'])) {
                $summary[] = 'Chẩn đoán: ' . $analysisResult['diagnosis'];
            }
            if (!empty($analysisResult['treatment'])) {
                $summary[] = 'Hướng dẫn điều trị: ' . $analysisResult['treatment'];
            }

            foreach ([
                'ket_qua_xet_nghiem' => 'Ket qua xet nghiem',
                'xquang' => 'X-quang / chan doan hinh anh',
                'lam_sang' => 'Lam sang',
                'loi_khuyen_dinh_duong' => 'Loi khuyen dinh duong',
            ] as $key => $label) {
                if (!empty($analysisResult[$key])) {
                    $summary[] = $label . ': ' . $this->stringifyMedicalValue($analysisResult[$key]);
                }
            }

            $metrics = $analysisResult['health_metrics'] ?? [];
            $metricLabels = [
                'tuoi' => 'Tuổi',
                'gioitinh' => 'Giới tính',
                'chieucao' => 'Chiều cao',
                'cannang' => 'Cân nặng',
                'tilemo' => 'Tỉ lệ mỡ cơ thể',
                'nhip_tim' => 'Nhịp tim',
                'huyet_ap' => 'Huyết áp',
                'chan_doan' => 'Chẩn đoán chi tiết',
                'dieu_tri' => 'Điều trị chi tiết',
                'tinhtrang_suckhoe' => 'Tình trạng sức khỏe',
            ];

            foreach ($metricLabels as $key => $label) {
                if (!empty($metrics[$key])) {
                    $summary[] = $label . ': ' . $metrics[$key];
                }
            }

            $cleanSummary = [];
            $seenValues = [];
            foreach ($summary as $line) {
                $line = trim((string)$line);
                if ($line === '' || stripos($line, 'OCR') !== false) {
                    continue;
                }

                $value = $line;
                $colonPos = strpos($line, ':');
                if ($colonPos !== false) {
                    $value = trim(substr($line, $colonPos + 1));
                }

                $dedupeKey = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value)), 'UTF-8');
                if ($dedupeKey !== '' && isset($seenValues[$dedupeKey])) {
                    continue;
                }

                if ($dedupeKey !== '') {
                    $seenValues[$dedupeKey] = true;
                }
                $cleanSummary[] = $line;
            }
            $summary = $cleanSummary;

            if (empty($summary)) {
                $summary[] = 'Không có dữ liệu bệnh án.';
            }

            $imageName = $metrics['medical_record_image'] ?? null;
            $this->benhAnModel->add($user_id, 'benh_an', 'Hồ sơ bệnh án', implode("\n\n", $summary), $imageName);

            error_log("NutritionController::saveMedicalDataToBenhAn saved successfully for user $user_id");
        } catch (Throwable $ex) {
            error_log("NutritionController::saveMedicalDataToBenhAn error: " . $ex->getMessage());
        }
    }
}

