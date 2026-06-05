<?php
ini_set('session.name', 'GV_SESSION');
session_start();
require_once '../../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit;
}

// Bắt lỗi chi tiết thay vì chỉ kiểm tra error === 0
if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    $errorMsg = 'Không đọc được file.';
    if (isset($_FILES['file'])) {
        $errCode = $_FILES['file']['error'];
        switch ($errCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errorMsg = 'Lỗi: File tải lên quá lớn. Vui lòng cấu hình lại upload_max_filesize trong php.ini.';
                break;
            case UPLOAD_ERR_PARTIAL:
                $errorMsg = 'Lỗi: Tải file bị gián đoạn.';
                break;
            case UPLOAD_ERR_NO_FILE:
                $errorMsg = 'Lỗi: Bạn chưa chọn file.';
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $errorMsg = 'Lỗi máy chủ: Thiếu thư mục tạm (tmp_dir).';
                break;
            default:
                $errorMsg = 'Lỗi tải file mã: ' . $errCode;
        }
    }
    echo json_encode(['success' => false, 'message' => $errorMsg]);
    exit;
}

$content = file_get_contents($_FILES['file']['tmp_name']);

// Normalize encoding
if (!mb_check_encoding($content, 'UTF-8')) {
    $content = mb_convert_encoding($content, 'UTF-8', 'auto');
}
$questions = parseQuestions($content);
echo json_encode(['success' => true, 'count' => count($questions), 'questions' => $questions], JSON_UNESCAPED_UNICODE);

function parseQuestions($text) {
    $questions = [];
    $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    $lines = explode("\n", $text);
    $current = null;

    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) continue;

        // ── NHẬN DIỆN CÂU HỎI ──────────────────────────────────────────────────
        // Hỗ trợ: "Câu 1.", "Câu 1:", "Câu 1)", "1.", "1)", "1:", "1-"
        // "Question 1.", "Q1:", cả chữ hoa lẫn thường
        $isQuestion = preg_match(
            '/^(?:(?:câu|question|q|cau)\s*)?(\d+)\s*[\.\:\)\-–—]\s*(.+)$/iu',
            $line, $m
        );

        // "Câu X" đứng riêng một dòng
        $isHeader = !$isQuestion && preg_match(
            '/^(?:câu|question|cau)\s*(\d+)\s*$/iu',
            $line, $mh
        );

        if ($isQuestion) {
            if ($current) $questions[] = $current;
            $current = ['text' => trim($m[2]), 'options' => [], 'correct' => 'A', 'type' => 'mc'];
            continue;
        }

        if ($isHeader) {
            if ($current) $questions[] = $current;
            $current = ['text' => '', 'options' => [], 'correct' => 'A', 'type' => 'mc'];
            continue;
        }

        // ── NHẬN DIỆN ĐÁP ÁN A/B/C/D/E ────────────────────────────────────────
        // Hỗ trợ: "A.", "A)", "A:", "A-", "A–" (cả chữ thường)
        if ($current !== null && preg_match('/^([A-Ea-e])\s*[\.\)\:\-–—]\s*(.+)$/u', $line, $m)) {
            $letter = strtoupper($m[1]);
            $optText = trim($m[2]);
            if (preg_match('/^\*(.+?)\*?$|^(.+?)\*$/', $optText, $star)) {
                $current['correct'] = $letter;
                $optText = trim($star[1] ?: $star[2]);
            }
            if (in_array($letter, ['A','B','C','D','E'])) {
                $current['options'][$letter] = $optText;
            }
            continue;
        }

        // ── NHẬN DIỆN DÒNG ĐÁP ÁN ĐÚNG ────────────────────────────────────────
        // Hỗ trợ: "Đáp án: A", "ĐA: B", "Đ.A: C", "Answer: D", "Ans:", "Correct:", "Key:"
        if ($current !== null && preg_match(
            '/^(?:đáp\s*án(?:\s*đúng)?|đ\.?\s*a|answer|ans|correct|key)\s*[:\-–]?\s*([A-Ea-e])/iu',
            $line, $m
        )) {
            $current['correct'] = strtoupper($m[1]);
            continue;
        }

        // ── NỐI NỘI DUNG CÂU HỎI DÀI NHIỀU DÒNG ──────────────────────────────
        if ($current !== null && empty($current['options'])) {
            $current['text'] = trim($current['text'] . ' ' . $line);
        }
    }

    if ($current) $questions[] = $current;

    // Chuẩn hóa: chuyển options từ ['A'=>..] sang mảng đánh số
    foreach ($questions as &$q) {
        $opts = [];
        foreach (['A','B','C','D','E'] as $l) {
            if (isset($q['options'][$l])) $opts[] = $q['options'][$l];
        }
        $q['options'] = $opts;
        if (count($opts) === 2) $q['type'] = 'tf';
        elseif (count($opts) === 0) $q['type'] = 'fill';
    }
    unset($q);

    return array_values(array_filter($questions, fn($q) => mb_strlen(trim($q['text'])) > 3));
}
?>