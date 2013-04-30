php_rbac - RBAC Level 1 Implementation
=======================================================

[![Build Status](https://travis-ci.org/leighmacdonald/php_rbac.png)](https://travis-ci.org/leighmacdonald/php_rbac)

This library aims to provide a modern PHP based RBAC (Role-Based Access Control) implementation.

For more information about RBAC please see the following links:

- [The NIST Model for Role-Based Access Control (pdf)](http://csrc.nist.gov/rbac/sandhu-ferraiolo-kuhn-00.pdf)
- [Role-based access control (wikipedia)](http://en.wikipedia.org/wiki/Role-based_access_control)

Installation / Usage
------------------------------------------------

1. Setup your composer.json file with the following declaration.

    ``` json
    {
        "require": {
            "leighmacdonald/php_rbac": "dev-master"
        }
    }
    ```

2. If you are just using the lib: `composer update`. If you are developing against the lib: `composer update --dev`

3. Check out the [docs](https://github.com/leighmacdonald/php_rbac/tree/master/docs) folder for usage examples and
 other information.

TODO
------------------------------------------------

- NIST Level 2, Currently only level 1 is implemented.
- Cached implementation using:
    - Memcache
    - APC

Contributing
------------------------------------------------

If you wish to contribute, please make sure the following criteria are met:

1. Your code conforms to PSR specifications.
2. All code additions should include test cases. (if applicable).
3. The test suite passes.

PHPUnit Tests
----------------------------

There is a example phpunit config file that should be edited before running. Notably
you should change the default database parameters.

1. Install the composer dev dependencies: `composer update --dev`
2. Run phpunit: `./vendor/bin/phpunit`


Authors
---------------------------

Leigh MacDonald - <leigh.macdonald@gmail.com> - <http://cudd.li>

License
------------------------------------------------

php_rbac is licensed under the MIT License - see the [`LICENSE`](https://github.com/leighmacdonald/php_rbac/blob/master/LICENSE).
