<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:28 PM
 * To change this template use File | Settings | File Templates.
 */

// Build the Header
echo Dimbal_DPM_FREE::buildHeader(array(
    'title'=>'Zone Manager',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/bricks.png',
    'description'=>'Use this manager to build and maintain custom zones for your polls.',
));

// Make sure that at least one zone currently exists
$zoneId = DimbalZoneManager_DPM_FREE::validateFreeZone(DimbalZone_DPM_FREE::TYPE_DPM);

$buttonHtml = Dimbal_DPM_FREE::buildButton(array('text'=>'Upgrade to Pro','url'=>'http://www.dimbal.com'));

?>
<h3>Upgrade to the Pro version for unlimited Zones.  In the free version only 1 zone is supported. <?=($buttonHtml)?></h3>
<?php

///////////////////////  Editor DISPLAY  ///////////////////////////
echo DimbalEditor_DPM_FREE::buildPageTemplate(Dimbal_DPM_FREE::buildAppClassName('DimbalZone'),'Zone Editor', true);

// Close the wrapper
echo Dimbal_DPM_FREE::buildFooter();
