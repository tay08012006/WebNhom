<?php
 * PHẦN 1: XỬ LÝ SERVER (BACKEND - PHP)
 * Công dụng: Kiểm tra đăng nhập, phân quyền giáo viên và cung cấp API để đọc file text (parse file).
ob_start();
// 1. Kiểm tra và khởi tạo Session an toàn
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'GV_SESSION');
    session_start();
}
// 2. Phân quyền: Chỉ cho phép tài khoản có role là 'teacher' truy cập trang này
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    // Nếu là request gọi từ AJAX (khi upload file), trả về lỗi dạng JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) || !empty($_POST['action'])) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }
    // Nếu truy cập bình thường, đẩy về trang đăng nhập
    header('Location: ../trangdangnhap.php');
    exit;
}
$ma_lop = $_GET['malop'] ?? '';

 * API XỬ LÝ UPLOAD FILE AJAX
 * Giao tiếp với Javascript bên dưới khi người dùng Kéo & Thả hoặc Chọn file tải lên
if (isset($_POST['action']) && $_POST['action'] === 'parse_file') {
    ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');

    // Kiểm tra xem file có được đẩy lên thành công không
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'message' => 'Lỗi upload file']);
        exit;
    }

    $file = $_FILES['file'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $tmp = $file['tmp_name'];
    $text = '';
    // Lọc định dạng file (Hiện tại hệ thống xử lý tốt nhất file .txt)
    if ($ext === 'txt') {
        $raw = file_get_contents($tmp);
        $text = mb_convert_encoding($raw, 'UTF-8', 'auto'); // Đảm bảo tiếng Việt không bị lỗi font
    } elseif (in_array($ext, ['docx', 'doc', 'pdf'])) {
        echo json_encode(['success' => false, 'message' => 'Chỉ hỗ trợ file .txt lúc này']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'Định dạng không hỗ trợ']);
        exit;
    }
    // Gọi hàm phân tích thông minh để tách text thành mảng câu hỏi
    $questions = parseQuestionsImproved($text);
    ob_end_clean();
    // Trả kết quả về cho Javascript (dưới dạng JSON) để hiển thị lên giao diện
    echo json_encode([
        'success' => true,
        'questions' => $questions,
        'total' => count($questions)
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

 * HÀM PHÂN TÍCH TEXT THÀNH CÂU HỎI (PARSER THÔNG MINH)
 * Sử dụng Biểu thức chính quy (Regex) để nhận diện định dạng câu hỏi và đáp án do giáo viên soạn.
function parseQuestionsImproved(string $text): array {
    // Xóa ký tự BOM của UTF-8 (nếu có) để tránh lỗi string
    $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
    // Chuẩn hóa ký tự ngắt dòng về dạng \n
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $lines = explode("\n", $text);
    $questions = [];
    $cur = null; // Biến lưu tạm câu hỏi đang được phân tích
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;
        // ── NHẬN DIỆN CÂU HỎI ──────────────────────────────────────────────────
        // Hỗ trợ tất cả dạng: "Câu 1.", "Câu 1:", "Câu 1)", "Câu 1 -", "câu1.", v.v.
        $isQuestion = preg_match(
            '/^(?:(?:câu|question|q|cau)\s*)?(\d+)\s*[\.\:\)\-–—]\s*(.+)$/iu',
            $line, $m
        );
        // Trường hợp "Câu X" đứng riêng một dòng, nội dung ở dòng tiếp theo
        $isQuestionHeader = !$isQuestion && preg_match(
            '/^(?:câu|question|cau)\s*(\d+)\s*$/iu',
            $line, $mh
        );
        if ($isQuestion) {
            if ($cur) $questions[] = $cur; // Lưu câu hỏi trước đó (nếu có)
            $cur = [
                'num'     => (int)$m[1],
                'text'    => trim($m[2]),
                'options' => [],
                'correct' => 'A',
                'type'    => 'mc' // Mặc định là Multiple Choice (Trắc nghiệm)
            ];
            continue;
        }
        if ($isQuestionHeader) {
            if ($cur) $questions[] = $cur;
            $cur = [
                'num'     => (int)$mh[1],
                'text'    => '',
                'options' => [],
                'correct' => 'A',
                'type'    => 'mc'
            ];
            continue;
        }
        // NHẬN DIỆN ĐÁP ÁN A/B/C/D/E 
        // Hỗ trợ: "A.", "A)", "A:", "A-", "A–"
        if ($cur !== null && preg_match('/^([A-Ea-e])\s*[\.\)\:\-–—]\s*(.+)$/u', $line, $m)) {
            $letter = strtoupper($m[1]);
            $val = trim($m[2]);
            // Nếu nội dung đáp án có bọc dấu * (ví dụ: *Nội dung*), hệ thống tự hiểu đó là đáp án đúng
            if (preg_match('/^\*(.+?)\*?$|^(.+?)\*$/', $val, $star)) {
                $cur['correct'] = $letter;
                $val = trim($star[1] ?: $star[2]);
            }

            if (in_array($letter, ['A','B','C','D','E'])) {
                $cur['options'][$letter] = $val;
            }
            continue;
        }

        // NHẬN DIỆN DÒNG GHI ĐÁP ÁN ĐÚNG RIÊNG BIỆT 
        // Hỗ trợ: "Đáp án: A", "ĐA: B", "Đ.A: C", "Answer: D"
        if ($cur !== null && preg_match(
            '/^(?:đáp\s*án(?:\s*đúng)?|đ\.?\s*a|answer|ans|correct|key)\s*[:\-–]?\s*([A-Ea-e])/iu',
            $line, $m
        )) {
            $cur['correct'] = strtoupper($m[1]);
            continue;
        }

        // NỐI NỘI DUNG CÂU HỎI DÀI NHIỀU DÒNG 
        // Nếu dòng hiện tại không phải là đáp án hay câu hỏi mới, nối nó vào nội dung câu hỏi hiện tại
        if ($cur !== null && empty($cur['options'])) {
            $cur['text'] = trim($cur['text'] . ' ' . $line);
        }
    }

    if ($cur !== null) $questions[] = $cur;

    // ── CHUẨN HÓA LẠI ĐỊNH DẠNG ĐỂ GỬI XUỐNG FRONTEND ─────────────────────────────────
    $result = [];
    foreach ($questions as $q) {
        $opts = [];
        foreach (['A','B','C','D','E'] as $l) {
            if (isset($q['options'][$l])) $opts[] = $q['options'][$l];
        }
        $q['options'] = $opts;
        // Tự động phân loại dựa trên số lượng đáp án
        if (count($opts) === 2) $q['type'] = 'tf'; // Đúng/Sai (True/False)
        elseif (count($opts) === 0) $q['type'] = 'fill'; // Điền khuyết
        unset($q['num']);
        $result[] = $q;
    }

    // Lọc bỏ những câu hỏi quá ngắn (lỗi định dạng)
    return array_values(array_filter($result, fn($q) => mb_strlen(trim($q['text'])) > 3));
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Đề Trắc Nghiệm</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        /* CSS tùy chỉnh bổ sung cho các hiệu ứng (Animations, Hover, Active) */
        body { font-family: 'Nunito', system-ui, sans-serif; }
        .question-card { transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1); }
        .question-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px -12px rgb(0 0 0 / 0.3); }
        .drop-zone.dragover { background: #1e3a8a; border-color: #60a5fa; transform: scale(1.01); }
        .type-item { transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); }
        .type-item:hover { transform: translateY(-4px) scale(1.03); }
        .back-btn:hover { transform: translateX(-5px); }

        .upload-zone { transition: all 0.3s ease; }
        .upload-zone.drag-over { background: rgba(99,102,241,0.15); border-color: #6366f1; transform: scale(1.02); }
        .upload-zone:hover { border-color: #818cf8; background: rgba(99,102,241,0.05); }

        .made-badge { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .progress-bar { transition: width 0.5s ease; }

        @keyframes pulse-ring { 0% { transform: scale(0.8); opacity: 1; } 100% { transform: scale(1.4); opacity: 0; } }
        .pulse-ring::before { content: ''; position: absolute; inset: -4px; border-radius: 50%; border: 2px solid #6366f1; animation: pulse-ring 1.5s infinite; }

        .tab-btn.active { background: #6366f1; color: white; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .slide-in { animation: slideIn 0.4s ease forwards; }
        .made-item { transition: all 0.2s ease; }
        .made-item:hover { transform: translateX(4px); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-900 dark:text-gray-100 min-h-screen">

<div class="max-w-7xl mx-auto p-6">
    <div class="flex justify-between items-center mb-8">
        <div class="flex items-center gap-4">
            <a href="../phonghoc.php?malop=<?= urlencode($ma_lop) ?>&tab=bai-tap"
               class="back-btn inline-flex items-center gap-3 px-6 py-3.5 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 rounded-2xl font-medium transition-all">
                <i class="fas fa-arrow-left"></i>
                <span>Quay về lớp học</span>
            </a>
            <h1 class="text-3xl font-bold">Tạo Đề Trắc Nghiệm</h1>
        </div>
        <div class="flex items-center gap-4">
            <button onclick="toggleTheme()" class="w-11 h-11 flex items-center justify-center rounded-2xl bg-gray-200 dark:bg-gray-800 hover:bg-gray-300 dark:hover:bg-gray-700 transition-all">
                <i id="theme-icon" class="fas fa-moon text-xl"></i>
            </button>
            <button onclick="saveQuiz()" class="bg-emerald-600 hover:bg-emerald-500 px-8 py-3.5 rounded-2xl font-semibold flex items-center gap-3 transition-all active:scale-95">
                <i class="fas fa-save"></i>
                LƯU & ĐĂNG BÀI
            </button>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        
        <div class="col-span-12 lg:col-span-3">
            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 sticky top-6 border border-gray-200 dark:border-gray-800 space-y-6">

                <div>
                    <h3 class="text-base font-bold mb-4 flex items-center gap-3">
                        <i class="fas fa-layer-group text-blue-500"></i>
                        CÁC DẠNG CÂU HỎI
                    </h3>
                    <div class="space-y-3">
                        <div draggable="true" ondragstart="dragStart(event)" data-type="mc"
                             class="type-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-blue-400 rounded-2xl p-4 cursor-grab flex items-center gap-4">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center text-xl">📝</div>
                            <span class="font-semibold">Trắc nghiệm</span>
                        </div>
                        <div draggable="true" ondragstart="dragStart(event)" data-type="tf"
                             class="type-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 hover:border-emerald-400 rounded-2xl p-4 cursor-grab flex items-center gap-4">
                            <div class="w-10 h-10 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center text-xl">✅</div>
                            <span class="font-semibold">Đúng / Sai</span>
                        </div>
                        <div draggable="true" ondragstart="dragStart(event)" data-type="fill"
                             class="type-item bg-white dark:bg-gray-800 border border-violet-400 rounded-2xl p-4 cursor-grab flex items-center gap-4 ring-2 ring-violet-300 dark:ring-violet-500/30">
                            <div class="w-10 h-10 bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 rounded-xl flex items-center justify-center text-xl">✍️</div>
                            <span class="font-semibold">Điền chỗ trống</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold mb-4 text-emerald-600 dark:text-emerald-400 text-sm">⚡ THÊM NHANH</h4>
                    <select id="bulk_type" class="w-full bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-2xl px-4 py-3 mb-3 text-sm">
                        <option value="mc">Trắc nghiệm</option>
                        <option value="tf">Đúng / Sai</option>
                        <option value="fill">Điền chỗ trống</option>
                    </select>
                    <div class="flex gap-3">
                        <input type="number" id="bulk_count" value="20" min="1" max="200"
                               class="bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-2xl px-4 py-3 w-24 text-center text-lg">
                        <button onclick="addBulkQuestions()" id="bulk-btn"
                                class="flex-1 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold py-3 rounded-2xl transition-all active:scale-95 text-sm">
                            Thêm Câu
                        </button>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="font-semibold mb-4 text-purple-600 dark:text-purple-400 text-sm flex items-center gap-2">
                        <i class="fas fa-shuffle"></i>
                        TẠO NHIỀU MÃ ĐỀ
                    </h4>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-600 dark:text-gray-400">Số mã đề:</label>
                            <input type="number" id="so_made" value="4" min="1" max="20"
                                   class="bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-xl px-3 py-2 w-20 text-center font-bold text-purple-600">
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-600 dark:text-gray-400">Xáo câu hỏi:</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="shuffle_questions" checked class="sr-only peer">
                                <div class="w-10 h-5 bg-gray-300 peer-checked:bg-purple-600 rounded-full transition-all"></div>
                                <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-5"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-600 dark:text-gray-400">Xáo đáp án:</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="shuffle_answers" checked class="sr-only peer">
                                <div class="w-10 h-5 bg-gray-300 peer-checked:bg-purple-600 rounded-full transition-all"></div>
                                <div class="absolute left-0.5 top-0.5 w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-5"></div>
                            </label>
                        </div>

                        <button onclick="previewMaDe()" class="w-full bg-purple-600 hover:bg-purple-500 text-white font-semibold py-3 rounded-2xl transition-all active:scale-95 text-sm flex items-center justify-center gap-2">
                            <i class="fas fa-eye"></i>
                            Xem trước mã đề
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="col-span-12 lg:col-span-9 space-y-6">

            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 border border-gray-200 dark:border-gray-800">
                <div class="flex flex-wrap gap-4">
                    <input type="text" id="quiz_title" class="flex-1 bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 focus:border-blue-500 rounded-2xl px-6 py-4 text-xl font-medium outline-none" placeholder="Nhập tiêu đề đề thi..." required>
                    <div class="flex items-center bg-gray-100 dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-2xl px-6">
                        <span class="text-gray-500 dark:text-gray-400 mr-3">⏱</span>
                        <input type="number" id="duration_minutes" value="15" min="5" max="180" class="bg-transparent w-16 text-center text-xl font-semibold outline-none">
                        <span class="text-gray-500 dark:text-gray-400 ml-2">phút</span>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-3xl p-6 border border-gray-200 dark:border-gray-800">
                <h3 class="font-bold text-lg mb-4 flex items-center gap-3">
                    <i class="fas fa-file-import text-indigo-500"></i>
                    NHẬP CÂU HỎI TỪ FILE
                    <span class="text-xs font-normal text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">.txt / .doc / .docx</span>
                </h3>

                <div id="upload-zone"
                     class="upload-zone border-2 border-dashed border-indigo-300 dark:border-indigo-700 rounded-2xl p-8 text-center cursor-pointer"
                     ondrop="handleFileDrop(event)" ondragover="handleFileDragOver(event)" ondragleave="handleFileDragLeave(event)"
                     onclick="document.getElementById('file-input').click()">
                    <i class="fas fa-cloud-upload-alt text-5xl text-indigo-400 mb-3"></i>
                    <p class="text-gray-600 dark:text-gray-400 font-medium">Kéo & thả file vào đây hoặc <span class="text-indigo-500 font-bold">nhấp để chọn</span></p>
                    <p class="text-sm text-gray-400 mt-2">Hỗ trợ: .txt, .doc, .docx — tối đa 10MB</p>
                    <input type="file" id="file-input" class="hidden" accept=".txt,.doc,.docx,.pdf" onchange="handleFileSelect(event)">
                </div>

                <div id="upload-progress" class="hidden mt-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-indigo-600" id="progress-label">Đang phân tích file...</span>
                        <span class="text-sm font-bold text-indigo-600" id="progress-count">0 câu</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div id="progress-bar" class="progress-bar bg-indigo-500 h-2.5 rounded-full" style="width: 0%"></div>
                    </div>
                </div>

                <div id="upload-result" class="hidden mt-4 p-4 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-2xl flex items-center gap-4">
                    <i class="fas fa-check-circle text-2xl text-emerald-500"></i>
                    <div>
                        <p class="font-semibold text-emerald-700 dark:text-emerald-400" id="upload-result-text">Đã nhập thành công!</p>
                        <p class="text-sm text-emerald-600 dark:text-emerald-500" id="upload-result-detail"></p>
                    </div>
                    <button onclick="clearUploadResult()" class="ml-auto text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div id="upload-error" class="hidden mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-2xl flex items-center gap-4">
                    <i class="fas fa-exclamation-circle text-2xl text-red-500"></i>
                    <p class="font-semibold text-red-700 dark:text-red-400" id="upload-error-text"></p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-3xl p-8 border border-gray-200 dark:border-gray-800">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-lg flex items-center gap-3">
                        <i class="fas fa-list-ol text-blue-500"></i>
                        DANH SÁCH CÂU HỎI
                        <span id="q-count-badge" class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 px-3 py-1 rounded-full text-sm font-bold">0 câu</span>
                    </h3>
                    <button onclick="clearAllQuestions()" id="clear-btn" class="hidden text-red-500 hover:text-red-600 text-sm font-medium flex items-center gap-2">
                        <i class="fas fa-trash"></i> Xóa tất cả
                    </button>
                </div>

                <div id="drop-zone" ondrop="drop(event)" ondragover="allowDrop(event)" ondragleave="removeDragover(event)"
                     class="drop-zone min-h-[400px] border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-3xl p-6">
                    <div id="questions-list" class="space-y-5"></div>
                    <div id="empty-state" class="flex flex-col items-center justify-center text-gray-400 dark:text-gray-500 py-16">
                        <i class="fas fa-cloud-arrow-down text-6xl mb-5 opacity-50"></i>
                        <p class="text-lg">Kéo & thả hoặc upload file để bắt đầu</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div id="made-modal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 hidden items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-900 rounded-3xl w-full max-w-4xl max-h-[90vh] flex flex-col border border-gray-200 dark:border-gray-700 shadow-2xl">
        <div class="flex items-center justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-bold flex items-center gap-3">
                <i class="fas fa-layer-group text-purple-500"></i>
                Xem trước các Mã Đề
                <span id="made-count-badge" class="bg-purple-100 dark:bg-purple-900/30 text-purple-600 text-sm px-3 py-1 rounded-full"></span>
            </h2>
            <button onclick="closeMadeModal()" class="w-10 h-10 flex items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all">
                <i class="fas fa-times text-gray-500"></i>
            </button>
        </div>

        <div class="flex gap-2 p-4 border-b border-gray-200 dark:border-gray-700 flex-wrap">
            <div id="made-tabs" class="flex gap-2 flex-wrap"></div>
        </div>

        <div id="made-content" class="flex-1 overflow-y-auto p-6"></div>

        <div class="p-6 border-t border-gray-200 dark:border-gray-700 flex gap-3 justify-end">
            <button onclick="closeMadeModal()" class="px-6 py-3 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-2xl font-medium transition-all">
                Đóng
            </button>
            <button onclick="exportAllMaDe()" class="px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white rounded-2xl font-semibold transition-all flex items-center gap-2">
                <i class="fas fa-download"></i>
                Xuất file TXT
            </button>
            <button onclick="saveQuiz()" class="px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white rounded-2xl font-semibold transition-all flex items-center gap-2">
                <i class="fas fa-save"></i>
                Lưu & Đăng bài
            </button>
        </div>
    </div>
</div>

<form id="quizForm" action="xuly_taotracnghiem.php" method="POST" class="hidden">
    <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
    <input type="hidden" name="quiz_title" id="form_title">
    <input type="hidden" name="duration_minutes" id="form_duration">
    <input type="hidden" name="so_made" id="form_so_made" value="4">
    <input type="hidden" name="shuffle_questions" id="form_shuffle_q" value="1">
    <input type="hidden" name="shuffle_answers" id="form_shuffle_a" value="1">
    <div id="form-questions"></div>
</form>

<script>
tailwind.config = { darkMode: 'class' };

// ---- Chức năng đổi Giao diện Tối/Sáng lưu qua LocalStorage ----
function toggleTheme() {
    document.documentElement.classList.toggle('dark');
    const icon = document.getElementById('theme-icon');
    const isDark = document.documentElement.classList.contains('dark');
    icon.className = isDark ? 'fas fa-sun text-xl' : 'fas fa-moon text-xl';
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
}
if (localStorage.getItem('theme') === 'dark' || window.matchMedia('(prefers-color-scheme: dark)').matches) {
    document.documentElement.classList.add('dark');
    document.getElementById('theme-icon').className = 'fas fa-sun text-xl';
}

// ---- Biến trạng thái toàn cục ----
let questionCounter = 0;
const questionsList = document.getElementById('questions-list');
const emptyState = document.getElementById('empty-state');

// ============================================================
// HỆ THỐNG KÉO - THẢ (DRAG & DROP) CHO LOẠI CÂU HỎI
// ============================================================
function allowDrop(e) { e.preventDefault(); e.currentTarget.classList.add('dragover'); }
function removeDragover(e) { e.currentTarget.classList.remove('dragover'); }
// Khi bắt đầu kéo item, lưu loại của nó (mc, tf, fill)
function dragStart(e) { e.dataTransfer.setData("text/plain", e.target.closest('[data-type]').getAttribute("data-type")); }
// Khi thả vào vùng nhận, đọc loại và gọi hàm tạo câu hỏi tương ứng
function drop(e) {
    e.preventDefault();
    const type = e.dataTransfer.getData("text/plain");
    e.currentTarget.classList.remove('dragover');
    addQuestion(type);
}

// ============================================================
// HÀM SINH MÃ HTML ĐỘNG CHO TỪNG CÂU HỎI
// Phụ thuộc vào biến type (mc: trắc nghiệm 4 đáp án, tf: đúng/sai, fill: điền khuyết)
// ============================================================
function createQuestionHTML(type, data = {}) {
    // Tạo ID ngẫu nhiên không trùng lặp cho mỗi khối HTML
    const id = 'q-' + Date.now() + '-' + Math.random().toString(36).slice(2, 6);
    const txt = (data.text || '').replace(/"/g, '&quot;');
    const opts = data.options || data.answers || ['', '', '', ''];
    const correct = data.correct || 'A';

    if (type === 'mc') {
        const labels = ['A', 'B', 'C', 'D'];
        const ansInputs = labels.map((l, i) =>
            `<input type="text" class="ans w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4 focus:border-blue-400 outline-none" placeholder="Đáp án ${l}" value="${(opts[i]||'').replace(/"/g,'&quot;')}">`
        ).join('');
        const selOpts = labels.map(l =>
            `<option value="${l}" ${correct === l ? 'selected' : ''}>${l}</option>`
        ).join('');
        // Trả về DOM String chứa Layout Câu Hỏi Trắc Nghiệm (4 Đáp án)
        return `<div class="question-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-6 slide-in" id="${id}" data-type="mc">
            <div class="flex justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="q-number font-bold text-2xl text-blue-500">Câu 1</span>
                    <span class="px-4 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-sm rounded-full">Trắc nghiệm</span>
                </div>
                <button onclick="removeThisQuestion(this)" class="text-red-500 hover:text-red-600 text-3xl leading-none">&times;</button>
            </div>
            <textarea class="q-text w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4 text-base focus:border-blue-400 outline-none resize-none" rows="2" placeholder="Nhập nội dung câu hỏi...">${txt}</textarea>
            <div class="grid grid-cols-2 gap-3 mt-4">${ansInputs}</div>
            <div class="flex items-center gap-3 mt-4">
                <label class="text-sm text-gray-500 dark:text-gray-400">Đáp án đúng:</label>
                <select class="correct_ans bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2">${selOpts}</select>
            </div>
        </div>`;
    } else if (type === 'tf') {
        // Layout Câu hỏi Dạng Đúng/Sai
        return `<div class="question-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-6 slide-in" id="${id}" data-type="tf">
            <div class="flex justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="q-number font-bold text-2xl text-emerald-500">Câu 1</span>
                    <span class="px-4 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 text-sm rounded-full">Đúng / Sai</span>
                </div>
                <button onclick="removeThisQuestion(this)" class="text-red-500 hover:text-red-600 text-3xl leading-none">&times;</button>
            </div>
            <textarea class="q-text w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4 text-base focus:border-emerald-400 outline-none resize-none" rows="2" placeholder="Nhập câu hỏi...">${txt}</textarea>
            <div class="flex items-center gap-3 mt-4">
                <label class="text-sm text-gray-500 dark:text-gray-400">Đáp án đúng:</label>
                <select class="correct_ans bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-xl px-4 py-2">
                    <option value="A" ${correct==='A'?'selected':''}>Đúng (A)</option>
                    <option value="B" ${correct==='B'?'selected':''}>Sai (B)</option>
                </select>
            </div>
        </div>`;
    } else {
        // Layout Câu hỏi Điền khuyết
        const ans = (data.correct_text || opts[0] || '').replace(/"/g, '&quot;');
        return `<div class="question-card bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-3xl p-6 slide-in" id="${id}" data-type="fill">
            <div class="flex justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="q-number font-bold text-2xl text-violet-500">Câu 1</span>
                    <span class="px-4 py-1 bg-violet-100 dark:bg-violet-900/30 text-violet-600 dark:text-violet-400 text-sm rounded-full">Điền chỗ trống</span>
                </div>
                <button onclick="removeThisQuestion(this)" class="text-red-500 hover:text-red-600 text-3xl leading-none">&times;</button>
            </div>
            <textarea class="q-text w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4 text-base focus:border-violet-400 outline-none resize-none" rows="2" placeholder="Nhập câu hỏi (dùng ___ cho chỗ trống)...">${txt}</textarea>
            <input type="text" class="correct_ans mt-4 w-full bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-2xl p-4 focus:border-violet-400 outline-none" placeholder="Đáp án đúng" value="${ans}">
        </div>`;
    }
}

// Hàm thêm 1 câu hỏi vào DOM
function addQuestion(type, data = {}) {
    questionCounter++;
    emptyState.style.display = 'none'; // Ẩn chữ "Kéo thả vào đây"
    document.getElementById('clear-btn').classList.remove('hidden');
    questionsList.insertAdjacentHTML('beforeend', createQuestionHTML(type, data));
    updateQuestionNumbers();
}

// Hàm thêm hàng loạt câu hỏi trống (Tính năng thêm nhanh ở Sidebar)
function addBulkQuestions() {
    const type = document.getElementById('bulk_type').value;
    let count = Math.min(Math.max(parseInt(document.getElementById('bulk_count').value) || 10, 1), 200);
    const btn = document.getElementById('bulk-btn');
    btn.innerHTML = 'Đang thêm...';
    btn.disabled = true;
    emptyState.style.display = 'none';
    document.getElementById('clear-btn').classList.remove('hidden');
    
    // Dùng DocumentFragment để tối ưu hóa hiệu suất, tránh Render lại DOM quá nhiều lần
    const fragment = document.createDocumentFragment();
    for (let i = 0; i < count; i++) {
        questionCounter++;
        const temp = document.createElement('div');
        temp.innerHTML = createQuestionHTML(type);
        fragment.appendChild(temp.firstElementChild);
    }
    questionsList.appendChild(fragment);
    updateQuestionNumbers();
    
    setTimeout(() => {
        btn.innerHTML = 'Thêm Câu';
        btn.disabled = false;
        questionsList.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'end' }); // Cuộn chuột xuống dưới cùng
    }, 300);
}

// Xoá một câu hỏi được chỉ định
function removeThisQuestion(btn) {
    btn.closest('.question-card').remove();
    questionCounter = Math.max(0, questionCounter - 1);
    updateQuestionNumbers();
    if (!questionsList.children.length) {
        emptyState.style.display = '';
        document.getElementById('clear-btn').classList.add('hidden');
    }
}

// Xoá tất cả câu hỏi
function clearAllQuestions() {
    if (!confirm('Xóa toàn bộ câu hỏi?')) return;
    questionsList.innerHTML = '';
    questionCounter = 0;
    emptyState.style.display = '';
    document.getElementById('clear-btn').classList.add('hidden');
    updateQuestionNumbers();
}

// Cập nhật lại số thứ tự Câu 1, Câu 2... khi bị xoá hoặc thêm
function updateQuestionNumbers() {
    const cards = document.querySelectorAll('.question-card');
    cards.forEach((card, i) => {
        const num = card.querySelector('.q-number');
        if (num) num.textContent = `Câu ${i + 1}`;
    });
    const badge = document.getElementById('q-count-badge');
    if (badge) badge.textContent = `${cards.length} câu`;
}

// ============================================================
// LOGIC UPLOAD VÀ TỰ ĐỘNG PHÂN TÍCH FILE (FETCH API)
// ============================================================
// Bắt sự kiện trực quan cho vùng kéo thả
function handleFileDragOver(e) {
    e.preventDefault();
    document.getElementById('upload-zone').classList.add('drag-over');
}
function handleFileDragLeave(e) {
    document.getElementById('upload-zone').classList.remove('drag-over');
}
function handleFileDrop(e) {
    e.preventDefault();
    document.getElementById('upload-zone').classList.remove('drag-over');
    const file = e.dataTransfer.files[0];
    if (file) processFile(file);
}
// Bắt sự kiện người dùng bấm vào vùng chọn file
function handleFileSelect(e) {
    const file = e.target.files[0];
    if (file) processFile(file);
}

// Hàm chính để gửi file lên server phân tích qua AJAX Fetch API
function processFile(file) {
    const ext = file.name.split('.').pop().toLowerCase();
    const allowed = ['txt','doc','docx','pdf'];
    if (!allowed.includes(ext)) { 
        showUploadError('Định dạng không hỗ trợ. Dùng .txt .doc .docx .pdf'); 
        return; 
    }

    hideUploadMessages();
    showProgress('Đang đọc file ' + file.name + '...', 20);

    // Chuẩn bị dữ liệu gửi đi (Multipart/form-data)
    const fd = new FormData();
    fd.append('action', 'parse_file');
    fd.append('file', file);

    showProgress('Đang phân tích câu hỏi...', 50);

    // Gửi POST Request vào chính trang này để được xử lý bởi khối PHP đầu file
    fetch(window.location.href, { 
        method: 'POST', 
        body: fd 
    })
    .then(r => r.text())
    .then(raw => {
        // Mẹo an toàn: Lọc ký tự rác để bắt JSON chuẩn
        let jsonStr = raw.trim();
        const jsonStart = jsonStr.indexOf('{');
        if (jsonStart !== -1) {
            jsonStr = jsonStr.substring(jsonStart);
        }

        let data;
        try {
            data = JSON.parse(jsonStr);
        } catch(e) {
            console.error('JSON parse error:', e);
            console.log('Raw response:', raw.substring(0, 500));
            showUploadError('Lỗi parse JSON. Mở F12 > Console để xem chi tiết.');
            return;
        }

        // Xử lý dữ liệu JSON trả về (Mảng các câu hỏi đã được format)
        if (data.success) {
            const qs = (data.questions || []).map(q => ({
                text: q.text || '',
                type: q.type || 'mc',
                options: q.options || [],
                correct: q.correct || 'A'
            }));

            if (qs.length === 0) {
                showUploadError('Không tìm thấy câu hỏi nào. Kiểm tra định dạng file.');
                return;
            }

            showProgress('Hoàn tất!', 100);
            setTimeout(() => importQuestions(qs, file.name), 300);
        } else {
            showUploadError(data.message || 'Không đọc được file');
        }
    })
    .catch(e => {
        console.error(e);
        showUploadError('Lỗi kết nối: ' + e.message);
    });
}

/**
 * [Dự phòng] Parse câu hỏi từ text thuần ở mức độ Frontend (Javascript)
 * Hàm này bổ trợ để dự phòng nếu Server không phân tích được hoặc sử dụng Offline.
 */
function parseQuestionsFromText(text) {
    const questions = [];
    text = text.replace(/\r\n/g, '\n').replace(/\r/g, '\n');
    const lines = text.split('\n');
    let current = null;

    const isQuestionLine = (l) => /^(câu\s*\d+[\s:.)]|q\s*\d+[\s:.)]|\d+[\s.):]+\s*\S)/i.test(l.trim());
    const isOptionLine = (l) => /^[A-Ea-e][\s.):]+\s*\S/.test(l.trim());
    const isAnswerLine = (l) => /^(đáp\s*án|đ\.?a|answer|ans|correct)[\s:]+[A-Ea-e]/i.test(l.trim());

    for (const rawLine of lines) {
        const line = rawLine.trim();
        if (!line) continue;

        if (isQuestionLine(line)) {
            if (current) questions.push(current);
            const qtext = line.replace(/^(câu\s*\d+[\s:.)]|\d+[\s.):]+)/i, '').trim();
            current = { text: qtext, options: [], correct: 'A', type: 'mc' };
        } else if (current && isOptionLine(line)) {
            const match = line.match(/^([A-Ea-e])[\s.):]+(.+)/);
            if (match) {
                const letter = match[1].toUpperCase();
                let optText = match[2].trim();
                if (optText.startsWith('*') || optText.endsWith('*')) {
                    current.correct = letter;
                    optText = optText.replace(/\*/g, '').trim();
                }
                current.options.push(optText);
            }
        } else if (current && isAnswerLine(line)) {
            const match = line.match(/[A-Ea-e]/i);
            if (match) current.correct = match[0].toUpperCase();
        } else if (current && !isOptionLine(line) && !isAnswerLine(line)) {
            if (current.options.length === 0) {
                current.text += ' ' + line;
            }
        }
    }
    if (current) questions.push(current);

    return questions.filter(q => q.text.trim().length > 3);
}

