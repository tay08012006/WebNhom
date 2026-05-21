<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$ma_lop = $_GET['malop'] ?? '';
$id_edit = $_GET['id_edit'] ?? ''; 

$ten_gv = $_SESSION['ho_ten'] ?? $_SESSION['hoten'] ?? $_SESSION['user_name'] ?? 'Giáo Viên';
$gv_avatar = '';
if (isset($_SESSION['user_id']) && isset($conn)) {
    $stmt_av = $conn->prepare("SELECT hoten, avatar FROM users WHERE id = ?");
    $stmt_av->bind_param("i", $_SESSION['user_id']);
    $stmt_av->execute();
    $row_av = $stmt_av->get_result()->fetch_assoc();
    if (!empty($row_av['hoten'])) $ten_gv = $row_av['hoten'];
    $gv_avatar = $row_av['avatar'] ?? '';
}
if (!empty($gv_avatar) && !str_starts_with($gv_avatar, 'http')) {
    $gv_avatar = '../uploads/' . $gv_avatar;
}
if (empty($gv_avatar)) {
    $gv_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($ten_gv) . '&background=0288d1&color=fff&bold=true';
}
?>

<style>
    .bt-card { background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; border: 1px solid #edf2f5; position: relative; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
    .form-control { width: 100%; padding: 12px; border: 1px solid #cfd8dc; border-radius: 8px; outline: none; margin-bottom: 10px; font-family: inherit; }
    .actions-top { position: absolute; top: 15px; right: 20px; display: flex; gap: 15px; align-items: center; }
    .btn-edit { color: #0288d1; text-decoration: none; font-size: 14px; }
    .btn-delete-x { color: #ff5252; text-decoration: none; font-size: 22px; font-weight: 300; line-height: 1; transition: 0.2s; }
    .btn-delete-x:hover { transform: scale(1.2); }
    .btn-upload-logo { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; background: #f0f2f5; border-radius: 50%; cursor: pointer; transition: 0.3s; color: #546e7a; }
    .btn-upload-logo:hover { background: #e1f5fe; color: #0288d1; }
    .preview-files { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .preview-tag { display: inline-flex; align-items: center; gap: 5px; background: #f1f8e9; color: #2e7d32; padding: 4px 10px; border-radius: 15px; font-size: 12px; border: 1px solid #c5e1a5; }
    .preview-tag.old-file { background: #e3f2fd; color: #0288d1; border-color: #bbdefb; }
    .remove-file-btn { color: #ff5252; font-weight: bold; cursor: pointer; text-decoration: none; padding-left: 5px; border-left: 1px solid rgba(0,0,0,0.1); margin-left: 3px; }
    .deadline-red { color: #ff5252; font-weight: 800; }
    .edited-text { font-size: 12px; color: #0288d1; font-style: italic; margin-left: 8px; font-weight: 600; }

    /* --- CSS CHO MENU 3 CHẤM BÀI THI TRẮC NGHIỆM --- */
    .dropdown-container { position: absolute; top: 15px; right: 20px; display: inline-block; }
    .quiz-dropdown-btn {
        background: none; border: none; font-size: 24px; cursor: pointer; color: #718096;
        padding: 0 10px; border-radius: 50%; transition: all 0.2s; outline: none; line-height: 1; font-weight: bold;
    }
    .quiz-dropdown-btn:hover { background: #f1f5f9; color: #2d3748; }
    .quiz-dropdown-content {
        display: none; position: absolute; right: 0; top: 100%; margin-top: 5px; background-color: #ffffff;
        min-width: 150px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 8px;
        z-index: 100; border: 1px solid #edf2f7; overflow: hidden;
    }
    .quiz-dropdown-content.show { display: block; animation: fadeIn 0.2s ease; }
    .quiz-dropdown-content a {
        color: #4a5568; padding: 12px 16px; text-decoration: none; display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 600; transition: background 0.2s;
    }
    .quiz-dropdown-content a:hover { background-color: #f8fafc; color: #0288d1; }
    .quiz-dropdown-content a.delete-btn { color: #e53e3e; border-top: 1px solid #edf2f7; }
    .quiz-dropdown-content a.delete-btn:hover { background-color: #fff5f5; color: #c53030; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; background: white; padding: 15px 25px; border-radius: 12px; border: 1px solid #edf2f5; box-shadow: 0 2px 10px rgba(0,0,0,0.02);">
    <div style="font-weight: 700; color: #37474f; font-size: 15px;">Bạn muốn giao thêm nội dung?</div>
    <a href="taotracnghiem.php?malop=<?= urlencode($ma_lop) ?>" style="padding: 8px 18px; background: #28a745; color: white; border: none; border-radius: 20px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; box-shadow: 0 3px 8px rgba(40,167,69,0.25); transition: 0.2s;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        + Tạo Đề Trắc Nghiệm
    </a>
</div>

<div class="bt-card" id="bt-form-add">
    <h3 style="margin-top: 0; color: #37474f; font-size: 18px;">Tạo bài tập mới</h3>
    <form action="xulybaitap.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_bt">
        <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
        
        <input type="text" name="tieu_de" class="form-control" placeholder="Tiêu đề bài tập..." required>
        <textarea name="noi_dung" class="form-control" style="min-height: 80px; resize: none;" placeholder="Hướng dẫn chi tiết..."></textarea>
        
        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
            <div style="flex: 1; min-width: 200px;">
                <label style="font-size: 13px; font-weight: bold; color: #78909c;">Hạn nộp bài:</label>
                <input type="datetime-local" name="deadline" class="form-control" style="margin-bottom: 0;" required>
            </div>
            
            <div style="display: flex; align-items: center; gap: 15px;">
                <label class="btn-upload-logo" title="Đính kèm tài liệu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    <input type="file" name="bt_files[]" id="bt_input_add" multiple style="display: none;" onchange="updatePreview('bt_input_add', 'preview-add')">
                </label>
                <button type="submit" style="padding: 10px 30px; background: #0288d1; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Giao bài</button>
            </div>
        </div>
        <div id="preview-add" class="preview-files"></div>
    </form>
</div>

<div id="assignment-list">
    <div style="margin-bottom: 15px;"><h4 style="color: #546e7a; font-size: 14px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">Bài tập trên lớp</h4></div>
    <?php 
    $ds_bt = [];
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT b.* FROM bai_tap b INNER JOIN classes c ON b.class_id = c.id WHERE c.ma_lop = ? ORDER BY b.id DESC");
        $stmt->bind_param("s", $ma_lop);
        $stmt->execute();
        $result = $stmt->get_result();
        while($row = $result->fetch_assoc()) {
            $row['files'] = !empty($row['file_dinh_kem']) ? explode(',', $row['file_dinh_kem']) : [];
            $row['ngay_dang'] = date('H:i d/m/Y', strtotime($row['ngay_tao']));
            $row['deadline'] = $row['han_nop'];
            $ds_bt[] = $row;
        }
    }

    if (count($ds_bt) > 0):
        foreach ($ds_bt as $bt): 
            if ($bt['id'] == $id_edit): 
        ?>
                    <div class="bt-card" id="bt-<?= $bt['id'] ?>" style="border: 2px solid #0288d1; background: #f9feff;">
                        <h3 style="margin-top: 0; color: #0288d1; font-size: 18px;"><i class="fas fa-wrench"></i> Chỉnh sửa bài tập</h3>
                        
                        <form action="xulybaitap.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="update_bt">
                            <input type="hidden" name="id_update" value="<?= $bt['id'] ?>">
                            <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
                            
                            <input type="text" name="tieu_de" class="form-control" placeholder="Tiêu đề bài tập..." required value="<?= htmlspecialchars($bt['tieu_de']) ?>">
                            <textarea name="noi_dung" class="form-control" style="min-height: 80px; resize: none;" placeholder="Hướng dẫn chi tiết..."><?= htmlspecialchars($bt['noi_dung']) ?></textarea>
                            
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
                                <div style="flex: 1; min-width: 200px;">
                                    <label style="font-size: 13px; font-weight: bold; color: #78909c;">Hạn nộp bài:</label>
                                    <input type="datetime-local" name="deadline" class="form-control" style="margin-bottom: 0;" required value="<?= !empty($bt['deadline']) ? date('Y-m-d\TH:i', strtotime($bt['deadline'])) : '' ?>">
                                </div>
                                
                                <div style="display: flex; align-items: center; gap: 15px;">
                                    <label class="btn-upload-logo" title="Thêm file mới">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                        <input type="file" name="bt_files[]" id="bt_input_<?= $bt['id'] ?>" multiple style="display: none;" onchange="updatePreview('bt_input_<?= $bt['id'] ?>', 'preview-<?= $bt['id'] ?>')">
                                    </label>
                                    <button type="submit" style="padding: 10px 30px; background: #0288d1; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">Lưu thay đổi</button>
                                    <a href="phonghoc.php?malop=<?= $ma_lop ?>&tab=bai-tap#bt-<?= $bt['id'] ?>" style="font-size: 13px; color: #90a4ae; text-decoration: none;">Hủy</a>
                                </div>
                            </div>

                            <div id="preview-<?= $bt['id'] ?>" class="preview-files">
                                <?php if(!empty($bt['files'])): ?>
                                    <?php foreach($bt['files'] as $f): $file_id = md5($f); ?>
                                        <span class="preview-tag old-file" id="file-<?= $file_id ?>">
                                            📄 <?= (strlen($f) > 20) ? substr($f, 0, 15).'...' : $f ?>
                                            <input type="hidden" name="old_files[]" value="<?= htmlspecialchars($f) ?>">
                                            <a onclick="document.getElementById('file-<?= $file_id ?>').remove()" class="remove-file-btn" title="Xóa file này">&times;</a>
                                        </span>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

        <?php 
            else: 
                $is_late = !empty($bt['deadline']) && strtotime(date('Y-m-d H:i')) > strtotime($bt['deadline']);
        ?>
                    <div class="bt-card" id="bt-<?= $bt['id'] ?>">
                        <div class="actions-top">
                            <a href="?malop=<?= $ma_lop ?>&tab=bai-tap&id_edit=<?= $bt['id'] ?>#bt-<?= $bt['id'] ?>" class="btn-edit" title="Sửa bài tập">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </a>
                            <a href="xulybaitap.php?action=delete_bt&id=<?= $bt['id'] ?>&malop=<?= $ma_lop ?>" class="btn-delete-x" onclick="return confirm('Xóa bài tập này?')">&times;</a>
                        </div>

                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                            <img src="<?= htmlspecialchars($gv_avatar) ?>" alt="Avatar" style="width: 38px; height: 38px; border-radius: 50%; object-fit: cover; box-shadow: 0 2px 4px rgba(2, 136, 209, 0.2); border: 1.5px solid #0288d1;">
                            <div>
                                <div style="font-weight: 700; color: #263238; font-size: 14px;"><?= htmlspecialchars($ten_gv) ?></div>
                                <div style="font-size: 11px; color: #78909c; margin-top: 2px;">
                                    Giao lúc: <?= $bt['ngay_dang'] ?>
                                    <?php if (isset($bt['chinh_sua']) && $bt['chinh_sua'] == 1): ?>
                                        <span class="edited-text">• đã chỉnh sửa</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <h4 style="margin: 0 0 10px 0; color: #0277bd; padding-right: 60px;"><?= htmlspecialchars($bt['tieu_de']) ?></h4>
                        <p style="font-size: 14px; line-height: 1.6; color: #546e7a; margin-bottom: 15px;"><?= nl2br(htmlspecialchars($bt['noi_dung'])) ?></p>

                        <?php if(!empty($bt['deadline'])): ?>
                            <div style="font-size: 14px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                                <span>Hạn nộp:</span>
                                <span class="<?= $is_late ? 'deadline-red' : '' ?>"><?= date('H:i d/m/Y', strtotime($bt['deadline'])) ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($bt['files'])): ?>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; padding-top: 10px; border-top: 1px solid #f1f1f1;">
                                <?php foreach($bt['files'] as $f): ?>
                                    <a href="../uploads/<?= htmlspecialchars($f) ?>" target="_blank" style="text-decoration: none; background: #f8fafb; padding: 6px 12px; border-radius: 6px; border: 1px solid #eceff1; font-size: 12px; color: #0288d1; font-weight: bold;">
                                        📄 <?= (strlen($f) > 15) ? substr($f, 0, 12).'...' : htmlspecialchars($f) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <div style="background: #fafbfc; padding: 12px; border-radius: 8px; font-size: 13px;">
                            <div style="color: #90a4ae; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; font-size: 10px;">Tình trạng nộp</div>
                            <?php 
                            $count_hs = 0;
                            if (isset($conn)) {
                                $stmt_nop = $conn->prepare("SELECT COUNT(*) as total FROM nop_bai WHERE bai_tap_id = ?");
                                $stmt_nop->bind_param("i", $bt['id']);
                                $stmt_nop->execute();
                                $count_hs = $stmt_nop->get_result()->fetch_assoc()['total'];
                            }
                            echo ($count_hs > 0) ? "Đã có $count_hs học sinh nộp bài." : "Chưa có ai nộp bài.";
                            ?>
                        </div>
                    </div>
        <?php 
            endif; 
        endforeach; 
    else:
    ?>
        <p style="font-size: 13px; color: #90a4ae; text-align: center; background: white; padding: 20px; border-radius: 12px; border: 1px dashed #cfd8dc; margin-bottom: 20px;">Chưa có bài tập nào được giao.</p>
    <?php endif; ?>
</div>

<div id="quiz-list" style="margin-top: 30px;">
    <div style="margin-bottom: 15px;"><h4 style="color: #2e7d32; font-size: 14px; text-transform: uppercase; font-weight: bold; letter-spacing: 0.5px;">Bài thi Trắc nghiệm tự động</h4></div>
    <?php 
    $ds_quizzes = [];
    if (isset($conn)) {
        // Lấy danh sách quiz thuộc về lớp học này
        $stmt_qz = $conn->prepare("SELECT q.* FROM quizzes q INNER JOIN classes c ON q.class_id = c.id WHERE c.ma_lop = ? ORDER BY q.id DESC");
        $stmt_qz->bind_param("s", $ma_lop);
        $stmt_qz->execute();
        $res_qz = $stmt_qz->get_result();
        while($row_qz = $res_qz->fetch_assoc()) {
            $ds_quizzes[] = $row_qz;
        }
    }

    if (count($ds_quizzes) > 0):
        foreach ($ds_quizzes as $qz): 
    ?>
            <div class="bt-card" id="quiz-<?= $qz['id'] ?>" style="border-left: 4px solid #28a745;">
                
                <div class="dropdown-container">
                    <button class="quiz-dropdown-btn" onclick="toggleQuizDropdown(event, 'quizMenu_<?= $qz['id'] ?>')">
                        ⋮
                    </button>
                    <div id="quizMenu_<?= $qz['id'] ?>" class="quiz-dropdown-content">
                        <a href="xem_tracnghiem.php?quiz_id=<?= $qz['id'] ?>&malop=<?= urlencode($ma_lop) ?>">
                            👁 Xem chi tiết
                        </a>
                        <a href="xoa_tracnghiem.php?quiz_id=<?= $qz['id'] ?>&malop=<?= urlencode($ma_lop) ?>" 
                           class="delete-btn" 
                           onclick="return confirm('Bạn có chắc chắn muốn xóa đề trắc nghiệm này không? Toàn bộ câu hỏi liên quan sẽ bị xóa sạch!');">
                            🗑 Xóa bài thi
                        </a>
                    </div>
                </div>
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                    <div style="background: rgba(40, 167, 69, 0.1); color: #28a745; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1.5px solid #28a745;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #263238; font-size: 14px;">Đề trắc nghiệm trực tuyến</div>
                        <div style="font-size: 11px; color: #78909c; margin-top: 2px;">
                            Tạo lúc: <?= date('H:i d/m/Y', strtotime($qz['created_at'])) ?>
                            
                            <span style="margin-left: 10px; background: #fff3e0; color: #e65100; font-size: 11px; padding: 2px 8px; border-radius: 12px; font-weight: bold; display: inline-flex; align-items: center; gap: 3px;">
                                ⏱ <?= isset($qz['duration_minutes']) ? $qz['duration_minutes'] : 15 ?> phút
                            </span>
                        </div>
                    </div>
                </div>

                <h4 style="margin: 0 0 10px 0; color: #2e7d32; padding-right: 120px;"><?= htmlspecialchars($qz['title']) ?></h4>
                
                <div style="background: #fafdfa; padding: 12px; border-radius: 8px; font-size: 13px; border: 1px solid #e8f5e9;">
                    <div style="color: #78909c; font-weight: bold; margin-bottom: 5px; text-transform: uppercase; font-size: 10px;">Tình trạng kết quả</div>
                    <span style="color: #607d8b;">Học sinh làm bài xong hệ thống sẽ trả kết quả tự động ngay lập tức.</span>
                </div>
            </div>
    <?php 
        endforeach; 
    else:
    ?>
        <p style="font-size: 13px; color: #90a4ae; text-align: center; background: white; padding: 20px; border-radius: 12px; border: 1px dashed #cfd8dc;">Chưa có đề thi trắc nghiệm trực tuyến nào được tạo.</p>
    <?php endif; ?>
</div>

<script>
function updatePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    const newTags = preview.querySelectorAll('.preview-tag.new-file');
    newTags.forEach(tag => tag.remove());

    Array.from(input.files).forEach((file, index) => {
        const tag = document.createElement('span');
        tag.className = 'preview-tag new-file';
        tag.innerHTML = `
            📄 ${(file.name.length > 15) ? file.name.substring(0, 12) + '...' : file.name} (Mới)
            <a onclick="removeNewFile('${inputId}', '${previewId}', ${index})" class="remove-file-btn" title="Hủy chọn">&times;</a>
        `;
        preview.appendChild(tag);
    });
}

function removeNewFile(inputId, previewId, indexToRemove) {
    const input = document.getElementById(inputId);
    const dt = new DataTransfer(); 
    const { files } = input;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== indexToRemove) {
            dt.items.add(files[i]); 
        }
    } 
    input.files = dt.files; 
    updatePreview(inputId, previewId);
}

// BẮT ĐẦU: JS XỬ LÝ CLICK HIỆN MENU 3 CHẤM BÀI TRẮC NGHIỆM
function toggleQuizDropdown(event, dropdownId) {
    event.stopPropagation();
    var currentDropdown = document.getElementById(dropdownId);
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
// KẾT THÚC JS XỬ LÝ CLICK HIỆN MENU
</script>