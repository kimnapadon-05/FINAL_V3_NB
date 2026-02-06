<?php
require_once "../db_connect.php"; // ตรวจสอบ path ให้ถูกต้อง (../ หรือ ./)
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    
</head>
<body>

    <!-- Sidebar Section -->
    <?php include 'Sidebar.php'; ?>

    <!-- Main Content -->
    <div class="main-content">
        
        <!-- Form สำหรับส่งค่าไปหน้า Print Multiple -->
        <form action="../print.php" method="POST" target="_blank" id="printForm">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="fw-bold text-dark m-0">📦 คลังอุปกรณ์ (Inventory)</h3>
                    <p class="text-muted small">จัดการข้อมูลและเลือกพิมพ์ QR Code</p>
                </div>
                <div class="d-flex gap-2">
                    <!-- ปุ่มสั่งพิมพ์ -->
                    <button type="submit" class="btn btn-print-selected shadow-sm">
                        <i class="bi bi-printer-fill me-2"></i> พิมพ์ที่เลือก
                    </button>
                </div>
            </div>

            <div class="card-luxury">
                <div class="table-responsive">
                    <table id="equipmentTable" class="table table-hover w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">
                                    <!-- ปุ่มเลือกทั้งหมด -->
                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                </th>
                                <th>รูปภาพ</th>
                                <th>เลขครุภัณฑ์</th>
                                <th>ชื่ออุปกรณ์ / Model</th>
                                <th>ประเภท</th>
                                <th>Location</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM equipment ORDER BY id DESC";
                            $result = $conn->query($sql);
                            if ($result->num_rows > 0):
                                while($row = $result->fetch_assoc()):
                            ?>
                            <tr class="align-middle">
                                <td class="text-center">
                                    <!-- Checkbox รายการ -->
                                    <input class="form-check-input item-checkbox" type="checkbox" name="selected_ids[]" value="<?php echo $row['asset_id']; ?>">
                                </td>
                                <td>
                                    <?php if(!empty($row['image_path'])): ?>
                                        <img src="<?php echo $row['image_path']; ?>" style="width: 45px; height: 45px; object-fit: cover; border-radius: 10px; border: 1px solid #eee;">
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark"><?php echo $row['asset_id']; ?></span><br>
                                    <span class="text-muted" style="font-size: 0.8em;">S/N: <?php echo $row['serial_no']; ?></span>
                                </td>
                                <td>
                                    <div class="fw-medium"><?php echo $row['equipment_name']; ?></div>
                                    <div class="text-muted small"><?php echo $row['model_name']; ?></div>
                                </td>
                                <td><span class="badge bg-light text-dark border rounded-pill fw-normal"><?php echo $row['equipment_type']; ?></span></td>
                                <td class="text-secondary"><small><i class="bi bi-geo-alt me-1"></i><?php echo $row['location']; ?></small></td>
                                <td>
                                    <!-- ปุ่ม View -->
                                    <button type="button" class="btn-icon btn-view me-1" 
                                        onclick="viewDetail(
                                            '<?php echo $row['asset_id']; ?>', 
                                            '<?php echo $row['equipment_name']; ?>', 
                                            '<?php echo $row['model_name']; ?>', 
                                            '<?php echo $row['equipment_type']; ?>', 
                                            '<?php echo $row['serial_no']; ?>', 
                                            '<?php echo $row['location']; ?>', 
                                            '<?php echo $row['image_path']; ?>',
                                            '<?php echo $row['qr_path']; ?>'
                                        )">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                    
                                    <!-- ปุ่ม Delete (ลิงก์ไป delete_item.php ตามชื่อไฟล์ที่ควรจะเป็น) -->
                                    <a href="delete_item.php?id=<?php echo $row['asset_id']; ?>" 
                                       class="btn-icon btn-del"
                                       onclick="return confirm('⚠️ ยืนยันการลบข้อมูล?\nAsset ID: <?php echo $row['asset_id']; ?>');">
                                        <i class="bi bi-trash-fill"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        
        </form> 

    </div>

    <!-- Modal View Detail (Popup) -->
    <div class="modal fade" id="viewModal" tabindex="-1">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
          <div class="modal-header bg-dark text-white">
            <h5 class="modal-title"><i class="bi bi-box-seam me-2"></i>รายละเอียดอุปกรณ์</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="row">
                <div class="col-md-5 text-center">
                    <img id="modalImg" src="" class="img-fluid rounded shadow-sm mb-3" style="max-height: 250px;">
                    <div class="p-3 bg-light rounded border text-center">
                        <img id="modalQr" src="" style="width: 100px; mix-blend-mode: multiply;">
                        <div class="small text-muted mt-2">QR Code สำหรับสแกน</div>
                    </div>
                </div>
                <div class="col-md-7">
                    <h4 id="modalAssetId" class="fw-bold text-dark mb-3"></h4>
                    <table class="table table-borderless">
                        <tr><td class="text-muted w-25">ชื่ออุปกรณ์:</td><td id="modalName" class="fw-medium"></td></tr>
                        <tr><td class="text-muted">ประเภท:</td><td id="modalType"></td></tr>
                        <tr><td class="text-muted">รุ่น/Model:</td><td id="modalModel"></td></tr>
                        <tr><td class="text-muted">Serial No:</td><td id="modalSerial"></td></tr>
                        <tr><td class="text-muted">Location:</td><td id="modalLocation"></td></tr>
                    </table>
                </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Scripts Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // เปิดใช้งาน DataTables
            var table = $('#equipmentTable').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.7/i18n/th.json" },
                "columnDefs": [
                    { "orderable": false, "targets": 0 } // ห้ามเรียงลำดับคอลัมน์ Checkbox
                ],
                "order": [[ 2, "desc" ]] // เรียงตาม Asset ID ล่าสุด
            });

            // ฟังก์ชัน Select All (ใช้ Event Delegation เพื่อให้ทำงานกับทุกหน้าของ DataTable)
            $('#selectAll').on('click', function(){
               var rows = table.rows({ 'search': 'applied' }).nodes();
               $('input[type="checkbox"]', rows).prop('checked', this.checked);
            });
        });

        // ฟังก์ชัน Modal View
        function viewDetail(id, name, model, type, serial, location, imgPath, qrPath) {
            document.getElementById('modalAssetId').innerText = id;
            document.getElementById('modalName').innerText = name;
            document.getElementById('modalModel').innerText = (model) ? model : '-';
            document.getElementById('modalType').innerText = type;
            document.getElementById('modalSerial').innerText = serial;
            document.getElementById('modalLocation').innerText = location;
            document.getElementById('modalImg').src = (imgPath) ? imgPath : 'https://placehold.co/400x300?text=No+Image';
            document.getElementById('modalQr').src = (qrPath) ? qrPath : 'https://placehold.co/150x150?text=No+QR';
            new bootstrap.Modal(document.getElementById('viewModal')).show();
        }
    </script>
</body>
</html>