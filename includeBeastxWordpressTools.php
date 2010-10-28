<?

ob_start();

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

if (!function_exists('debug')) {
    require_once 'thirdParty/firePHP/FirePHP.class.php';
    function debug($var, $title = null) {
        $firephp = FirePHP::getInstance(true);
        $firephp->log($var, $title);
    }
}

require_once 'class.BeastxInputs.php';
require_once 'class.BeastxEnviroment.php';
require_once 'class.BeastxFileSystemHelper.php';
require_once 'class.BeastxMysqlHelper.php';
require_once 'class.BeastxOptionsManager.php';
require_once 'class.BeastxPlugin.php';
require_once 'class.BeastxAdminPage.php';
require_once 'class.BeastxCustomPostType.php';

if (defined(BEASTXDEBUG) && BEASTXDEBUG) {
    $wpdb->show_errors = true;
}
?>