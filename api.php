<?php
// i:/My Drive/PARENTAL CONTROL/web/api.php
// API Endpoint tunggal untuk menangani request dari Android Client dan Dashboard Web

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    // ----------------------------------------------------
    // API UNTUK CLIENT ANDROID
    // ----------------------------------------------------

    case 'register_device':
        // Mendaftarkan atau memperbarui status perangkat (baterai, nama, online)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $deviceId = $input['device_id'] ?? '';
        $deviceName = $input['device_name'] ?? 'Android Device';
        $batteryLevel = intval($input['battery_level'] ?? 100);

        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        // Simpan perangkat ke db
        $stmt = $pdo->prepare("INSERT INTO devices (device_id, device_name, battery_level, last_seen) 
            VALUES (:id, :name, :battery, NOW()) 
            ON DUPLICATE KEY UPDATE device_name = :name, battery_level = :battery, last_seen = NOW()");
        $stmt->execute([
            'id' => $deviceId,
            'name' => $deviceName,
            'battery' => $batteryLevel
        ]);

        // Ambil status kunci perangkat
        $stmt = $pdo->prepare("SELECT is_locked FROM devices WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);
        $device = $stmt->fetch();

        echo json_encode([
            "status" => "success",
            "is_locked" => (int)($device['is_locked'] ?? 0)
        ]);
        break;

    case 'send_notification':
        // Android client mengirimkan notifikasi baru ke database
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $deviceId = $input['device_id'] ?? '';
        $appName = $input['app_name'] ?? 'System';
        $title = $input['title'] ?? '';
        $message = $input['message'] ?? '';
        $postTime = $input['post_time'] ?? time() * 1000; // Epoch milliseconds

        if (empty($deviceId) || empty($message)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID and message are required"]);
            exit;
        }

        // Pastikan perangkat terdaftar
        $stmt = $pdo->prepare("INSERT IGNORE INTO devices (device_id, device_name) VALUES (:id, 'Android Device')");
        $stmt->execute(['id' => $deviceId]);

        // Simpan notifikasi
        $stmt = $pdo->prepare("INSERT INTO notifications (device_id, app_name, title, message, post_time) 
            VALUES (:device_id, :app_name, :title, :message, :post_time)");
        $stmt->execute([
            'device_id' => $deviceId,
            'app_name' => $appName,
            'title' => $title,
            'message' => $message,
            'post_time' => $postTime
        ]);

        echo json_encode(["status" => "success"]);
        break;

    case 'check_lock':
        // Android client melakukan polling untuk mengecek apakah harus dikunci/dibuka
        $deviceId = $_GET['device_id'] ?? '';
        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        // Update last_seen untuk menjaga status online
        $stmt = $pdo->prepare("UPDATE devices SET last_seen = NOW() WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);

        $stmt = $pdo->prepare("SELECT is_locked FROM devices WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);
        $device = $stmt->fetch();

        echo json_encode([
            "is_locked" => (int)($device['is_locked'] ?? 0)
        ]);
        break;

    case 'check_device_registered':
        // Cek apakah perangkat dengan device_id tertentu sudah sukses terdaftar di tabel devices
        $deviceId = $_GET['device_id'] ?? '';
        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT COUNT(*) AS count FROM devices WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);
        $row = $stmt->fetch();
        $isRegistered = intval($row['count'] ?? 0) > 0;

        echo json_encode([
            "registered" => $isRegistered
        ]);
        break;

    // ----------------------------------------------------
    // API UNTUK DASHBOARD WEB
    // ----------------------------------------------------

    case 'get_devices':
        // Dashboard mengambil daftar seluruh perangkat beserta status online/offline
        $stmt = $pdo->query("SELECT *, (last_seen > DATE_SUB(NOW(), INTERVAL 12 SECOND)) AS is_online FROM devices ORDER BY last_seen DESC");
        $devices = $stmt->fetchAll();
        echo json_encode($devices);
        break;

    case 'lock_device':
        // Dashboard meminta penguncian perangkat
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $deviceId = $input['device_id'] ?? '';

        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE devices SET is_locked = 1 WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);

        echo json_encode(["status" => "success", "message" => "Device lock command sent"]);
        break;

    case 'unlock_device':
        // Dashboard membuka kunci perangkat
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $deviceId = $input['device_id'] ?? '';

        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE devices SET is_locked = 0 WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);

        echo json_encode(["status" => "success", "message" => "Device unlock command sent"]);
        break;

    case 'get_notifications':
        // Dashboard memuat log notifikasi dari database
        $deviceId = $_GET['device_id'] ?? '';
        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT * FROM notifications WHERE device_id = :id ORDER BY id DESC LIMIT 50");
        $stmt->execute(['id' => $deviceId]);
        $notifications = $stmt->fetchAll();

        echo json_encode($notifications);
        break;

    case 'clear_notifications':
        // Dashboard membersihkan log notifikasi
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $deviceId = $input['device_id'] ?? '';

        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM notifications WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);

        echo json_encode(["status" => "success", "message" => "Notification logs cleared"]);
        break;

    // ----------------------------------------------------
    // WEBRTC SIGNALING API
    // ----------------------------------------------------

    case 'send_offer':
        // Dashboard mengirim WebRTC SDP Offer
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $deviceId = $input['device_id'] ?? '';
        $offer = $input['sdp_offer'] ?? '';

        if (empty($deviceId) || empty($offer)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID and SDP Offer are required"]);
            exit;
        }

        // Daftarkan dulu signaling record atau perbarui jika sudah ada
        $stmt = $pdo->prepare("INSERT INTO signaling (device_id, sdp_offer, sdp_answer, ice_candidates_client, ice_candidates_dashboard) 
            VALUES (:id, :offer, NULL, NULL, NULL) 
            ON DUPLICATE KEY UPDATE sdp_offer = :offer, sdp_answer = NULL, ice_candidates_client = NULL, ice_candidates_dashboard = NULL");
        $stmt->execute([
            'id' => $deviceId,
            'offer' => $offer
        ]);

        echo json_encode(["status" => "success", "message" => "WebRTC offer stored"]);
        break;

    case 'get_offer':
        // Android client mengambil WebRTC SDP Offer
        $deviceId = $_GET['device_id'] ?? '';
        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT sdp_offer FROM signaling WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);
        $row = $stmt->fetch();

        echo json_encode([
            "sdp_offer" => $row['sdp_offer'] ?? null
        ]);
        break;

    case 'send_answer':
        // Android client mengirim WebRTC SDP Answer setelah menerima offer
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $deviceId = $input['device_id'] ?? '';
        $answer = $input['sdp_answer'] ?? '';

        if (empty($deviceId) || empty($answer)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID and SDP Answer are required"]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE signaling SET sdp_answer = :answer WHERE device_id = :id");
        $stmt->execute([
            'id' => $deviceId,
            'answer' => $answer
        ]);

        echo json_encode(["status" => "success", "message" => "WebRTC answer stored"]);
        break;

    case 'get_answer':
        // Dashboard mengambil WebRTC SDP Answer yang dikirim dari Android
        $deviceId = $_GET['device_id'] ?? '';
        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT sdp_answer FROM signaling WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);
        $row = $stmt->fetch();

        echo json_encode([
            "sdp_answer" => $row['sdp_answer'] ?? null
        ]);
        break;

    case 'send_candidates':
        // Mengirim ICE Candidates (baik dari Dashboard atau Android Client)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $deviceId = $input['device_id'] ?? '';
        $role = $input['role'] ?? ''; // 'client' atau 'dashboard'
        $candidates = $input['ice_candidates'] ?? ''; // format string JSON dari list of candidates

        if (empty($deviceId) || empty($role) || empty($candidates)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID, role, and candidates are required"]);
            exit;
        }

        if ($role === 'client') {
            $stmt = $pdo->prepare("UPDATE signaling SET ice_candidates_client = :candidates WHERE device_id = :id");
        } else {
            $stmt = $pdo->prepare("UPDATE signaling SET ice_candidates_dashboard = :candidates WHERE device_id = :id");
        }

        $stmt->execute([
            'id' => $deviceId,
            'candidates' => $candidates
        ]);

        echo json_encode(["status" => "success", "message" => "ICE candidates updated"]);
        break;

    case 'get_candidates':
        // Mengambil ICE Candidates (baik client atau dashboard)
        $deviceId = $_GET['device_id'] ?? '';
        $role = $_GET['role'] ?? ''; // 'client' untuk mengambil kandidat dari dashboard, 'dashboard' untuk dari client

        if (empty($deviceId) || empty($role)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID and role are required"]);
            exit;
        }

        $stmt = $pdo->prepare("SELECT ice_candidates_client, ice_candidates_dashboard FROM signaling WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);
        $row = $stmt->fetch();

        if ($role === 'client') {
            // Client ingin mendapatkan kandidat milik dashboard
            $candidates = $row['ice_candidates_dashboard'] ?? null;
        } else {
            // Dashboard ingin mendapatkan kandidat milik client
            $candidates = $row['ice_candidates_client'] ?? null;
        }

        echo json_encode([
            "ice_candidates" => $candidates ? json_decode($candidates, true) : null
        ]);
        break;

    case 'clear_signaling':
        // Dashboard membersihkan data signaling setelah pemutusan panggilan
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["error" => "Method not allowed"]);
            exit;
        }

        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $deviceId = $input['device_id'] ?? '';

        if (empty($deviceId)) {
            http_response_code(400);
            echo json_encode(["error" => "Device ID is required"]);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE signaling SET sdp_offer = NULL, sdp_answer = NULL, ice_candidates_client = NULL, ice_candidates_dashboard = NULL WHERE device_id = :id");
        $stmt->execute(['id' => $deviceId]);

        echo json_encode(["status" => "success", "message" => "Signaling cleared"]);
        break;

    default:
        http_response_code(404);
        echo json_encode(["error" => "Action not found"]);
        break;
}
?>
