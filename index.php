<?php include 'includes/header.php'; ?>
<?php 
// ส่วนรองรับการสแกนผ่าน URL
$url_asset_id = isset($_GET['asset_id']) ? htmlspecialchars($_GET['asset_id']) : '';
?>

<!-- 1. โหลดไลบรารี QR Code -->
<link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4e54c8 0%, #8f94fb 100%);
        --card-shadow: 0 10px 20px rgba(0, 0, 0, 0.05); 
    }

    body { font-family: 'Kanit', sans-serif; background-color: #f8f9fa; }

    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        border-radius: 1.5rem;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(255,255,255,0.8);
    }
    
    .custom-nav-pills {
        background: rgba(248, 249, 250, 0.8);
        border-radius: 1rem;
        padding: 0.5rem;
    }
    .custom-nav-pills .nav-link {
        border-radius: 0.8rem;
        color: #6c757d;
        font-weight: 500;
        border: none; transition: all 0.2s;
    }
    .custom-nav-pills .nav-link.active {
        background: var(--primary-gradient);
        color: white;
        box-shadow: 0 4px 10px rgba(78, 84, 200, 0.3);
    }
    
    .btn-rgb-active {
        background: var(--primary-gradient);
        border: none; color: white;
        border-radius: 1rem; padding: 12px 30px;
        font-weight: 600;
        box-shadow: 0 10px 20px rgba(78, 84, 200, 0.3);
        transition: transform 0.2s;
    }
    .btn-rgb-active:hover { transform: translateY(-2px); color: white; }

    .form-control, .form-select {
        border-radius: 1rem;
        padding: 0.75rem 1rem;
        border: 1px solid #dee2e6;
    }

    /* กล้อง */
    #reader {
        width: 100%;
        min-height: 300px;
        max-height: 400px;
        border-radius: 1rem;
        background-color: #000;
        display: none;
        overflow: hidden;
        position: relative;
    }
    #reader video {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
    }

    /* Modal */
    #statusModal {
        position: fixed;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(5px);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
    }
    #statusModal.show { display: flex; }
    
    .status-card-content {
        background: #fff;
        width: 90%; max-width: 500px; max-height: 90vh;
        overflow-y: auto;
        border-radius: 1.5rem;
        box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        position: relative;
        animation: slideUp 0.3s ease-out;
    }
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }

    .close-modal-btn {
        position: absolute; top: 15px; right: 15px;
        background: rgba(0,0,0,0.05); border: none;
        border-radius: 50%; width: 36px; height: 36px;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; z-index: 10;
    }
</style>

