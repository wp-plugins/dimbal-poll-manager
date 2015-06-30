<?php

// Standard Setup Steps
include dirname(__FILE__).'/inc.setup.php';

// Class Includes
include dirname(__FILE__).'/../classes/class.DimbalTipManager.php';
include dirname(__FILE__).'/../classes/class.DimbalTipItem.php';
include dirname(__FILE__).'/../classes/class.DimbalTipWidget.php';

// Constants
define('DIMBAL_CONST_DPM_PRO_APP_CODE', 'dtm');
define('DIMBAL_CONST_DPM_PRO_PAGE_ZONES', DimbalTipManager::PAGE_ZONES);
define('DIMBAL_CONST_DPM_PRO_PAGE_SETTINGS', DimbalTipManager::PAGE_SETTINGS);
define('DIMBAL_CONST_DPM_PRO_PAGE_PREVIEW', DimbalTipManager::PAGE_PREVIEW);
define('DIMBAL_CONST_DPM_PRO_SETTINGS_PREFIX', DIMBAL_CONST_DPM_PRO_SLUG.'-');
define('DIMBAL_CONST_DPM_PRO_URL', WP_PLUGIN_URL . '/' . DIMBAL_CONST_DPM_PRO_SLUG);
define('DIMBAL_CONST_DPM_PRO_URL_IMAGES', DIMBAL_CONST_DPM_PRO_URL . '/images');

// Actions
add_action( 'plugins_loaded', array(Dimbal_DPM_PRO::buildAppClassName('DimbalTipManager'), 'wpActionPluginsLoaded'));
add_action( 'widgets_init', array(Dimbal_DPM_PRO::buildAppClassName('DimbalTipManager'), 'wpActionWidgetsInit'));
add_action( 'admin_enqueue_scripts', array(Dimbal_DPM_PRO::buildAppClassName('DimbalTipManager'), 'wpActionAdminEnqueueScripts'));
add_action( 'admin_menu', array(Dimbal_DPM_PRO::buildAppClassName('DimbalTipManager'), 'wpActionAdminMenu'));

// Shortcodes
add_shortcode( DIMBAL_CONST_DPM_PRO_SLUG, array(Dimbal_DPM_PRO::buildAppClassName('DimbalTipManager'), 'shortcodeHandler') );

// Ajax Hooks
Dimbal_DPM_PRO::ajaxRegisterPublicHandlers(DimbalTipManager::ajaxGetPublicHandlerMappings(), array(Dimbal_DPM_PRO::buildAppClassName('DimbalTipManager'), 'ajaxProcessHandlerWrapper'));
Dimbal_DPM_PRO::ajaxRegisterAdminHandlers(DimbalTipManager::ajaxGetAllHandlerMappings(), array(Dimbal_DPM_PRO::buildAppClassName('DimbalTipManager'), 'ajaxProcessHandlerWrapper'));

// Make sure the session object has been started if not already
add_action('init', array(Dimbal_DPM_PRO::buildAppClassName('Dimbal'), 'startSession'), 1);
