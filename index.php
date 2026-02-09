<?php include 'includes/header.php'; ?>
<?php 
// ตรวจสอบว่ามีการ redirect มาจากการ timeout ของ session
$show_timeout = isset($_GET['timeout']) && $_GET['timeout'] == 1;
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
        min-height: 250px;
        max-height: 350px;
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
        background: rgba(0, 0, 0, 0.6);
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
        box-shadow: 0 25px 50px rgba(0,0,0,0.3);
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
        transition: 0.2s;
    }
    .close-modal-btn:hover { background: rgba(0,0,0,0.1); }
</style>

<?php if ($show_timeout): ?>
<div class="container mt-3">
    <div class="alert alert-warning text-center mb-0">
        เซสชันของคุณหมดอายุแล้ว กรุณาเข้าสู่ระบบใหม่
    </div>
</div>
<?php endif; ?>

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
                            <!-- ปุ่มสแกน QR สำหรับ Auto-fill -->
                            <button type="button" class="btn btn-outline-primary rounded-pill px-3" onclick="startFormScan()">
                                <i class="bi bi-qr-code"></i> สแกนอุปกรณ์
                            </button>
                        </div>
                        
                        <div id="equipmentInfo" class="alert alert-info d-none">
                            <div class="d-flex align-items-center">
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
                                        <label class="form-label">ชื่ออุปกรณ์ (Asset Name)</label>
                                        <input type="text" class="form-control bg-light" name="device_name" id="deviceNameInput" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">เลขครุภัณฑ์ (Asset Number) </label>
                                        <input type="text" class="form-control bg-light" id="assetNumberInput" readonly>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">ชื่อรุ่น / Model</label>
                                        <input type="text" class="form-control bg-light" name="device_model" id="deviceModelInput" readonly>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Serial Number (S/N)</label>
                                        <input type="text" class="form-control bg-light" name="device_serial" id="deviceSerialInput" readonly>
                                    </div>

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
                                
                                <!--div class="col-md-6">
                                    <label class="form-label">ตึก <span class="text-danger">*</span></label>
                                    <select class="form-select" id="buildingSelect" name="building" required>
                                        <option value="">-- เลือกตึก --</option>
                                        <option value="ตึก 14">ตึก 14</option>
                                        <option value="ตึก 26">ตึก 26</option>
                                        <option value="Other">ตึกอื่นๆ</option>
                                    </select>
                                </div-->
                                <!--div class="col-md-6" id="roomDropdownContainer" style="display: none;">
                                    <label class="form-label">ห้อง</label>
                                    <select class="form-select" id="roomSelect" name="room">
                                        <option value="">-- เลือกห้อง --</option>
                                    </select>
                                </div>
                            </div-->

                            <div class="mb-3">
                                <label class="form-label">รายละเอียดปัญหา <span class="text-danger">*</span></label>
                                <textarea class="form-control" rows="3" name="problem_detail" id="problemDetail" required></textarea>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">รูปภาพประกอบ</label>
                                <input class="form-control" type="file" name="repair_image" accept="image/*">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-rgb-active btn-lg">ยืนยันการแจ้งซ่อม</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Tab 2: ติดตามสถานะ (แยกปุ่ม) -->
                <div class="tab-pane fade" id="track-pane" role="tabpanel">
                    <div class="glass-card p-0 overflow-hidden">
                        <div class="p-4 p-md-5 text-center">
                            <div class="mb-4">
                                <i class="bi bi-qr-code-scan display-1 text-primary"></i>
                                <h2 class="h3 fw-bold mt-3">ติดตามสถานะ</h2>
                                <p class="text-muted">สแกน QR Code ติดตามงานซ่อม</p>
                            </div>
                            
                            <!-- แยกปุ่ม -->
                            <div class="d-grid gap-3 col-md-8 mx-auto">
                                <button id="trackScanBtn" class="btn btn-primary btn-lg rounded-pill shadow-sm" onclick="startTrackScan()">
                                    <i class="bi bi-camera-fill me-2"></i> เปิดกล้องสแกน
                                </button>
                                
                                <div class="text-muted my-1 small">- หรือ -</div>
                                
                                <input type="file" id="trackQrInput" accept="image/*" style="display:none;" onchange="handleTrackFileUpload(this)">
                                <button onclick="document.getElementById('trackQrInput').click()" class="btn btn-outline-primary btn-lg rounded-pill">
                                    <i class="bi bi-image me-2"></i> อัปโหลดรูป QR Code
                                </button>
                            </div>
                            
                            <!-- Manual Input -->
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
</div>

