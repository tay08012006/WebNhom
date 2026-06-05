<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    die("Chỉ giáo viên mới có quyền truy cập trang này.");
}
$ma_lop = $_GET['malop'] ?? ''; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Tạo Bài Tập Trắc Nghiệm Nhanh</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Nunito', sans-serif; background: #f4f6f9; padding: 20px; color: #333; }
        .container { max-width: 800px; margin: auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h2 { color: #0288d1; margin-bottom: 25px; font-weight: 800; text-align: center; }
        .form-group { margin-bottom: 18px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 600; color: #4a5568; }
        .form-group input[type="text"], .form-group input[type="number"], .form-group select { 
            width: 100%; padding: 12px; border: 1px solid #cbd5e0; border-radius: 8px; box-sizing: border-box; font-family: inherit; font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus { border-color: #0288d1; outline: none; box-shadow: 0 0 0 3px rgba(2,136,209,0.15); }
        
        .question-block { background: #f8f9fa; border-left: 4px solid #0288d1; padding: 20px; margin-bottom: 25px; border-radius: 8px; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .remove-btn { position: absolute; top: 20px; right: 20px; color: #e53935; cursor: pointer; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 4px; }
        .remove-btn:hover { color: #c62828; text-underline-offset: 3px; text-decoration: underline; }
        
        .btn { padding: 12px 24px; border: none; border-radius: 8px; cursor: pointer; font-weight: 700; font-family: inherit; transition: all 0.2s; font-size: 15px; }
        .btn-add { background: #e0f2f1; color: #00796b; margin-bottom: 25px; width: 100%; border: 2px dashed #009688; }
        .btn-add:hover { background: #b2dfdb; }
        .btn-submit { background: #28a745; color: white; width: 100%; font-size: 16px; box-shadow: 0 4px 6px rgba(40,167,69,0.2); }
        .btn-submit:hover { background: #218838; }
        .btn-back { background: #edf2f7; color: #4a5568; text-decoration: none; padding: 10px 18px; border-radius: 8px; display: inline-block; margin-bottom: 20px; font-size: 14px; font-weight: 600; border: 1px solid #cbd5e0; }
        .btn-back:hover { background: #e2e8f0; }
        .grid-options { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 10px; }
    </style>
</head>
<body>

<div class="container">
    <a href="phonghoc.php?malop=<?= urlencode($ma_lop) ?>&tab=trac-nghiem" class="btn-back">← Quay về phòng học</a>
    
    <h2>Tạo Bài Tập Trắc Nghiệm Mới</h2>
    
    <form action="xuly_taotracnghiem.php" method="POST">
        <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
        
        <div class="form-group">
            <label for="quiz_title">Tiêu đề bài kiểm tra trắc nghiệm:</label>
            <input type="text" id="quiz_title" name="quiz_title" placeholder="Nhập tên bài kiểm tra..." required>
        </div>
        
        <div class="form-group">
            <label for="duration_minutes">Thời gian làm bài giới hạn (phút):</label>
            <input type="number" id="duration_minutes" name="duration_minutes" value="" min="1" max="180" required>
        </div>
        
        <hr style="border: 0; border-top: 1px solid #e2e8f0; margin: 30px 0;">
        
        <div id="questions_container">
            <div class="question-block" id="qBlock_1">
                <div class="form-group">
                    <label class="question-title"><b>Câu hỏi số 1:</b></label>
                    <input type="text" name="question_text[]" placeholder="Nhập nội dung câu hỏi trắc nghiệm..." required>
                </div>
                
                <div class="grid-options">
                    <div class="form-group"><input type="text" name="ans_a[]" placeholder="Đáp án tùy chọn A" required></div>
                    <div class="form-group"><input type="text" name="ans_b[]" placeholder="Đáp án tùy chọn B" required></div>
                    <div class="form-group"><input type="text" name="ans_c[]" placeholder="Đáp án tùy chọn C" required></div>
                    <div class="form-group"><input type="text" name="ans_d[]" placeholder="Đáp án tùy chọn D" required></div>
                </div>

                <div class="form-group" style="margin-top: 10px; max-width: 200px;">
                    <label><b>Đáp án đúng:</b></label>
                    <select name="correct_ans[]" required>
                        <option value="A">Đáp án A</option>
                        <option value="B">Đáp án B</option>
                        <option value="C">Đáp án C</option>
                        <option value="D">Đáp án D</option>
                    </select>
                </div>
            </div>
        </div>
        
        <button type="button" class="btn btn-add" onclick="addQuestion()">+ Thêm Câu Hỏi Mới</button>
        <button type="submit" class="btn btn-submit">Lưu & Đăng Bài Tập Kiểm Tra</button>
    </form>
</div>

<script>
    let uniqueIdCounter = 1;

    function addQuestion() {
        uniqueIdCounter++;
        const container = document.getElementById('questions_container');
        const qBlock = document.createElement('div');
        qBlock.className = 'question-block';
        qBlock.id = 'qBlock_' + uniqueIdCounter;
        qBlock.innerHTML = `
            <span class="remove-btn" onclick="removeQuestion(${uniqueIdCounter})">🗑 Xóa câu này</span>
            <div class="form-group">
                <label class="question-title"><b>Câu hỏi số:</b></label>
                <input type="text" name="question_text[]" placeholder="Nhập nội dung câu hỏi trắc nghiệm..." required>
            </div>
            <div class="grid-options">
                <div class="form-group"><input type="text" name="ans_a[]" placeholder="Đáp án tùy chọn A" required></div>
                <div class="form-group"><input type="text" name="ans_b[]" placeholder="Đáp án tùy chọn B" required></div>
                <div class="form-group"><input type="text" name="ans_c[]" placeholder="Đáp án tùy chọn C" required></div>
                <div class="form-group"><input type="text" name="ans_d[]" placeholder="Đáp án tùy chọn D" required></div>
            </div>
            <div class="form-group" style="margin-top: 10px; max-width: 200px;">
                <label><b>Đáp án đúng:</b></label>
                <select name="correct_ans[]" required>
                    <option value="A">Đáp án A</option>
                    <option value="B">Đáp án B</option>
                    <option value="C">Đáp án C</option>
                    <option value="D">Đáp án D</option>
                </select>
            </div>
        `;
        container.appendChild(qBlock);
        
        updateQuestionNumbers();
    }

    function removeQuestion(id) {
        const block = document.getElementById('qBlock_' + id);
        if (block) {
            block.remove();
            
            updateQuestionNumbers();
        }
    }

    function updateQuestionNumbers() {
    
        const blocks = document.querySelectorAll('#questions_container .question-block');
        blocks.forEach((block, index) => {
         
            const titleLabel = block.querySelector('.question-title b');
            if (titleLabel) {
       
                titleLabel.innerText = `Câu hỏi số ${index + 1}:`;
            }
        });
    }
</script>
</body>
</html>