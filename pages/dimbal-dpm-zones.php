<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:28 PM
 * To change this template use File | Settings | File Templates.
 */

// Build the Header
echo Dimbal_DPM_PRO::buildHeader(array(
    'title'=>'Zone Manager',
    'icon'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/bricks.png',
    'description'=>'Use this manager to build and maintain custom zones for your polls.',
    'buttons'=>array(
        0=>array('text'=>'Create New Zone','params'=>array('page'=>Dimbal_DPM_PRO::buildPageSlug(DimbalPollManager_DPM_PRO::PAGE_ZONES), 'id'=>'new')),
        1=>array('text'=>'View All','params'=>array('page'=>Dimbal_DPM_PRO::buildPageSlug(DimbalPollManager_DPM_PRO::PAGE_ZONES))),
    )
));

// Check for a delete request
echo DimbalZone_DPM_PRO::checkForDelete(array());

///////////////////////  Editor DISPLAY  ///////////////////////////
echo DimbalEditor_DPM_PRO::buildPageTemplate(Dimbal_DPM_PRO::buildAppClassName('DimbalZone'),'Zone Editor');

// If the ID field was removed or is not present that means we want the Manager
$id = Dimbal_DPM_PRO::getRequestVarIfExists('id');
if(empty($id)){
    ///////////////////////  MANAGER DISPLAY  ///////////////////////////
    $rows = DimbalZone_DPM_PRO::managerBuildOptions(DimbalZone_DPM_PRO::getAllByTypeId(DimbalZone_DPM_PRO::TYPE_DPM));
    echo DimbalManager_DPM_PRO::buildManagerTable($rows);
}

// Close the wrapper
echo Dimbal_DPM_PRO::buildFooter();
