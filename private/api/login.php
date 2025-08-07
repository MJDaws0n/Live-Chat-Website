<?php
// Get the kernal
$kernal = $page['kernal'];

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['status'=>'error', 'message' => 'Invalid method '.$_SERVER['REQUEST_METHOD']]);
    $kernal->quit();
}

// Get inputs
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if(!isset($data['login'])){
    echo json_encode(['status'=>'error', 'message' => 'Missing field login']);
    $kernal->quit();
}

// Get the code
$code = (int)$data['login'];

// Check the code is 6 digits long
if (strlen($code) != 6) {
    echo json_encode(['status'=>'error', 'message' => 'Login field length unacceptable']);
    $kernal->quit();
}

// The username and password are the same as the token, the users actual username is stored as an additional value
$user = $kernal->newUser($code, $code);

// Check if the account exists
if($user->get() === null){
    echo json_encode(['status'=>'error', 'message' => 'Invalid code']);
    $kernal->quit();
}

$userData = $user->get();

// Remove the password field
unset($userData['password']);
unset($userData['reset_token']);
echo json_encode(['status' => 'success', 'user' => $userData]);
$kernal->quit();