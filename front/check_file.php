<?php
$file = 'img/property/metro-plus.png';
echo "Checking file: $file\n";
if (file_exists($file)) {
    echo "File exists!\n";
    echo "Permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "\n";
    echo "Owner: " . fileowner($file) . "\n";
} else {
    echo "File does NOT exist!\n";
}
?>