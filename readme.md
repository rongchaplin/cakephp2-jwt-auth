# CakePHP 2.x JWT Authentication #

This plugin is a CakePHP 2.x Authentication component and view helper for JWT.

## Components ##

* Auth/JwtTokeAuthenticate - A JSON Web Token implementation for CakePHP 2.6.x

## Helpers

* AuthHelper - Utility functions for helping with the authentication of users.

## Requirements ##

* PHP version: PHP 5.3+
* CakePHP version: 2.6 Stable

## Support ##

For support and feature request, please visit the [JWT Authentication Plugin Support](https://github.com/t73biz/cakephp2-jwt-auth/issues) section.

## License ##

Copyright 2011 - 2014, [Florian Krämer](http://github.com/burzum)
Copyright 2015, [Ronald Chaplin](http://github.com/t73biz)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.

## Copyright ###

Copyright 2011 - 2014
Florian Krämer
http://github.com/burzum

Copyright 2015
Ronald Chaplin
http://github.com/t73biz

## Version ##

1.0.6


## Installation ##

```composer require t73biz/cakephp2-jwt-auth 1.0.6```

This will install into the Plugin directory (in the ```JwtAuth``` folder). To run the tests, simply navigate to your webroot/test.php and follow the links for the test cases for the Authentication Adapter.

## Usage ##

### Configuration ###

You can either declare this in your Controller's ```$components``` array, or on the fly in an ```action``` (if you need to load any configuration values, which you can't do when declaring in the ```$components``` array, for example).

```
public $components = array(
    'Auth' => array(
        'authenticate' => array(
            'JwtAuth.JwtToken' => array(
                'fields' => array(
                    'username' => 'username',
                    'password' => 'password',
                    'token' => 'public_key',
                ),
                'parameter' => '_token',
                'userModel' => 'User',
                'scope' => array('User.active' => 1),
                'pepper' => 'sneezing',
            ),
        ),
    ),
);
```
Or
```
$this->Auth->authenticate['JwtAuth.JwtToken'] = array(
    'fields' => array(
        'username' => 'username',
        'password' => 'password',
        'token' => 'public_key',
    ),
    'parameter' => '_token',
    'userModel' => 'User',
    'scope' => array('User.active' => 1),
    'pepper' => Configure::read('API.token.pepper'),
);
```

Where (excluding common authentication items):

- ```fields``` is an array containing the details of which passed values (POSTed) contain the ```username```, ```password``` and ```token```
  - ```token``` is used to hold a unique key against the user once authenticated and is also stored in the JWT
- ```parameter``` is the query string parameter that could hold the JWT
- ```header``` is the HTTP header that could hold the JWT
- ```pepper``` is the salt to use when encrypting your JWT (keep this super secret!)

#### Defaults ####

```
array(
    'fields' => array(
        'username' => 'username',
        'token' => 'token'
    ),
    'parameter' => '_token',
    'header' => 'X_JSON_WEB_TOKEN',
    'userModel' => 'User',
    'scope' => array(),
    'recursive' => 0,
    'contain' => null,
    'pepper' => '123'
);
```

### Authentication ###

You can authenticate by passing a valid JWT as either:

- The query string parameter defined as ```parameter``` in the config array (defaults to ```_token```)
- The contents of the header defined as ```header``` in the config array (defaults to ```X_JSON_WEB_TOKEN```)

## TODO ##

Implement an end to end example for inside clients and 3rd party client usage.
