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
    'title'=>'Poll Manager',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/chart_pie.png',
    'description'=>'Use this manager to build and maintain your polls.',
    'buttons'=>array(
        0=>array('text'=>'Create New Poll','params'=>array('page'=>Dimbal_DPM_FREE::buildPageSlug(DimbalPollManager_DPM_FREE::PAGE_POLLS), 'id'=>'new')),
        1=>array('text'=>'View All','params'=>array('page'=>Dimbal_DPM_FREE::buildPageSlug(DimbalPollManager_DPM_FREE::PAGE_POLLS))),
    )
));

// Check for a delete request
echo DimbalPollQuestion_DPM_FREE::checkForDelete(array());

///////////////////////  Editor DISPLAY  ///////////////////////////
echo DimbalEditor_DPM_FREE::buildPageTemplate(Dimbal_DPM_FREE::buildAppClassName('DimbalPollQuestion'),'Poll Editor');

// If the ID field was removed or is not present that means we want the Manager
$id = Dimbal_DPM_FREE::getRequestVarIfExists('id');
if(empty($id)){
    ///////////////////////  MANAGER DISPLAY  ///////////////////////////
    $rows = DimbalPollQuestion_DPM_FREE::managerBuildOptions(DimbalPollQuestion_DPM_FREE::getAll());
    echo DimbalManager_DPM_FREE::buildManagerTable($rows);
}

// Close the wrapper
echo Dimbal_DPM_FREE::buildFooter();
