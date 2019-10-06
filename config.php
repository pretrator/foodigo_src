<?php
// HTTP
define('HTTP_SERVER', 'https://foodigo.in/');

// HTTPS
define('HTTPS_SERVER', 'https://foodigo.in/');

// DIR
define('DIR_APPLICATION', '/var/app/current/catalog/');
define('DIR_SYSTEM', '/var/app/current/system/');
define('DIR_IMAGE', '/var/app/current/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');
define('IMAGE_URL',"https://s3.ap-south-1.amazonaws.com/foodigoimagebuck/image/");
// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'uzzrest.cbmbpjnncgh2.ap-south-1.rds.amazonaws.com');
define('DB_USERNAME', 'uzawal');
define('DB_PASSWORD', 'uzawal21');
define('DB_DATABASE', 'foodigo');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');