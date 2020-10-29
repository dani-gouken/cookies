<h3 align="center">Cookies</h3>

<div align="center">

[![Status](https://img.shields.io/badge/status-active-success.svg)]()
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](/LICENSE)


</div>

---

<p align="center">
    A simple php library to manage cookies on PSR7 object
    <br> 
</p>

## 📝 Table of Contents

- [Prerequisites](#prerequisites)
- [Installing](#installing)
- [Testing](#testing)
- [Coding Style](#coding_style)
- [Getting Started](#getting_started)
- [Usage](#usage)
- [Contributing](#contributing)
- [Authors](#authors)


## Prerequisites <a name = "prerequisites"></a>


- PHP 7.3 +
- Composer 


## Installing <a name = "installing"></a>

The recommended way to install is via Composer:


```
composer require phpatom/cookies
```


## Testing Installing <a name = "testing"></a>
 
```
composer test
```

### Coding style <a name = "coding_style"></a>

```
./vendor/bin/phpcs
```

## Getting Started <a name = "getting_started"></a>
### Basic usage 
```php

# create a new router

use Atom\Cookies\Cookie;
use Atom\Cookies\CookieConfig;

$myCookie = Cookie::create("foo","bar")
            ->withDomain("mydomain.com")
            ->withPath("/")
            ->thatExpiresOn("2 days");

$myCookie->applyTo($response); // ResponseInterface

// Cookie default config
CookieConfig::configure()
            ->withDomain("foo.com")
            ->withHttpOnly(true);

//will use default config
$myCookie = new Cookie("foo","bar");
echo $myCookie->getDomain(); // foo.com
echo $myCookie->isHttpOnly(); // true

```
### Read cookies
```php
 $cookies = Cookies::of($request);
 echo $cookies->get("key"); //value 
 echo $cookies->get("badkey",'defaultValue'); // defaultValue
 var_dump($cookies->getCookies("badkey")); // RequestCookie;
 echo $cookies->has("key"); //value boolean

 //also works with responses
 $cookies = Cookies::of($response);
 var_dump($cookies->getCookie("badkey")); // RequestCookie;Cookie

```

## Contributing <a name = "contributing"></a>
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.


## ✍️ Author <a name = "authors"></a>

- [@dani-gouken](https://github.com/dani-gouken) - Idea & Initial work

