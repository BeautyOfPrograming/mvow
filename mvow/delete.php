<?php
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $filePath = 'recordings/' . $file;

    if (file_exists($filePath)) {
        unlink($filePath);
        echo 'File deleted successfully.';
    } else {
        echo 'File not found.';
    }
}
?>