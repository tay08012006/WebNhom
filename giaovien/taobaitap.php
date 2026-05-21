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
if (empty($gv_avatar)) {
    $gv_avatar = 'https://ui-avatars.com/api/?name=' . urlencode($ten_gv) . '&background=0288d1&color=fff&bold=true';
}
?>

<style>
    .bt-card { background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; border: 1px solid #edf2f5; position: relative; box-shadow: 0 4px 12px rgba(0,0,0,0.02); }
    .bt-header { display: flex; align-items: center; gap: 12px; margin-bottom: 15px; }
    .bt-avatar { width: 44px; height: 44px; border-radius: 50%; object-fit: cover; }
    .bt-author-info h4 { margin: 0; color: #2c3e50; font-size: 15px; font-weight: 700; }
    .bt-author-info span { font-size: 12px; color: #95a5a6; }
    .bt-title { color: #2c3e50; font-size: 18px; font-weight: 800; margin-bottom: 10px; }
    .bt-content { color: #555; font-size: 14.5px; line-height: 1.6; white-space: pre-line; margin-bottom: 15px; }
    .bt-meta { display: flex; flex-wrap: wrap; gap: 15px; font-size: 13px; color: #7f8c8d; border-top: 1px solid #f8f9fa; padding-top: 12px; margin-top: 15px; align-items: center; }
    .bt-badge { background: #fdf2e9; color: #e67e22; padding: 4px 10px; border-radius: 6px; font-weight: 700; }
    .bt-files { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 15px; }
    .bt-file-tag { background: #f0f4f8; color: #2b6cb0; padding: 6px 12px; border-radius: 8px; font-size: 13px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; border: 1px solid #e2e8f0; }
    .bt-file-tag:hover { background: #e2e8f0; }
    
    /* Giao diện nút bấm và bảng danh sách học sinh nộp bài mới bổ sung */
    .btn-toggle-nop { background: #0288d1; color: white; border: none; padding: 6px 12px; border-radius: 6px; font-weight: 700; font-size: 13px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 5px; }
    .btn-toggle-nop:hover { background: #01579b; }
    .assignment-list-box { margin-top: 15px; background: #fafbfc; border: 1px dashed #cfd8dc; border-radius: 8px; padding: 15px; display: none; }
    .table-sub-list { width: 100%; border-collapse: collapse; font-size: 13.5px; background: white; }
    .table-sub-list th { background: #f1f5f9; color: #475569; text-align: left; padding: 10px; font-weight: 700; border-bottom: 2px solid #e2e8f0; }
    .table-sub-list td { padding: 10px; border-bottom: 1px solid #e2e8f0; color: #334155; }
    .link-view-btn { text-decoration: none; font-weight: 700; font-size: 12.5px; padding: 4px 8px; border-radius: 4px; display: inline-flex; align-items: center; gap: 4px; }
</style>

<div style="background: white; border-radius: 12px; padding: 25px; margin-bottom: 25px; border: 1px solid #edf2f5;">
    <h3 style="margin-top:0; margin-bottom:20px; color:#2c3e50; font-weight:800; font-size:20px;">
        <?= !empty($id_edit) ? '✏️ Chỉnh sửa bài tập' : '📝 Giao bài tập mới cho lớp' ?>
    </h3>
    
    <?php
    $edit_data = ['tieude' => '', 'noi_dung' => '', 'ngay_han' => ''];
    $edit_files = [];
    if (!empty($id_edit) && isset($conn)) {
        $stmt_e = $conn->prepare("SELECT * FROM baitap WHERE id = ?");
        $stmt_e->bind_param("i", $id_edit);
        $stmt_e->execute();
        $edit_data = $stmt_e->get_result()->fetch_assoc() ?: $edit_data;
        
        if (!empty($edit_data['file_dinhkem'])) {
            $edit_files = json_decode($edit_data['file_dinhkem'], true) ?: explode(',', $edit_data['file_dinhkem']);
        }
    }
    ?>

    <form action="xuly_baitap.php" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 15px;">
        <input type="hidden" name="action" value="<?= !empty($id_edit) ? 'edit' : 'create' ?>">
        <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
        <?php if (!empty($id_edit)): ?>
            <input type="hidden" name="id_baitap" value="<?= $id_edit ?>">
        <?php endif; ?>

        <div>
            <label style="display:block; font-weight:700; margin-bottom:6px; color:#4a5568; font-size:14px;">Tiêu đề bài tập <span style="color:red;">*</span></label>
            <input type="text" name="tieude" value="<?= htmlspecialchars($edit_data['tieude']) ?>" placeholder="Ví dụ: Bài tập làm văn số 1, Bài tập JavaScript cơ bản..." required style="width:100%; padding:10px 14px; border:1px solid #cbd5e0; border-radius:8px; font-size:14px; outline:none; font-weight:600;">
        </div>

        <div>
            <label style="display:block; font-weight:700; margin-bottom:6px; color:#4a5568; font-size:14px;">Hướng dẫn chi tiết bài tập</label>
            <textarea name="noi_dung" rows="5" placeholder="Viết yêu cầu đề bài, hướng dẫn học sinh các bước làm bài tại đây..." style="width:100%; padding:10px 14px; border:1px solid #cbd5e0; border-radius:8px; font-size:14px; outline:none; font-weight:600; resize:vertical;"><?= htmlspecialchars($edit_data['noi_dung']) ?></textarea>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div>
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#4a5568; font-size:14px;">Thời hạn nộp bài (Hạn chót)</label>
                <input type="datetime-local" name="ngay_han" value="<?= !empty($edit_data['ngay_han']) ? date('Y-m-m\TH:i', strtotime($edit_data['ngay_han'])) : '' ?>" style="width:100%; padding:10px 14px; border:1px solid #cbd5e0; border-radius:8px; font-size:14px; outline:none; font-weight:600; color:#4a5568;">
            </div>
            <div>
                <label style="display:block; font-weight:700; margin-bottom:6px; color:#4a5568; font-size:14px;">Đính kèm tài liệu học tập (Nhiều file)</label>
                <input type="file" name="files[]" id="bt-file-input" multiple onchange="updatePreview('bt-file-input', 'bt-file-preview')" style="display:none;">
                <button type="button" onclick="document.getElementById('bt-file-input').click()" style="width:100%; padding:10px; border:2px dashed #cbd5e0; background:#f7fafc; border-radius:8px; font-weight:700; color:#4a5568; cursor:pointer; text-align:center; font-size:14px;">📁 Chọn tài liệu tải lên...</button>
                
                <div id="bt-file-preview" style="display:flex; flex-wrap:wrap; gap:6px; margin-top:8px;">
                    <?php foreach ($edit_files as $f): if(!empty($f)): ?>
                        <span class="preview-tag old-file" style="background:#edf2f7; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600; display:inline-flex; align-items:center; gap:4px;">
                            📄 <?= (strlen($f) > 15) ? substr($f, 0, 12).'...' : $f ?>
                            <input type="hidden" name="keep_files[]" value="<?= htmlspecialchars($f) ?>">
                            <a onclick="this.parentElement.remove()" style="color:red; cursor:pointer; font-weight:bold; margin-left:4px;">&times;</a>
                        </span>
                    <?php endif; endforeach; ?>
                </div>
            </div>
        </div>

        <div style="display:flex; justify-content: flex-end; gap: 10px; margin-top: 10px;">
            <?php if (!empty($id_edit)): ?>
                <a href="?malop=<?= htmlspecialchars($ma_lop) ?>&tab=bai-tap" style="padding:10px 20px; background:#e2e8f0; color:#4a5568; border-radius:8px; text-decoration:none; font-weight:700; font-size:14px;">Hủy bỏ</a>
            <?php endif; ?>
            <button type="submit" style="padding:10px 25px; background:#0288d1; color:white; border:none; border-radius:8px; font-weight:700; font-size:14px; cursor:pointer;">
                <?= !empty($id_edit) ? 'Cập nhật bài tập' : ' Giao bài tập ngay' ?>
            </button>
        </div>
    </form>
</div>

<div style="margin-top: 25px;">
    <h3 style="color: #2c3e50; font-weight: 800; font-size: 18px; margin-bottom: 15px;">📌 Danh sách bài tập đã giao</h3>
    
    <?php
    $list_baitap = [];
    if (isset($conn) && !empty($ma_lop)) {
        // Lấy danh sách lớp
        $stmt_c = $conn->prepare("SELECT id FROM classes WHERE ma_lop = ?");
        $stmt_c->bind_param("s", $ma_lop);
        $stmt_c->execute();
        $class_info = $stmt_c->get_result()->fetch_assoc();
        
        if ($class_info) {
            $stmt_b = $conn->prepare("SELECT * FROM bai_tap WHERE class_id = ? ORDER BY ngay_tao DESC");
            $stmt_b->bind_param("i", $class_info['id']);
            $stmt_b->execute();
            $res_b = $stmt_b->get_result();
            while ($row = $res_b->fetch_assoc()) {
                $list_baitap[] = $row;
            }
        }
    }

    if (empty($list_baitap)):
    ?>
        <div style="background: white; border-radius:12px; padding:30px; text-align:center; color:#95a5a6; border:1px solid #edf2f5; font-weight:600;">Lớp học chưa có bài tập nào được giao.</div>
    <?php
    else:
        foreach ($list_baitap as $bt):
            $files = [];
            if (!empty($bt['file_dinhkem'])) {
                $files = json_decode($bt['file_dinhkem'], true) ?: explode(',', $bt['file_dinhkem']);
            }
    ?>
            <div class="bt-card">
                <div style="position: absolute; top: 20px; right: 20px; display: flex; gap: 8px;">
                    <a href="?malop=<?= htmlspecialchars($ma_lop) ?>&tab=bai-tap&id_edit=<?= $bt['id'] ?>" style="color: #3182ce; text-decoration: none; font-size: 13px; font-weight: 700;">✏️ Sửa</a>
                    <a href="xuly_baitap.php?action=delete&id=<?= $bt['id'] ?>&ma_lop=<?= htmlspecialchars($ma_lop) ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa bài tập này và toàn bộ dữ liệu học sinh đã nộp không?')" style="color: #e53e3e; text-decoration: none; font-size: 13px; font-weight: 700;">🗑️ Xóa</a>
                </div>

                <div class="bt-header">
                    <img src="<?= htmlspecialchars($gv_avatar) ?>" class="bt-avatar" alt="GV">
                    <div class="bt-author-info">
                        <h4><?= htmlspecialchars($ten_gv) ?> (Bạn)</h4>
                        <span>Đăng lúc: <?= date('H:i d/m/Y', strtotime($bt['ngay_tao'])) ?></span>
                    </div>
                </div>

                <div class="bt-title"><?= htmlspecialchars($bt['tieu_de']) ?></div>
                <div class="bt-content"><?= htmlspecialchars($bt['noi_dung']) ?></div>

                <?php if (!empty($files)): ?>
                    <div class="bt-files">
                        <?php foreach ($files as $f): if(!empty($f)): ?>
                            <a href="../uploads/<?= htmlspecialchars($f) ?>" target="_blank" class="bt-file-tag">📁 <?= htmlspecialchars($f) ?></a>
                        <?php endif; endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="bt-meta">
                    <span class="bt-badge">🕒 Hạn chót: <?= !empty($bt['ngay_han']) ? date('H:i d/m/Y', strtotime($bt['ngay_han'])) : 'Không giới hạn thời gian' ?></span>
                    
                    <div style="margin-left: auto; display: flex; align-items: center; gap: 10px;">
                        <span style="font-weight: 700; color: #4a5568;">
                            <?php
                            $count_hs = 0;
                            if (isset($conn)) {
                                $sql_count_nop = "SELECT COUNT(*) as total FROM nop_bai WHERE bai_tap_id = ?";
                                $stmt_nop = $conn->prepare($sql_count_nop);
                                $stmt_nop->bind_param("i", $bt['id']);
                                $stmt_nop->execute();
                                $count_hs = $stmt_nop->get_result()->fetch_assoc()['total'];
                            }
                            echo ($count_hs > 0) ? "🔹 Đã có $count_hs học sinh nộp bài" : "🔸 Chưa có ai nộp bài";
                            ?>
                        </span>
                        
                        <?php if ($count_hs > 0): ?>
                            <button class="btn-toggle-nop" onclick="toggleViewNop(<?= $bt['id'] ?>)">Xem bài nộp</button>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($count_hs > 0): ?>
                    <div class="assignment-list-box" id="nop-box-<?= $bt['id'] ?>">
                        <h4 style="margin-top: 0; margin-bottom: 10px; color: #1e293b; font-size: 14px;">📊 Chi tiết danh sách bài nộp:</h4>
                        <table class="table-sub-list">
                            <thead>
                                <tr>
                                    <th>Họ tên học sinh</th>
                                    <th>Thời gian gửi</th>
                                    <th>Tệp bài làm (File)</th>
                                    <th>Đường dẫn (Link)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql_sub = "SELECT n.*, u.hoten FROM nop_bai n 
                                            JOIN users u ON n.student_id = u.id 
                                            WHERE n.bai_tap_id = ? ORDER BY n.ngay_nop DESC";
                                $stmt_sub = $conn->prepare($sql_sub);
                                $stmt_sub->bind_param("i", $bt['id']);
                                $stmt_sub->execute();
                                $res_sub = $stmt_sub->get_result();
                                while ($sub = $res_sub->fetch_assoc()):
                                ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($sub['hoten']) ?></strong></td>
                                        <td><?= date('H:i d/m/Y', strtotime($sub['ngay_nop'])) ?></td>
                                        <td>
                                            <?php if (!empty($sub['file_nop'])): ?>
                                                <a href="/web/uploads/<?= htmlspecialchars($sub['file_nop']) ?>" target="_blank" class="link-view-btn" style="color: #0288d1; background: #e0f2fe;">📁 Xem File bài</a>
                                            <?php else: ?>
                                                <span style="color: #94a3b8; font-style: italic;">Không đính kèm file</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($sub['link_nop'])): ?>
                                                <a href="<?= htmlspecialchars($sub['link_nop']) ?>" target="_blank" class="link-view-btn" style="color: #ea580c; background: #fff7ed;">🔗 Mở Link bài</a>
                                            <?php else: ?>
                                                <span style="color: #94a3b8; font-style: italic;">Không đính kèm link</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
    <?php 
        endforeach; 
    endif; 
    ?>
</div>

<script>
function toggleViewNop(idBaitap) {
    const box = document.getElementById('nop-box-' + idBaitap);
    if (box.style.display === 'none' || box.style.display === '') {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
    }
}

function updatePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    
    const newTags = preview.querySelectorAll('.preview-tag.new-file');
    newTags.forEach(tag => tag.remove());

    Array.from(input.files).forEach((file, index) => {
        const tag = document.createElement('span');
        tag.className = 'preview-tag new-file';
        tag.style.cssText = "background:#e3faf2; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600; display:inline-flex; align-items:center; gap:4px; border:1px solid #c6f6d5; color:#2f855a;";
        tag.innerHTML = `
            📄 ${(file.name.length > 15) ? file.name.substring(0, 12) + '...' : file.name} (Mới)
        `;
        preview.appendChild(tag);
    });
}
</script>