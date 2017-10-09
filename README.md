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

# Install assets from bundle

+ php app/console assets:install

# Cache clear

+ bin/console cache:clear --env=prod --no-warmup 
+ bin/console cache:clear --env=dev --no-warmup

# Configuration Reference & Dumping

+  php bin/console config:dump-reference twig

# Services search

+ php bin/console debug:container logger

# Get Route List

+ php bin/console debug:route