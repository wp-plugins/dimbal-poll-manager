<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/27/15
 * Time: 1:45 PM
 * To change this template use File | Settings | File Templates.
 */

// Build the Header
echo Dimbal_DPM_PRO::buildHeader(array(
    'title'=>'Feature Preview',
    'icon'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/magnifier.png',
    'description'=>'This page allows for previewing an asset or feature.  The display will differe based on the feature.',
    'buttons'=>array(
        0=>array('text'=>'Home','params'=>array('page'=>Dimbal_DPM_PRO::buildPageSlug(Dimbal_DPM_PRO::PAGE_HOME)))
    )
));

echo "<hr />";

// Now we need to see which type of Preview this is
$ac = Dimbal_DPM_PRO::getRequestVarIfExists('ac');

switch($ac){
    case '1':
        // Preview a Poll
        $pollId = Dimbal_DPM_PRO::getRequestVarIfExists('pollId');
        $poll = DimbalPollQuestion_DPM_PRO::get($pollId);
        if(!empty($poll)){
            echo $poll->getDisplayCode();
        }
        break;
    case '2':
        $tipId = Dimbal_DPM_PRO::getRequestVarIfExists('tipId');
        $tip = DimbalTipItem::get($tipId);
        if(!empty($tip)){
            echo $tip->getDisplayCode();
        }
        break;
    case '3':
        // Preview a Zone
        $zoneId = Dimbal_DPM_PRO::getRequestVarIfExists('zoneId');
        $zone = DimbalZone_DPM_PRO::get($zoneId);
        if(!empty($zone)){
            echo $zone->getDisplayCode();
        }
        break;
}



// Close the wrapper
echo Dimbal_DPM_PRO::buildFooter();
