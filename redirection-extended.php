<?php

/**
 * Plugin Name:       Redirection Extended
 * Plugin URI:        https://github.com/helsingborg-stad/redirection-extended
 * Description:       Extends the Wordpress Redirection plugin
 * Version:           1.0.0
 * Author:            Nikolas Ramstedt
 * Author URI:        https://github.com/nramstedt
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       redirection-extended
 * Domain Path:       /languages
 */

// Protect agains direct file access
if (!defined('WPINC')) {
    die;
}

define('REDIRECTIONEXTENDED_PATH', plugin_dir_path(__FILE__));
define('REDIRECTIONEXTENDED_URL', plugins_url('', __FILE__));
define('REDIRECTIONEXTENDED_TEMPLATE_PATH', REDIRECTIONEXTENDED_PATH . 'templates/');

load_plugin_textdomain('redirection-extended', false, plugin_basename(dirname(__FILE__)) . '/languages');

require_once REDIRECTIONEXTENDED_PATH . 'source/php/Vendor/Psr4ClassLoader.php';
require_once REDIRECTIONEXTENDED_PATH . 'Public.php';

// Instantiate and register the autoloader
$loader = new RedirectionExtended\Vendor\Psr4ClassLoader();
$loader->addPrefix('RedirectionExtended', REDIRECTIONEXTENDED_PATH);
$loader->addPrefix('RedirectionExtended', REDIRECTIONEXTENDED_PATH . 'source/php/');
$loader->register();

// Start application
new RedirectionExtended\App();

// Acf auto import and export
add_action('plugins_loaded', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('redirection-extended');
    $acfExportManager->setExportFolder(REDIRECTIONEXTENDED_PATH . 'acf-fields/');
    $acfExportManager->autoExport(array(
        'redirection-extended' => 'group_5de91ccdd7b7e',
    ));
    $acfExportManager->import();
});
