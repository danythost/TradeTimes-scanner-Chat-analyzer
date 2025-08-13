
<?php
// upload.php
header('Content-Type: application/json');

$targetDir = 'uploads/';
if (!file_exists($targetDir)) mkdir($targetDir);

if ($_FILES['image']) {
    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $ext;
    $path = $targetDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
        echo json_encode(['success' => true, 'path' => $path]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Upload failed']);
    }
}
