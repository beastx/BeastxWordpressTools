<?

ob_start();

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

require_once 'thirdParty/firePHP/FirePHP.class.php';
require_once 'class.BeastxInputs.php';
require_once 'class.BeastxEnviroment.php';
require_once 'class.BeastxFileSystemHelper.php';
require_once 'class.BeastxMysqlHelper.php';
require_once 'class.BeastxOptionsManager.php';
require_once 'class.BeastxPlugin.php';
require_once 'class.BeastxAdminPage.php';
require_once 'class.BeastxCustomPostType.php';

if (!function_exists('debug')) {
    function debug($var, $title = null) {
        $firephp = FirePHP::getInstance(true);
        $firephp->log($var, $title);
    }
}

if (defined(BEASTXDEBUG) && BEASTXDEBUG) {
    $wpdb->show_errors = true;
}
?>