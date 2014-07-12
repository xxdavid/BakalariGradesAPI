BakalariGradesAPI
=================
An unofficial PHP API for [Bakaláři](http://www.bakalari.cz/webapp.aspx) -- Czech school administration application.

##Requirements
- PHP 5.3 or higher
- cURL

##Instalation
Via [Composer](http://getcomposer.org/):
```json
{
    "require": {
        "xxdavid/bakalarigradesapi": "dev-master"
    }
}
```

##Usage
```php
$bakalari = new BakalariGradesAPI($username, $password, $host, $cookieFile);
$subjects = $bakalari->getGrades();
```
For more information see [example.php](example.php)

##Supported versions
These versions are tested and  **should be** compatible:

- lbver 17.5.2012
- lbver 31.8.2012
- lbver 2.9.2013

*lbver* is name of class of an element on the login page.
```html
<div class="lbver">2.9.2013</div>
```
If your version isn't compatible please don't be afraid of implementing it. Just fork and send me a pull request with your awesome work.

##License: MIT
See LICENSE
