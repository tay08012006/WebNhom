<div class="bt-card" style="background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
    <h3 style="color: #0288d1; border-bottom: 2px solid #e1f5fe; padding-bottom: 10px; margin-top: 0;">Quản lý Bài tập Nhóm</h3>
    
    <?php
    // Lấy chuẩn xác mã lớp từ URL hiện tại để chuyển hướng không bị lỗi
    $ma_lop_hien_tai = isset($_GET['malop']) ? trim($_GET['malop']) : '';
    $class_id = $current_class['id'];

    // ==================== XỬ LÝ: GIÁO VIÊN GIAO BÀI TẬP NHÓM ====================
    if (isset($_POST['action']) && $_POST['action'] == 'giao_bai_tap_nhom') {
        $tieu_de = "[BÀI TẬP NHÓM] " . trim($_POST['tieu_de']);
        $noi_dung = trim($_POST['noi_dung']);
        $han_nop = !empty($_POST['han_nop']) ? $_POST['han_nop'] : NULL;

        $stmt_giao = $conn->prepare("INSERT INTO bai_tap (class_id, tieu_de, noi_dung, han_nop) VALUES (?, ?, ?, ?)");
        $stmt_giao->bind_param("isss", $class_id, $tieu_de, $noi_dung, $han_nop);
        if ($stmt_giao->execute()) {
            echo "<script>alert('Giao bài tập nhóm thành công!'); window.location.href='phonghoc.php?malop=$ma_lop_hien_tai&tab=nhom';</script>";
            exit();
        }
    }

    // ==================== CHỨC NĂNG MỚI: XỬ LÝ GỠ BÀI TẬP NHÓM ====================
    if (isset($_POST['action']) && $_POST['action'] == 'xoa_bai_tap_nhom') {
        $id_bai_tap = intval($_POST['id_bai_tap']);
        
        // 1. Xóa các bài nộp liên quan của học sinh trước để tránh lỗi khóa ngoại database
        $stmt_xoa_nop = $conn->prepare("DELETE FROM nop_bai WHERE bai_tap_id = ?");
        $stmt_xoa_nop->bind_param("i", $id_bai_tap);
        $stmt_xoa_nop->execute();

        // 2. Xóa chính xác đề bài tập này dựa trên ID bài tập và ID lớp học
        $stmt_xoa_bt = $conn->prepare("DELETE FROM bai_tap WHERE id = ? AND class_id = ?");
        $stmt_xoa_bt->bind_param("ii", $id_bai_tap, $class_id);
        
        if ($stmt_xoa_bt->execute()) {
            echo "<script>alert('Đã gỡ bài tập nhóm thành công!'); window.location.href='phonghoc.php?malop=$ma_lop_hien_tai&tab=nhom';</script>";
            exit();
        }
    }
    ?>

    <div style="background: #f0f9ff; border: 1px solid #bae6fd; padding: 15px; border-radius: 8px; margin-bottom: 25px;">
        <h4 style="margin-top: 0; color: #0288d1; font-size: 16px;">Giao Bài Tập Mới Cho Các Nhóm</h4>
        <form method="POST" style="display: flex; flex-direction: column; gap: 10px;">
            <input type="hidden" name="action" value="giao_bai_tap_nhom">
            <input type="text" name="tieu_de" placeholder="Nhập tiêu đề bài tập nhóm..." required style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px;">
            <textarea name="noi_dung" rows="3" placeholder="Ghi chú, yêu cầu nội dung bài tập..." style="padding: 10px; border: 1px solid #cbd5e1; border-radius: 5px; font-family: inherit;"></textarea>
            <div style="display: flex; gap: 10px; align-items: center;">
                <label style="font-size: 13px; font-weight: bold; color: #475569;">Hạn nộp (Tùy chọn):</label>
                <input type="datetime-local" name="han_nop" style="padding: 8px; border: 1px solid #cbd5e1; border-radius: 5px;">
                <button type="submit" style="background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 6px; font-weight: bold; cursor: pointer; margin-left: auto;">
                    Phát Đề Cho Nhóm
                </button>
            </div>
        </form>
    </div>

    <h4 style="color: #1e293b; margin-bottom: 10px;"> Các đề bài tập nhóm đã phát:</h4>
    <div style="background: white; padding: 15px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 25px; max-height: 250px; overflow-y: auto;">
        <?php
        $sql_bt = "SELECT * FROM bai_tap WHERE class_id = ? AND tieu_de LIKE '[BÀI TẬP NHÓM]%' ORDER BY id DESC";
        $stmt_bt = $conn->prepare($sql_bt);
        $stmt_bt->bind_param("i", $class_id);
        $stmt_bt->execute();
        $result_bt = $stmt_bt->get_result();
        if ($result_bt->num_rows > 0) {
            while ($bt = $result_bt->fetch_assoc()) {
                // Tạo bố cục Flexbox để đẩy nút Gỡ xuống về bên tay phải
                echo "<div style='padding: 10px 0; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center; gap: 15px;'>";
                echo "<div>";
                echo "<strong style='color: #0288d1;'>" . htmlspecialchars($bt['tieu_de']) . "</strong>";
                if (!empty($bt['han_nop'])) {
                    echo " <span style='font-size: 12px; color: #ef4444;'>(Hạn nộp: " . date('d/m/Y H:i', strtotime($bt['han_nop'])) . ")</span>";
                }
                echo "<p style='margin: 5px 0 0 0; font-size: 14px; color: #475569;'>" . nl2br(htmlspecialchars($bt['noi_dung'])) . "</p>";
                echo "</div>";
                
                // Form chứa nút gỡ bài viết
                echo "<form method='POST' onsubmit='return confirm(\"Bạn có chắc chắn muốn gỡ bài tập này không?\\nLưu ý: Tất cả bài làm của học sinh đã nộp cho đề này cũng sẽ bị xóa hoàn toàn!\");'>";
                echo "<input type='hidden' name='action' value='xoa_bai_tap_nhom'>";
                echo "<input type='hidden' name='id_bai_tap' value='" . $bt['id'] . "'>";
                echo "<button type='submit' style='background: #ef4444; color: white; border: none; padding: 6px 12px; border-radius: 5px; font-size: 12px; cursor: pointer; font-weight: bold; white-space: nowrap;'>Gỡ xuống</button>";
                echo "</form>";
                
                echo "</div>";
            }
        } else {
            echo "<p style='color: #64748b; font-style: italic; margin: 0;'>Chưa có bài tập nhóm nào được phát.</p>";
        }
        ?>
    </div>

    <h4 style="color: #1e293b; margin-bottom: 10px;">Tình trạng nộp bài của các nhóm:</h4>
    <?php
    $sql_nhom = "SELECT * FROM nhom_hoc WHERE class_id = ?";
    $stmt_nhom = $conn->prepare($sql_nhom);
    $stmt_nhom->bind_param("i", $class_id);
    $stmt_nhom->execute();
    $result_nhom = $stmt_nhom->get_result();

    if ($result_nhom->num_rows > 0):
        while ($nhom = $result_nhom->fetch_assoc()):
    ?>
        <div style="border: 1px solid #cfd8dc; border-radius: 8px; margin-top: 15px; padding: 15px; background: #f8fafc;">
            <h4 style="margin-top: 0; color: #1e293b; font-size: 18px;">
                Tên Nhóm: <span style="color: #0288d1;"><?= htmlspecialchars($nhom['ten_nhom']) ?></span>
            </h4>
            
            <?php
            // Lấy danh sách bài nộp của nhóm này
            $sql_nopbai = "SELECT n.*, b.tieu_de FROM nop_bai n 
                        JOIN bai_tap b ON n.bai_tap_id = b.id 
                        WHERE n.nhom_id = ? ORDER BY n.ngay_nop DESC";
            $stmt_nop = $conn->prepare($sql_nopbai);
            $stmt_nop->bind_param("i", $nhom['id']);
            $stmt_nop->execute();
            $bai_nops = $stmt_nop->get_result();

            if ($bai_nops->num_rows > 0):
                while ($bai = $bai_nops->fetch_assoc()):
            ?>
                <div style="background: white; padding: 15px; border-radius: 8px; border-left: 4px solid #4caf50; margin-top: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                    <p style="margin-bottom: 5px;"><strong>Đề bài:</strong> <?= htmlspecialchars($bai['tieu_de']) ?></p>
                    <p style="margin-bottom: 10px; font-size: 14px; color: #64748b;"><strong>Thời gian nộp:</strong> <?= date('d/m/Y H:i', strtotime($bai['ngay_nop'])) ?></p>
                    
                    <div style="margin-bottom: 15px;">
                        <?php if (!empty($bai['file_nop'])): ?>
                            <a href="/web/uploads/<?= htmlspecialchars($bai['file_nop']) ?>" target="_blank" style="display: inline-block; padding: 6px 12px; background: #e0f2fe; color: #0288d1; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 13px;">📁 Xem File bài làm</a>
                        <?php endif; ?>
                        
                        <?php if (!empty($bai['link_nop'])): ?>
                            <a href="<?= htmlspecialchars($bai['link_nop']) ?>" target="_blank" style="display: inline-block; padding: 6px 12px; background: #f3e8ff; color: #7e22ce; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 13px; margin-left: 10px;">🔗 Xem Link (Drive/Github)</a>
                        <?php endif; ?>
                    </div>

                    <form action="xuly_chamdiem_nhom.php" method="POST" style="border-top: 1px dashed #cbd5e1; padding-top: 15px;">
                        <input type="hidden" name="ma_lop" value="<?= htmlspecialchars($ma_lop_hien_tai) ?>">
                        <input type="hidden" name="nop_bai_id" value="<?= $bai['id'] ?>">
                        
                        <div style="display: flex; gap: 15px; align-items: flex-start;">
                            <div>
                                <label style="display: block; font-size: 13px; font-weight: bold; color: #475569; margin-bottom: 5px;">Điểm số (Hệ 10):</label>
                                <input type="number" step="0.1" max="10" name="diem" value="<?= htmlspecialchars($bai['diem'] ?? '') ?>" style="width: 100px; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px;" required>
                            </div>
                            <div style="flex-grow: 1;">
                                <label style="display: block; font-size: 13px; font-weight: bold; color: #475569; margin-bottom: 5px;">Nhận xét chung cho cả nhóm:</label>
                                <textarea name="nhan_xet" rows="2" style="width: 100%; padding: 8px; border: 1px solid #cbd5e1; border-radius: 4px; font-family: inherit;" placeholder="Viết nhận xét vào đây..."><?= htmlspecialchars($bai['nhan_xet'] ?? '') ?></textarea>
                            </div>
                            <button type="submit" style="margin-top: 26px; padding: 9px 18px; background: #0288d1; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: bold;">Lưu Điểm</button>
                        </div>
                    </form>
                </div>
            <?php 
                endwhile; 
            else: 
                echo "<p style='color: #ef4444; font-style: italic; font-size: 14px; margin-top: 10px;'>Nhóm này chưa nộp bài tập nào.</p>";
            endif; 
            ?>
        </div>
    <?php 
        endwhile;
    else:
        echo "<p style='color: #64748b; margin-top: 15px;'>Chưa có học sinh nào tạo nhóm trong lớp này.</p>";
    endif;
    ?>
</div>