// Render dữ liệu câu hỏi từ Server/File ra DOM Giao diện
function importQuestions(questions, filename) {
    if (!questions || questions.length === 0) {
        showUploadError('Không tìm thấy câu hỏi nào trong file. Vui lòng kiểm tra định dạng.');
        return;
    }

    showProgress('Đang thêm câu hỏi...', 90);

    emptyState.style.display = 'none';
    document.getElementById('clear-btn').classList.remove('hidden');

    const fragment = document.createDocumentFragment();
    questions.forEach(q => {
        questionCounter++;
        // Ánh xạ format PHP (options[]) về format của JS createQuestionHTML()
        const opts = q.options || [];
        const type = q.type || (opts.length <= 2 && opts.length > 0 ? 'tf' : 'mc');
        const letters = ['A','B','C','D'];
        const correctLetter = (q.correct || 'A').toUpperCase();
        const correctIdx = letters.indexOf(correctLetter);
        
        const normalised = {
            text: q.text || '',
            type: type,
            options: opts,
            correct: correctLetter,
            correctIdx: correctIdx >= 0 ? correctIdx : 0,
            answers: opts,                        
            correct_text: opts[correctIdx] || q.correct_text || '',
        };
        const temp = document.createElement('div');
        temp.innerHTML = createQuestionHTML(type, normalised);
        fragment.appendChild(temp.firstElementChild);
    });
    questionsList.appendChild(fragment);
    updateQuestionNumbers();

    showProgress('Hoàn tất!', 100);
    setTimeout(() => {
        hideProgress();
        showUploadSuccess(
            `Nhập thành công ${questions.length} câu hỏi`,
            `Từ file: ${filename}`
        );
    }, 500);
}

