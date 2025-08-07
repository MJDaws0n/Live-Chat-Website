<?php
namespace Net\MJDawson\LiveChat;

use Exception;
use Net\MJDawson\ApiSystem\API;
use Net\MJDawson\ApiSystem\PageManager;
use Net\MJDawson\AccountSystem\Accounts;
use Net\MJDawson\AccountSystem\User;
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'ApiSystem'.DIRECTORY_SEPARATOR.'main.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'AccountsSystem'.DIRECTORY_SEPARATOR.'main.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'pages.php';

class kernal{
    private $conn;
    private $user;

    public function __construct() {
        $apiPages = [
            [
                'name' => 'Login',
                'security' => PageManager::SECURITY_PUBLIC_ACCESS,
                'file' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'api'.DIRECTORY_SEPARATOR.'login.php',
                'API_KEYS' => [],
                'roles' => [],
                'subscriptions' => [],
                'users' => [],
                'uri' => '/api/user/login',
                'kernal' => $this
            ],
            [
                'name' => 'Create Account',
                'security' => PageManager::SECURITY_ADMIN_ACCESS,
                'file' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'api'.DIRECTORY_SEPARATOR.'create.php',
                'API_KEYS' => [],
                'roles' => [],
                'subscriptions' => [],
                'users' => [],
                'uri' => '/api/user/create',
                'kernal' => $this
            ],
            [
                'name' => 'Get Messages',
                'security' => PageManager::SECURITY_LOGGED_IN_ONLY,
                'file' => dirname(__FILE__) . DIRECTORY_SEPARATOR . 'api'.DIRECTORY_SEPARATOR.'getMessages.php',
                'API_KEYS' => [],
                'roles' => [],
                'subscriptions' => [],
                'users' => [],
                'uri' => '/api/messages',
                'kernal' => $this
            ]
        ];

        $api = new API($apiPages);

        // Get the user
        $database = new Database;
        $this->conn = $database->conn;
        new Accounts($database->conn, 'accounts');

        // Get the users session
        if(isset($_COOKIE['session'])){
            $session = $_COOKIE['session'];
        } else{
            $session = null;
        }
        $user = new User($database->conn, 'accounts', null, null, $session);
        $this->user = $user;

        if(str_starts_with($this->getCurrentUri(), '/api')){
            header('Content-Type: application/json; charset=utf-8');
            $newUser = $user->get();
            if($newUser !== null){
                $newUser['roles'] = [];
                $newUser['admin'] = false;
                if(isset($newUser['additional_values']['admin']) && $newUser['additional_values']['admin'] == 'true'){
                    $newUser['admin'] = true;
                }
                $newUser['mfa'] = false;
                $newUser['subscriptions'] = [];
            }

            $api->get($newUser);
            $this->quit();
        }
    }
    public function showLogin(){
        $pages = new Pages;
        echo $pages->get('login');
    }
    public function showChat(){
        $pages = new Pages;
        echo $pages->get('chat');
    }

    // Does not create an account, just selects a new user as the current user
    public function newUser($username = null, $password = null, $session = null, $id = null){
        $user = new User($this->conn, 'accounts', $username, $password, $session, $id);
        $this->user = $user;

        return $this->user;
    }
    public function user(){
        return $this->user;
    }
    public function getCurrentUri() {
        $uri = $_SERVER['REQUEST_URI']; // Get the request URI
        $uri = strtok($uri, '?'); // Remove query string if exists
        return $uri;
    }
    public function getMessages(){
        $sql = "SELECT * FROM `messages` WHERE `visible` = '1' ORDER BY `id` ASC LIMIT 100 ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();

        // Initialize an array to hold all messages
        $messages = [];
        
        if ($result->num_rows > 0) {
            // Fetch all rows and store them in the messages array
            while ($row = $result->fetch_assoc()) {
                $messages[] = $row;
            }
        }
        
        $stmt->close();

        return $messages;
    }

    public function quit(){
        $this->conn->close();
        exit();
    }
}
class Database{
    public $conn;
    public function __construct() {
        $env = $this->loadEnv(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env');
        if (!isset($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME'])) {
            throw new Exception('Database credentials not found in .env file');
        }
        $this->conn = new \mysqli($env['DB_HOST'], $env['DB_USER'], $env['DB_PASS'], $env['DB_NAME']);
    }

    private function loadEnv($filePath) {
        $env = [];
    
        if (!file_exists($filePath)) {
            throw new Exception('No .env found, please re-name the .env_example to .env');
        }
    
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
    
            list($key, $value) = explode('=', $line, 2);
            $value = trim($value, '"\' ');
            $env[trim($key)] = $value;
        }
    
        return $env;
    }
}