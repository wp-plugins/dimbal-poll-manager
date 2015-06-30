<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 8:47 PM
 * To change this template use File | Settings | File Templates.
 */

echo Dimbal_DPM_FREE::buildHeader(array(
    'title'=>'Software Settings',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/cog.png',
    'description'=>'Change default behaviour and more in this settings panel.',
));

$buttonHtml = Dimbal_DPM_FREE::buildButton(array('text'=>'Upgrade to Pro','url'=>'http://www.dimbal.com'));

?>
<h3>Upgrade to the Pro version to access powerful Settings and Control options to extend your software. <?=($buttonHtml)?></h3>
<?php

$boxes = array();
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Custom Styles',
    'content'=>'Set default custom styles for use while your polls are being displayed.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/bricks.png',
));
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Global On/Off Flag',
    'content'=>'Disable the plugin software without having to uninstall via WordPress.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/compass.png',
));
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Reset Responses',
    'content'=>'Delete saved responses in bulk for a given poll in a quick and easy fashion.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/comments.png',
));
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Default Poll Settings',
    'content'=>'Set default behaviour for new polls including viewing results, allowing multiple responses and more.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/chart_pie.png',
));
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Default Zone Settings',
    'content'=>'Set default behaviour for new zones as they display your polls.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/server_components.png',
));
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Database Maintenance',
    'content'=>'Perform optimization commands and backup commands against your plugin data to enhance performance.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/cog.png',
));

echo DimbalBox_DPM_FREE::renderBoxes($boxes);

// Close the wrapper
echo Dimbal_DPM_FREE::buildFooter();