// ----- CÁC HÀM XỬ LÝ GIAO DIỆN HIỂN THỊ TRẠNG THÁI TIẾN TRÌNH -----
function showProgress(label, pct) {
    document.getElementById('upload-progress').classList.remove('hidden');
    document.getElementById('progress-label').textContent = label;
    document.getElementById('progress-count').textContent = pct + '%';
    document.getElementById('progress-bar').style.width = pct + '%';
    document.getElementById('upload-result').classList.add('hidden');
    document.getElementById('upload-error').classList.add('hidden');
}
function hideProgress() { document.getElementById('upload-progress').classList.add('hidden'); }
function showUploadSuccess(msg, detail) {
    document.getElementById('upload-result').classList.remove('hidden');
    document.getElementById('upload-result-text').textContent = msg;
    document.getElementById('upload-result-detail').textContent = detail;
    document.getElementById('upload-error').classList.add('hidden');
}
function showUploadError(msg) {
    hideProgress();
    document.getElementById('upload-error').classList.remove('hidden');
    document.getElementById('upload-error-text').textContent = msg;
    document.getElementById('upload-result').classList.add('hidden');
}
function hideUploadMessages() {
    document.getElementById('upload-result').classList.add('hidden');
    document.getElementById('upload-error').classList.add('hidden');
    hideProgress();
}
function clearUploadResult() { hideUploadMessages(); }

