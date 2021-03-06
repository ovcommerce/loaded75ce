<?php
/**
 * This file is part of Loaded Commerce.
 * 
 * @link http://www.loadedcommerce.com
 * @copyright Copyright (c) 2017 Global Ecommerce Solutions Ltd
 * 
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

// define our database connection
  define('DB_SERVER', 'localhost'); // eg, localhost - should not be empty for productive servers
  define('DB_SERVER_USERNAME', 'root');
  define('DB_SERVER_PASSWORD', '');
  define('DB_DATABASE', 'tlsvn');
  define('USE_PCONNECT', 'false'); // use persistent connections?
  define('STORE_SESSIONS', ''); // leave empty '' for default handler or set to 'mysql'

// include the database functions
  global $dir_ws_includes;
  require_once(($dir_ws_includes ? $dir_ws_includes : 'includes/') . 'functions/database.php');

// make a connection to the database... now
  tep_db_connect() or die('Unable to connect to database server!');

  global $request_type;

  $platform = false;
  if (file_exists('lib/common/extensions/AdditionalPlatforms/AdditionalPlatforms.php')) {
    if ( !class_exists('\common\extensions\AdditionalPlatforms\AdditionalPlatforms') ) {
      include_once('lib/common/extensions/AdditionalPlatforms/AdditionalPlatforms.php');
      $platform = \common\extensions\AdditionalPlatforms\AdditionalPlatforms::configure();
    }
  } else {
      $platform = tep_db_fetch_array(tep_db_query("select *, IF(LENGTH(platform_url_secure)>0,platform_url_secure,platform_url) AS _platform_url_secure from platforms where platform_id='1' LIMIT 1"));
  }
  
  if ($platform['platform_id'] > 0) {
      if ($platform['is_virtual'] == 1) {
          $default_platform = tep_db_fetch_array(tep_db_query(
            "select *, IF(LENGTH(platform_url_secure)>0,platform_url_secure,platform_url) AS _platform_url_secure from platforms " .
            "where is_default=1 " .
            "LIMIT 1 "
          ));
          $platform['platform_url'] = $default_platform['platform_url'];
          $platform['_platform_url_secure'] = $default_platform['_platform_url_secure'];
          $platform['ssl_enabled'] = $default_platform['ssl_enabled'];
          $platform['need_login'] = $default_platform['need_login'];
          $theme_array = tep_db_fetch_array(tep_db_query("select t.theme_name from platforms_to_themes AS p2t INNER JOIN themes as t ON (p2t.theme_id=t.id) where p2t.is_default = 1 and p2t.platform_id = " . (int)$default_platform['platform_id']));
          if ($theme_array['theme_name']){
                $_GET['theme_name'] = $theme_array['theme_name'];
            } else {
                $_GET['theme_name'] = 'theme-1';
            }
      }

    if ($platform['ssl_enabled'] == 2) {
        if ($request_type == 'NONSSL') {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 302 Moved Temporarily');
            header('Location: ' . $redirect);
            exit();
        }
        $secureProtocol = 'https';
    } else {
        $secureProtocol = 'http';
    }
    if ( isset($_GET['theme_name']) ) {
      $REQUEST_PLATFORM_URL = rtrim($_SERVER['HTTP_HOST'].'/'.trim(dirname($_SERVER['SCRIPT_NAME']),'/'),'/');
      $parsed = parse_url('http://'.$REQUEST_PLATFORM_URL.'/');
      $parsed_ssl = $parsed;
      define('HTTP_SERVER', '//' . $parsed['host']);
      define('HTTPS_SERVER', '//' . $parsed['host']);
    }else{
      $parsed = parse_url('http://'.rtrim($platform['platform_url']).'/');
      $parsed_ssl = parse_url('http://'.rtrim($platform['_platform_url_secure']).'/');
      define('HTTP_SERVER', $secureProtocol . '://' . $parsed['host']);
      define('HTTPS_SERVER', 'https://' . $parsed_ssl['host']);
    }

    define('HTTP_COOKIE_DOMAIN', preg_replace('/^www/','',$parsed['host']));
    define('HTTPS_COOKIE_DOMAIN', preg_replace('/^www/','',$parsed_ssl['host']));

    define('HTTP_COOKIE_PATH', $parsed['path']);
    define('HTTPS_COOKIE_PATH', $parsed_ssl['path']);
    define('DIR_WS_HTTP_CATALOG', $parsed['path']);
    define('DIR_WS_HTTPS_CATALOG', $parsed_ssl['path']);

    define('ENABLE_SSL', !!$platform['ssl_enabled']);

    define('PLATFORM_ID', $platform['platform_id']);

    define('PLATFORM_NEED_LOGIN', $platform['need_login']);

    define('STORE_NAME', $platform['platform_name']);
    define('STORE_OWNER', $platform['platform_owner']);

    if ($platform['is_default_contact'] == 1) {
        $platformDefault = tep_db_fetch_array(tep_db_query(
            "select * from platforms " .
            "where is_default=1 " .
            "LIMIT 1 "
        ));
        define('EMAIL_FROM', $platformDefault['platform_email_from']);
        define('STORE_OWNER_EMAIL_ADDRESS', $platformDefault['platform_email_address']);
        define('SEND_EXTRA_ORDER_EMAILS_TO', $platformDefault['platform_email_extra']);
    } else {
        define('EMAIL_FROM', $platform['platform_email_from']);
        define('STORE_OWNER_EMAIL_ADDRESS', $platform['platform_email_address']);
        define('SEND_EXTRA_ORDER_EMAILS_TO', $platform['platform_email_extra']);
    }

    if ($platform['is_default_address'] == 1) {
        $get_store_country_config_r = tep_db_query("SELECT pab.entry_country_id, pab.entry_zone_id FROM platforms_address_book AS pab INNER JOIN platforms AS p ON pab.platform_id=p.platform_id WHERE p.is_default=1 and pab.is_default=1 LIMIT 1");
    } else {
        $get_store_country_config_r = tep_db_query("SELECT entry_country_id, entry_zone_id FROM platforms_address_book WHERE platform_id='".$platform['platform_id']."' AND is_default=1 LIMIT 1");
    }

    if ( tep_db_num_rows($get_store_country_config_r)>0 ) {
      $_store_country_config = tep_db_fetch_array($get_store_country_config_r);
      define('STORE_COUNTRY', $_store_country_config['entry_country_id']);
      define('STORE_ZONE', $_store_country_config['entry_zone_id']);
    }

    define('IS_IMAGE_CDN_SERVER', !!$platform['_platform_cdn_server']);
  } else {
    // fallback - db connect error
    define('HTTP_SERVER', 'http://localhost');
    define('HTTPS_SERVER', 'https://localhost');
    define('HTTP_COOKIE_DOMAIN', 'localhost');
    define('HTTPS_COOKIE_DOMAIN', 'localhost');

    define('HTTP_COOKIE_PATH', '/corporate_tl/');
    define('HTTPS_COOKIE_PATH', '/corporate_tl/');
    define('DIR_WS_HTTP_CATALOG', '/corporate_tl/');
    define('DIR_WS_HTTPS_CATALOG', '/corporate_tl/');

    define('ENABLE_SSL', false);

    define('PLATFORM_ID', 0);

    define('PLATFORM_NEED_LOGIN', 0);

    define('IS_IMAGE_CDN_SERVER', false);
  }

  define('AFFILIATE_ID', 0);
  
  define('DIR_WS_HTTP_ADMIN_CATALOG', 'admin/');
  define('DIR_WS_IMAGES', 'images/');
  define('DIR_WS_ICONS', DIR_WS_IMAGES . 'icons/');
  define('DIR_WS_INCLUDES', 'includes/');
  define('DIR_WS_BOXES', DIR_WS_INCLUDES . 'boxes/');
  define('DIR_WS_FUNCTIONS', DIR_WS_INCLUDES . 'functions/');
  define('DIR_WS_CLASSES', DIR_WS_INCLUDES . 'classes/');
  define('DIR_WS_MODULES', DIR_WS_INCLUDES . 'modules/');
  define('DIR_WS_LANGUAGES', DIR_WS_INCLUDES . 'languages/');
  define('DIR_WS_AFFILIATES', 'affiliates/');

//Added for BTS1.0
  define('DIR_WS_TEMPLATES', 'templates/');
  define('DIR_WS_CONTENT', DIR_WS_TEMPLATES . 'content/');
  define('DIR_WS_JAVASCRIPT', DIR_WS_INCLUDES . 'javascript/');
//End BTS1.0
  define('DIR_WS_DOWNLOAD_PUBLIC', '/pub/');
  define('DIR_FS_CATALOG', dirname(dirname(__FILE__)) . '/');
  define('DIR_FS_DOWNLOAD', DIR_FS_CATALOG . 'download/');
  define('DIR_FS_DOWNLOAD_PUBLIC', DIR_FS_CATALOG . 'pub/');
  define('DIR_FS_AFFILIATES', DIR_FS_CATALOG . 'affiliates/');
