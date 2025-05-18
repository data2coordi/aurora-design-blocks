#!/bin/bash

clear


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_blocksTest.php"



  exit


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_designTest.php"
