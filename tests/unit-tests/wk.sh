#!/bin/bash

clear



#sudo docker exec -it dev_wp_env_wordpress_1 bash \
#  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
#  tests/unit-tests/function/aurora_design_blocks_forBlocksTest.php"



sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit --debug \
  tests/unit-tests/function/"





  exit

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_designTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_blocksTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_blocks_customizerTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_blocks_outerAssetsTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_functions_outerAssets_MoveScriptsTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_functions_outerAssets_DeferCssTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_functions_outerAssets_EditorScriptsTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_functions_outerAssets_EditorStylesTest.php"

sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_functions_outerAssets_FrontendScriptsTest.php"


sudo docker exec -it dev_wp_env_wordpress_1 bash \
  -c "cd /var/www/html/wp-content/plugins/aurora-design-blocks && ./vendor/bin/phpunit \
  tests/unit-tests/function/aurora_design_functions_outerAssets_FrontendStylesTest.php"

