#!/bin/bash

clear


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_designTest.php"

#sudo docker exec -it dev_wp_env_wordpress_1 bash \
 #   -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/template"

  exit

#sudo docker exec -it dev_wp_env_wordpress_1 bash \
 #   -c "cd /var/www/html/wp-content/themes/integlight && ./vendor/bin/phpunit tests/unit-tests/template"

