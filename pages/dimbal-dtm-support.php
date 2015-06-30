<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 8:47 PM
 * To change this template use File | Settings | File Templates.
 */


echo Dimbal_DPM_FREE::buildHeader(array(
    'title'=>'Help and Support',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/info_rhombus.png',
    'description'=>'Use this page to gain access to helpful resources and advice.',
));


$boxesLeft[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_FULL,
    'title'=>'Knowledge Base Articles',
    'content'=>'Knowledge base articles are tutorials and references written to assist in the use of our products.  They address commonly asked questions as well as provide step by step instructions through various pieces of the software functionality.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/document_layout.png',
    'buttons'=>array(
        0=>array('url'=>'http://www.dimbal.com/kb/','text'=>'View Knowledge Base Articles')
    )
));

$boxesLeft[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_FULL,
    'title'=>'User Forums',
    'content'=>'The user forums contain questions asked by users of the Dimbal Software products.  You may find answers to questions you currently have as well as useful answers about the Dimbal Software plugins.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/group.png',
    'buttons'=>array(
        0=>array('url'=>'http://www.dimbal.com/forum/','text'=>'Visit User Forums')
    )
));

$boxesLeft[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_FULL,
    'title'=>'Still Need Help?',
    'content'=>'Still need help beyond the Knowledge Base and User Forums?  Not to worry.  You can contact our support team directly.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/help.png',
    'buttons'=>array(
        0=>array('url'=>'http://www.dimbal.com/lib/dimbalcore/support/','text'=>'Contact Support')
    )
));




$boxesRight[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_FULL,
    'title'=>'Rate Us',
    'content'=>'Did you like this Plugin?  Please leave positive feedback to help it grow.',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/award_star_gold_3.png',
    'buttons'=>array(
        0=>array('url'=>'http://wordpress.org/support/view/plugin-reviews/dimbal-social-popup','text'=>'Rate this Plugin')
    )
));

$boxesRight[] = new DimbalBox_DPM_FREE(array(
    'type'=>DimbalBox_DPM_FREE::TYPE_TRIM,
    'size'=>DimbalBox_DPM_FREE::SIZE_FULL,
    'title'=>'Follow Us',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/facebook-icon32.png',
    'content'=>"
        <div>Follow us on Facebook for free giveaways, product announcements and more...</div>
        <br />
        <div id='fb-root'></div>
        <script type='text/javascript'>
            // Additional JS functions here
            window.fbAsyncInit = function() {
                FB.init({
                    appId      : '539348092746687',
                    status     : true,
                    cookie     : true,
                    xfbml      : true,
                    frictionlessRequests: true
                });
            };
            // Load the SDK Asynchronously
            (function(d){
                var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
                if (d.getElementById(id)) {return;}
                js = d.createElement('script'); js.id = id; js.async = true;
                js.src = '//connect.facebook.net/en_US/all.js';
                ref.parentNode.insertBefore(js, ref);
            }(document));
        </script>
        <div style='text-align:center;'><div class='fb-like' data-href='https://www.facebook.com/dimbalsoftware' data-send='false' data-layout='standard' data-show-faces='false' data-width='200'></div></div>
    ",
));

?>

<div style="display:table; width:100%;">
    <div style="display:table-cell; width:66%; vertical-align: top;">
        <?php
        echo DimbalBox_DPM_FREE::renderBoxes($boxesLeft);
        ?>
    </div>
    <div style="display:table-cell; width:33%; vertical-align: top; margin-right:25px;">
        <?php
        echo DimbalBox_DPM_FREE::renderBoxes($boxesRight);
        ?>
    </div>
</div>


<?php
// Close the wrapper
echo Dimbal_DPM_FREE::buildFooter();

