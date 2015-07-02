<?php
/*
 * Plugin Name:   Dimbal Poll Manager - Professional
 * Version:       1.1.0
 * Plugin URI:    http://www.dimbal.com/
 * Description:   A powerful Poll Management plugin allowing you to create and maintain user interest polls for your blog or website.
 * Author:        Dimbal Software
 * Author URI:    http://www.dimbal.com/
 */

define('DIMBAL_CONST_DPM_PRO_SLUG', 'dimbal-poll-manager');
define('DIMBAL_CONST_DPM_PRO_PAGE_PREFIX', 'dimbal-dpm-pro');
define('DIMBAL_CONST_DPM_PRO_PLUGIN_TITLE', 'Dimbal Poll Manager');
define('DIMBAL_CONST_DPM_PRO_PLUGIN_TITLE_SHORT', 'Dimbal Polls');
define('DIMBAL_CONST_DPM_PRO_PURCHASE_LEVEL', 'pro');
define('DIMBAL_CONST_DPM_PRO_WP_PLUGIN_HOMEPAGE', 'https://wordpress.org/plugins/dimbal-poll-manager/');

// Dimbal Poll Manager Setup Steps
include 'inc/inc.setup-dpm.php';

// Activations Hooks
register_activation_hook(__FILE__, array(Dimbal_DPM_PRO::buildAppClassName('DimbalPollManager'), 'wpActionActivate'));



