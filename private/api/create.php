<?php
// Get the kernal
$kernal = $page['kernal'];

if(!isset($_GET['name'])){
    echo json_encode([ 'status' => 'error', 'message' => "Name value not set" ]);
    $kernal->quit();
}

$code = rand(100000, 999999);

$kernal->user()->create($code, $code, ['name' => $_GET['name']]);

echo json_encode($kernal->user()->get());
$kernal->quit();