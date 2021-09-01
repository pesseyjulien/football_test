# Football-Test

Postman Doc : https://documenter.getpostman.com/view/406856/U16dSpHo

1. Git clone the repo and run the following commands (make sure to create an .env file before : use the .env.dist as an example and modify values accordingly) :
    - composer install (install dependencies)
    - php bin/console doctrine:database:create (if not exists already)
    - php bin/console doctrine:schema:create
    - php bin/console doctrine:fixtures:load (to load some basic data and create default user, see src/DataFixtures/AppFixtures for details)

2. Testing is made by phpUnit, make sure to run the following commands before testing (it will create a testing database) :
     - php bin/console doctrine:database:create --env=test
     - php bin/console doctrine:schema:create --env=test
     - php bin/console doctrine:fixtures:load --env=test

###### Testing example :

**All** : php ./vendor/bin/phpunit

**Specific class** : php ./vendor/bin/phpunit tests/Controller/TeamsControllerTest.php

**Specific function** : php ./vendor/bin/phpunit --filter testUpdateTeam tests/Controller/TeamsControllerTest.php

_Note_: although a test database is used for testing, all transactions are rollback after each test.

    
