<?php
if (isset($_FILES['audio'])) {
    $audio = $_FILES['audio'];
    $targetDir = 'recordings/';
    $targetFile = $targetDir . basename($audio['name']);

    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    if (move_uploaded_file($audio['tmp_name'], $targetFile)) {
        echo 'File uploaded successfully.';
    } else {
        echo 'Error uploading file.';
    }
}
?>