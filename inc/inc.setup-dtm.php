<?php

// Standard Setup Steps
include dirname(__FILE__).'/inc.setup.php';

// Class Includes
include dirname(__FILE__).'/../classes/class.DimbalTipManager.php';
include dirname(__FILE__).'/../classes/class.DimbalTipItem.php';
include dirname(__FILE__).'/../classes/class.DimbalTipWidget.php';

// Constants
define('DIMBAL_CONST_DPM_FREE_APP_CODE', 'dtm');
define('DIMBAL_CONST_DPM_FREE_PAGE_ZONES', DimbalTipManager::PAGE_ZONES);
define('DIMBAL_CONST_DPM_FREE_PAGE_SETTINGS', DimbalTipManager::PAGE_SETTINGS);
define('DIMBAL_CONST_DPM_FREE_PAGE_PREVIEW', DimbalTipManager::PAGE_PREVIEW);
define('DIMBAL_CONST_DPM_FREE_SETTINGS_PREFIX', DIMBAL_CONST_DPM_FREE_SLUG.'-');
define('DIMBAL_CONST_DPM_FREE_URL', WP_PLUGIN_URL . '/' . DIMBAL_CONST_DPM_FREE_SLUG);
define('DIMBAL_CONST_DPM_FREE_URL_IMAGES', DIMBAL_CONST_DPM_FREE_URL . '/images');

// Actions
add_action( 'plugins_loaded', array(Dimbal_DPM_FREE::buildAppClassName('DimbalTipManager'), 'wpActionPluginsLoaded'));
add_action( 'widgets_init', array(Dimbal_DPM_FREE::buildAppClassName('DimbalTipManager'), 'wpActionWidgetsInit'));
add_action( 'admin_enqueue_scripts', array(Dimbal_DPM_FREE::buildAppClassName('DimbalTipManager'), 'wpActionAdminEnqueueScripts'));
add_action( 'admin_menu', array(Dimbal_DPM_FREE::buildAppClassName('DimbalTipManager'), 'wpActionAdminMenu'));

// Shortcodes
add_shortcode( DIMBAL_CONST_DPM_FREE_SLUG, array(Dimbal_DPM_FREE::buildAppClassName('DimbalTipManager'), 'shortcodeHandler') );

// Ajax Hooks
Dimbal_DPM_FREE::ajaxRegisterPublicHandlers(DimbalTipManager::ajaxGetPublicHandlerMappings(), array(Dimbal_DPM_FREE::buildAppClassName('DimbalTipManager'), 'ajaxProcessHandlerWrapper'));
Dimbal_DPM_FREE::ajaxRegisterAdminHandlers(DimbalTipManager::ajaxGetAllHandlerMappings(), array(Dimbal_DPM_FREE::buildAppClassName('DimbalTipManager'), 'ajaxProcessHandlerWrapper'));

// Make sure the session object has been started if not already
add_action('init', array(Dimbal_DPM_FREE::buildAppClassName('Dimbal'), 'startSession'), 1);
