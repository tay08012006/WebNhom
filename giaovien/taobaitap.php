<?php
// Kiểm tra session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['bai_tap_list'])) $_SESSION['bai_tap_list'] = [];
if (!isset($_SESSION['nop_bai_hoc_sinh'])) $_SESSION['nop_bai_hoc_sinh'] = []; 

$ma_lop = $_GET['malop'] ?? '';
$id_edit = $_GET['id_edit'] ?? ''; // Lấy ID bài tập đang muốn sửa
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
</style>

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
    <?php 
    $ds_bt = array_reverse($_SESSION['bai_tap_list']);
    foreach ($ds_bt as $bt): 
        if ($bt['ma_lop'] == $ma_lop):
            
            // --- NẾU BÀI TẬP NÀY ĐANG ĐƯỢC CHỌN ĐỂ SỬA (Hiển thị Form sửa) ---
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
                                <input type="datetime-local" name="deadline" class="form-control" style="margin-bottom: 0;" required value="<?= $bt['deadline'] ?>">
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
            // --- NẾU KHÔNG SỬA (Hiển thị thẻ bài tập bình thường) ---
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

                    <h4 style="margin: 0 0 10px 0; color: #0277bd; padding-right: 60px;"><?= htmlspecialchars($bt['tieu_de']) ?></h4>
                    <p style="font-size: 14px; line-height: 1.6; color: #546e7a; margin-bottom: 15px;"><?= nl2br(htmlspecialchars($bt['noi_dung'])) ?></p>

                    <?php if(!empty($bt['deadline'])): ?>
                        <div style="font-size: 14px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                            <span>⏳ Hạn nộp:</span>
                            <span class="<?= $is_late ? 'deadline-red' : '' ?>"><?= date('H:i d/m/Y', strtotime($bt['deadline'])) ?></span>
                            <?php if (!empty($bt['is_edited'])): ?>
                                <span style="font-size: 12px; color: #90a4ae; font-style: italic;">(đã chỉnh sửa)</span>
                            <?php endif; ?>
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
                        foreach ($_SESSION['nop_bai_hoc_sinh'] as $hs) if($hs['id_bai_tap'] == $bt['id']) $count_hs++;
                        echo ($count_hs > 0) ? "Đã có $count_hs học sinh nộp bài." : "Chưa có ai nộp bài.";
                        ?>
                    </div>
                </div>
    <?php 
            endif; // End if id_edit
        endif; // End if ma_lop
    endforeach; 
    ?>
</div>

<script>
// Cập nhật hàm JS nhận tham số ID để phân biệt giữa Form Tạo mới và các Form Sửa
function updatePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    // Xóa các preview file mới cũ (chỉ giữ lại file old-file nếu đang ở form sửa)
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
    updatePreview(inputId, previewId); // Gọi lại hàm để render lại danh sách
}
</script>