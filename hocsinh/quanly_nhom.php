<?php
// Bắt lỗi và khởi tạo biến chứa thông báo lỗi
$msg_error = '';

// Đếm số lượng yêu cầu xin vào nhóm đang chờ duyệt để thông báo cho nhóm trưởng
$so_yeu_cau_cho_duyet = 0;
if (isset($id_lop) && isset($id_hocsinh)) {
    $stmt_count_pending = $conn->prepare("
        SELECT COUNT(tv.id) as total 
        FROM thanh_vien_nhom tv 
        JOIN nhom_hoc n ON tv.nhom_id = n.id 
        WHERE n.class_id = ? AND n.id_truong_nhom = ? AND tv.trang_thai = 0
    ");
    $stmt_count_pending->bind_param("ii", $id_lop, $id_hocsinh);
    $stmt_count_pending->execute();
    $res_pending = $stmt_count_pending->get_result()->fetch_assoc();
    if ($res_pending) {
        $so_yeu_cau_cho_duyet = $res_pending['total'];
    }
}

// Xử lý hành động đại diện nhóm nộp bài tập
if (isset($_POST['action']) && $_POST['action'] == 'nop_bai_nhom') {
    $bai_tap_id = intval($_POST['bai_tap_id']);
    $nhom_id = intval($_POST['nhom_id']);
    $link_nop = trim($_POST['link_nop']);
    $file_name = '';

    if (isset($_FILES['file_nop']) && $_FILES['file_nop']['error'] == 0) {
        $file_name = time() . '_' . basename($_FILES['file_nop']['name']);
        move_uploaded_file($_FILES['file_nop']['tmp_name'], '../uploads/' . $file_name);
    }

    if (!empty($file_name) || !empty($link_nop)) {
        $stmt_nop = $conn->prepare("INSERT INTO nop_bai (bai_tap_id, student_id, nhom_id, file_nop, link_nop) VALUES (?, ?, ?, ?, ?)");
        $stmt_nop->bind_param("iiiss", $bai_tap_id, $id_hocsinh, $nhom_id, $file_name, $link_nop);
        if ($stmt_nop->execute()) {
            echo "<script>alert('Đã đại diện nhóm nộp bài thành công!'); window.location.href='?id=$id_lop&tab=nhom';</script>";
            exit();
        } else { 
            $msg_error = "Lỗi khi lưu dữ liệu nộp bài!"; 
        }
    } else { 
        $msg_error = "Vui lòng chọn File hoặc nhập Link bài làm!"; 
    }
}

// Xử lý tạo nhóm học tập mới
if (isset($_POST['action']) && $_POST['action'] == 'tao_nhom') {
    $ten_nhom = trim($_POST['ten_nhom']);
    if (!empty($ten_nhom)) {
        $stmt_chk = $conn->prepare("SELECT tv.id FROM thanh_vien_nhom tv JOIN nhom_hoc n ON tv.nhom_id = n.id WHERE n.class_id = ? AND tv.student_id = ?");
        $stmt_chk->bind_param("ii", $id_lop, $id_hocsinh);
        $stmt_chk->execute();
        if ($stmt_chk->get_result()->num_rows > 0) {
            $msg_error = "Bạn đang ở trong một nhóm hoặc đang có yêu cầu chờ duyệt!";
        } else {
            $conn->begin_transaction();
            try {
                $stmt_ins = $conn->prepare("INSERT INTO nhom_hoc (ten_nhom, class_id, id_truong_nhom) VALUES (?, ?, ?)");
                $stmt_ins->bind_param("sii", $ten_nhom, $id_lop, $id_hocsinh);
                $stmt_ins->execute();
                $new_nhom_id = $conn->insert_id;

                // Tự động gán người tạo làm nhóm trưởng và đánh dấu là thành viên chính thức
                $stmt_tv = $conn->prepare("INSERT INTO thanh_vien_nhom (nhom_id, student_id, vai_tro, trang_thai) VALUES (?, ?, 'nhom_truong', 1)");
                $stmt_tv->bind_param("ii", $new_nhom_id, $id_hocsinh);
                $stmt_tv->execute();

                $conn->commit();
                echo "<script>window.location.href='?id=$id_lop&tab=nhom';</script>"; exit();
            } catch (Exception $e) { 
                $conn->rollback(); 
                $msg_error = "Lỗi khi tạo nhóm!"; 
            }
        }
    } else { 
        $msg_error = "Vui lòng nhập tên nhóm!"; 
    }
}

// Xử lý gửi yêu cầu xin gia nhập nhóm
if (isset($_POST['action']) && $_POST['action'] == 'gia_nhap') {
    $nhom_id = intval($_POST['nhom_id']);
    $stmt_chk = $conn->prepare("SELECT tv.id FROM thanh_vien_nhom tv JOIN nhom_hoc n ON tv.nhom_id = n.id WHERE n.class_id = ? AND tv.student_id = ?");
    $stmt_chk->bind_param("ii", $id_lop, $id_hocsinh);
    $stmt_chk->execute();
    if ($stmt_chk->get_result()->num_rows > 0) {
        $msg_error = "Bạn đã có nhóm hoặc đang gửi yêu cầu chờ duyệt ở nhóm khác!";
    } else {
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM thanh_vien_nhom WHERE nhom_id = ? AND trang_thai = 1");
        $stmt_count->bind_param("i", $nhom_id);
        $stmt_count->execute();
        if ($stmt_count->get_result()->fetch_assoc()['total'] >= 5) {
            $msg_error = "Nhóm này đã đủ thành viên chính thức!";
        } else {
            $stmt_join = $conn->prepare("INSERT INTO thanh_vien_nhom (nhom_id, student_id, vai_tro, trang_thai) VALUES (?, ?, 'thanh_vien', 0)");
            $stmt_join->bind_param("ii", $nhom_id, $id_hocsinh);
            if ($stmt_join->execute()) { 
                echo "<script>alert('Đã gửi yêu cầu gia nhập! Vui lòng chờ nhóm trưởng chấp nhận.'); window.location.href='?id=$id_lop&tab=nhom';</script>"; 
                exit(); 
            }
        }
    }
}

// Xử lý phê duyệt thành viên vào nhóm (chỉ dành cho trưởng nhóm)
if (isset($_POST['action']) && $_POST['action'] == 'phe_duyet') {
    $tv_id = intval($_POST['tv_id']);
    $nhom_id = intval($_POST['nhom_id']);
    
    $stmt_leader = $conn->prepare("SELECT id FROM nhom_hoc WHERE id = ? AND id_truong_nhom = ?");
    $stmt_leader->bind_param("ii", $nhom_id, $id_hocsinh);
    $stmt_leader->execute();
    if ($stmt_leader->get_result()->num_rows > 0) {
        $stmt_count = $conn->prepare("SELECT COUNT(*) as total FROM thanh_vien_nhom WHERE nhom_id = ? AND trang_thai = 1");
        $stmt_count->bind_param("i", $nhom_id);
        $stmt_count->execute();
        if ($stmt_count->get_result()->fetch_assoc()['total'] >= 5) {
            $msg_error = "Nhóm đã đủ 5 thành viên chính thức, không thể duyệt thêm!";
        } else {
            $stmt_app = $conn->prepare("UPDATE thanh_vien_nhom SET trang_thai = 1 WHERE id = ? AND nhom_id = ?");
            $stmt_app->bind_param("ii", $tv_id, $nhom_id);
            if ($stmt_app->execute()) { echo "<script>window.location.href='?id=$id_lop&tab=nhom';</script>"; exit(); }
        }
    }
}

// Xử lý từ chối đơn xin vào nhóm (chỉ dành cho trưởng nhóm)
if (isset($_POST['action']) && $_POST['action'] == 'tu_choi') {
    $tv_id = intval($_POST['tv_id']);
    $nhom_id = intval($_POST['nhom_id']);
    $stmt_leader = $conn->prepare("SELECT id FROM nhom_hoc WHERE id = ? AND id_truong_nhom = ?");
    $stmt_leader->bind_param("ii", $nhom_id, $id_hocsinh);
    $stmt_leader->execute();
    if ($stmt_leader->get_result()->num_rows > 0) {
        $stmt_rej = $conn->prepare("DELETE FROM thanh_vien_nhom WHERE id = ? AND nhom_id = ? AND trang_thai = 0");
        $stmt_rej->bind_param("ii", $tv_id, $nhom_id);
        if ($stmt_rej->execute()) { echo "<script>window.location.href='?id=$id_lop&tab=nhom';</script>"; exit(); }
    }
}

// Xử lý xóa thành viên khỏi nhóm (chỉ dành cho trưởng nhóm)
if (isset($_POST['action']) && $_POST['action'] == 'xoa_thanh_vien') {
    $tv_id = intval($_POST['tv_id']);
    $nhom_id = intval($_POST['nhom_id']);
    $stmt_leader = $conn->prepare("SELECT id FROM nhom_hoc WHERE id = ? AND id_truong_nhom = ?");
    $stmt_leader->bind_param("ii", $nhom_id, $id_hocsinh);
    $stmt_leader->execute();
    if ($stmt_leader->get_result()->num_rows > 0) {
        $stmt_del = $conn->prepare("DELETE FROM thanh_vien_nhom WHERE id = ? AND nhom_id = ? AND vai_tro != 'nhom_truong'");
        $stmt_del->bind_param("ii", $tv_id, $nhom_id); 
        if ($stmt_del->execute()) { echo "<script>window.location.href='?id=$id_lop&tab=nhom';</script>"; exit(); }
    }
}

// Xử lý rời nhóm tự do hoặc hủy đơn xin gia nhập
if (isset($_POST['action']) && $_POST['action'] == 'roi_nhom') {
    $nhom_id = intval($_POST['nhom_id']);
    $stmt_role = $conn->prepare("SELECT vai_tro FROM thanh_vien_nhom WHERE nhom_id = ? AND student_id = ?");
    $stmt_role->bind_param("ii", $nhom_id, $id_hocsinh);
    $stmt_role->execute();
    $role_res = $stmt_role->get_result()->fetch_assoc();

    if ($role_res) {
        if ($role_res['vai_tro'] == 'nhom_truong') {
            $conn->begin_transaction();
            try {
                $conn->query("DELETE FROM thanh_vien_nhom WHERE nhom_id = $nhom_id");
                $conn->query("DELETE FROM nhom_hoc WHERE id = $nhom_id");
                $conn->commit();
                echo "<script>window.location.href='?id=$id_lop&tab=nhom';</script>"; exit();
            } catch(Exception $e) { $conn->rollback(); }
        } else {
            $stmt_leave = $conn->prepare("DELETE FROM thanh_vien_nhom WHERE nhom_id = ? AND student_id = ?");
            $stmt_leave->bind_param("ii", $nhom_id, $id_hocsinh);
            if ($stmt_leave->execute()) { echo "<script>window.location.href='?id=$id_lop&tab=nhom';</script>"; exit(); }
        }
    }
}

// Lấy thông tin nhóm hiện tại mà học sinh đang tham gia (nếu có)
$stmt_my_group = $conn->prepare("SELECT n.*, tv.vai_tro, tv.trang_thai FROM thanh_vien_nhom tv JOIN nhom_hoc n ON tv.nhom_id = n.id WHERE n.class_id = ? AND tv.student_id = ?");
$stmt_my_group->bind_param("ii", $id_lop, $id_hocsinh);
$stmt_my_group->execute();
$my_group = $stmt_my_group->get_result()->fetch_assoc();
?>

<style>
    /* CSS cho hộp thông báo lỗi */
    .msg-error { 
        background: #fee2e2; 
        border: 1px solid #fca5a5; 
        color: #991b1b; 
        padding: 12px; 
        border-radius: 8px; 
        margin-bottom: 20px; 
        font-weight: bold; 
        font-size: 14px; 
    }
    
    /* CSS cho khu vực thông tin nhóm của tôi */
    .my-group-box { 
        background: #f8fafc; 
        border: 1px solid #e2e8f0; 
        padding: 25px; 
        border-radius: 12px; 
    }
    .my-group-header { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        border-bottom: 2px solid #cbd5e1; 
        padding-bottom: 15px; 
        margin-bottom: 20px; 
    }
    .my-group-title { 
        color: #1e3a8a; 
        font-size: 20px; 
        font-weight: 800; 
        margin: 0; 
    }
    .my-group-role { 
        font-size: 13px; 
        color: #64748b; 
        margin-top: 4px; 
        margin-bottom: 0; 
    }
    
    /* CSS cho khu vực hiển thị người xin vào nhóm */
    .pending-box { 
        background: #fffbeb; 
        border: 1px solid #fef3c7; 
        padding: 15px; 
        border-radius: 8px; 
        margin-bottom: 20px; 
    }
    .pending-title { 
        color: #b45309; 
        font-size: 14px; 
        font-weight: 700; 
        margin-top: 0; 
        margin-bottom: 10px; 
    }
    .pending-list { 
        display: flex; 
        flex-direction: column; 
        gap: 8px; 
    }
    .pending-item { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        background: white; 
        padding: 10px 15px; 
        border-radius: 6px; 
        border: 1px solid #fde68a; 
    }
    
    /* CSS cho danh sách thành viên nhóm */
    .member-list { 
        display: flex; 
        flex-direction: column; 
        gap: 10px; 
        margin-bottom: 25px; 
    }
    .member-item { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        background: white; 
        padding: 10px 15px; 
        border-radius: 8px; 
        border: 1px solid #e2e8f0; 
    }
    .role-badge { 
        font-size: 12px; 
        font-weight: bold; 
        color: #1e3a8a; 
        background: #e0f2fe; 
        padding: 3px 8px; 
        border-radius: 6px; 
    }
    .badge-you { 
        font-size: 10px; 
        background: #e2e8f0; 
        color: #475569; 
        padding: 1px 5px; 
        border-radius: 4px; 
        margin-left: 5px; 
    }
    
    /* CSS cho khu vực bài tập nhóm */
    .hw-title { 
        color: #1e3a8a; 
        font-size: 17px; 
        font-weight: 800; 
        margin-bottom: 15px; 
    }
    .hw-item { 
        background: white; 
        border: 1px solid #cbd5e1; 
        padding: 15px; 
        border-radius: 8px; 
        margin-bottom: 15px; 
    }
    .hw-name { 
        color: #0f172a; 
        font-size: 16px; 
        margin-bottom: 8px; 
        margin-top: 0; 
    }
    .hw-desc { 
        font-size: 14px; 
        color: #475569; 
        margin-bottom: 10px; 
    }
    .hw-deadline { 
        font-size: 13px; 
        color: #ef4444; 
        font-weight: bold; 
        margin-bottom: 10px; 
    }
    .hw-submitted { 
        background: #ecfdf5; 
        border: 1px solid #a7f3d0; 
        padding: 12px; 
        border-radius: 6px; 
    }
    .form-submit-hw { 
        background: #f8fafc; 
        border: 1px dashed #94a3b8; 
        padding: 15px; 
        border-radius: 8px; 
    }
    
    /* CSS cho trang trạng thái chờ duyệt */
    .waiting-box { 
        background: #fffbeb; 
        border: 1px solid #fde68a; 
        padding: 30px; 
        border-radius: 12px; 
        text-align: center; 
    }
    .waiting-title { 
        color: #b45309; 
        font-size: 18px; 
        margin-top: 10px; 
        margin-bottom: 5px; 
    }
    .waiting-desc { 
        color: #475569; 
        font-size: 14px; 
        margin-bottom: 20px; 
    }
    
    /* CSS cho form tạo nhóm mới */
    .create-group-box { 
        background: #f8fafc; 
        border: 1px dashed #cbd5e1; 
        padding: 20px; 
        border-radius: 12px; 
        margin-bottom: 25px; 
    }
    .create-group-title { 
        color: #1e3a8a; 
        margin-bottom: 12px; 
        font-weight: 700; 
        margin-top: 0; 
    }
    
    /* CSS cho danh sách các nhóm trong lớp */
    .all-groups-title { 
        color: #334155; 
        margin-bottom: 15px; 
        font-weight: 700; 
    }
    .all-groups-list { 
        display: flex; 
        flex-direction: column; 
        gap: 12px; 
    }
    .group-list-item { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        background: white; 
        padding: 16px 20px; 
        border-radius: 12px; 
        border: 1px solid #e2e8f0; 
    }
    .group-list-name { 
        color: #1e293b; 
        font-size: 15px; 
        font-weight: 700; 
        margin: 0; 
    }
    .group-list-info { 
        font-size: 12px; 
        color: #64748b; 
        margin-top: 5px; 
        margin-bottom: 0; 
    }
    
    /* CSS cho các loại nút bấm chung */
    .btn-red { 
        background: #dc2626; 
        color: white; 
        border: none; 
        padding: 9px 15px; 
        border-radius: 8px; 
        font-weight: 700; 
        cursor: pointer; 
        font-size: 13px; 
    }
    .btn-green-sm { 
        background: #10b981; 
        color: white; 
        border: none; 
        padding: 5px 12px; 
        border-radius: 4px; 
        font-weight: bold; 
        cursor: pointer; 
        font-size: 12px; 
    }
    .btn-red-sm { 
        background: #ef4444; 
        color: white; 
        border: none; 
        padding: 5px 12px; 
        border-radius: 4px; 
        font-weight: bold; 
        cursor: pointer; 
        font-size: 12px; 
    }
    .btn-remove { 
        background: #fee2e2; 
        border: 1px solid #fca5a5; 
        color: #dc2626; 
        padding: 4px 10px; 
        border-radius: 5px; 
        cursor: pointer; 
        font-size: 12px; 
        font-weight: bold; 
    }
    .btn-blue { 
        background: #2563eb; 
        color: white; 
        border: none; 
        padding: 10px 15px; 
        border-radius: 6px; 
        font-weight: bold; 
        cursor: pointer; 
    }
    .btn-gray { 
        background: #64748b; 
        color: white; 
        border: none; 
        padding: 8px 16px; 
        border-radius: 6px; 
        font-weight: bold; 
        cursor: pointer; 
        font-size: 13px; 
    }
    .btn-blue-lg { 
        background: #0288d1; 
        color: white; 
        border: none; 
        padding: 10px 20px; 
        border-radius: 8px; 
        font-weight: bold; 
        cursor: pointer; 
    }
    .btn-green { 
        background: #10b981; 
        color: white; 
        border: none; 
        padding: 8px 14px; 
        border-radius: 6px; 
        font-weight: 700; 
        cursor: pointer; 
        font-size: 12px; 
    }
    
    /* CSS tiện ích khác */
    .m-0 { 
        margin: 0; 
    }
    .text-blue { 
        color: #2563eb; 
    }
    .fw-bold-dark { 
        font-weight: bold; 
        color: #1e293b; 
    }
    .flex-gap-8 { 
        display: flex; 
        gap: 8px; 
    }
    .flex-gap-10 { 
        display: flex; 
        gap: 10px; 
    }
    .flex-align-center-gap-10 { 
        display: flex; 
        align-items: center; 
        gap: 10px; 
    }
    .dashed-divider { 
        border: 1px dashed #cbd5e1; 
        margin-bottom: 20px; 
    }
    .input-text { 
        flex: 1; 
        padding: 10px 14px; 
        border: 1px solid #cbd5e1; 
        border-radius: 8px; 
    }
    .badge-full { 
        font-size: 12px; 
        color: #94a3b8; 
        background: #f1f5f9; 
        padding: 6px 12px; 
        border-radius: 6px; 
        font-weight: bold; 
    }
    .empty-list { 
        text-align: center; 
        color: #94a3b8; 
        padding: 30px 0; 
    }
</style>

<div style="padding: 5px 0;">
    <?php if (!empty($msg_error)): ?>
        <div class="msg-error">
            Cảnh báo: <?= htmlspecialchars($msg_error) ?>
        </div>
    <?php endif; ?>

    <?php 
    if ($my_group): 
        $curr_nhom_id = $my_group['id'];
        $is_leader = ($my_group['vai_tro'] == 'nhom_truong');
        
        // Hiển thị giao diện khi học sinh đã có nhóm chính thức
        if ($my_group['trang_thai'] == 1):
            
            // Lấy danh sách thành viên trong nhóm
            $stmt_mems = $conn->prepare("SELECT tv.id as tv_id, tv.vai_tro, u.hoten, u.id as user_id FROM thanh_vien_nhom tv JOIN users u ON tv.student_id = u.id WHERE tv.nhom_id = ? AND tv.trang_thai = 1 ORDER BY (case when tv.vai_tro = 'nhom_truong' then 1 else 2 end) ASC");
            $stmt_mems->bind_param("i", $curr_nhom_id);
            $stmt_mems->execute();
            $members = $stmt_mems->get_result();

            // Nếu là trưởng nhóm, lấy thêm danh sách chờ duyệt
            $pending_members = null;
            if ($is_leader) {
                $stmt_pend = $conn->prepare("SELECT tv.id as tv_id, u.hoten FROM thanh_vien_nhom tv JOIN users u ON tv.student_id = u.id WHERE tv.nhom_id = ? AND tv.trang_thai = 0");
                $stmt_pend->bind_param("i", $curr_nhom_id);
                $stmt_pend->execute();
                $pending_members = $stmt_pend->get_result();
            }
    ?>
        <div class="my-group-box">
            <div class="my-group-header">
                <div>
                    <h3 class="my-group-title">Nhóm: <span class="text-blue"><?= htmlspecialchars($my_group['ten_nhom']) ?></span></h3>
                    <p class="my-group-role">Quyền hạn của bạn: <b><?= $is_leader ? 'Trưởng nhóm' : 'Thành viên' ?></b></p>
                </div>
                <form method="POST" onsubmit="return confirm('<?= $is_leader ? 'Hành động này sẽ GIẢI TÁN nhóm hoàn toàn. Xác nhận?' : 'Bạn muốn rời khỏi nhóm?' ?>');" class="m-0">
                    <input type="hidden" name="action" value="roi_nhom">
                    <input type="hidden" name="nhom_id" value="<?= $curr_nhom_id ?>">
                    <button type="submit" class="btn-red">
                        <?= $is_leader ? 'Giải tán nhóm' : 'Rời nhóm' ?>
                    </button>
                </form>
            </div>

            <?php if ($is_leader && $pending_members && $pending_members->num_rows > 0): ?>
                <div class="pending-box">
                    <h4 class="pending-title">Có yêu cầu gia nhập nhóm mới cần bạn duyệt:</h4>
                    <div class="pending-list">
                        <?php while ($p_mem = $pending_members->fetch_assoc()): ?>
                            <div class="pending-item">
                                <span class="fw-bold-dark">Họ tên: <?= htmlspecialchars($p_mem['hoten']) ?></span>
                                <div class="flex-gap-8">
                                    <form method="POST" class="m-0">
                                        <input type="hidden" name="action" value="phe_duyet">
                                        <input type="hidden" name="tv_id" value="<?= $p_mem['tv_id'] ?>">
                                        <input type="hidden" name="nhom_id" value="<?= $curr_nhom_id ?>">
                                        <button type="submit" class="btn-green-sm">Chấp nhận</button>
                                    </form>
                                    <form method="POST" class="m-0">
                                        <input type="hidden" name="action" value="tu_choi">
                                        <input type="hidden" name="tv_id" value="<?= $p_mem['tv_id'] ?>">
                                        <input type="hidden" name="nhom_id" value="<?= $curr_nhom_id ?>">
                                        <button type="submit" class="btn-red-sm">Từ chối</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                </div>
            <?php endif; ?>

            <h4 style="color: #475569; font-size: 15px; font-weight: 700; margin-bottom: 12px;">Thành viên chính thức (Max 5):</h4>
            <div class="member-list">
                <?php while ($mem = $members->fetch_assoc()): ?>
                    <div class="member-item">
                        <div class="flex-align-center-gap-10">
                            <?php if ($mem['vai_tro'] == 'nhom_truong'): ?>
                                <span class="role-badge">Trưởng nhóm</span>
                            <?php else: ?>
                                <span class="role-badge" style="background:#f1f5f9; color:#475569;">Thành viên</span>
                            <?php endif; ?>
                            <div>
                                <span style="font-weight: 700; color: #1e293b;"><?= htmlspecialchars($mem['hoten']) ?></span>
                                <?php if ($mem['user_id'] == $id_hocsinh) echo '<span class="badge-you">Bạn</span>'; ?>
                            </div>
                        </div>
                        <?php if ($is_leader && $mem['vai_tro'] != 'nhom_truong'): ?>
                            <form method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn LOẠI BỎ thành viên này khỏi nhóm?');" class="m-0">
                                <input type="hidden" name="action" value="xoa_thanh_vien">
                                <input type="hidden" name="tv_id" value="<?= $mem['tv_id'] ?>">
                                <input type="hidden" name="nhom_id" value="<?= $curr_nhom_id ?>">
                                <button type="submit" class="btn-remove">Loại bỏ</button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>

            <hr class="dashed-divider">
            <h4 class="hw-title">Bài tập cần nộp (Dành cho Nhóm)</h4>
            
            <?php
            $stmt_bt = $conn->prepare("SELECT * FROM bai_tap WHERE class_id = ? AND tieu_de LIKE '%[BÀI TẬP NHÓM]%' ORDER BY id DESC");
            $stmt_bt->bind_param("i", $id_lop);
            $stmt_bt->execute();
            $bai_taps = $stmt_bt->get_result();

            if ($bai_taps->num_rows > 0):
                while ($bt = $bai_taps->fetch_assoc()):
                    $stmt_check = $conn->prepare("SELECT * FROM nop_bai WHERE bai_tap_id = ? AND nhom_id = ?");
                    $stmt_check->bind_param("ii", $bt['id'], $curr_nhom_id);
                    $stmt_check->execute();
                    $da_nop = $stmt_check->get_result()->fetch_assoc();
            ?>
                    <div class="hw-item">
                        <h5 class="hw-name"><?= htmlspecialchars($bt['tieu_de']) ?></h5>
                        <p class="hw-desc"><?= nl2br(htmlspecialchars($bt['noi_dung'] ?? '')) ?></p>
                        
                        <?php if (!empty($bt['han_nop'])): ?>
                            <p class="hw-deadline">Hạn nộp: <?= date("H:i d/m/Y", strtotime($bt['han_nop'])) ?></p>
                        <?php endif; ?>
                        
                        <?php if ($da_nop): ?>
                            <div class="hw-submitted">
                                <p style="color: #065f46; font-weight: bold; margin: 0 0 8px 0;">Nhóm đã nộp bài thành công!</p>
                                <?php if (!empty($da_nop['diem'])): ?>
                                    <p style="color: #b91c1c; font-weight: bold; font-size: 15px; margin: 0 0 5px 0;">Điểm số: <?= $da_nop['diem'] ?>/10</p>
                                    <p style="font-size: 13px; color: #475569; margin:0;"><strong>GV nhận xét:</strong> <?= htmlspecialchars($da_nop['nhan_xet'] ?? 'Chưa có nhận xét') ?></p>
                                <?php else: ?>
                                    <p style="font-size: 13px; color: #047857; font-style: italic; margin:0;">Đang chờ giáo viên chấm điểm...</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <form method="POST" enctype="multipart/form-data" class="form-submit-hw">
                                <input type="hidden" name="action" value="nop_bai_nhom">
                                <input type="hidden" name="bai_tap_id" value="<?= $bt['id'] ?>">
                                <input type="hidden" name="nhom_id" value="<?= $curr_nhom_id ?>">
                                
                                <p style="font-size: 13px; font-weight: bold; margin-bottom: 8px; color: #334155; margin-top:0;">Thành viên đại diện nhóm nộp bài tại đây:</p>
                                
                                <label style="display: block; font-size: 12px; color: #64748b; margin-bottom: 3px;">Tải File đính kèm lên (nếu có):</label>
                                <input type="file" name="file_nop" style="margin-bottom: 10px; width: 100%; border: 1px solid #cbd5e1; padding: 5px; border-radius: 4px; background: white;">
                                
                                <label style="display: block; font-size: 12px; color: #64748b; margin-bottom: 3px;">Hoặc dán Link (Drive, Docs, Github...):</label>
                                <input type="url" name="link_nop" placeholder="https://..." style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #cbd5e1; border-radius: 4px;">
                                
                                <button type="submit" class="btn-blue">
                                    Gửi Bài Làm Của Nhóm
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
            <?php 
                endwhile;
            else:
                echo "<p style='color: #64748b; font-size: 14px; text-align: center; padding: 20px 0; margin:0;'>Giáo viên chưa giao bài tập nhóm nào.</p>";
            endif;
            ?>
        </div>

    <?php 
        // Hiển thị giao diện khi học sinh đang chờ duyệt vào nhóm
        else: 
    ?>
        <div class="waiting-box">
            <h3 class="waiting-title">Yêu cầu gia nhập đang chờ xử lý</h3>
            <p class="waiting-desc">Bạn đã gửi yêu cầu xin vào nhóm <b><?= htmlspecialchars($my_group['ten_nhom']) ?></b>. Vui lòng đợi nhóm trưởng chấp nhận!</p>
            <form method="POST" onsubmit="return confirm('Bạn có muốn HỦY đơn yêu cầu xin gia nhập nhóm này không?');" class="m-0">
                <input type="hidden" name="action" value="roi_nhom">
                <input type="hidden" name="nhom_id" value="<?= $curr_nhom_id ?>">
                <button type="submit" class="btn-gray">Hủy đơn xin gia nhập</button>
            </form>
        </div>
    <?php 
        endif;

    // Hiển thị danh sách nhóm cho học sinh chưa tham gia nhóm nào
    else: 
        $sql_groups = "SELECT n.*, u.hoten as ten_truong, COUNT(DISTINCT CASE WHEN tv.trang_thai = 1 THEN tv.id END) as sothanhvien 
                    FROM nhom_hoc n 
                    JOIN users u ON n.id_truong_nhom = u.id 
                    LEFT JOIN thanh_vien_nhom tv ON n.id = tv.nhom_id 
                    WHERE n.class_id = ? 
                    GROUP BY n.id ORDER BY n.ngay_tao DESC";
        $stmt_all = $conn->prepare($sql_groups);
        $stmt_all->bind_param("i", $id_lop);
        $stmt_all->execute();
        $all_groups = $stmt_all->get_result();
    ?>
        <div class="create-group-box">
            <h4 class="create-group-title">Tạo nhóm học tập mới</h4>
            <form method="POST" class="flex-gap-10 m-0">
                <input type="hidden" name="action" value="tao_nhom">
                <input type="text" name="ten_nhom" placeholder="Nhập tên nhóm của bạn..." required class="input-text">
                <button type="submit" class="btn-blue-lg">Tạo Nhóm</button>
            </form>
        </div>

        <h4 class="all-groups-title">Danh sách nhóm hiện có trong lớp học:</h4>
        <?php if ($all_groups->num_rows > 0): ?>
            <div class="all-groups-list">
                <?php while ($g = $all_groups->fetch_assoc()): ?>
                    <div class="group-list-item">
                        <div>
                            <h5 class="group-list-name"><?= htmlspecialchars($g['ten_nhom']) ?></h5>
                            <p class="group-list-info">Trưởng nhóm: <b><?= htmlspecialchars($g['ten_truong']) ?></b> | Thành viên chính thức: <b style="color: #1e3a8a;"><?= $g['sothanhvien'] ?> / 5</b></p>
                        </div>
                        <?php if ($g['sothanhvien'] < 5): ?>
                            <form method="POST" class="m-0">
                                <input type="hidden" name="action" value="gia_nhap">
                                <input type="hidden" name="nhom_id" value="<?= $g['id'] ?>">
                                <button type="submit" class="btn-green">Xin gia nhập</button>
                            </form>
                        <?php else: ?>
                            <span class="badge-full">Đầy nhóm</span>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-list">
                <p>Lớp học chưa có nhóm nào. Hãy tạo nhóm đầu tiên của riêng bạn!</p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>