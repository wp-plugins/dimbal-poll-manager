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
    'title'=>'Poll Manager',
    'icon'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/chart_pie.png',
    'description'=>'Use this manager to build and maintain your polls.',
    'buttons'=>array(
        0=>array('text'=>'Create New Poll','params'=>array('page'=>Dimbal_DPM_PRO::buildPageSlug(DimbalPollManager_DPM_PRO::PAGE_POLLS), 'id'=>'new')),
        1=>array('text'=>'View All','params'=>array('page'=>Dimbal_DPM_PRO::buildPageSlug(DimbalPollManager_DPM_PRO::PAGE_POLLS))),
    )
));

// Check for a delete request
echo DimbalPollQuestion_DPM_PRO::checkForDelete(array());

///////////////////////  Editor DISPLAY  ///////////////////////////
echo DimbalEditor_DPM_PRO::buildPageTemplate(Dimbal_DPM_PRO::buildAppClassName('DimbalPollQuestion'),'Poll Editor');

// If the ID field was removed or is not present that means we want the Manager
$id = Dimbal_DPM_PRO::getRequestVarIfExists('id');
if(empty($id)){
    ///////////////////////  MANAGER DISPLAY  ///////////////////////////
    $rows = DimbalPollQuestion_DPM_PRO::managerBuildOptions(DimbalPollQuestion_DPM_PRO::getAll());
    echo DimbalManager_DPM_PRO::buildManagerTable($rows);
}

// Close the wrapper
echo Dimbal_DPM_PRO::buildFooter();
