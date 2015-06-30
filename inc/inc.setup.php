<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 5/19/15
 * Time: 9:23 PM
 * To change this template use File | Settings | File Templates.
 */

if ( ! defined( 'ABSPATH' ) ) exit();	// sanity check

/********** INCLUDES **********/
$classes = array();

// Core Files
$classes[] = dirname(__FILE__).'/../classes/class.Dimbal_DPM_PRO.php';
$classes[] = dirname(__FILE__).'/../classes/class.DimbalStandardObjectRecord_DPM_PRO.php';
$classes[] = dirname(__FILE__).'/../classes/class.DimbalStandardLinkRecord_DPM_PRO.php';
$classes[] = dirname(__FILE__).'/../classes/class.DimbalBox_DPM_PRO.php';
$classes[] = dirname(__FILE__).'/../classes/class.DimbalManager_DPM_PRO.php';
$classes[] = dirname(__FILE__).'/../classes/class.DimbalEditor_DPM_PRO.php';
$classes[] = dirname(__FILE__).'/../classes/class.DimbalSetting_DPM_PRO.php';

// Zone Manager
$classes[] = dirname(__FILE__).'/../classes/class.DimbalZoneManager_DPM_PRO.php';
$classes[] = dirname(__FILE__).'/../classes/class.DimbalZone_DPM_PRO.php';
$classes[] = dirname(__FILE__).'/../classes/class.DimbalZoneItem_DPM_PRO.php';

foreach($classes as $classpath){
    include($classpath);
}
