<?php
namespace Net\MJDawson\ApiSystem;
use Exception;

class API{
    private $pages;

    /**
     * Class constructor for handling page security.
     *
     * @param Page[] $pages An array of pages where each page is an associative array with the following keys:
     * - name (string): Name of the page.
     * - security (int): Security level (-2 to 10).
     * - file (string): Page directory to include when accessed.
     * - API_KEYS (string[]): List of API keys that can access the page.
     * - roles (string[]): List of user roles that can access the page.
     * - users (string[]): List of users that can access the page.
     * - subscriptions (string[]): List of subscriptions that can access the page.
     * - uri (string): The URI of the page (e.g., /login).
     *
     * @throws Exception If any page is missing required information.
     */
    public function __construct($pages){
        // Page security works like the following:
        // -2 — Page temperaraly unavailable
        // -1 — Page cannot be accessed
        // 0 — Page can be accessed by anyone
        // 1 — Page can be accessed by logged in users
        // 2 — Page can be accessed by admins
        // 3 — Page can be accessed with an API key only
        // 4 — Page can be accessed with an API key or as a logged in user
        // 5 — Page can be accessed by an admin or using an API key
        // 6 — Page can be accessed by specific user roles or API Key
        // 7 — Page can be accessed only by specific users or API Key
        // 8 — Page can be accessed by logged in users with multi-factor authentication (MFA)
        // 9 — Page can be accessed by internal API calls only
        // 10 — Page can be accessed by users with an active subscription or API Key
        // 11 — Page can be accessed by specific user where roles match exactly or API Key

        // Pages variable
        // Each page variable must include the following:
        // — name (name of the page) — string
        // — security (security of the page) — integer (-2 to 10)
        // — file (page directory to include when the page is accessed) — string
        // — API_KEYS (list of API keys that can access the page) — array
        // — roles (list of user roles that can access the page) — array
        // — users (list of users that can access the page) — array
        // — users (list of subscriptions that can access the page) — array
        // — uri (the URI of the page e.g. /login) — string


        foreach($pages as $page) {
            if (!isset($page['name']) || !isset($page['security']) || !isset($page['file']) || !isset($page['API_KEYS']) || !isset($page['roles']) || !isset($page['users']) || !isset($page['uri']) || !isset($page['subscriptions'])) {
                if(isset($page['name'])){
                    throw new Exception("Page '".$page['name']."' is missing required information");
                } else{
                    throw new Exception("Page 'unknown' is missing required information");
                }
                return;
            }
        }

        $this->pages = $pages;
    }
    /**
     * Retrieve user information.
     *
     * @param array|null $user An associative array representing user information with the following keys:
     * - id (int): User ID.
     * - roles (string[]): User roles.
     * - admin (bool): Indicates if the user is an admin.
     * - mfa (bool): Indicates if the user has multi-factor authentication.
     * - subscriptions (string[]): User subscriptions.
     *
     * @return mixed The user data or null if user is not provided.
     */
    public function get($user = null) {
        // User variable must include the following:
        // — id (user ID) — integer
        // — roles (users roles) — array
        // — admin (is the user an admin) — boolean
        // — mfa (has multi-factor authentication) — boolean
        // — subscriptions (user subscriptions) — array

        $page = null;
        foreach($this->pages as $localPage) {
            if ($localPage['uri'] == $this->getCurrentUri()) {
                $page = $localPage;
                break;
            }
        }
        
        if ($page === null) {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Page not found']);
            return;
        }
        if ($user !== null && (!isset($user['id']) || !isset($user['roles']) || !isset($user['admin']) || !isset($user['mfa']) || !isset($user['subscriptions']))) {
            throw new Exception("User is missing required information");
            return;
        }
        if($page['security'] == -2){
            http_response_code(503);
            header('Retry-After: 3600');
            echo json_encode(['status' => 'error', 'message' => 'Service temporarily unavailable']);
            return;
        }
        if($page['security'] == -1){
            http_response_code(503);
            echo json_encode(['status' => 'error', 'message' => 'Service unavailable']);
            return;
        }
        if($page['security'] == 0){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 1 && $user !== null){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 2 && $user !== null && isset($user['admin']) && $user['admin']){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 3 && isset($_SERVER['HTTP_API_KEY']) && in_array($_SERVER['HTTP_API_KEY'], $page['API_KEYS'])){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 4 && ((isset($_SERVER['HTTP_API_KEY']) && in_array($_SERVER['HTTP_API_KEY'], $page['API_KEYS'])) || $user !== null)){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 5 && ((isset($_SERVER['HTTP_API_KEY']) && in_array($_SERVER['HTTP_API_KEY'], $page['API_KEYS'])) || ($user !== null && isset($user['admin']) && $user['admin']))){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 6 && ((isset($_SERVER['HTTP_API_KEY']) && in_array($_SERVER['HTTP_API_KEY'], $page['API_KEYS'])) ||
        ($user !== null && $user['roles'] !== null && $page['roles'] !== null && !empty(array_intersect($page['roles'], $user['roles']))))){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 7 && ((isset($_SERVER['HTTP_API_KEY']) && in_array($_SERVER['HTTP_API_KEY'], $page['API_KEYS'])) ||
        ($user !== null && $user['id'] !== null && $page['users'] !== null && in_array($user['id'], $page['users'])))){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 8 && $user !== null && $user['mfa']){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 9 && $this->isInternalRequest()){
            $this->servePage($page);
            return;
        }
        if($page['security'] == 10 && ((isset($_SERVER['HTTP_API_KEY']) && in_array($_SERVER['HTTP_API_KEY'], $page['API_KEYS'])) ||
        ($user !== null && $user['subscriptions'] !== null && $page['subscriptions'] !== null && !empty(array_intersect($page['subscriptions'], $user['subscriptions']))))){
            $this->servePage($page);
            return;
        }
        if ($page['security'] == 11 && 
        ((isset($_SERVER['HTTP_API_KEY']) && in_array($_SERVER['HTTP_API_KEY'], $page['API_KEYS'])) ||
        ($user !== null && $user['roles'] !== null && $page['roles'] !== null && 
        empty(array_diff($page['roles'], $user['roles']))))) {
        $this->servePage($page);
            return;
        }    
        

        http_response_code(403);
        echo json_encode(['status' => 'error', 'message' => 'Permission denied']);
        return;
    }
    private function servePage($page) {
        include_once $page['file'];
        return;
    }
    private $isInternal = null; // For caching the IP address
    private function isInternalRequest() {
        if ($this->isInternal !== null) return $this->isInternal;
    
        $internalRanges = ['10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.0/8'];
        $ip = $_SERVER['REMOTE_ADDR'];
    
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
    
        foreach ($internalRanges as $range) {
            list($rangeBase, $netmask) = explode('/', $range);
            if ($this->ipInRange($ip, $rangeBase, $netmask)) {
                $this->isInternal = true;
                return true;
            }
        }
    
        $this->isInternal = false;
        return false;
    }    
    private function ipInRange($ip, $rangeBase, $netmask) {
        $ipDec = ip2long($ip);
        $rangeDec = ip2long($rangeBase);
        $maskDec = -1 << (32 - $netmask);
        
        return ($ipDec & $maskDec) == ($rangeDec & $maskDec);
    }
    private function getCurrentUri() {
        $uri = $_SERVER['REQUEST_URI']; // Get the request URI
        $uri = strtok($uri, '?'); // Remove query string if exists
        return $uri;
    }
}
class PageManager {
    // Define security levels as constants
    const SECURITY_TEMP_UNAVAILABLE = -2;
    const SECURITY_NOT_ACCESSIBLE = -1;
    const SECURITY_PUBLIC_ACCESS = 0;
    const SECURITY_LOGGED_IN_ONLY = 1;
    const SECURITY_ADMIN_ACCESS = 2;
    const SECURITY_API_KEY_ONLY = 3;
    const SECURITY_API_OR_LOGGED_IN = 4;
    const SECURITY_ADMIN_OR_API = 5;
    const SECURITY_SPECIFIC_ROLES_OR_API = 6;
    const SECURITY_SPECIFIC_USERS_OR_API = 7;
    const SECURITY_MFA_LOGGED_IN = 8;
    const SECURITY_INTERNAL_API_ONLY = 9;
    const SECURITY_SUBSCRIPTION_OR_API = 10;
    const SECURITY_SPECIFIC_ROLES_MATCH = 11;