// ============================================================
// HỆ THỐNG TRỘN MÃ ĐỀ & PREVIEW GIAO DIỆN MODAL
// ============================================================
// Hàm trộn ngẫu nhiên mảng (Thuật toán Fisher-Yates shuffle)
function shuffleArray(arr) {
    const a = [...arr];
    for (let i = a.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [a[i], a[j]] = [a[j], a[i]];
    }
    return a;
}

// Trích xuất cấu trúc dữ liệu câu hỏi hiện tại từ các ô input DOM
function getQuestionsData() {
    const cards = document.querySelectorAll('.question-card');
    const data = [];
    cards.forEach(card => {
        const type = card.dataset.type || 'mc';
        const text = card.querySelector('.q-text')?.value?.trim() || '';
        if (!text) return;

        let q = { text, type };

        if (type === 'mc') {
            const answers = [...card.querySelectorAll('input.ans')].map(i => i.value.trim());
            const correctLetter = card.querySelector('.correct_ans')?.value || 'A';
            const correctIdx = ['A','B','C','D'].indexOf(correctLetter);
            q.answers = answers; 
            q.correctIdx = correctIdx;
        } else if (type === 'tf') {
            q.correctLetter = card.querySelector('.correct_ans')?.value || 'A';
        } else {
            q.correctText = card.querySelector('.correct_ans')?.value?.trim() || '';
        }
        data.push(q);
    });
    return data;
}

