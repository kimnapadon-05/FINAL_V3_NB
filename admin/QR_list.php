<?php
require_once "../db_connect.php"; 

// ดึงข้อมูลอุปกรณ์ทั้งหมด
$sql = "SELECT * FROM equipment ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เลือกรายการพิมพ์ QR Code</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f4f7fe; padding: 20px; }
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); background: white; padding: 20px; }
        .btn-luxury { background: #1e1e2d; color: #c5a47e; border: none; padding: 10px 20px; border-radius: 8px; transition: 0.3s; }
        .btn-luxury:hover { background: #2b2b40; color: #fff; transform: translateY(-2px); }
        .table thead th { background-color: #f8f9fa; border: none; color: #666; font-weight: 600; }
        .form-check-input:checked { background-color: #c5a47e; border-color: #c5a47e; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark">🖨️ เลือกรายการที่จะพิมพ์</h3>
        <a href="dashboard.php" class="btn btn-outline-secondary">กลับหน้าหลัก</a>
    </div>

    <form action="print_multiple.php" method="POST" target="_blank">
        <div class="card-custom">
            
            <div class="d-flex justify-content-between mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll" onclick="toggleAll(this)">
                    <label class="form-check-label" for="selectAll">เลือกทั้งหมด</label>
                </div>
                <button type="submit" class="btn btn-luxury">
                    <i class="bi bi-printer-fill me-2"></i> พิมพ์รายการที่เลือก
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th style="width: 50px;">เลือก</th>
                            <th>รูปภาพ</th>
                            <th>รหัสครุภัณฑ์ (Asset ID)</th>
                            <th>ชื่ออุปกรณ์</th>
                            <th>ประเภท</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="text-center">
                                    <input class="form-check-input item-checkbox" type="checkbox" name="selected_ids[]" value="<?php echo $row['asset_id']; ?>">
                                </td>
                                <td>
                                    <?php if(!empty($row['image_path'])): ?>
                                        <img src="<?php echo $row['image_path']; ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="fw-bold text-primary"><?php echo $row['asset_id']; ?></td>
                                <td><?php echo $row['equipment_name']; ?></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $row['equipment_type']; ?></span></td>
                                <td class="text-muted small"><?php echo $row['location']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-4">ไม่พบข้อมูลอุปกรณ์</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
    // ฟังก์ชันเลือกทั้งหมด
    function toggleAll(source) {
        checkboxes = document.querySelectorAll('.item-checkbox');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = source.checked;
        }
    }
</script>

</body>
</html>
<?php $conn->close(); ?>