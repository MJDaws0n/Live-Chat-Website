# PHP Easy API System
## Usage
Use the following code to create a require the API system
```PHP
use Net\MJDawson\ApiSystem\API;
use Net\MJDawson\ApiSystem\PageManager;
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'apiSystem'.DIRECTORY_SEPARATOR.'main.php';
```

Create the instance
```php
// Example pages - just one but you can add as many as you want
// All values are required even if not used
$pages = [
    [
        'name' => 'Public Page', // Can be whatever you want is purley for display
        'security' => PageManager::SECURITY_NOT_ACCESSIBLE, // These options are avaiable in Security options
        'file' => dirname(__FILE__).DIRECTORY_SEPARATOR.'testingAPI.php', // Can be anywhere and is relate to the file being ran so in most cases dirname will have to be used
        'API_KEYS' => [], // Any API keys you want to allow for the page
        'roles' => [],
        'subscriptions' => [],
        'users' => [], // A list of the ID's of the users that are aloud on the page
        'uri' => '/api/example', // The URI of the API page
    ]
];

// Initialize the API management
$api = new API($pages);
```

## Get the API page
```php
$api->get($user);
```

## Example user
```php
$user = [
    'id' => 1,
    'roles' => [],
    'admin' => true,
    'mfa' => false,
    'subscriptions' => [],
];
```

Example function to get the current URI of page, you can use anyfunction though
```php
function getCurrentUri() {
    $uri = $_SERVER['REQUEST_URI']; // Get the request URI
    $uri = strtok($uri, '?'); // Remove query string if exists
    return $uri;
}
```

## Security options

| Constant Name                              | Security Level | Description                                                                             |
|--------------------------------------------|----------------|-----------------------------------------------------------------------------------------|
| `SECURITY_TEMP_UNAVAILABLE`                | -2             | The page is temporarily unavailable. Access is restricted until the page is restored. |
| `SECURITY_NOT_ACCESSIBLE`                  | -1             | The page cannot be accessed by any user.                                              |
| `SECURITY_PUBLIC_ACCESS`                   | 0              | The page can be accessed by anyone without any restrictions.                           |
| `SECURITY_LOGGED_IN_ONLY`                  | 1              | The page can be accessed only by users who are logged in.                              |
| `SECURITY_ADMIN_ACCESS`                    | 2              | The page can be accessed only by users with admin privileges.                          |
| `SECURITY_API_KEY_ONLY`                    | 3              | The page can be accessed only with a valid API key.                                   |
| `SECURITY_API_OR_LOGGED_IN`                | 4              | The page can be accessed either with a valid API key or by logged-in users.           |
| `SECURITY_ADMIN_OR_API`                    | 5              | The page can be accessed by either an admin user or using a valid API key.            |
| `SECURITY_SPECIFIC_ROLES_OR_API`          | 6              | The page can be accessed by specific user roles or with a valid API key.              |
| `SECURITY_SPECIFIC_USERS_OR_API`          | 7              | The page can be accessed only by specific users or with a valid API key.              |
| `SECURITY_MFA_LOGGED_IN`                   | 8              | The page can be accessed by logged-in users who have enabled multi-factor authentication (MFA). |
| `SECURITY_INTERNAL_API_ONLY`              | 9              | The page can be accessed only by internal API calls.                                   |
| `SECURITY_SUBSCRIPTION_OR_API`             | 10             | The page can be accessed by users with an active subscription or with a valid API key.|
| `SECURITY_SPECIFIC_ROLES_MATCH`           | 11             | The page can be accessed only by specific users where roles match exactly or with a valid API key. |