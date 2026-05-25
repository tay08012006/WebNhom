<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.name', 'GV_SESSION');
    session_start();
}

$ma_lop  = $_GET['malop']   ?? '';
$id_edit = $_GET['id_edit'] ?? '';

$ten_gv    = $_SESSION['ho_ten'] ?? $_SESSION['hoten'] ?? $_SESSION['user_name'] ?? 'Giáo Viên';
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
    .btn-upload-logo { display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px; background: #f0f2f5; border-radius: 50%; cursor: pointer; transition: 0.3s; color: #546e7a; }
    .btn-upload-logo:hover { background: #e1f5fe; color: #0288d1; }
    .preview-files { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
    .preview-tag { display: inline-flex; align-items: center; gap: 5px; background: #f1f8e9; color: #2e7d32; padding: 4px 10px; border-radius: 15px; font-size: 12px; border: 1px solid #c5e1a5; }
    .preview-tag.old-file { background: #e3f2fd; color: #0288d1; border-color: #bbdefb; }
    .remove-file-btn { color: #ff5252; font-weight: bold; cursor: pointer; text-decoration: none; padding-left: 5px; border-left: 1px solid rgba(0,0,0,0.1); margin-left: 3px; }
    .deadline-red { color: #ff5252; font-weight: 800; }
    .edited-text { font-size: 12px; color: #0288d1; font-style: italic; margin-left: 8px; font-weight: 600; }

    /* ── Dropdown 3 chấm ─────────────────────────────────── */
    .dropdown-container { position: absolute; top: 15px; right: 20px; display: inline-block; }
    .dropdown-btn {
        background: none; border: none; font-size: 22px; cursor: pointer; color: #718096;
        width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center;
        justify-content: center; transition: 0.2s; outline: none; font-weight: bold;
    }
    .dropdown-btn:hover { background: #f1f5f9; color: #2d3748; }
    .dropdown-content {
        display: none; position: absolute; right: 0; top: calc(100% + 4px);
        background: #fff; min-width: 200px; box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        border-radius: 10px; z-index: 200; border: 1px solid #edf2f7; overflow: hidden;
    }
    .dropdown-content.show { display: block; animation: fadeInDD 0.18s ease; }
    @keyframes fadeInDD { from { opacity:0; transform:translateY(-4px); } to { opacity:1; transform:translateY(0); } }
    .dropdown-content a {
        display: flex; align-items: center; gap: 9px; padding: 11px 16px;
        font-size: 13px; font-weight: 600; color: #4a5568; text-decoration: none; transition: 0.15s;
    }
    .dropdown-content a:hover { background: #f8fafc; color: #0288d1; }
    .dropdown-content a.danger { color: #e53e3e; border-top: 1px solid #f0f0f0; }
    .dropdown-content a.danger:hover { background: #fff5f5; color: #c53030; }

    /* ── Quiz dropdown (giữ nguyên) ─────────────────────── */
    .quiz-dropdown-btn {
        background: none; border: none; font-size: 24px; cursor: pointer; color: #718096;
        padding: 0 10px; border-radius: 50%; transition: all 0.2s; outline: none; line-height: 1; font-weight: bold;
    }
    .quiz-dropdown-btn:hover { background: #f1f5f9; color: #2d3748; }
    .quiz-dropdown-content {
        display: none; position: absolute; right: 0; top: 100%; margin-top: 5px; background-color: #ffffff;
        min-width: 180px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); border-radius: 8px;
        z-index: 100; border: 1px solid #edf2f7; overflow: hidden;
    }
    .quiz-dropdown-content.show { display: block; animation: fadeInDD 0.2s ease; }
    .quiz-dropdown-content a {
        color: #4a5568; padding: 12px 16px; text-decoration: none; display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 600; transition: background 0.2s;
    }
    .quiz-dropdown-content a:hover { background-color: #f8fafc; color: #0288d1; }
    .quiz-dropdown-content a.delete-btn { color: #e53e3e; border-top: 1px solid #edf2f7; }
    .quiz-dropdown-content a.delete-btn:hover { background-color: #fff5f5; color: #c53030; }

    /* ── Modal danh sách nộp bài ────────────────────────── */
    .modal-overlay {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45);
        z-index: 1000; align-items: center; justify-content: center;
    }
    .modal-overlay.show { display: flex; }
    .modal-box {
        background: #fff; border-radius: 16px; width: 90%; max-width: 620px;
        max-height: 82vh; display: flex; flex-direction: column;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2); animation: modalIn 0.22s ease;
    }
    @keyframes modalIn { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }
    .modal-header {
        padding: 20px 24px 16px; border-bottom: 1px solid #edf2f5;
        display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
    }
    .modal-header h3 { margin: 0; font-size: 16px; color: #263238; font-weight: 800; }
    .modal-close {
        background: #f1f5f9; border: none; border-radius: 50%; width: 32px; height: 32px;
        font-size: 18px; cursor: pointer; color: #64748b; display: flex; align-items: center; justify-content: center;
        transition: 0.2s;
    }
    .modal-close:hover { background: #fee2e2; color: #dc2626; }
    .modal-body { padding: 20px 24px; overflow-y: auto; flex: 1; }
    .nop-row {
        display: flex; align-items: center; gap: 14px; padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .nop-row:last-child { border-bottom: none; }
    .nop-avatar {
        width: 38px; height: 38px; border-radius: 50%; object-fit: cover;
        border: 2px solid #e1f5fe; flex-shrink: 0;
    }
    .nop-info { flex: 1; min-width: 0; }
    .nop-name { font-weight: 700; font-size: 14px; color: #263238; }
    .nop-time { font-size: 12px; color: #78909c; margin-top: 2px; }
    .badge-som {
        background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9;
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap;
    }
    .badge-tre {
        background: #fff3e0; color: #e65100; border: 1px solid #ffe0b2;
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap;
    }
    .badge-dung {
        background: #e3f2fd; color: #0277bd; border: 1px solid #bbdefb;
        padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; white-space: nowrap;
    }
    .nop-file-link {
        font-size: 12px; color: #0288d1; text-decoration: none; display: inline-flex;
        align-items: center; gap: 4px; margin-top: 3px;
    }
    .nop-file-link:hover { text-decoration: underline; }
    .empty-nop {
        text-align: center; color: #90a4ae; font-size: 14px; padding: 30px 0; font-weight: 600;
    }
    .modal-stats {
        background: #f8fafc; border-radius: 10px; padding: 12px 16px; margin-bottom: 16px;
        display: flex; gap: 20px; font-size: 13px; flex-wrap: wrap;
    }
    .stat-item { display: flex; align-items: center; gap: 6px; font-weight: 700; }
</style>

<!-- Nút tạo bài tập mới + tạo trắc nghiệm -->
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; background:white; padding:15px 25px; border-radius:12px; border:1px solid #edf2f5; box-shadow:0 2px 10px rgba(0,0,0,0.02);">
    <div style="font-weight:700; color:#37474f; font-size:15px;">Bạn muốn giao thêm nội dung?</div>
    <a href="tracnghiem/taotracnghiem.php?malop=<?= urlencode($ma_lop) ?>" style="padding:8px 18px; background:#28a745; color:white; border-radius:20px; font-weight:bold; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; font-size:13px; box-shadow:0 3px 8px rgba(40,167,69,0.25); transition:0.2s;">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        Tạo Đề Trắc Nghiệm
    </a>
</div>

<!-- Form tạo bài tập mới -->
<div class="bt-card" id="bt-form-add">
    <h3 style="margin-top:0; color:#37474f; font-size:18px;">Tạo bài tập mới</h3>
    <form action="xulybaitap.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add_bt">
        <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
        <input type="text" name="tieu_de" class="form-control" placeholder="Tiêu đề bài tập..." required>
        <textarea name="noi_dung" class="form-control" style="min-height:80px; resize:none;" placeholder="Hướng dẫn chi tiết..."></textarea>
        <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:15px;">
            <div style="flex:1; min-width:200px;">
                <label style="font-size:13px; font-weight:bold; color:#78909c;">Hạn nộp bài:</label>
                <input type="datetime-local" name="deadline" class="form-control" style="margin-bottom:0;" required>
            </div>
            <div style="display:flex; align-items:center; gap:15px;">
                <label class="btn-upload-logo" title="Đính kèm tài liệu">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                    <input type="file" name="bt_files[]" id="bt_input_add" multiple style="display:none;" onchange="updatePreview('bt_input_add','preview-add')">
                </label>
                <button type="submit" style="padding:10px 30px; background:#0288d1; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">Giao bài</button>
            </div>
        </div>
        <div id="preview-add" class="preview-files"></div>
    </form>
</div>

<!-- ── Danh sách bài tập ───────────────────────────────── -->
<div id="assignment-list">
    <div style="margin-bottom:15px;">
        <h4 style="color:#546e7a; font-size:14px; text-transform:uppercase; font-weight:bold; letter-spacing:0.5px;">Bài tập về nhà</h4>
    </div>

    <?php
    /* ── Lấy danh sách bài tập ── */
    $ds_bt = [];
    if (isset($conn)) {
        $stmt = $conn->prepare("SELECT b.* FROM bai_tap b INNER JOIN classes c ON b.class_id = c.id WHERE c.ma_lop = ? ORDER BY b.id DESC");
        $stmt->bind_param("s", $ma_lop);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $row['files']     = !empty($row['file_dinh_kem']) ? explode(',', $row['file_dinh_kem']) : [];
            $row['ngay_dang'] = date('H:i d/m/Y', strtotime($row['ngay_tao']));
            $row['deadline']  = $row['han_nop'];
            $ds_bt[] = $row;
        }
    }

    /* ── Lấy danh sách nộp bài cho từng bài tập ── */
    $nop_bai_map = [];  // [bai_tap_id => [ [ten, ngay_nop, file, ...], ... ]]
    if (isset($conn) && count($ds_bt) > 0) {
        $bt_ids = implode(',', array_map('intval', array_column($ds_bt, 'id')));
        $stmt_nb = $conn->query(
            "SELECT n.bai_tap_id, n.ngay_nop, n.file_nop, n.link_nop,
                    u.hoten, u.avatar
             FROM nop_bai n
             INNER JOIN users u ON n.student_id = u.id
             WHERE n.bai_tap_id IN ($bt_ids)
             ORDER BY n.ngay_nop ASC"
        );
        if ($stmt_nb) {
            while ($r = $stmt_nb->fetch_assoc()) {
                $nop_bai_map[$r['bai_tap_id']][] = $r;
            }
        }
    }

    if (count($ds_bt) > 0):
        foreach ($ds_bt as $bt):
            $bt_id      = $bt['id'];
            $submissions = $nop_bai_map[$bt_id] ?? [];
            $cnt         = count($submissions);
            $is_late_now = !empty($bt['deadline']) && strtotime(date('Y-m-d H:i')) > strtotime($bt['deadline']);

            if ($bt_id == $id_edit): /* ── CHẾ ĐỘ SỬA ── */
    ?>
        <div class="bt-card" id="bt-<?= $bt_id ?>" style="border:2px solid #0288d1; background:#f9feff;">
            <h3 style="margin-top:0; color:#0288d1; font-size:18px;">✏️ Chỉnh sửa bài tập</h3>
            <form action="xulybaitap.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_bt">
                <input type="hidden" name="id_update" value="<?= $bt_id ?>">
                <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop) ?>">
                <input type="text" name="tieu_de" class="form-control" placeholder="Tiêu đề bài tập..." required value="<?= htmlspecialchars($bt['tieu_de']) ?>">
                <textarea name="noi_dung" class="form-control" style="min-height:80px; resize:none;" placeholder="Hướng dẫn chi tiết..."><?= htmlspecialchars($bt['noi_dung']) ?></textarea>
                <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:15px;">
                    <div style="flex:1; min-width:200px;">
                        <label style="font-size:13px; font-weight:bold; color:#78909c;">Hạn nộp bài:</label>
                        <input type="datetime-local" name="deadline" class="form-control" style="margin-bottom:0;" required value="<?= !empty($bt['deadline']) ? date('Y-m-d\TH:i', strtotime($bt['deadline'])) : '' ?>">
                    </div>
                    <div style="display:flex; align-items:center; gap:15px;">
                        <label class="btn-upload-logo" title="Thêm file mới">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                            <input type="file" name="bt_files[]" id="bt_input_<?= $bt_id ?>" multiple style="display:none;" onchange="updatePreview('bt_input_<?= $bt_id ?>','preview-<?= $bt_id ?>')">
                        </label>
                        <button type="submit" style="padding:10px 30px; background:#0288d1; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">Lưu thay đổi</button>
                        <a href="phonghoc.php?malop=<?= $ma_lop ?>&tab=bai-tap#bt-<?= $bt_id ?>" style="font-size:13px; color:#90a4ae; text-decoration:none;">Hủy</a>
                    </div>
                </div>
                <div id="preview-<?= $bt_id ?>" class="preview-files">
                    <?php if (!empty($bt['files'])): ?>
                        <?php foreach ($bt['files'] as $f): $file_id = md5($f); ?>
                            <span class="preview-tag old-file" id="file-<?= $file_id ?>">
                                📄 <?= (strlen($f) > 20) ? substr($f, 0, 15) . '...' : $f ?>
                                <input type="hidden" name="old_files[]" value="<?= htmlspecialchars($f) ?>">
                                <a onclick="document.getElementById('file-<?= $file_id ?>').remove()" class="remove-file-btn" title="Xóa file này">&times;</a>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>

    <?php else: /* ── CHẾ ĐỘ XEM ── */ ?>

        <div class="bt-card" id="bt-<?= $bt_id ?>">

            <!-- ── Menu 3 chấm ── -->
            <div class="dropdown-container">
                <button class="dropdown-btn" onclick="toggleDD(event,'ddBT_<?= $bt_id ?>')" title="Tuỳ chọn">⋮</button>
                <div id="ddBT_<?= $bt_id ?>" class="dropdown-content">
                    <a href="?malop=<?= $ma_lop ?>&tab=bai-tap&id_edit=<?= $bt_id ?>#bt-<?= $bt_id ?>">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4z"/></svg>
                        Sửa bài tập
                    </a>
                    <a href="#" onclick="openNopModal(<?= $bt_id ?>); return false;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Xem danh sách nộp
                        <?php if ($cnt > 0): ?>
                            <span style="margin-left:auto; background:#0288d1; color:#fff; border-radius:12px; padding:1px 8px; font-size:11px; font-weight:800;"><?= $cnt ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="xulybaitap.php?action=delete_bt&id=<?= $bt_id ?>&malop=<?= $ma_lop ?>" class="danger" onclick="return confirm('Xóa bài tập này? Toàn bộ bài đã nộp sẽ bị xóa.')">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                        Xóa bài tập
                    </a>
                </div>
            </div>

            <!-- Avatar + tên GV -->
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                <img src="<?= htmlspecialchars($gv_avatar) ?>" alt="Avatar" style="width:38px; height:38px; border-radius:50%; object-fit:cover; box-shadow:0 2px 4px rgba(2,136,209,0.2); border:1.5px solid #0288d1;">
                <div>
                    <div style="font-weight:700; color:#263238; font-size:14px;"><?= htmlspecialchars($ten_gv) ?></div>
                    <div style="font-size:11px; color:#78909c; margin-top:2px;">
                        Giao lúc: <?= $bt['ngay_dang'] ?>
                        <?php if (isset($bt['chinh_sua']) && $bt['chinh_sua'] == 1): ?>
                            <span class="edited-text">• đã chỉnh sửa</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <h4 style="margin:0 0 10px 0; color:#0277bd; padding-right:50px;"><?= htmlspecialchars($bt['tieu_de']) ?></h4>
            <p style="font-size:14px; line-height:1.6; color:#546e7a; margin-bottom:15px;"><?= nl2br(htmlspecialchars($bt['noi_dung'])) ?></p>

            <?php if (!empty($bt['deadline'])): ?>
                <div style="font-size:14px; margin-bottom:10px; display:flex; align-items:center; gap:6px;">
                    <span>Hạn nộp:</span>
                    <span class="<?= $is_late_now ? 'deadline-red' : '' ?>"><?= date('H:i d/m/Y', strtotime($bt['deadline'])) ?></span>
                </div>
            <?php endif; ?>

            <?php if (!empty($bt['files'])): ?>
                <div style="display:flex; flex-wrap:wrap; gap:8px; padding-top:10px; border-top:1px solid #f1f1f1;">
                    <?php foreach ($bt['files'] as $f): ?>
                        <a href="../uploads/<?= htmlspecialchars($f) ?>" target="_blank" style="text-decoration:none; background:#f8fafb; padding:6px 12px; border-radius:6px; border:1px solid #eceff1; font-size:12px; color:#0288d1; font-weight:bold;">
                            📄 <?= (strlen($f) > 15) ? substr($f, 0, 12) . '...' : htmlspecialchars($f) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>

        <!-- ── Modal danh sách nộp bài (ẩn, mở khi click) ── -->
        <div class="modal-overlay" id="modal_nop_<?= $bt_id ?>" onclick="if(event.target===this)closeNopModal(<?= $bt_id ?>)">
            <div class="modal-box">
                <div class="modal-header">
                    <h3>📋 Danh sách nộp bài — <?= htmlspecialchars($bt['tieu_de']) ?></h3>
                    <button class="modal-close" onclick="closeNopModal(<?= $bt_id ?>)">✕</button>
                </div>
                <div class="modal-body">
                    <?php if ($cnt > 0):
                        // Thống kê sớm / trễ
                        $cnt_som = 0; $cnt_tre = 0;
                        foreach ($submissions as $s) {
                            if (!empty($bt['deadline'])) {
                                if (strtotime($s['ngay_nop']) <= strtotime($bt['deadline'])) $cnt_som++;
                                else $cnt_tre++;
                            }
                        }
                    ?>
                    <div class="modal-stats">
                        <div class="stat-item">
                            <span style="color:#546e7a;">Tổng:</span>
                            <span style="color:#0288d1;"><?= $cnt ?> học sinh</span>
                        </div>
                        <?php if (!empty($bt['deadline'])): ?>
                        <div class="stat-item">
                            <span style="background:#e8f5e9;color:#2e7d32;padding:2px 8px;border-radius:12px;">✅ Đúng/Sớm: <?= $cnt_som ?></span>
                        </div>
                        <div class="stat-item">
                            <span style="background:#fff3e0;color:#e65100;padding:2px 8px;border-radius:12px;">⏰ Trễ: <?= $cnt_tre ?></span>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php foreach ($submissions as $idx => $s):
                        $avatar_hs = !empty($s['avatar'])
                            ? (str_starts_with($s['avatar'],'http') ? $s['avatar'] : '../uploads/'.$s['avatar'])
                            : 'https://ui-avatars.com/api/?name='.urlencode($s['hoten']).'&background=0288d1&color=fff&bold=true';

                        // Xác định sớm / trễ / không có deadline
                        $nop_ts     = strtotime($s['ngay_nop']);
                        $deadline_ts = !empty($bt['deadline']) ? strtotime($bt['deadline']) : null;
                        $diff_sec   = $deadline_ts ? ($deadline_ts - $nop_ts) : null;

                        if ($deadline_ts === null) {
                            $badge_html = '<span class="badge-dung">Không có hạn</span>';
                        } elseif ($nop_ts <= $deadline_ts) {
                            // Nộp sớm — tính còn bao lâu trước deadline
                            $diff_min = abs(round($diff_sec / 60));
                            if ($diff_min < 60)       $diff_txt = $diff_min . ' phút trước HN';
                            elseif ($diff_min < 1440) $diff_txt = round($diff_min/60) . ' giờ trước HN';
                            else                      $diff_txt = round($diff_min/1440) . ' ngày trước HN';
                            $badge_html = '<span class="badge-som">✅ Sớm — '.$diff_txt.'</span>';
                        } else {
                            // Nộp trễ — tính trễ bao lâu
                            $over_min = abs(round(($nop_ts - $deadline_ts) / 60));
                            if ($over_min < 60)       $diff_txt = $over_min . ' phút';
                            elseif ($over_min < 1440) $diff_txt = round($over_min/60) . ' giờ';
                            else                      $diff_txt = round($over_min/1440) . ' ngày';
                            $badge_html = '<span class="badge-tre">⏰ Trễ '.$diff_txt.'</span>';
                        }

                        $file_nop = $s['file_nop'] ?? '';
                    ?>
                    <div class="nop-row">
                        <img src="<?= htmlspecialchars($avatar_hs) ?>" class="nop-avatar" alt="Avatar">
                        <div class="nop-info">
                            <div class="nop-name"><?= htmlspecialchars($s['hoten']) ?></div>
                            <div class="nop-time">Nộp lúc: <?= date('H:i — d/m/Y', strtotime($s['ngay_nop'])) ?></div>
                            <?php if (!empty($file_nop)): ?>
                                <a href="../uploads/baitap/<?= htmlspecialchars($file_nop) ?>" target="_blank" class="nop-file-link">
                                    📎 <?= (strlen($file_nop) > 25) ? substr($file_nop, 0, 22) . '...' : htmlspecialchars($file_nop) ?>
                                </a>
                            <?php elseif (!empty($s['link_nop'])): ?>
                                <a href="<?= htmlspecialchars($s['link_nop']) ?>" target="_blank" class="nop-file-link">
                                    🔗 Link bài nộp
                                </a>
                            <?php endif; ?>
                        </div>
                        <?= $badge_html ?>
                    </div>
                    <?php endforeach; ?>

                    <?php else: ?>
                    <div class="empty-nop">
                        📭 Chưa có học sinh nào nộp bài
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    <?php
            endif;
        endforeach;
    else:
    ?>
        <p style="font-size:13px; color:#90a4ae; text-align:center; background:white; padding:20px; border-radius:12px; border:1px dashed #cfd8dc; margin-bottom:20px;">Chưa có bài tập nào được giao.</p>
    <?php endif; ?>
</div>

<!-- ── Danh sách bài thi trắc nghiệm ─────────────────── -->
<div id="quiz-list" style="margin-top:30px;">
    <div style="margin-bottom:15px;">
        <h4 style="color:#2e7d32; font-size:14px; text-transform:uppercase; font-weight:bold; letter-spacing:0.5px;">Bài thi Trắc nghiệm tự động</h4>
    </div>
    <?php
    $ds_quizzes = [];
    if (isset($conn)) {
        $stmt_qz = $conn->prepare("SELECT q.* FROM quizzes q INNER JOIN classes c ON q.class_id = c.id WHERE c.ma_lop = ? ORDER BY q.id DESC");
        $stmt_qz->bind_param("s", $ma_lop);
        $stmt_qz->execute();
        $res_qz = $stmt_qz->get_result();
        while ($row_qz = $res_qz->fetch_assoc()) $ds_quizzes[] = $row_qz;
    }

    if (count($ds_quizzes) > 0):
        foreach ($ds_quizzes as $qz):
    ?>
        <div class="bt-card" id="quiz-<?= $qz['id'] ?>" style="border-left:4px solid #28a745;">
            <div class="dropdown-container">
                <button class="quiz-dropdown-btn" onclick="toggleDD(event,'quizMenuBT_<?= $qz['id'] ?>')">⋮</button>
                <div id="quizMenuBT_<?= $qz['id'] ?>" class="quiz-dropdown-content">
                    <a href="tracnghiem/xem_tracnghiem.php?quiz_id=<?= $qz['id'] ?>&malop=<?= urlencode($ma_lop) ?>">👁 Xem chi tiết</a>
                    <a href="tracnghiem/danhsach_thi.php?quiz_id=<?= $qz['id'] ?>&malop=<?= urlencode($ma_lop) ?>">📋 Xem danh sách thi</a>
                    <a href="tracnghiem/xoa_tracnghiem.php?quiz_id=<?= $qz['id'] ?>&malop=<?= urlencode($ma_lop) ?>" class="delete-btn" onclick="return confirm('Xóa đề trắc nghiệm này? Toàn bộ câu hỏi liên quan sẽ bị xóa!');">🗑 Xóa bài thi</a>
                </div>
            </div>
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                <div style="background:rgba(40,167,69,0.1); color:#28a745; width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; border:1.5px solid #28a745;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                </div>
                <div>
                    <div style="font-weight:700; color:#263238; font-size:14px;">Đề trắc nghiệm trực tuyến</div>
                    <div style="font-size:11px; color:#78909c; margin-top:2px;">
                        Tạo lúc: <?= date('H:i d/m/Y', strtotime($qz['created_at'])) ?>
                        <span style="margin-left:10px; background:#fff3e0; color:#e65100; font-size:11px; padding:2px 8px; border-radius:12px; font-weight:bold; display:inline-flex; align-items:center; gap:3px;">
                            ⏱ <?= $qz['duration_minutes'] ?? 15 ?> phút
                        </span>
                    </div>
                </div>
            </div>
            <h4 style="margin:0 0 10px 0; color:#2e7d32; padding-right:50px;"><?= htmlspecialchars($qz['title']) ?></h4>
        </div>
    <?php
        endforeach;
    else:
    ?>
        <p style="font-size:13px; color:#90a4ae; text-align:center; background:white; padding:20px; border-radius:12px; border:1px dashed #cfd8dc;">Chưa có đề thi trắc nghiệm trực tuyến nào được tạo.</p>
    <?php endif; ?>
</div>

<script>
/* ── Dropdown 3 chấm ── */
function toggleDD(event, id) {
    event.stopPropagation();
    var el = document.getElementById(id);
    if (!el) return;
    var wasOpen = el.classList.contains('show');
    // Đóng tất cả dropdown
    document.querySelectorAll('.dropdown-content, .quiz-dropdown-content').forEach(function(d) {
        d.classList.remove('show');
    });
    if (!wasOpen) el.classList.add('show');
}
window.addEventListener('click', function() {
    document.querySelectorAll('.dropdown-content, .quiz-dropdown-content').forEach(function(d) {
        d.classList.remove('show');
    });
});

/* ── Modal danh sách nộp ── */
function openNopModal(btId) {
    // Đóng dropdown trước
    document.querySelectorAll('.dropdown-content').forEach(function(d) { d.classList.remove('show'); });
    var m = document.getElementById('modal_nop_' + btId);
    if (m) { m.classList.add('show'); document.body.style.overflow = 'hidden'; }
}
function closeNopModal(btId) {
    var m = document.getElementById('modal_nop_' + btId);
    if (m) { m.classList.remove('show'); document.body.style.overflow = ''; }
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.show').forEach(function(m) {
            m.classList.remove('show');
        });
        document.body.style.overflow = '';
    }
});

/* ── Preview file đính kèm ── */
function updatePreview(inputId, previewId) {
    var input   = document.getElementById(inputId);
    var preview = document.getElementById(previewId);
    preview.querySelectorAll('.preview-tag.new-file').forEach(function(t) { t.remove(); });
    Array.from(input.files).forEach(function(file, index) {
        var tag = document.createElement('span');
        tag.className = 'preview-tag new-file';
        var name = file.name.length > 15 ? file.name.substring(0, 12) + '...' : file.name;
        tag.innerHTML = '📄 ' + name + ' (Mới) <a onclick="removeNewFile(\'' + inputId + '\',\'' + previewId + '\',' + index + ')" class="remove-file-btn" title="Hủy chọn">&times;</a>';
        preview.appendChild(tag);
    });
}
function removeNewFile(inputId, previewId, indexToRemove) {
    var input = document.getElementById(inputId);
    var dt = new DataTransfer();
    for (var i = 0; i < input.files.length; i++) {
        if (i !== indexToRemove) dt.items.add(input.files[i]);
    }
    input.files = dt.files;
    updatePreview(inputId, previewId);
}
</script>