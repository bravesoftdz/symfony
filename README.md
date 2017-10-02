Symfony
=======

1) git clone https://github.com/dykyi-roman/symfony
2) composer install
3) bit/console doctrine:database:create
4) bit/console doctrine:schema:update --force
5) bit/console server:start

# Update your Database Schema

+ php bin/console doctrine:schema:update --force

# Fixtures create

+ php bin/console doctrine:fixtures:load