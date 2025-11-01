<?php
// includes/helpers.php
function upload_file($file, $dest_folder = __DIR__ . '/../assets/uploads/') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid() . '.' . $ext;
    if (!is_dir($dest_folder)) mkdir($dest_folder, 0755, true);
    move_uploaded_file($file['tmp_name'], $dest_folder . $name);
    return 'assets/uploads/' . $name;
}

function flash($msg = null) {
    if ($msg === null) {
        if (isset($_SESSION['flash'])) { $m = $_SESSION['flash']; unset($_SESSION['flash']); return $m; }
        return null;
    } else {
        $_SESSION['flash'] = $msg;
    }
}

function average_student($pdo, $etudiant_id) {
    $stmt = $pdo->prepare("SELECT AVG(note) as avg_note FROM notes WHERE etudiant_id = ?");
    $stmt->execute([$etudiant_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? floatval($row['avg_note']) : 0;
}

function send_notification($pdo, $user_id, $message) {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?,?)");
    $stmt->execute([$user_id, $message]);
}

function send_global_notification($pdo, $role_id, $message) {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role_id = ?");
    $stmt->execute([$role_id]);
    while ($u = $stmt->fetch(PDO::FETCH_ASSOC)) {
        send_notification($pdo, $u['id'], $message);
    }
}
?>