// Xây dựng Mã đề độc lập (nhận vào setting từ người dùng: Xáo câu hay xáo đáp án)
function buildMaDe(questions, madeNum, shuffleQ, shuffleA) {
    let qs = shuffleQ ? shuffleArray(questions) : [...questions];

    return qs.map((q, idx) => {
        if (q.type === 'mc' && shuffleA) {
            // Nối text đáp án với trạng thái isCorrect, trộn lên, sau đó cập nhật lại vị trí Index của đáp án đúng
            const pairs = q.answers.map((a, i) => ({ text: a, isCorrect: i === q.correctIdx }));
            const shuffled = shuffleArray(pairs);
            const newCorrectIdx = shuffled.findIndex(p => p.isCorrect);
            return { ...q, answers: shuffled.map(p => p.text), correctIdx: newCorrectIdx };
        }
        return { ...q };
    });
}

// Khởi tạo hiển thị Preview Modal
function previewMaDe() {
    const questions = getQuestionsData();
    if (questions.length === 0) {
        alert('Chưa có câu hỏi nào! Hãy thêm câu hỏi trước.');
        return;
    }

    const soMade = Math.min(Math.max(parseInt(document.getElementById('so_made').value) || 4, 1), 20);
    const shuffleQ = document.getElementById('shuffle_questions').checked;
    const shuffleA = document.getElementById('shuffle_answers').checked;

    // Tạo mảng dữ liệu chứa tất cả mã đề
    const allMade = [];
    for (let i = 1; i <= soMade; i++) {
        allMade.push({ madeNum: i, questions: buildMaDe(questions, i, shuffleQ, shuffleA) });
    }

    document.getElementById('made-count-badge').textContent = `${soMade} mã đề · ${questions.length} câu`;

    // Render các nút chuyển Tab dựa trên số lượng mã đề
    const tabsEl = document.getElementById('made-tabs');
    tabsEl.innerHTML = allMade.map((m, i) =>
        `<button onclick="switchMadeTab(${i})" class="tab-btn px-4 py-2 rounded-xl text-sm font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 hover:bg-purple-100 dark:hover:bg-purple-900/30 transition-all ${i===0?'active':''}" id="tab-${i}">
            Mã đề ${m.madeNum.toString().padStart(3,'0')}
        </button>`
    ).join('');

    // Lưu trữ tạm dữ liệu mã đề vào Object toàn cục (window) để tái sử dụng khi xuất File hoặc Đổi Tab
    window._allMade = allMade;
    renderMadeContent(0);

    // Bật Modal
    document.getElementById('made-modal').classList.remove('hidden');
    document.getElementById('made-modal').classList.add('flex');
}

