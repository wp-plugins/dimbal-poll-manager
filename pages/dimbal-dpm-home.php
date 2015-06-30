<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 8:47 PM
 * To change this template use File | Settings | File Templates.
 */


echo Dimbal_DPM_FREE::buildHeader(array(
    'title'=>'',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/logo_300.png',
    'description'=>'Software to help make your website stand out.',
));


$boxes = array();

$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_STANDARD,
    'size'=>DimbalBox_DPM_FREE::SIZE_FULL,
    'title'=>'Welcome to the Dimbal Poll Manager!',
    'content'=>'<p>The Dimbal Poll Manager is a powerful Poll Management plugin allowing you to create and maintain user interest polls for your blog or website.  Easily integrate polls directly into your blog using the provided shortcodes or widgets.</p><p>The Dimbal Poll Manager comes fully loaded with unique features that allow you to customize how your polls function.  Take advantage of the unique poll settings to add variety and flexibility to your site and content.</p>',
    'icon'=>'https://s3.amazonaws.com/dimbal/dimbalsoftware/images/dpm-software-box-200.png',
    'iconStyle'=>'width:150px;',
    'contentStyle'=>'font-size:larger;',
));

$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Manage Polls',
    'content'=>'Create and manage your Polls through this easy-to-use manager to engage your website visitors.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/chart_pie.png',
    'buttons'=>array(
        0=>array('params'=>array('page'=>Dimbal_DPM_FREE::buildPageSlug(DimbalPollManager_DPM_FREE::PAGE_POLLS)),'text'=>'Manage Polls')
    )
));
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Manage Zones',
    'content'=>'Zones are a powerful grouping feature allowing you to display random polls from a designated group.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/bricks.png',
    'buttons'=>array(
        0=>array('params'=>array('page'=>Dimbal_DPM_FREE::buildPageSlug(DimbalPollManager_DPM_FREE::PAGE_ZONES)),'text'=>'Manage Zones')
    )
));

/*
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_STANDARD,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_HALF,
    'title'=>'Dimbal Link Manager',
    'content'=>'URL and QR Code Tracking.  Understand the source of every click with the Dimbal Link Manager.',
    'icon'=>'https://s3.amazonaws.com/dimbal/dimbalsoftware/images/dlm-software-box-100.png',
    'buttons'=>array(
        0=>array('url'=>'http://www.dimbal.com','text'=>'Learn More')
    )
));
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_STANDARD,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_HALF,
    'title'=>'Dimbal Banner Manager',
    'content'=>'A powerful Banner Management solution.  Manage website banner ads using this feature rich software.',
    'icon'=>'https://s3.amazonaws.com/dimbal/dimbalsoftware/images/dbm-software-box-100.png',
    'buttons'=>array(
        0=>array('url'=>'http://www.dimbal.com','text'=>'Learn More')
    )
));
*/


$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Reports and Analytics',
    'content'=>'The Dimbal Poll Manager has powerful analytics built into the core.  View custom reports on your polls activities.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/document_layout.png',
    'buttons'=>array(
        0=>array('params'=>array('page'=>Dimbal_DPM_FREE::buildPageSlug(DimbalPollManager_DPM_FREE::PAGE_REPORTS)),'text'=>'Reports and Analytics')
    )
));
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Settings and Tools',
    'content'=>'Your software is flexible and has a powerful collection of settings.  Change default behaviour and more in the settings panel.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/cog.png',
    'buttons'=>array(
        0=>array('params'=>array('page'=>Dimbal_DPM_FREE::buildPageSlug(DimbalPollManager_DPM_FREE::PAGE_SETTINGS)),'text'=>'Settings and Tools')
    )
));
$boxes[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_ONE_THIRD,
    'title'=>'Help and Support',
    'content'=>'Need help or have a question?  Jump over to our support page to get information on our Knowledge Base articles, user forums and more.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/info_rhombus.png',
    'buttons'=>array(
        0=>array('params'=>array('page'=>Dimbal_DPM_FREE::buildPageSlug(DimbalPollManager_DPM_FREE::PAGE_SUPPORT)),'text'=>'Help and Support')
    )
));

// Render the boxes
echo DimbalBox_DPM_FREE::renderBoxes($boxes);


// Close the wrapper
echo Dimbal_DPM_FREE::buildFooter();
?>
