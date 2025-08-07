<?php
// Get the kernal
$kernal = $page['kernal'];

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    echo json_encode(['status'=>'error', 'message' => 'Invalid method '.$_SERVER['REQUEST_METHOD']]);
    $kernal->quit();
}

// Fetch the last 100 messages from the database
$messages = $kernal->getMessages();

// Get users name
if(!isset($kernal->user()->get()['additional_values']['name'])){
    throw new Exception('Users name not found');
}

foreach ($messages as &$message) {
    if($message['usr_from'] == $kernal->user()->get()['additional_values']['name']){
        $message['usr_from'] = 'You';
    }
}

echo json_encode(['status'=>'success', 'messages' => $messages , 'username' => $kernal->user()->get()['additional_values']['name'], 'admin' => (isset($kernal->user()->get()['additional_values']['admin']) && $kernal->user()->get()['additional_values']['admin'] == 'true')]);
$kernal->quit();