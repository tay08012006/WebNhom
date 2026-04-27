<?php
// Kiểm tra session an toàn
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['bang_tin_list'])) {
    $_SESSION['bang_tin_list'] = [];
}

$ma_lop = $_GET['malop'] ?? '';
$id_edit = $_GET['id_edit'] ?? ''; // Lấy ID bảng tin đang muốn sửa
$tab = $_GET['tab'] ?? 'bang-tin'; 
?>

<style>
    /* Nâng cấp UI tổng thể */
    .bt-card { background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; border: 1px solid #edf2f5; position: relative; box-shadow: 0 4px 15px rgba(0,0,0,0.03); transition: all 0.3s ease; }
    .bt-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
    
    .form-control { width: 100%; padding: 15px; border: 1px solid #cfd8dc; border-radius: 10px; outline: none; font-family: inherit; font-size: 15px; line-height: 1.5; transition: border-color 0.3s, box-shadow 0.3s; }
    .form-control:focus { border-color: #0288d1; box-shadow: 0 0 0 3px rgba(2, 136, 209, 0.1); background: #fff !important; }
    
    .actions-top { position: absolute; top: 20px; right: 20px; display: flex; gap: 12px; align-items: center; }
    .btn-edit { color: #0288d1; text-decoration: none; padding: 6px; border-radius: 6px; background: #e1f5fe; display: flex; align-items: center; transition: 0.2s; }
    .btn-edit:hover { background: #b3e5fc; transform: translateY(-2px); }
    
    .btn-delete-x { color: #ff5252; text-decoration: none; font-size: 20px; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; border-radius: 6px; background: #ffebee; transition: 0.2s; }
    .btn-delete-x:hover { background: #ffcdd2; transform: translateY(-2px); }
    
    .btn-upload-logo { display: inline-flex; align-items: center; justify-content: center; width: 45px; height: 45px; background: #f0f2f5; border-radius: 50%; cursor: pointer; transition: 0.3s; color: #546e7a; }
    .btn-upload-logo:hover { background: #e1f5fe; color: #0288d1; transform: scale(1.05); }
    
    .btn-primary { padding: 10px 25px; background: #0288d1; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s; font-size: 14px; }
    .btn-primary:hover { background: #0277bd; transform: translateY(-1px); box-shadow: 0 4px 10px rgba(2, 136, 209, 0.2); }
    
    .btn-secondary { padding: 10px 20px; background: #f0f2f5; color: #546e7a; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; text-decoration: none; transition: 0.2s; font-size: 14px; }
    .btn-secondary:hover { background: #e4e8eb; color: #37474f; }
    
    .preview-files { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 15px; }
    .preview-tag { display: inline-flex; align-items: center; gap: 6px; background: #f1f8e9; color: #2e7d32; padding: 6px 12px; border-radius: 20px; font-size: 13px; border: 1px solid #c5e1a5; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    .preview-tag.old-file { background: #e3f2fd; color: #0288d1; border-color: #bbdefb; }
    .remove-file-btn { color: #ff5252; font-weight: bold; cursor: pointer; text-decoration: none; padding-left: 6px; border-left: 1px solid rgba(0,0,0,0.1); margin-left: 4px; font-size: 16px; line-height: 1; }
    
    .empty-state { text-align: center; padding: 40px 20px; color: #90a4ae; font-size: 15px; background: #fafbfc; border-radius: 12px; border: 1px dashed #cfd8dc; }
</style>

<div class="bt-card" style="padding: 20px;">
    <form action="xulybangtin.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_post">
        <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
        
        <textarea name="noi_dung" class="form-control" style="min-height: 80px; resize: none; background: #f9fbfb; overflow: hidden;" placeholder="Thông báo nội dung gì đó cho lớp học..." required oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"></textarea>
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
            <label class="btn-upload-logo" title="Đính kèm tài liệu">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                <input type="file" name="post_files[]" id="post_input_add" multiple style="display: none;" onchange="updatePostPreview('post_input_add', 'preview-post-add')">
            </label>
            <button type="submit" class="btn-primary">Đăng thông báo</button>
        </div>
        <div id="preview-post-add" class="preview-files"></div>
    </form>
</div>

<div id="post-list">
    <?php 
    $ds_post = array_reverse($_SESSION['bang_tin_list']);
    $has_posts = false;

    foreach ($ds_post as $post): 
        if ($post['ma_lop'] == $ma_lop):
            $has_posts = true;
            
            // --- NẾU ĐANG CHỌN SỬA BẢNG TIN NÀY ---
            if ($post['id'] == $id_edit): 
    ?>
                <div class="bt-card" id="post-<?= $post['id'] ?>" style="border: 2px solid #0288d1; background: #f2fbff;">
                    <div style="font-weight: bold; color: #0288d1; margin-bottom: 15px; font-size: 16px; display: flex; align-items: center; gap: 8px;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        Đang chỉnh sửa thông báo
                    </div>
                    
                    <form action="xulybangtin.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="update_post">
                        <input type="hidden" name="id_update" value="<?= $post['id'] ?>">
                        <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
                        
                        <textarea name="noi_dung" class="form-control" style="min-height: 100px; resize: none; overflow: hidden;" placeholder="Nội dung thông báo..." required oninput="this.style.height = ''; this.style.height = this.scrollHeight + 'px'"><?= htmlspecialchars($post['noi_dung']) ?></textarea>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 15px;">
                            <label class="btn-upload-logo" title="Thêm file mới">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                <input type="file" name="post_files[]" id="post_input_<?= $post['id'] ?>" multiple style="display: none;" onchange="updatePostPreview('post_input_<?= $post['id'] ?>', 'preview-post-<?= $post['id'] ?>')">
                            </label>
                            
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <a href="phonghoc.php?malop=<?= $ma_lop ?>&tab=<?= $tab ?>#post-<?= $post['id'] ?>" class="btn-secondary">Hủy</a>
                                <button type="submit" class="btn-primary">Lưu thay đổi</button>
                            </div>
                        </div>

                        <div id="preview-post-<?= $post['id'] ?>" class="preview-files">
                            <?php if(!empty($post['files'])): ?>
                                <?php foreach($post['files'] as $f): $file_id = md5($f); ?>
                                    <span class="preview-tag old-file" id="file-<?= $file_id ?>">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                                        <?= (strlen($f) > 20) ? substr($f, 0, 15).'...' : $f ?>
                                        <input type="hidden" name="old_files[]" value="<?= htmlspecialchars($f) ?>">
                                        <a onclick="document.getElementById('file-<?= $file_id ?>').remove()" class="remove-file-btn" title="Xóa file này">&times;</a>
                                    </span>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

    <?php 
            // --- NẾU KHÔNG SỬA (Hiển thị thẻ bình thường) ---
            else: 
    ?>
                <div class="bt-card" id="post-<?= $post['id'] ?>">
                    <div class="actions-top">
                        <a href="?malop=<?= $ma_lop ?>&tab=<?= $tab ?>&id_edit=<?= $post['id'] ?>#post-<?= $post['id'] ?>" class="btn-edit" title="Sửa thông báo">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </a>
                        <a href="xulybangtin.php?action=delete_post&id=<?= $post['id'] ?>&malop=<?= $ma_lop ?>" class="btn-delete-x" onclick="return confirm('Bạn có chắc chắn muốn xóa thông báo này?')" title="Xóa thông báo">&times;</a>
                    </div>

                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #0288d1, #26c6da); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; box-shadow: 0 2px 5px rgba(2, 136, 209, 0.3);">
                            GV
                        </div>
                        <div>
                            <div style="font-weight: 700; color: #263238; font-size: 15px;">Giáo viên</div>
                            <div style="font-size: 12px; color: #78909c; margin-top: 2px;">
                                <?= $post['ngay_dang'] ?? date('H:i d/m/Y') ?>
                                <?php if (!empty($post['is_edited'])): ?>
                                    <span style="font-style: italic; background: #eceff1; padding: 2px 6px; border-radius: 4px; margin-left: 5px;">đã chỉnh sửa</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <p style="font-size: 15px; line-height: 1.7; color: #37474f; margin: 15px 0; padding-right: 50px; white-space: pre-wrap;"><?= htmlspecialchars($post['noi_dung']) ?></p>

                    <?php if(!empty($post['files'])): ?>
                        <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; padding-top: 15px; border-top: 1px solid #f0f2f5;">
                            <?php foreach($post['files'] as $f): ?>
                                <a href="../uploads/<?= htmlspecialchars($f) ?>" target="_blank" style="text-decoration: none; background: #f4f6f8; padding: 8px 16px; border-radius: 8px; border: 1px solid #e4e8eb; font-size: 13px; color: #0277bd; font-weight: 600; display: flex; align-items: center; gap: 8px; transition: 0.2s;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
                                    <?= (strlen($f) > 25) ? substr($f, 0, 22).'...' : htmlspecialchars($f) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
    <?php 
            endif; // End if id_edit
        endif; // End if ma_lop
    endforeach; 

    // Hiển thị trạng thái rỗng nếu chưa có bài nào
    if (!$has_posts):
    ?>
        <div class="empty-state">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#cfd8dc" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="margin-bottom: 10px;"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            <br>Lớp học chưa có thông báo nào.
        </div>
    <?php endif; ?>
</div>

<script>
// Chạy hàm auto-resize một lần lúc load trang cho các textarea đang có sẵn nội dung
document.addEventListener("DOMContentLoaded", function() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(ta => {
        if(ta.value.trim() !== '') {
            ta.style.height = ta.scrollHeight + 'px';
        }
    });
});

// Hàm cập nhật preview file
function updatePostPreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    const newTags = preview.querySelectorAll('.preview-tag.new-file');
    newTags.forEach(tag => tag.remove());

    Array.from(input.files).forEach((file, index) => {
        const tag = document.createElement('span');
        tag.className = 'preview-tag new-file';
        tag.innerHTML = `
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"></path><polyline points="13 2 13 9 20 9"></polyline></svg>
            ${(file.name.length > 15) ? file.name.substring(0, 12) + '...' : file.name} (Mới)
            <a onclick="removePostNewFile('${inputId}', '${previewId}', ${index})" class="remove-file-btn" title="Hủy chọn">&times;</a>
        `;
        preview.appendChild(tag);
    });
}

function removePostNewFile(inputId, previewId, indexToRemove) {
    const input = document.getElementById(inputId);
    const dt = new DataTransfer(); 
    const { files } = input;
    
    for (let i = 0; i < files.length; i++) {
        if (i !== indexToRemove) {
            dt.items.add(files[i]); 
        }
    } 
    input.files = dt.files; 
    updatePostPreview(inputId, previewId); 
}
</script>