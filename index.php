<?php
// i:/My Drive/PARENTAL CONTROL/web/index.php
// Halaman Dashboard Orang Tua - FamilySync
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FamilySync - Dashboard Pengelolaan Perangkat Keluarga</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* MODAL AND ANIMATIONS */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeInOverlay 0.3s ease forwards;
        }
        .modal-content {
            width: 100%;
            max-width: 400px;
            position: relative;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            animation: slideUpContent 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .modal-header h2 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }
        @keyframes fadeInOverlay {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideUpContent {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        @keyframes scaleIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .animate-scale {
            animation: scaleIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
    </style>
</head>
<body>

    <header>
        <h1>FamilySync</h1>
        <div class="user-profile">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                <circle cx="12" cy="7" r="4" />
            </svg>
            <span>Orang Tua (Admin)</span>
        </div>
    </header>

    <div class="container">
        <div class="dashboard-layout">
            
            <!-- SIDEBAR: DAFTAR PERANGKAT -->
            <div class="sidebar">
                <div class="glass-card">
                    <div class="card-title">
                        <span>Perangkat Anak</span>
                        <button class="round-control-btn" id="btn-refresh-devices" title="Refresh Daftar">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67" />
                            </svg>
                        </button>
                    </div>
                    
                    <button class="btn btn-primary" id="btn-add-device" style="width: calc(100% - 2rem); margin: 0 1rem 1rem 1rem; font-size: 0.85rem; padding: 0.55rem 1rem; background-color: #0284c7; display: flex; align-items: center; justify-content: center; gap: 6px; border: none; font-weight: 600;">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Tambah Perangkat Baru
                    </button>

                    <div class="device-list" id="device-list-container">
                        <!-- Dimuat otomatis via JS -->
                        <div style="text-align: center; color: var(--text-muted); font-size: 0.875rem; padding: 2rem 0;">
                            Memuat daftar perangkat...
                        </div>
                    </div>
                </div>

                <div class="glass-card">
                    <div class="card-title">
                        <span>Panduan Cepat</span>
                    </div>
                    <div style="font-size: 0.8rem; color: var(--text-muted); line-height: 1.6; display: flex; flex-direction: column; gap: 0.75rem;">
                        <p><strong>1. Hubungkan Klien:</strong> Instal aplikasi FamilySync di HP anak Anda, lalu masukkan Device ID yang tertera.</p>
                        <p><strong>2. Berikan Izin:</strong> Pastikan Anda mengaktifkan <em>Device Admin</em> dan <em>Notification Access</em> pada perangkat anak.</p>
                        <p><strong>3. Mulai Panggilan:</strong> Klik tombol Panggil Kamera atau Panggil Layar. Anak Anda akan menerima notifikasi panggilan masuk di layarnya untuk persetujuan koneksi.</p>
                    </div>
                </div>
            </div>

            <!-- MAIN DOCK: KONTROL DAN MONITORING -->
            <div class="main-content" id="main-dashboard" style="display: none;">
                
                <!-- HEADER DETAIL PERANGKAT -->
                <div class="device-dashboard-header">
                    <div class="device-title-large">
                        <span id="active-device-name">Nama Perangkat</span>
                        <div id="active-device-online-status" class="status-indicator"></div>
                    </div>
                    <div class="quick-stats">
                        <div class="stat-chip" title="Tingkat Daya Baterai">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="1" y="6" width="18" height="12" rx="2" ry="2" />
                                <line x1="23" y1="11" x2="23" y2="13" />
                            </svg>
                            <span id="active-device-battery">--%</span>
                        </div>
                        <div class="stat-chip" id="active-device-id-chip" title="Salin ID Perangkat">
                            <span style="font-family: monospace; font-size: 0.8rem;" id="active-device-id">ID: --</span>
                        </div>
                    </div>
                </div>

                <!-- AKSI KONTROL CEPAT -->
                <div class="control-actions-grid">
                    <button class="btn btn-danger" id="btn-lock-device">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                        </svg>
                        Kunci Perangkat Instan
                    </button>
                    <button class="btn btn-secondary" id="btn-unlock-device">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                            <path d="M7 11V7a5 5 0 0 1 9.9-1" />
                        </svg>
                        Buka Kunci Layar
                    </button>
                </div>

                <!-- KOMPONEN MONITORING & PENGINTAI TRANSPARAN -->
                <div class="monitoring-container">
                    
                    <!-- MONITORING AUDIO-VIDEO (WEBRTC) -->
                    <div class="glass-card video-card">
                        <div class="card-title">
                            <span>Sesi Komunikasi Transparan</span>
                            <span id="webrtc-status" style="font-size: 0.75rem; color: var(--text-muted); font-weight: normal;">Disconnected</span>
                        </div>
                        <div class="video-screen">
                            <div class="video-placeholder" id="video-placeholder-overlay">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 7l-7 5 7 5V7z" />
                                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                                </svg>
                                <span>Pilih jenis panggilan transparan di bawah untuk memulai</span>
                            </div>
                            
                            <video id="remote-video" autoplay playsinline style="display: none;"></video>

                            <div class="video-controls" id="webrtc-controls" style="display: none;">
                                <button class="round-control-btn active" id="btn-hangup" title="Akhiri Sesi">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M10.68 13.31a16 16 0 0 0 3.41 2.6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7 2 2 0 0 1 1.72 2v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.42 19.42 0 0 1-3.33-2.67m-2.67-3.34a19.79 19.79 0 0 1-3.07-8.63A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91" />
                                        <line x1="23" y1="1" x2="1" y2="23" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem; display: flex; gap: 0.75rem;">
                            <button class="btn btn-primary" id="btn-call-camera" style="flex: 1;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M23 7l-7 5 7 5V7z" />
                                    <rect x="1" y="5" width="15" height="14" rx="2" ry="2" />
                                </svg>
                                Panggil Kamera & Mic
                            </button>
                            <button class="btn btn-accent" id="btn-call-screen" style="flex: 1;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="2" y="3" width="20" height="14" rx="2" ry="2" />
                                    <line x1="8" y1="21" x2="16" y2="21" />
                                    <line x1="12" y1="17" x2="12" y2="21" />
                                </svg>
                                Panggil Layar & Mic
                            </button>
                        </div>
                    </div>

                    <!-- NOTIFICATION LOGGER PANEL -->
                    <div class="glass-card notif-card">
                        <div class="card-title">
                            <span>Log Notifikasi</span>
                            <button class="round-control-btn" id="btn-clear-notif" title="Bersihkan Log">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="3 6 5 6 21 6" />
                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                </svg>
                            </button>
                        </div>
                        <div class="notif-list" id="notifications-container">
                            <!-- Dimuat otomatis via JS -->
                        </div>
                    </div>

                </div>
            </div>

            <!-- JIKA BELUM ADA PERANGKAT TERPILIH -->
            <div class="main-content" id="no-device-selected" style="display: flex; justify-content: center; align-items: center; min-height: 400px;">
                <div class="glass-card" style="text-align: center; max-width: 480px; padding: 3rem 2rem;">
                    <div style="width: 72px; height: 72px; background: rgba(139, 92, 246, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--primary);">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2" />
                            <line x1="12" y1="18" x2="12.01" y2="18" />
                        </svg>
                    </div>
                    <h3 style="font-size: 1.25rem; margin-bottom: 0.5rem; font-weight: 700;">Dashboard FamilySync</h3>
                    <p style="color: var(--text-muted); font-size: 0.9rem; line-height: 1.6;">Silakan pilih perangkat anak di sidebar sebelah kiri untuk mulai mengelola pembatasan dan memulai panggilan komunikasi transparan secara real-time.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- WEBRTC & CONTROLLER STATE LOGIC JS -->
    <script>
        const API_URL = 'api.php';
        let devices = [];
        let selectedDeviceId = null;
        let selectedDeviceName = '';
        let devicePollingInterval = null;
        let notifPollingInterval = null;
        
        // WebRTC Signaling Variables
        let peerConnection = null;
        let signalingInterval = null;
        let candidateInterval = null;
        let iceCandidatesCollected = [];

        // Konfigurasi server STUN (Gratis dari Google untuk negosiasi P2P)
        const rtcConfig = {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        };

        document.addEventListener('DOMContentLoaded', () => {
            loadDevices();
            
            // Auto refresh devices list every 10 seconds
            setInterval(loadDevices, 10000);

            document.getElementById('btn-refresh-devices').addEventListener('click', loadDevices);
            document.getElementById('btn-lock-device').addEventListener('click', () => setLockStatus(true));
            document.getElementById('btn-unlock-device').addEventListener('click', () => setLockStatus(false));
            document.getElementById('btn-clear-notif').addEventListener('click', clearNotifications);
            
            // WebRTC buttons
            document.getElementById('btn-call-camera').addEventListener('click', () => startWebRTCCall('camera'));
            document.getElementById('btn-call-screen').addEventListener('click', () => startWebRTCCall('screen'));
            document.getElementById('btn-hangup').addEventListener('click', hangupCall);

            // Tambah Perangkat Event Listeners
            document.getElementById('btn-add-device').addEventListener('click', openAddDeviceModal);
            document.getElementById('btn-close-modal').addEventListener('click', closeAddDeviceModal);
        });

        // 1. Memuat daftar perangkat dari API
        async function loadDevices() {
            try {
                const response = await fetch(`${API_URL}?action=get_devices`);
                devices = await response.json();
                renderDeviceList();
            } catch (err) {
                console.error("Gagal memuat perangkat:", err);
            }
        }

        // Render daftar perangkat ke UI sidebar
        function renderDeviceList() {
            const container = document.getElementById('device-list-container');
            if (devices.length === 0) {
                container.innerHTML = `
                    <div style="text-align: center; color: var(--text-muted); font-size: 0.85rem; padding: 2rem 0;">
                        Tidak ada perangkat terdaftar.<br>Silakan instal aplikasi klien Android.
                    </div>`;
                return;
            }

            container.innerHTML = '';
            devices.forEach(dev => {
                const isOnline = dev.is_online == 1;
                const activeClass = selectedDeviceId === dev.device_id ? 'active' : '';
                
                const item = document.createElement('div');
                item.className = `device-item ${activeClass}`;
                item.onclick = () => selectDevice(dev.device_id, dev.device_name);

                item.innerHTML = `
                    <div class="device-info">
                        <span class="device-name">${escapeHtml(dev.device_name)}</span>
                        <span class="device-meta">Baterai: ${dev.battery_level}% | ID: ${dev.device_id.substring(0, 6)}...</span>
                    </div>
                    <div class="status-indicator ${isOnline ? 'online' : 'offline'}" title="${isOnline ? 'Online' : 'Offline'}"></div>
                `;
                container.appendChild(item);
            });
        }

        // Memilih perangkat aktif
        function selectDevice(deviceId, deviceName) {
            selectedDeviceId = deviceId;
            selectedDeviceName = deviceName;
            
            // Update active state in UI
            renderDeviceList();

            // Sembunyikan panel kosong, tampilkan dashboard aktif
            document.getElementById('no-device-selected').style.display = 'none';
            document.getElementById('main-dashboard').style.display = 'flex';

            // Bersihkan polling perangkat sebelumnya
            if (devicePollingInterval) clearInterval(devicePollingInterval);
            if (notifPollingInterval) clearInterval(notifPollingInterval);

            // Set data awal
            const dev = devices.find(d => d.device_id === deviceId);
            updateDeviceHeaderUI(dev);

            // Muat data langsung
            loadNotifications();

            // Jalankan polling berkala untuk detail dan notifikasi (per 3 detik)
            devicePollingInterval = setInterval(pollActiveDeviceStatus, 3000);
            notifPollingInterval = setInterval(loadNotifications, 4000);

            // Putuskan panggilan WebRTC aktif jika ada saat berganti perangkat
            hangupCall();
        }

        // Memperbarui UI Informasi Header
        function updateDeviceHeaderUI(dev) {
            if (!dev) return;
            document.getElementById('active-device-name').innerText = dev.device_name;
            document.getElementById('active-device-battery').innerText = `${dev.battery_level}%`;
            document.getElementById('active-device-id').innerText = `ID: ${dev.device_id}`;
            
            const statusDot = document.getElementById('active-device-online-status');
            statusDot.className = 'status-indicator';
            statusDot.classList.add(dev.is_online == 1 ? 'online' : 'offline');

            // Set style status tombol lock
            const lockBtn = document.getElementById('btn-lock-device');
            const unlockBtn = document.getElementById('btn-unlock-device');
            if (dev.is_locked == 1) {
                lockBtn.style.opacity = '0.5';
                lockBtn.disabled = true;
                unlockBtn.style.opacity = '1';
                unlockBtn.disabled = false;
            } else {
                lockBtn.style.opacity = '1';
                lockBtn.disabled = false;
                unlockBtn.style.opacity = '0.5';
                unlockBtn.disabled = true;
            }
        }

        // Polling status detil perangkat yang dipilih
        async function pollActiveDeviceStatus() {
            if (!selectedDeviceId) return;
            try {
                const response = await fetch(`${API_URL}?action=get_devices`);
                const allDevs = await response.json();
                const dev = allDevs.find(d => d.device_id === selectedDeviceId);
                if (dev) {
                    updateDeviceHeaderUI(dev);
                }
            } catch (err) {
                console.error("Gagal melakukan polling status perangkat:", err);
            }
        }

        // 2. KONTROL DEVICE LOCK/UNLOCK
        async function setLockStatus(shouldLock) {
            if (!selectedDeviceId) return;
            const endpoint = shouldLock ? 'lock_device' : 'unlock_device';
            try {
                const response = await fetch(`${API_URL}?action=${endpoint}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ device_id: selectedDeviceId })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    // Berhasil mengirim perintah, perbarui status lokal secara langsung
                    pollActiveDeviceStatus();
                } else {
                    alert("Gagal mengirimkan perintah: " + result.error);
                }
            } catch (err) {
                alert("Kesalahan koneksi API!");
            }
        }

        // 3. MEMBACA & MEMBERSIHKAN LOG NOTIFIKASI
        async function loadNotifications() {
            if (!selectedDeviceId) return;
            try {
                const response = await fetch(`${API_URL}?action=get_notifications&device_id=${selectedDeviceId}`);
                const notifs = await response.json();
                renderNotifications(notifs);
            } catch (err) {
                console.error("Gagal memuat notifikasi:", err);
            }
        }

        function renderNotifications(notifs) {
            const container = document.getElementById('notifications-container');
            if (!notifs || notifs.length === 0) {
                container.innerHTML = `
                    <div class="notif-empty">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <span>Belum ada notifikasi terekam.</span>
                    </div>`;
                return;
            }

            container.innerHTML = '';
            notifs.forEach(n => {
                const time = new Date(parseInt(n.post_time)).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                
                const item = document.createElement('div');
                item.className = 'notif-item';
                item.innerHTML = `
                    <div class="notif-header">
                        <span class="notif-app">${escapeHtml(n.app_name)}</span>
                        <span class="notif-time">${time}</span>
                    </div>
                    <div class="notif-title">${escapeHtml(n.title)}</div>
                    <div class="notif-body">${escapeHtml(n.message)}</div>
                `;
                container.appendChild(item);
            });
        }

        async function clearNotifications() {
            if (!selectedDeviceId) return;
            if (!confirm("Apakah Anda yakin ingin menghapus seluruh log notifikasi perangkat ini?")) return;
            try {
                const response = await fetch(`${API_URL}?action=clear_notifications`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ device_id: selectedDeviceId })
                });
                const result = await response.json();
                if (result.status === 'success') {
                    loadNotifications();
                }
            } catch (err) {
                alert("Gagal menghapus log.");
            }
        }

        // 4. WEBRTC ENGINE (HTTP SIGNALING STATE MACHINE)
        async function startWebRTCCall(mode) {
            if (!selectedDeviceId) return;
            
            // Verifikasi status perangkat harus online
            const activeDev = devices.find(d => d.device_id === selectedDeviceId);
            if (!activeDev || activeDev.is_online != 1) {
                alert("Perangkat anak sedang Offline! Panggilan WebRTC transparan hanya dapat dilakukan saat perangkat online.");
                return;
            }

            updateWebRTCStatus("Mempersiapkan koneksi...");
            
            // Menyiapkan elemen UI
            document.getElementById('video-placeholder-overlay').style.display = 'none';
            document.getElementById('remote-video').style.display = 'block';
            document.getElementById('webrtc-controls').style.display = 'flex';
            
            // Pastikan media session lama ditutup
            hangupCall();

            try {
                // Inisialisasi peer connection
                peerConnection = new RTCPeerConnection(rtcConfig);
                iceCandidatesCollected = [];

                // Daftarkan handler jika stream audio/video masuk
                peerConnection.ontrack = (event) => {
                    updateWebRTCStatus("Komunikasi Aktif - Menerima Stream");
                    const remoteVideo = document.getElementById('remote-video');
                    if (remoteVideo.srcObject !== event.streams[0]) {
                        remoteVideo.srcObject = event.streams[0];
                    }
                };

                // Kumpulkan ICE candidates lokal yang dihasilkan
                peerConnection.onicecandidate = (event) => {
                    if (event.candidate) {
                        iceCandidatesCollected.push(event.candidate);
                        sendIceCandidatesToAPI();
                    }
                };

                // Karena kita bertindak sebagai Penerima Stream (Receiver Only), 
                // kita mendefinisikan transceiver ke status 'recvonly'
                peerConnection.addTransceiver('video', { direction: 'recvonly' });
                peerConnection.addTransceiver('audio', { direction: 'recvonly' });

                // Buat SDP Offer
                const offer = await peerConnection.createOffer();
                await peerConnection.setLocalDescription(offer);

                // Kirim SDP Offer ke PHP API
                updateWebRTCStatus(`Memanggil perangkat (${mode === 'camera' ? 'Kamera' : 'Layar'})...`);
                
                // Tambahkan keterangan mode di sdp offer dengan menyimpan penanda panggilan
                await fetch(`${API_URL}?action=send_offer`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        device_id: selectedDeviceId, 
                        sdp_offer: JSON.stringify({
                            type: offer.type,
                            sdp: offer.sdp,
                            mode: mode // kamera atau screen
                        })
                    })
                });

                // Lakukan polling jawaban (SDP Answer) dari Android
                signalingInterval = setInterval(pollForSDPAnswer, 2000);
                
                // Mulai polling ICE candidates dari klien Android
                candidateInterval = setInterval(pollForClientIceCandidates, 2000);

            } catch (err) {
                console.error("Gagal memulai WebRTC:", err);
                updateWebRTCStatus("Gagal memulai koneksi.");
                hangupCall();
            }
        }

        // Polling SDP Answer yang dikirimkan oleh klien Android
        async function pollForSDPAnswer() {
            if (!selectedDeviceId || !peerConnection) return;
            try {
                const response = await fetch(`${API_URL}?action=get_answer&device_id=${selectedDeviceId}`);
                const data = await response.json();
                
                if (data.sdp_answer) {
                    clearInterval(signalingInterval);
                    signalingInterval = null;
                    
                    updateWebRTCStatus("Menerima respons perangkat...");
                    const answer = JSON.parse(data.sdp_answer);
                    
                    await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
                    updateWebRTCStatus("Negosiasi koneksi berhasil. Menghubungkan...");
                }
            } catch (err) {
                console.error("Gagal polling SDP Answer:", err);
            }
        }

        // Mengirimkan ICE candidates dashboard ke server API
        async function sendIceCandidatesToAPI() {
            if (!selectedDeviceId) return;
            try {
                await fetch(`${API_URL}?action=send_candidates`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        device_id: selectedDeviceId,
                        role: 'dashboard',
                        ice_candidates: JSON.stringify(iceCandidatesCollected)
                    })
                });
            } catch (err) {
                console.error("Gagal mengirim ICE Candidates:", err);
            }
        }

        // Polling ICE Candidates yang dikumpulkan oleh Android
        async function pollForClientIceCandidates() {
            if (!selectedDeviceId || !peerConnection) return;
            try {
                const response = await fetch(`${API_URL}?action=get_candidates&device_id=${selectedDeviceId}&role=dashboard`);
                const data = await response.json();
                
                if (data.ice_candidates && Array.isArray(data.ice_candidates)) {
                    for (const candidate of data.ice_candidates) {
                        try {
                            await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
                        } catch (e) {
                            // Abaikan error format kandidat yang tidak kompatibel di fase negosiasi awal
                        }
                    }
                }
            } catch (err) {
                console.error("Gagal mengambil ICE Candidates klien:", err);
            }
        }

        // Mengakhiri panggilan WebRTC
        async function hangupCall() {
            updateWebRTCStatus("Disconnected");
            
            if (signalingInterval) {
                clearInterval(signalingInterval);
                signalingInterval = null;
            }
            if (candidateInterval) {
                clearInterval(candidateInterval);
                candidateInterval = null;
            }

            if (peerConnection) {
                peerConnection.close();
                peerConnection = null;
            }

            const remoteVideo = document.getElementById('remote-video');
            if (remoteVideo) {
                remoteVideo.srcObject = null;
                remoteVideo.style.display = 'none';
            }

            document.getElementById('video-placeholder-overlay').style.display = 'flex';
            document.getElementById('webrtc-controls').style.display = 'none';

            // Bersihkan sesi signaling di database
            if (selectedDeviceId) {
                try {
                    await fetch(`${API_URL}?action=clear_signaling`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ device_id: selectedDeviceId })
                    });
                } catch (e) {}
            }
        }

        function updateWebRTCStatus(statusText) {
            document.getElementById('webrtc-status').innerText = statusText;
        }

        // Helper fungsi sanitasi input
        function escapeHtml(str) {
            if (!str) return '';
            return str
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // --- PAIRING DEVICE MODAL LOGIC ---
        let pairingCheckInterval = null;

        function openAddDeviceModal() {
            // Generate a random dynamic pairing ID (e.g. FS-XXXXXX)
            const randomCode = 'FS-' + Math.floor(100000 + Math.random() * 900000);
            
            // Build the dynamic server URL
            const currentOrigin = window.location.origin;
            const currentPath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/'));
            const serverUrl = `${currentOrigin}${currentPath}/api.php`;
            
            // Generate QR Code Payload JSON
            const qrPayload = JSON.stringify({
                device_id: randomCode,
                server_url: serverUrl
            });
            
            // Set values in UI
            document.getElementById('connection-code-text').innerText = randomCode;
            
            // Set dynamic QR code image via free qrserver API
            const qrApiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=${encodeURIComponent(qrPayload)}`;
            document.getElementById('qr-code-img').src = qrApiUrl;
            
            // Hide success screen and show modal
            document.getElementById('pairing-success-view').style.display = 'none';
            document.getElementById('add-device-modal').style.display = 'flex';
            
            // Clear any old pairing check interval
            if (pairingCheckInterval) clearInterval(pairingCheckInterval);
            
            // Start AJAX Polling to check if this device gets registered in the database
            pairingCheckInterval = setInterval(async () => {
                try {
                    const response = await fetch(`${API_URL}?action=check_device_registered&device_id=${randomCode}`);
                    const data = await response.json();
                    
                    if (data.registered === true) {
                        // Pairing SUCCESS!
                        clearInterval(pairingCheckInterval);
                        pairingCheckInterval = null;
                        
                        // Show success checkmark panel
                        document.getElementById('pairing-success-view').style.display = 'flex';
                        
                        // Refresh device lists after 2.5 seconds, and close modal
                        setTimeout(() => {
                            closeAddDeviceModal();
                            loadDevices();
                        }, 2500);
                    }
                } catch (e) {
                    console.error("Gagal memeriksa status pairing:", e);
                }
            }, 2000);
        }

        function closeAddDeviceModal() {
            document.getElementById('add-device-modal').style.display = 'none';
            if (pairingCheckInterval) {
                clearInterval(pairingCheckInterval);
                pairingCheckInterval = null;
            }
        }
    </script>

    <!-- MODAL POPUP TAMBAH PERANGKAT (GLASSMORPHISM) -->
    <div id="add-device-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content glass-card animate-fade-in">
            <div class="modal-header">
                <h2>Hubungkan Perangkat Baru</h2>
                <button class="round-control-btn" id="btn-close-modal" title="Tutup">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <div class="modal-body" style="text-align: center; padding: 1.5rem 0; position: relative;">
                <div class="qr-container" style="background: white; padding: 12px; display: inline-block; border-radius: 12px; margin-bottom: 1rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);">
                    <!-- QR Code Generated Dynamically -->
                    <img id="qr-code-img" src="" alt="QR Code Koneksi" style="width: 180px; height: 180px; display: block;" />
                </div>
                
                <div class="connection-code-box" style="margin-bottom: 1.5rem;">
                    <span style="font-size: 0.85rem; color: var(--text-muted); display: block; margin-bottom: 0.25rem; font-weight: 500;">KODE KONEKSI MANUAL</span>
                    <strong id="connection-code-text" style="font-family: monospace; font-size: 1.75rem; color: #0284c7; letter-spacing: 2px;">FS-XXXXXX</strong>
                </div>

                <div class="modal-instructions" style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; max-width: 320px; margin: 0 auto; padding: 0 1rem; text-align: left;">
                    <p style="margin: 0.4rem 0;">1. Buka aplikasi <strong>FamilySync Klien</strong> di HP anak.</p>
                    <p style="margin: 0.4rem 0;">2. Klik <strong>PINDAI QR CODE DASHBOARD</strong> di HP anak dan arahkan ke QR Code di atas.</p>
                    <p style="margin: 0.4rem 0;">3. Atau masukkan <strong>Kode Koneksi</strong> di atas secara manual di kolom input HP anak.</p>
                </div>

                <!-- SUCCESS STATUS VIEW -->
                <div id="pairing-success-view" style="display: none; position: absolute; inset: 0; background: #0f172a; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 0 0 16px 16px; transition: all 0.3s ease; z-index: 10;">
                    <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 1.25rem; border-radius: 50%; margin-bottom: 1rem; border: 2px solid #10b981;">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="animate-scale">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <h3 style="color: #10b981; font-size: 1.25rem; margin-bottom: 0.25rem; font-weight: 600;">Koneksi Sukses!</h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Perangkat anak telah terdaftar dan terhubung.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