// Chuyển Tab qua lại giữa các mã đề trên Giao diện Preview
function switchMadeTab(idx) {
    document.querySelectorAll('.tab-btn').forEach((b, i) => {
        b.classList.toggle('active', i === idx);
    });
    renderMadeContent(idx);
}

// Render dữ liệu html cho thân của một Mã Đề
function renderMadeContent(idx) {
    const made = window._allMade[idx];
    const letters = ['A', 'B', 'C', 'D', 'E'];
    const content = document.getElementById('made-content');

    const html = made.questions.map((q, qi) => {
        let ansHtml = '';
        if (q.type === 'mc') {
            ansHtml = q.answers.map((a, ai) =>
                `<div class="flex items-start gap-3 py-1.5 ${ai === q.correctIdx ? 'text-emerald-600 dark:text-emerald-400 font-semibold' : 'text-gray-600 dark:text-gray-400'}">
                    <span class="w-6 h-6 flex-shrink-0 rounded-full flex items-center justify-center text-xs font-bold ${ai === q.correctIdx ? 'bg-emerald-100 dark:bg-emerald-900/40' : 'bg-gray-100 dark:bg-gray-700'}">${letters[ai]}</span>
                    <span>${a || '(trống)'}</span>
                    ${ai === q.correctIdx ? '<i class="fas fa-check-circle text-emerald-500 ml-auto self-center"></i>' : ''}
                </div>`
            ).join('');
        } else if (q.type === 'tf') {
            ansHtml = `<span class="inline-block mt-2 px-4 py-1 rounded-full text-sm font-semibold ${q.correctLetter==='A' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400'}">
                ${q.correctLetter === 'A' ? '✓ Đúng' : '✗ Sai'}
            </span>`;
        } else {
            ansHtml = `<div class="mt-2 px-4 py-2 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-700 rounded-xl text-violet-700 dark:text-violet-400 font-medium">
                Đáp án: ${q.correctText || '(chưa nhập)'}
            </div>`;
        }

        return `<div class="mb-6 pb-6 border-b border-gray-100 dark:border-gray-800 last:border-0">
            <div class="flex items-start gap-3 mb-3">
                <span class="w-8 h-8 flex-shrink-0 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-xl flex items-center justify-center text-sm font-bold">${qi+1}</span>
                <p class="text-gray-800 dark:text-gray-200 font-medium pt-1">${q.text}</p>
            </div>
            <div class="ml-11">${ansHtml}</div>
        </div>`;
    }).join('');

    content.innerHTML = `<div class="mb-4 flex items-center gap-3">
        <span class="made-badge text-white px-4 py-2 rounded-xl font-bold text-sm">Mã đề ${made.madeNum.toString().padStart(3,'0')}</span>
        <span class="text-gray-500 text-sm">${made.questions.length} câu hỏi</span>
    </div>${html}`;
}