    /**
     * Get a description for a given security level.
     *
     * @param int $securityLevel The security level constant.
     * @return string The description of the security level.
     */
    public static function getSecurityDescription(int $securityLevel): string {
        switch ($securityLevel) {
            case self::SECURITY_TEMP_UNAVAILABLE:
                return "Page temporarily unavailable";
            case self::SECURITY_NOT_ACCESSIBLE:
                return "Page cannot be accessed";
            case self::SECURITY_PUBLIC_ACCESS:
                return "Page can be accessed by anyone";
            case self::SECURITY_LOGGED_IN_ONLY:
                return "Page can be accessed by logged in users";
            case self::SECURITY_ADMIN_ACCESS:
                return "Page can be accessed by admins";
            case self::SECURITY_API_KEY_ONLY:
                return "Page can be accessed with an API key only";
            case self::SECURITY_API_OR_LOGGED_IN:
                return "Page can be accessed with an API key or as a logged in user";
            case self::SECURITY_ADMIN_OR_API:
                return "Page can be accessed by an admin or using an API key";
            case self::SECURITY_SPECIFIC_ROLES_OR_API:
                return "Page can be accessed by specific user roles or API Key";
            case self::SECURITY_SPECIFIC_USERS_OR_API:
                return "Page can be accessed only by specific users or API Key";
            case self::SECURITY_MFA_LOGGED_IN:
                return "Page can be accessed by logged in users with multi-factor authentication (MFA)";
            case self::SECURITY_INTERNAL_API_ONLY:
                return "Page can be accessed by internal API calls only";
            case self::SECURITY_SUBSCRIPTION_OR_API:
                return "Page can be accessed by users with an active subscription or API Key";
            case self::SECURITY_SPECIFIC_ROLES_MATCH:
                return "Page can be accessed by specific users where roles match exactly or API Key";
            default:
                return "Unknown security level";
        }
    }
}
