<?php
header("Content-Type: application/json");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

echo json_encode([
    "success" => true,
    "timestamp" => time(),
    "message" => "Fresh data - no caching",
    "random" => rand(1000, 9999)
]);
?>