// Chức năng Xuất tất cả các Mã Đề ra file Text để in ấn (Dùng HTML5 Blob API)
function exportAllMaDe() {
    if (!window._allMade || !window._allMade.length) return;
    const title = document.getElementById('quiz_title').value.trim() || 'DeThi';
    const time  = document.getElementById('duration_minutes').value || '15';
    const letters = ['A','B','C','D'];

    window._allMade.forEach(made => {
        const code = 'MaDe' + String(made.madeNum).padStart(2,'0');
        let txt = code + ' — ' + title + '\nThời gian: ' + time + ' phút\n' + '='.repeat(50) + '\n\n';
        const answerKey = []; // Chìa khoá đáp án nằm cuối file text

        made.questions.forEach((q, qi) => {
            txt += 'Câu ' + (qi+1) + '. ' + q.text + '\n';
            if (q.type === 'mc') {
                (q.answers || []).forEach((a, ai) => { if(a) txt += '  ' + letters[ai] + '. ' + a + '\n'; });
                answerKey.push((qi+1) + letters[q.correctIdx ?? 0]);
            } else if (q.type === 'tf') {
                txt += '  A. Đúng\n  B. Sai\n';
                answerKey.push((qi+1) + (q.correctLetter || 'A'));
            } else {
                answerKey.push((qi+1) + ': ' + (q.correctText||''));
            }
            txt += '\n';
        });

        txt += 'Đáp án: ' + answerKey.join(', ') + '\n';

        // Khởi tạo file ảo trên trình duyệt và tự động kích hoạt Tải Xuống
        const blob = new Blob([txt], {type:'text/plain;charset=utf-8'});
        const url  = URL.createObjectURL(blob);
        const a    = document.createElement('a');
        a.href = url; a.download = title.replace(/\s+/g,'-') + '_' + code + '.txt';
        document.body.appendChild(a); a.click();
        document.body.removeChild(a);
        setTimeout(() => URL.revokeObjectURL(url), 1000); // Giải phóng bộ nhớ
    });
}