<div class="luxury-content-wrapper py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">

            <ul class="nav nav-pills nav-fill custom-nav-pills mb-5" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active py-3" id="submit-tab" data-bs-toggle="tab" data-bs-target="#submit-pane" type="button" role="tab">
                        <i class="bi bi-send-plus-fill me-2 h5"></i> แจ้งซ่อมใหม่
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link py-3" id="track-tab" data-bs-toggle="tab" data-bs-target="#track-pane" type="button" role="tab">
                        <i class="bi bi-qr-code-scan me-2 h5"></i> ติดตามสถานะ
                    </button>
                </li>
            </ul>
            
            <div class="tab-content" id="myTabContent">
                
                <!-- Tab 1: ฟอร์มแจ้งซ่อม -->
                <div class="tab-pane fade show active" id="submit-pane" role="tabpanel">
                    <div class="glass-card p-4 p-md-5">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h2 class="h3 fw-bold text-primary mb-1">แบบฟอร์มแจ้งซ่อม</h2>
                                <p class="text-muted small mb-0">กรุณากรอกข้อมูลหรือสแกน QR ที่อุปกรณ์</p>
                            </div>
                            <!-- *** ปุ่มสแกน QR สำหรับ Auto-fill *** -->
                            <button type="button" class="btn btn-outline-primary rounded-pill px-3" onclick="startFormScan()">
                                <i class="bi bi-qr-code"></i> สแกนอุปกรณ์
                            </button>
                        </div>
                        
                        <!-- พื้นที่แสดงข้อมูลอุปกรณ์ที่สแกนเจอ -->
                        <div id="equipmentInfo" class="alert alert-info d-none">
                            <div class="d-flex align-items-center">
                                <!-- แสดงรูปอุปกรณ์ตรงนี้ -->
                                <div class="me-3">
                                    <img id="equipImagePreview" src="" alt="Equipment Image" class="rounded shadow-sm bg-white d-none" style="width: 80px; height: 80px; object-fit: cover;">
                                    <i id="equipIconDefault" class="bi bi-cpu fs-1"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">พบข้อมูลอุปกรณ์!</h6>
                                    <p class="mb-0 small" id="equipDetailText">...</p>
                                </div>
                            </div>
                        </div>
                        
                        <form action="save_repair.php" method="POST" enctype="multipart/form-data" id="repairForm">
                            <input type="hidden" name="email" id="emailInputHidden"> 
                            <!-- เก็บ Asset ID ที่สแกนได้ -->
                            <input type="hidden" name="scanned_asset_id" id="scannedAssetId" value="<?php echo $url_asset_id; ?>">

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">ชื่อ-นามสกุล <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="reporter_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">รหัสบุคลากร</label>
                                    <input type="text" class="form-control" name="reporter_id" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">เบอร์โทรติดต่อ</label>
                                    <input type="tel" class="form-control" name="reporter_phone" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Username อีเมล <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="emailUsernameInput" placeholder="Username" required>
                                        <span class="input-group-text">@lbtech.ac.th</span>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label class="form-label">ประเภทการซ่อม <span class="text-danger">*</span></label>
                                    <select class="form-select" name="device_type" id="deviceTypeSelect" required>
                                        <option value="">-- เลือกประเภท --</option>
                                        <option value="Computer">คอมพิวเตอร์</option>
                                        <option value="Projector">โปรเจคเตอร์</option>
                                        <option value="AccessPoint">Access point</option>
                                        <option value="Other">อื่นๆ</option>
                                    </select>
                                </div>
                                <!-- *** เพิ่มช่องชื่อรุ่น (Model) *** -->
                                <div class="col-md-6">
                                    <label class="form-label">ชื่อรุ่น / Model</label>
                                    <input type="text" class="form-control bg-light" name="device_model" id="deviceModelInput" placeholder="เช่น Lenovo ThinkPad" readonly>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">ตึก <span class="text-danger">*</span></label>
                                    <select class="form-select" id="buildingSelect" name="building" required>
                                        <option value="">-- เลือกตึก --</option>
                                        <option value="ตึก 14">ตึก 14</option>
                                        <option value="ตึก 26">ตึก 26</option>
                                        <option value="Other">ตึกอื่นๆ</option>
                                    </select>
                                </div>
                                <div class="col-md-6" id="roomDropdownContainer" style="display: none;">
                                    <label class="form-label">ห้อง</label>
                                    <select class="form-select" id="roomSelect" name="room">
                                        <option value="">-- เลือกห้อง --</option>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">รายละเอียดปัญหา <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="3" name="problem_detail" id="problemDetail" required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">รูปภาพประกอบ (ภาพอุปกรณ์ หรือ อาการเสีย)</label>
                                <input class="form-control" type="file" name="repair_image" accept="image/*">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-rgb-active btn-lg">ยืนยันการแจ้งซ่อม</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tab 2: ติดตามสถานะ -->
                <div class="tab-pane fade" id="track-pane" role="tabpanel">
                    <div class="glass-card p-5 overflow-hidden">
                        <div class="p-4 p-md-5 text-center">
                            <div class="mb-4">
                                <i class="bi bi-qr-code-scan display-1 text-primary"></i>
                                <h2 class="h3 fw-bold mt-3">ติดตามสถานะ</h2>
                                <p class="text-muted">สแกน QR Code ติดตามงานซ่อม</p>
                            </div>
                            <!-- (ส่วนควบคุมกล้อง ใช้ร่วมกันกับ Modal ได้) -->
                            <button id="trackScanBtn" class="btn btn-primary btn-lg rounded-pill" onclick="startTrackScan()">
                                <i class="bi bi-camera-fill me-2"></i> สแกนติดตามงาน
                            </button>
                        </div>
                        <!-- *** ส่วนที่เพิ่ม: Manual Input *** -->
                            <div class="mt-4 pt-4 border-top">
                                <p class="small text-muted mb-2">หรือกรอกรหัสด้วยตนเอง (หากกล้องใช้งานไม่ได้):</p>
                                <div class="d-flex justify-content-center gap-2">
                                    <input type="text" id="manualTrackingId" class="form-control w-50 text-center" placeholder="เช่น REP-XXXX" style="max-width: 250px;">
                                    <button class="btn btn-secondary" onclick="fetchStatusManually()">ค้นหา</button>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- *** Modal สำหรับแสดงสถานะ / กล้อง *** -->
