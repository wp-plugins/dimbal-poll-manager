<?php
/*
 * Plugin Name:   Dimbal Poll Manager - Free Version
 * Version:       1.0
 * Plugin URI:    http://www.dimbal.com/
 * Description:   A powerful and free Poll Management plugin allowing you to create and maintain user interest polls for your blog or website.
 * Author:        Dimbal Software
 * Author URI:    http://www.dimbal.com/
 */

define('DIMBAL_CONST_DPM_FREE_SLUG', 'dimbal-dpm-free');
define('DIMBAL_CONST_DPM_FREE_PLUGIN_TITLE', 'Dimbal Poll Manager - Free');
define('DIMBAL_CONST_DPM_FREE_PLUGIN_TITLE_SHORT', 'Dimbal Polls Lite');
define('DIMBAL_CONST_DPM_FREE_PURCHASE_LEVEL', 'free');

// Dimbal Poll Manager Setup Steps
include 'inc/inc.setup-dpm.php';

// Activations Hooks
register_activation_hook(__FILE__, array(Dimbal_DPM_FREE::buildAppClassName('DimbalPollManager'), 'wpActionActivate'));