<!-- *** Modal สำหรับแสดงสถานะ / กล้อง *** -->
<div id="statusModal">
    <div class="status-card-content">
        <button class="close-modal-btn" onclick="closeModal()">
            <i class="bi bi-x-lg"></i>
        </button>
        <div id="modalContent" class="pb-3">
            
            <!-- 1. พื้นที่กล้อง -->
            <div id="reader" class="mb-3"></div>

            <!-- 2. พื้นที่ปุ่มควบคุม (แสดงเฉพาะตอนเปิดกล้อง) -->
            <div id="scanControls" class="text-center px-4" style="display:none;">
                <button onclick="closeModal()" class="btn btn-outline-danger rounded-pill w-100 mb-3">
                    <i class="bi bi-x-circle me-2"></i> ยกเลิกสแกน
                </button>
                <div id="scanMsg" class="text-muted small">กำลังค้นหา QR Code...</div>
            </div>

            <!-- 3. พื้นที่ข้อความ (Loading / Error / Content) -->
            <div id="modalBody"></div>

        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- ส่วน Dropdown ห้อง (คงเดิม) ---
        const roomData = {
            "ตึก 14": ["1425", "1441", "1442", "1443"],
            "ตึก 26": ["TC201", "TC202", "TC203", "TC204", "TC205","TC303"],
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

        // --- 2. Scanner Logic (แก้ไขการ Parsing Text ให้ฉลาดขึ้น) ---
        let html5QrCode = null;
        let scanMode = null; 
        
        const statusModal = document.getElementById('statusModal');
        const modalBody = document.getElementById('modalBody');
        const readerDiv = document.getElementById('reader');
        const scanControls = document.getElementById('scanControls');

        if (typeof Html5Qrcode !== 'undefined') {
            try { html5QrCode = new Html5Qrcode("reader"); } catch (e) { console.log(e); }
        }

        // ฟังก์ชันเปิด Modal สแกน
        window.openScannerModal = function(mode) {
            scanMode = mode;
            statusModal.classList.add('show');
            readerDiv.style.display = 'block';
            scanControls.style.display = 'block'; 
            modalBody.innerHTML = '';
            
            // เช็ค HTTPS
            if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                modalBody.innerHTML = '<div class="alert alert-warning m-3">⚠️ กล้องต้องใช้ผ่าน HTTPS เท่านั้น</div>';
                readerDiv.style.display = 'none';
                scanControls.style.display = 'none';
                return;
            }

            if (html5QrCode) {
                html5QrCode.start({ facingMode: "environment" }, { fps: 10, qrbox: 250 }, onScanSuccess)
                .catch(err => {
                    modalBody.innerHTML = `<div class="alert alert-danger m-3">เปิดกล้องไม่ได้: ${err}</div>`;
                    readerDiv.style.display = 'none';
                    scanControls.style.display = 'none';
                });
            }
        };

        // ฟังก์ชันจัดการการอัปโหลดไฟล์ใน Modal
        window.handleTrackFileUpload = function(input) {
            if (input.files.length == 0) return;
            const imageFile = input.files[0];
            
            scanMode = 'track'; // บังคับโหมดติดตาม
            statusModal.classList.add('show');
            readerDiv.style.display = 'none'; 
            scanControls.style.display = 'none';
            modalBody.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-primary"></div><p class="mt-2">กำลังสแกนรูปภาพ...</p></div>';

            if (html5QrCode) {
                if (html5QrCode.isScanning) {
                    html5QrCode.stop().catch(err => console.log(err));
                }

                html5QrCode.scanFile(imageFile, true)
                .then(decodedText => {
                    onScanSuccess(decodedText);
                })
                .catch(err => {
                    modalBody.innerHTML = `<div class="alert alert-warning m-3 text-center">
                        <i class="bi bi-exclamation-triangle fs-1"></i><br>
                        ไม่พบ QR Code ในรูปภาพนี้<br>
                        <button class="btn btn-outline-primary mt-3" onclick="closeModal()">ปิด</button>
                    </div>`;
                });
            }
            input.value = '';
        };

        // ฟังก์ชันเมื่อสแกนสำเร็จ (ฉบับแก้ไขสมบูรณ์)
        const onScanSuccess = (decodedText) => {
            // 1. หยุดกล้องเมื่อสแกนเจอ
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().then(() => {
                    readerDiv.style.display = 'none';
                }).catch(err => console.log(err));
            }
            scanControls.style.display = 'none';

            let id = decodedText.trim();
            console.log("Scanned Text:", id);

            // 2. เช็คว่าเป็น Format ใหม่ของเราไหม (ขึ้นต้นด้วย IT|)
            // Format: IT | AssetID | Type | Model | Serial | Name | Location
            if (id.toUpperCase().startsWith("IT|")) {
                const parts = id.split("|");
                
                if (scanMode === 'form') {
                    // --- ส่วนกรอกฟอร์มอัตโนมัติ ---
                    // 1. เติม Asset ID (Hidden และ Visible)
                    if(parts[1]) {
                    // ใส่ค่าลง Hidden Input (สำหรับส่งไป DB)
                    document.getElementById('scannedAssetId').value = parts[1];
                    const displayInput = document.getElementById('assetNumberInput'); 
                    if(displayInput) displayInput.value = parts[1];
                    }

                    // [2] Type (parts[2]) -> Auto Select
                    if(parts[2]) {
                        const typeMap = {
                            'COMPUTER EQUIPMENT': 'Computer',
                            'NOTEBOOK': 'Computer',
                            'PROJECTOR': 'Projector',
                            'ACCESS POINT': 'AccessPoint',
                            'PRINTER': 'Other'
                        };
                        const mappedType = typeMap[parts[2].toUpperCase()] || 'Other';
                        const deviceSelect = document.getElementById('deviceTypeSelect');
                        if(deviceSelect) deviceSelect.value = mappedType;
                    }

                    // [3] Model (parts[3]) -> ช่องชื่อรุ่น
                    if(parts[3]) {
                        const modelInput = document.getElementById('deviceModelInput');
                        if(modelInput) modelInput.value = parts[3];
                    }

                    // [4] Serial (parts[4]) -> ช่อง Serial
                    if(parts[4]) {
                        const serialInput = document.getElementById('deviceSerialInput');
                        if(serialInput) serialInput.value = parts[4];
                    }

                    // [5] Name (parts[5]) -> ช่องชื่ออุปกรณ์
                    if(parts[5]) {
                        const nameInput = document.getElementById('deviceNameInput');
                        if(nameInput) nameInput.value = parts[5];
                    }

                    // [6] Location (parts[6]) -> เลือกตึก/ห้อง
                    // 6. Location (parts[6]) -> เลือกตึก/ห้อง
                    // 6. Location (parts[6]) -> เลือกตึก/ห้อง
                    if(parts[6]) {
                        const loc = parts[6]; // ค่าที่ได้: "ตึก 26 TC203"
                        
                        // 1. เลือกตึก (Logic เดิม)
                        if (loc.includes("14")) buildingSelect.value = "ตึก 14";
                        else if (loc.includes("26")) buildingSelect.value = "ตึก 26";
                        else buildingSelect.value = "Other"; 

                        loadRooms(); // โหลดรายชื่อห้องมารรอ

                        // 2. เลือกห้อง (Logic ใหม่: ตัดคำเอาเฉพาะตัวหลังสุด)
                        setTimeout(() => {
                            // ตัดช่องว่าง แล้วเอาตัวสุดท้าย (จะได้ "TC203")
                            const roomCode = loc.trim().split(" ").pop(); 
                            
                            const options = roomSelect.options;
                            let found = false;

                            for(let i=0; i<options.length; i++) {
                                // เช็คว่า ตัวเลือกใน Dropdown ตรงกับ roomCode ที่เราตัดมาไหม
                                if(options[i].value === roomCode) {
                                    roomSelect.value = options[i].value;
                                    found = true;
                                    break;
                                }
                            }
                            
                            // ถ้าหาไม่เจอจริงๆ ค่อยยัดค่าเต็มลงไป (กันเหนียว)
                            if (!found) {
                                var opt = document.createElement('option');
                                opt.value = roomCode; // ใส่แค่รหัสห้องพอ
                                opt.innerHTML = roomCode;
                                opt.selected = true;
                                roomSelect.appendChild(opt);
                            }

                        }, 500);
                    }

                    closeModal(); // ปิดหน้าต่างสแกน
                    
                } else if (scanMode === 'track') {
                    // ถ้าเป็นโหมดติดตามงานซ่อม ให้ใช้ Asset ID (parts[1])
                    fetchStatus(parts[1]); 
                }
                
                return; // จบการทำงาน (สำคัญมาก ห้ามลบ)
            } 
            
            // 3. ถ้าเจอ URL: index.php?asset_id=... (เผื่อใช้ QR แบบเก่า)
            else if (id.includes('asset_id=')) {
                try { id = id.split('asset_id=')[1].split('&')[0]; } catch(e){}
            } 
            // 4. ถ้าเจอ URL: track.php?tracking_id=...
            else if (id.includes('tracking_id=')) {
                try { id = id.split('tracking_id=')[1].split('&')[0]; } catch(e){}
            }

            console.log("Parsed ID:", id, "Mode:", scanMode);

            if (scanMode === 'form') {
                // ถ้าสแกนได้แค่ ID เดี่ยวๆ ให้ไปดึงข้อมูลจาก DB
                fetchEquipmentData(id);
                closeModal();
            } else if (scanMode === 'track') {
                fetchStatus(id);
            }
        };

        function fetchEquipmentData(assetId) {
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
                        document.getElementById('scannedAssetId').value = data.asset_id;
                        document.getElementById('deviceModelInput').value = data.model_name;
                        detailText.innerHTML = `<strong>${data.equipment_name}</strong> (${data.model_name})<br>S/N: ${data.serial_no}`;
                        
                        if (data.image_path) {
                            let imgPath = data.image_path;
                            // ปรับ path ถ้าจำเป็น
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
                        
                        const typeMap = {'COMPUTER EQUIPMENT': 'Computer', 'NETWORK': 'AccessPoint', 'PRINTER': 'Other'};
                        const mappedType = typeMap[data.equipment_type] || 'Other';
                        const deviceSelect = document.getElementById('deviceTypeSelect');
                        if(deviceSelect) deviceSelect.value = mappedType;

                        // Auto Select Building
                        if (data.location.includes("14")) buildingSelect.value = "ตึก 14";
                        else if (data.location.includes("26")) buildingSelect.value = "ตึก 26";
                        else buildingSelect.value = "Other";
                        
                        loadRooms();
                        setTimeout(() => {
                            if (data.location.includes("14")) { 
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
                    detailText.innerHTML = `<span class="text-danger">เกิดข้อผิดพลาดในการดึงข้อมูล (API Error)</span>`;
                    console.error(err);
                });
        }

        window.fetchStatus = function(trackingId) {
            modalBody.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>กำลังโหลด...</p></div>`;
            readerDiv.style.display = 'none'; 
            scanControls.style.display = 'none';

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
        window.fetchStatus = function(trackingId) {
            modalBody.innerHTML = `<div class="text-center p-5"><div class="spinner-border text-primary"></div><p>กำลังโหลด...</p></div>`;
            readerDiv.style.display = 'none'; // ซ่อนกล้อง
            scanControls.style.display = 'none'; // ซ่อนปุ่ม

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

        window.fetchStatusManually = function() {
            const manualId = document.getElementById('manualTrackingId').value;
            if (manualId) {
                statusModal.classList.add('show');
                fetchStatus(manualId);
            } else {
                alert("กรุณากรอกรหัสติดตาม");
            }
        };

        window.closeModal = function() {
            statusModal.classList.remove('show');
            if (html5QrCode && html5QrCode.isScanning) {
                html5QrCode.stop().catch(err => console.log(err));
            }
            // รีเซ็ตค่า Input File เพื่อให้เลือกรูปเดิมซ้ำได้
            document.getElementById('trackQrInput').value = '';
        };

        window.startFormScan = function() { openScannerModal('form'); };
        window.startTrackScan = function() { openScannerModal('track'); };

        const urlAssetId = "<?php echo $url_asset_id; ?>";
        if (urlAssetId) {
            fetchEquipmentData(urlAssetId);
        }
    });
</script>

<?php include 'includes/footer.php'; ?>