<div id="statusModal">
    <div class="status-card-content">
        <button class="close-modal-btn" onclick="closeModal()">
            <i class="bi bi-x-lg"></i>
        </button>
        <div id="modalContent">
            <!-- พื้นที่สำหรับกล้อง -->
            <div id="reader"></div>
            <!-- พื้นที่สำหรับเนื้อหาอื่นๆ -->
            <div id="modalBody"></div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- ส่วน Dropdown ห้อง (คงเดิม) ---
        const roomData = {
            "ตึก 14": ["1411", "1412", "1413", "1414", "1415", "1421", "1422", "1423", "1424", "1425", "1431", "1432", "1433", "1434", "1435", "1441", "1442", "1443", "1444", "1445"],
            "ตึก 26": ["TC201", "TC202", "TC203", "TC204", "TC205"],
            "Other": ["ห้อง IOT", "อื่นๆ"]
        };
        const buildingSelect = document.getElementById('buildingSelect');
        const roomContainer = document.getElementById('roomDropdownContainer');
        const roomSelect = document.getElementById('roomSelect');
        const repairForm = document.getElementById('repairForm');
        const emailUsernameInput = document.getElementById('emailUsernameInput');
        const emailInputHidden = document.getElementById('emailInputHidden');
        const requiredDomain = '@lbtech.ac.th';

        function loadRooms() {
            if (!buildingSelect) return;
            const selectedBuilding = buildingSelect.value;
            if (roomSelect) roomSelect.innerHTML = '<option value="">-- เลือกห้อง --</option>';
            
            if (selectedBuilding && roomData[selectedBuilding] && roomSelect) {
                roomData[selectedBuilding].forEach(room => {
                    const option = document.createElement('option');
                    option.value = room;
                    option.textContent = room;
                    roomSelect.appendChild(option);
                });
                if (roomContainer) roomContainer.style.display = 'block';
                roomSelect.required = true;
            } else {
                if (roomContainer) roomContainer.style.display = 'none';
                if (roomSelect) roomSelect.required = false;
            }
        }
        if (buildingSelect) buildingSelect.addEventListener('change', loadRooms);
        loadRooms();
        
        // Form Validation
        if (repairForm) {
            repairForm.addEventListener('submit', function(event) {
                const username = emailUsernameInput.value.trim();
                if (username === '' || username.includes('@')) {
                    event.preventDefault();
                    alert('โปรดกรอกแค่ชื่อผู้ใช้ (ไม่ต้องใส่ @lbtech.ac.th)');
                    emailUsernameInput.focus(); return;
                }
                emailInputHidden.value = username + requiredDomain;
            });
        }

        // --- ส่วน Scanner Logic ---
        let html5QrCode = null;
        let scanMode = null; // 'form' หรือ 'track'
        
        const statusModal = document.getElementById('statusModal');
        const modalBody = document.getElementById('modalBody');
        const readerDiv = document.getElementById('reader');

        // เริ่มต้น Scanner Library
        if (typeof Html5Qrcode !== 'undefined') {
            try { html5QrCode = new Html5Qrcode("reader"); } catch (e) { console.log(e); }
        }

        // ฟังก์ชันเปิด Modal และเริ่มสแกน
        window.openScannerModal = function(mode) {
            scanMode = mode;
            statusModal.classList.add('show');
            readerDiv.style.display = 'block';
            modalBody.innerHTML = '<p class="text-center mt-3 text-muted">กำลังเปิดกล้อง...</p>';
            
            // เช็ค HTTPS
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                modalBody.innerHTML = '<div class="alert alert-warning m-3">⚠️ กล้องต้องใช้ผ่าน HTTPS เท่านั้น</div>';
                readerDiv.style.display = 'none';
                return;
            }

            if (html5QrCode) {
                html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, onScanSuccess)
                .catch(err => {
                    modalBody.innerHTML = `<div class="alert alert-danger m-3">เปิดกล้องไม่ได้: ${err}</div>`;
                    readerDiv.style.display = 'none';
                });
            }
        };

        // Callback เมื่อสแกนเจอ
        const onScanSuccess = (decodedText) => {
            // ปิดกล้อง
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    readerDiv.style.display = 'none';
                }).catch(err => console.log(err));
            }

            // ตัด ID จาก URL
            let id = decodedText;
            if (decodedText.includes('asset_id=')) {
                try { id = decodedText.split('asset_id=')[1].split('&')[0]; } catch(e){}
            } else if (decodedText.includes('tracking_id=')) {
                try { id = decodedText.split('tracking_id=')[1].split('&')[0]; } catch(e){}
            }

            console.log("Scanned ID:", id, "Mode:", scanMode);

            if (scanMode === 'form') {
                // โหมดกรอกฟอร์มอัตโนมัติ
                fetchEquipmentData(id);
                closeModal();
            } else if (scanMode === 'track') {
                // โหมดติดตามสถานะ
                fetchStatus(id);
            }
        };

        // ฟังก์ชันดึงข้อมูลอุปกรณ์ (Auto-fill)
        function fetchEquipmentData(assetId) {
            // แสดง Loading
            const infoBox = document.getElementById('equipmentInfo');
            const detailText = document.getElementById('equipDetailText');
            const equipImage = document.getElementById('equipImagePreview');
            const equipIcon = document.getElementById('equipIconDefault');
            
            infoBox.classList.remove('d-none');
            detailText.innerHTML = 'กำลังค้นหาข้อมูล...';

            fetch('get_equipment.php?id=' + assetId)
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        const data = res.data;
                        
                        // กรอกข้อมูลลงฟอร์ม
                        document.getElementById('scannedAssetId').value = data.asset_id;
                        document.getElementById('deviceModelInput').value = data.model_name; // เติมชื่อรุ่น
                        
                        detailText.innerHTML = `<strong>${data.equipment_name}</strong> (${data.model_name})<br>S/N: ${data.serial_no}`;
                        
                        // แสดงรูปภาพอุปกรณ์ (ถ้ามี)
                        // path ใน DB เป็น admin/uploads/.. ต้องปรับให้ถูกต้องถ้าเรียกจาก index.php
                        if (data.image_path) {
                            // ถ้า path ใน db เป็น uploads/file.jpg ให้เติม admin/ ข้างหน้า
                            // หรือถ้าเป็น path เต็ม ก็ใช้ได้เลย
                            let imgPath = data.image_path;
                            if(!imgPath.startsWith('admin/') && !imgPath.startsWith('http')) {
                                imgPath = 'admin/' + imgPath; 
                            }
                            
                            equipImage.src = imgPath;
                            equipImage.classList.remove('d-none');
                            equipIcon.classList.add('d-none');
                        } else {
                            equipImage.classList.add('d-none');
                            equipIcon.classList.remove('d-none');
                        }
                        
                        // Auto Select: Type
                        const typeMap = {
                            'COMPUTER EQUIPMENT': 'Computer',
                            'NETWORK': 'AccessPoint',
                            'PRINTER': 'Other'
                        };
                        const mappedType = typeMap[data.equipment_type] || 'Other';
                        const deviceSelect = document.getElementById('deviceTypeSelect');
                        if(deviceSelect) deviceSelect.value = mappedType;

                        // Auto Select: Building & Room (พยายามแกะจาก text)
                        // สมมติ location = "ตึก 14 ห้อง 1411"
                        if (data.location.includes("14")) {
                            buildingSelect.value = "ตึก 14";
                        } else if (data.location.includes("26")) {
                            buildingSelect.value = "ตึก 26";
                        } else {
                            buildingSelect.value = "Other";
                        }
                        
                        // Trigger change เพื่อโหลดห้อง
                        loadRooms();
                        
                        // รอแป๊บแล้วเลือกห้อง
                        setTimeout(() => {
                            if (data.location.includes("14")) { // ตัวอย่าง logic ง่ายๆ
                                // ลองวนลูปหา option ที่ตรงกับ location
                                const options = roomSelect.options;
                                for(let i=0; i<options.length; i++) {
                                    if(data.location.includes(options[i].value)) {
                                        roomSelect.value = options[i].value;
                                        break;
                                    }
                                }
                            }
                        }, 500);

                    } else {
                        detailText.innerHTML = `<span class="text-danger">ไม่พบข้อมูล: ${res.message}</span>`;
                        equipImage.classList.add('d-none');
                        equipIcon.classList.remove('d-none');
                    }
                })
                .catch(err => {
                    detailText.innerHTML = `<span class="text-danger">เกิดข้อผิดพลาดในการดึงข้อมูล</span>`;
                });
        }

        // ฟังก์ชันดึงสถานะซ่อม (Mode: Track)
        window.fetchStatus = function(trackingId) {
            // เปิด Modal และแสดง Loading
            statusModal.classList.add('show');
            readerDiv.style.display = 'none'; // ซ่อนกล้องถ้ามีการเปิดอยู่
            modalBody.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>กำลังโหลด...</p></div>`;
            
            fetch('get_status.php?id=' + trackingId)
                .then(response => response.text())
                .then(html => {
                    modalBody.innerHTML = html;
                    const homeBtn = modalBody.querySelector('button[onclick="resetScanner()"]');
                    if(homeBtn) {
                        homeBtn.setAttribute('onclick', 'closeModal()');
                        homeBtn.innerHTML = 'ปิดหน้าต่าง';
                    }
                });
        };

        // ฟังก์ชันค้นหาแบบ Manual
        window.fetchStatusManually = function() {
            const manualId = document.getElementById('manualTrackingId').value;
            if (manualId) {
                fetchStatus(manualId);
            } else {
                alert("กรุณากรอกรหัสติดตาม");
            }
        };

        // ปุ่มปิด Modal
        window.closeModal = function() {
            statusModal.classList.remove('show');
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().catch(err => console.log(err));
            }
        };

        // เรียกใช้ปุ่มสแกน
        window.startFormScan = function() { openScannerModal('form'); };
        window.startTrackScan = function() { openScannerModal('track'); };

        // เช็ค URL Asset ID (กรณีเข้าผ่านลิงก์ QR)
        const urlAssetId = "<?php echo $url_asset_id; ?>";
        if (urlAssetId) {
            fetchEquipmentData(urlAssetId);
        }
    });
</script>

<?php include 'includes/footer.php'; ?> 