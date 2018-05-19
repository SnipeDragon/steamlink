# SteamLink
[![GitHub release](https://img.shields.io/github/release/snipedragon/steamlink.svg?style=plastic)](https://packagist.org/packages/snipedragon/steamlink)
[![PHP from Packagist](https://img.shields.io/packagist/php-v/snipedragon/steamlink.svg?style=plastic)](https://packagist.org/packages/snipedragon/steamlink)
[![GitHub license](https://img.shields.io/github/license/snipedragon/steamlink.svg?style=plastic)](https://github.com/SnipeDragon/steamlink/blob/master/LICENSE)
[![Packagist](https://img.shields.io/packagist/dt/snipedragon/steamlink.svg?style=plastic)](https://packagist.org/packages/snipedragon/steamlink)
[![GitHub issues](https://img.shields.io/github/issues/snipedragon/steamlink.svg?style=plastic)](https://github.com/snipedragon/steamlink/issues)


Provides authentication through Steam's OpenID and returns an object for the authenticated user.

I could not find a solution that met my needs, so I made my own, the goals I set for myself are as follows:

1.  Generate a Login Button/URL using Steam's provided login button graphics.
2.  Begin a session (optional) and return a user object on login.
3.  Have the ability to refresh a user object by providing a steamid.

## Getting Started - Composer

Add this to your `composer.json` file, in the require object:

```javascript
"snipedragon/steamlink": "1.*"
```

After that, run `composer install` to install the package.

#### OR

```javascript
composer require snipedragon/steamlink:1.*
```

## Example

```php
require __DIR__ . '/vendor/autoload.php';

$options = array(
    'apiKey' => 'YOUR-API-KEY-HERE', // Steam API KEY
    'domainName' => 'https://your-site.net', // Shown on the Steam Login page to your users.
    'loginRedirect' => 'https://your-site.net/index.php?page=SteamLink&action=Login', // Returns user to this page on login.
    'logoutRedirect' => 'https://your-site.net/index.php?page=SteamLink&action=Logout', // Returns user to this page on logout.
    'startSession' => false //true to start session, false to only validate and return a steam user object.
);

$steamlink = new SnipeDragon\SteamLink($options);

echo "<p>Click on the following button to login and authenticate yourself through Steam:</p>";
echo $steamlink->loginButton("rectangle"); //Can be "rectangle" or "square".
```