// Ẩn Modal
function closeMadeModal() {
    document.getElementById('made-modal').classList.add('hidden');
    document.getElementById('made-modal').classList.remove('flex');
}
// Ẩn Modal khi người dùng click bên ngoài hộp thoại (Backdrop)
document.getElementById('made-modal').addEventListener('click', function(e) {
    if (e.target === this) closeMadeModal();
});

// ============================================================
// HÀM LƯU & SUBMIT DỮ LIỆU ĐỀ THI LÊN SERVER (xuly_taotracnghiem.php)
// ============================================================
function saveQuiz() {
    const title = document.getElementById('quiz_title').value.trim();
    if (!title) { alert('Vui lòng nhập tiêu đề đề thi!'); return; }

    const cards = document.querySelectorAll('.question-card');
    if (!cards.length) { alert('Chưa có câu hỏi nào!'); return; }

    // Map dữ liệu từ UI vào thẻ Form ẩn để gửi POST request
    document.getElementById('form_title').value = title;
    document.getElementById('form_duration').value = document.getElementById('duration_minutes').value;
    document.getElementById('form_so_made').value = document.getElementById('so_made').value;
    document.getElementById('form_shuffle_q').value = document.getElementById('shuffle_questions').checked ? 1 : 0;
    document.getElementById('form_shuffle_a').value = document.getElementById('shuffle_answers').checked ? 1 : 0;

    const container = document.getElementById('form-questions');
    container.innerHTML = '';

    // Lặp qua từng thẻ câu hỏi, tạo các thẻ <input type="hidden" name="mảng[]"> để đẩy sang File PHP xử lý
    cards.forEach(card => {
        const textarea = card.querySelector('textarea');
        if (!textarea || !textarea.value.trim()) return;
        const answers = card.querySelectorAll('input.ans');
        const correct = card.querySelector('.correct_ans');

        // Tạo mảng dữ liệu song song (Array[] của PHP)
        container.innerHTML += `
            <input type="hidden" name="question_text[]" value="${textarea.value.replace(/"/g, '&quot;')}">
            <input type="hidden" name="ans_a[]" value="${answers[0] ? answers[0].value.replace(/"/g, '&quot;') : ''}">
            <input type="hidden" name="ans_b[]" value="${answers[1] ? answers[1].value.replace(/"/g, '&quot;') : ''}">
            <input type="hidden" name="ans_c[]" value="${answers[2] ? answers[2].value.replace(/"/g, '&quot;') : ''}">
            <input type="hidden" name="ans_d[]" value="${answers[3] ? answers[3].value.replace(/"/g, '&quot;') : ''}">
            <input type="hidden" name="correct_ans[]" value="${correct ? correct.value : 'A'}">
        `;
    });

    // Kích hoạt nút Submit form
    document.getElementById('quizForm').submit();
}
</script>
</body>
</html>