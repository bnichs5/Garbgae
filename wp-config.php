<?php

// note that this is loaded from wp-config.d by the real wp-config.php

    !defined('DB_USER')          && define('DB_USER',          getenv('QOVERY_DATABASE_WORDPRESS_USERNAME')    ?: 'superuser');
    !defined('DB_NAME')          && define('DB_NAME',          getenv('QOVERY_DATABASE_WORDPRESS_NAME')        ?: 'mysql-fred');
    !defined('DB_PASSWORD')      && define('DB_PASSWORD',      getenv('QOVERY_DATABASE_WORDPRESS_PASSWORD')    ?: 'GwAMM05QCrWmLKu9');
    !defined('DB_HOST')          && define('DB_HOST',          getenv('QOVERY_DATABASE_WORDPRESS_HOST')        ?: 'fred-xiti7fl268g2v72r-svc.qovery.io');


    if(!defined('WP_SITEURL')) {
        if (getenv('QOVERY_ROUTER_MAIN_WORDPRESSFORA2SIDEDMARKET_URL')) {
            define('WP_SITEURL', getenv('QOVERY_ROUTER_MAIN_WORDPRESSFORA2SIDEDMARKET_URL'));
        } else {
            define('WP_SITEURL', $proto . $_SERVER['SERVER_NAME']);
        }
    }

    if(!defined('WP_HOME')) {
        if (getenv('QOVERY_ROUTER_MAIN_WORDPRESSFORA2SIDEDMARKET_URL')) {
            define('WP_HOME', getenv('QOVERY_ROUTER_MAIN_WORDPRESSFORA2SIDEDMARKET_URL'));
        } else {
            define('WP_HOME', $proto . $_SERVER['SERVER_NAME']);
        }
    }
