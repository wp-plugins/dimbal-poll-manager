<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 8:47 PM
 * To change this template use File | Settings | File Templates.
 */

// Build the Header
echo Dimbal_DPM_FREE::buildHeader(array(
    'title'=>'Admin Tools',
    'icon'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/wrench.png',
    'description'=>'Use this admin console to administer tasks against your software.',
));

//Dimbal_DPM_FREE::logMessage("Request Vars: ".print_r($_REQUEST, true));


$ac = Dimbal_DPM_FREE::getRequestVarIfExists('ac');
$ag = Dimbal_DPM_FREE::getRequestVarIfExists('ag');
if(empty($ag)){
    $ag = '1';
}

$defaultParams = array('page'=>Dimbal_DPM_FREE::buildPageSlug(Dimbal_DPM_FREE::PAGE_TOOLS), 'ag'=>$ag);

// Perform actions as needed
switch($ac){
    case '1':
        // Framework Report
        echo "<h2>Framework Report</h2>";
        $pollCount = DimbalPollQuestion_DPM_FREE::getCount();
        $pollResponseCount = DimbalPollResponse_DPM_FREE::getCount();
        $tipCount = DimbalTipItem::getCount();
        $zoneCount = DimbalZone_DPM_FREE::getCount();
        echo "<p>Polls: ".$pollCount."</p>";
        echo "<p>Poll Responses: ".$pollResponseCount."</p>";
        echo "<p>Tips: ".$tipCount."</p>";
        echo "<p>Zones: ".$zoneCount."</p>";
        break;
    case '2':
        // Reset Poll Responses for given Poll
        $pollId = Dimbal_DPM_FREE::getRequestVarIfExists('pollId');
        $poll = DimbalPollQuestion_DPM_FREE::get($pollId);
        if(!empty($poll)){
            DimbalPollResponse_DPM_FREE::deleteByPollId($pollId);
            echo "Poll Responses Deleted";
        }
        break;
}


// Build the UI

// General tools
echo "<hr />";
echo "<h2>Framework Report</h2>";
echo "<p>Build a report showcasing details about your stored objects.</p>";
echo Dimbal_DPM_FREE::buildButton(array('text'=>'Build Framework Report','params'=>array_merge($defaultParams, array('ac'=>'1'))));

// Dimbal Poll Manager
$allPolls = DimbalPollQuestion_DPM_FREE::getAll();
// Clear responses for a given poll
?>
<hr />
<h2>Delete Poll Responses</h2>
<p>Delete all Poll Responses for a given Poll.  Once deleted, responses cannot be recovered.</p>
<form method="post">
    <input type="hidden" name="page" value="<?=(Dimbal_DPM_FREE::buildPageSlug(Dimbal_DPM_FREE::PAGE_TOOLS))?>">
    <input type="hidden" name="ag" value="<?=($ag)?>">
    <input type="hidden" name="ac" value="2">
    <select name="pollId">
        <option value="">--- Select Poll ---</option>
        <?php
        foreach($allPolls as $poll){
            echo '<option value="'.$poll->id.'">['.$poll->id.'] '.$poll->text.'</option>';
        }
        ?>
    </select>
    <input type="submit" name="submit" value="Delete all Poll Responses" />
</form>


<hr />
<?php

// Close the wrapper
echo Dimbal_DPM_FREE::buildFooter();

?>
