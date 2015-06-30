<?php

// Standard Setup Steps
include dirname(__FILE__).'/inc.setup.php';

// Class Includes
include dirname(__FILE__).'/../classes/class.DimbalPollManager_DPM_FREE.php';
include dirname(__FILE__).'/../classes/class.DimbalPollQuestion_DPM_FREE.php';
include dirname(__FILE__).'/../classes/class.DimbalPollAnswerChoice_DPM_FREE.php';
include dirname(__FILE__).'/../classes/class.DimbalPollResponse_DPM_FREE.php';
include dirname(__FILE__).'/../classes/class.DimbalPollWidget_DPM_FREE.php';

// Constants
define('DIMBAL_CONST_DPM_FREE_APP_CODE', 'dpm');
define('DIMBAL_CONST_DPM_FREE_PAGE_ZONES', DimbalPollManager_DPM_FREE::PAGE_ZONES);            // Might be able to replace these with Dimbal_DPM_FREE::static vars
define('DIMBAL_CONST_DPM_FREE_PAGE_SETTINGS', DimbalPollManager_DPM_FREE::PAGE_SETTINGS);
define('DIMBAL_CONST_DPM_FREE_PAGE_PREVIEW', DimbalPollManager_DPM_FREE::PAGE_PREVIEW);
define('DIMBAL_CONST_DPM_FREE_SETTINGS_PREFIX', DIMBAL_CONST_DPM_FREE_SLUG.'-');
define('DIMBAL_CONST_DPM_FREE_URL', WP_PLUGIN_URL . '/' . DIMBAL_CONST_DPM_FREE_SLUG);
define('DIMBAL_CONST_DPM_FREE_URL_IMAGES', DIMBAL_CONST_DPM_FREE_URL . '/images');

// Actions
add_action( 'plugins_loaded', array(Dimbal_DPM_FREE::buildAppClassName('DimbalPollManager'), 'wpActionPluginsLoaded'));
add_action( 'widgets_init', array(Dimbal_DPM_FREE::buildAppClassName('DimbalPollManager'), 'wpActionWidgetsInit'));
add_action( 'admin_enqueue_scripts', array(Dimbal_DPM_FREE::buildAppClassName('DimbalPollManager'), 'wpActionAdminEnqueueScripts'));
add_action( 'admin_menu', array(Dimbal_DPM_FREE::buildAppClassName('DimbalPollManager'), 'wpActionAdminMenu'));

//error_log("Action: ".Dimbal_DPM_FREE::getRequestVarIfExists('action'));

// Ajax Hooks
Dimbal_DPM_FREE::ajaxRegisterPublicHandlers(DimbalPollManager_DPM_FREE::ajaxGetPublicHandlerMappings(), array(Dimbal_DPM_FREE::buildAppClassName('DimbalPollManager'), 'ajaxProcessHandlerWrapper'));
Dimbal_DPM_FREE::ajaxRegisterAdminHandlers(DimbalPollManager_DPM_FREE::ajaxGetAllHandlerMappings(), array(Dimbal_DPM_FREE::buildAppClassName('DimbalPollManager'), 'ajaxProcessHandlerWrapper'));

// Shortcodes
add_shortcode( DIMBAL_CONST_DPM_FREE_SLUG, array(Dimbal_DPM_FREE::buildAppClassName('DimbalPollManager'), 'shortcodeHandler') );

// Make sure the session object has been started if not already
add_action('init', array(Dimbal_DPM_FREE::buildAppClassName('Dimbal'), 'startSession'), 1);
