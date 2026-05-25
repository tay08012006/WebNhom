<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'GV_SESSION');
session_start();
}
// Đảm bảo file này chỉ chạy khi được include hợp lệ và đã có biến kết nối $conn
if (!isset($conn) || !isset($current_class)) {
    die("Truy cập trái phép.");
}

$class_id = $current_class['id'];

// Lấy danh sách bài thi của lớp
$stmt_list = $conn->prepare("SELECT * FROM quizzes WHERE class_id = ? ORDER BY created_at DESC");
$stmt_list->bind_param("i", $class_id);
$stmt_list->execute();
$quizzes = $stmt_list->get_result();
?>

<style>
    .quiz-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.04); }
    .quiz-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 1px solid #edf2f7; }
    .quiz-item { display: flex; justify-content: space-between; align-items: center; padding: 16px; border-bottom: 1px solid #edf2f7; transition: background 0.2s; border-radius: 8px; }
    .quiz-item:hover { background: #f8fafc; }
    
    /* CSS cho Menu 3 chấm */
    .dropdown-container { position: relative; display: inline-block; }
    .quiz-dropdown-btn {
        background: none; border: none; font-size: 20px; font-weight: bold; cursor: pointer; color: #718096;
        padding: 4px 12px; border-radius: 6px; transition: all 0.2s; outline: none;
    }
    .quiz-dropdown-btn:hover, .quiz-dropdown-btn.active { background: #e2e8f0; color: #2d3748; }
    
    .quiz-dropdown-content {
        display: none; position: absolute; right: 0; top: 100%; margin-top: 5px; background-color: #ffffff;
        min-width: 160px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 8px;
        z-index: 100; border: 1px solid #edf2f7; overflow: hidden;
    }
    .quiz-dropdown-content.show { display: block; animation: fadeIn 0.2s ease-in-out; }
    .quiz-dropdown-content a {
        color: #4a5568; padding: 12px 16px; text-decoration: none; display: flex; align-items: center; gap: 8px;
        font-size: 14px; font-weight: 600; transition: background 0.2s;
    }
    .quiz-dropdown-content a:hover { background-color: #f1f5f9; color: #0288d1; }
    .quiz-dropdown-content a.delete-btn { color: #e53e3e; border-top: 1px solid #edf2f7; }
    .quiz-dropdown-content a.delete-btn:hover { background-color: #fee2e2; color: #c53030; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div class="quiz-card">
    <div class="quiz-header">
        <div>
            <h3 style="font-size: 20px; font-weight: 800; color: #0277bd;">Bài trắc nghiệm trực tuyến</h3>
            <p style="font-size: 14px; color: #718096; margin-top: 4px;">Quản lý các bài kiểm tra trắc nghiệm của lớp</p>
        </div>
        <a href="taotracnghiem.php?malop=<?= urlencode($ma_lop) ?>" 
           style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 700; box-shadow: 0 4px 6px rgba(40,167,69,0.2); transition: all 0.2s;">
           + Tạo bài tập mới
        </a>
    </div>

    <?php if ($quizzes->num_rows > 0): ?>
        <div>
            <?php while ($quiz = $quizzes->fetch_assoc()): ?>
                <div class="quiz-item">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="background: #e1f5fe; width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #0288d1;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                        <div>
                            <h4 style="margin: 0 0 4px 0; font-size: 16px; color: #2d3748;"><?= htmlspecialchars($quiz['title']) ?></h4>
                            <small style="color: #a0aec0; font-size: 13px;">
                                Ngày tạo: <?= date('d/m/Y H:i', strtotime($quiz['created_at'])) ?> 
                                • Thời gian: <?= isset($quiz['duration_minutes']) ? $quiz['duration_minutes'] . ' phút' : '15 phút' ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="dropdown-container">
                        <button class="quiz-dropdown-btn" onclick="toggleQuizDropdown(event, 'quizMenuQL_<?= $quiz['id'] ?>')">
                            ⋮
                        </button>
                        <div id="quizMenuQL_<?= $quiz['id'] ?>" class="quiz-dropdown-content">
                            <a href="xem_tracnghiem.php?quiz_id=<?= $quiz['id'] ?>&malop=<?= urlencode($ma_lop) ?>">
                                👁 Xem chi tiết đề
                            </a>
                            <a href="danhsach_thi.php?quiz_id=<?= $quiz['id'] ?>&malop=<?= urlencode($ma_lop) ?>">
                                📋 Xem danh sách thi
                            </a>
                            <a href="xoa_tracnghiem.php?quiz_id=<?= $quiz['id'] ?>&malop=<?= urlencode($ma_lop) ?>" 
                               class="delete-btn" 
                               onclick="return confirm('Bạn có chắc chắn muốn xóa bài kiểm tra này không? Mọi dữ liệu câu hỏi sẽ bị mất!');">
                                🗑 Xóa bài thi
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align: center; padding: 40px 20px; color: #a0aec0;">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cbd5e0" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 12px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
            <p>Chưa có đề thi trắc nghiệm trực tuyến nào được tạo.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleQuizDropdown(event, dropdownId) {
    event.stopPropagation();
    var currentDropdown = document.getElementById(dropdownId);
    if (!currentDropdown) return;
    var isCurrentlyShowing = currentDropdown.classList.contains('show');
    
    var dropdowns = document.getElementsByClassName("quiz-dropdown-content");
    for (var i = 0; i < dropdowns.length; i++) {
        dropdowns[i].classList.remove('show');
    }
    
    if (!isCurrentlyShowing) {
        currentDropdown.classList.add('show');
    }
}

window.addEventListener('click', function(event) {
    if (!event.target.matches('.quiz-dropdown-btn')) {
        var dropdowns = document.getElementsByClassName("quiz-dropdown-content");
        for (var i = 0; i < dropdowns.length; i++) {
            if (dropdowns[i].classList.contains('show')) {
                dropdowns[i].classList.remove('show');
            }
        }
    }
});
</script>