<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 8:47 PM
 * To change this template use File | Settings | File Templates.
 */

echo Dimbal_DPM_PRO::buildHeader(array(
    'title'=>'Plugin Settings and Tools',
    'icon'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/cog.png',
    'description'=>'Change default behaviour and more in this settings panel.',
));



//////////// TOOLS

// Default tool params
$defaultParams = array('page'=>Dimbal_DPM_PRO::buildPageSlug(DimbalPollManager_DPM_PRO::PAGE_SETTINGS));

// Perform actions as needed for the TOOLS
$ac = Dimbal_DPM_PRO::getRequestVarIfExists('ac');
if(!empty($ac)){
    switch($ac){
        case '1':
            // Framework Report
            echo "<h2>Framework Report</h2>";
            $pollCount = DimbalPollQuestion_DPM_PRO::getCount();
            $pollResponseCount = DimbalPollResponse_DPM_PRO::getCount();
            $zoneCount = DimbalZone_DPM_PRO::getCount();
            echo "<p>Polls: ".$pollCount."</p>";
            echo "<p>Poll Responses: ".$pollResponseCount."</p>";
            echo "<p>Zones: ".$zoneCount."</p>";
            Dimbal_DPM_PRO::addUserMessage("Plugin Framework Report Generated.");
            break;
        case '2':
            // Reset Poll Responses for given Poll
            $pollId = Dimbal_DPM_PRO::getRequestVarIfExists('pollId');
            $poll = DimbalPollQuestion_DPM_PRO::get($pollId);
            if(!empty($poll)){
                DimbalPollResponse_DPM_PRO::deleteByPollId($pollId);
                $poll->responseCount = 0;
                $poll->hitCount = 0;
                $poll->save();

                Dimbal_DPM_PRO::addUserMessage("Poll Responses Deleted.");
            }
            break;
        case '3':
            /*
            for($i=0;$i<100;$i++){
                DimbalPollManager_DPM_PRO::buildSampleData();
            }
            */
            DimbalPollManager_DPM_PRO::buildSampleData();
            Dimbal_DPM_PRO::addUserMessage("Same Data Created.");
            break;
    }

    echo '<hr />';
}


//////////// SETTINGS

echo '<h3>Settings</h3>';

// See if the editor was passed
$editor = Dimbal_DPM_PRO::getRequestVarIfExists('formEditor');

// Get the Settings Object
$object = DimbalSetting_DPM_PRO::getSettingsObject();

// Update or Insert the Choices as appropriate
$options = DimbalPollManager_DPM_PRO::buildSettingsEditorOptions();
if($object && $editor){

    // Save the changes from the editor into the object
    $object = DimbalEditor_DPM_PRO::saveEditorChanges($object,$options,$_REQUEST);

    // Now set the cache object back
    DimbalSetting_DPM_PRO::$settings = $object;

    // Now rebuild the options with the new saved data
    $options = DimbalPollManager_DPM_PRO::buildSettingsEditorOptions();

    echo "<hr />";

}

// Build the editor in almost all circumstances
echo DimbalEditor_DPM_PRO::buildEditor($options, '#');

echo '<hr />';

echo '<h3>Tools</h3>';

// General tools
echo "<hr />";
echo "<h2>Framework Report</h2>";
echo "<p>Build a report showcasing details about your stored objects.</p>";
echo Dimbal_DPM_PRO::buildButton(array('text'=>'Build Framework Report','params'=>array_merge($defaultParams, array('ac'=>'1'))));

// Dimbal Poll Manager
$allPolls = DimbalPollQuestion_DPM_PRO::getAll();
// Clear responses for a given poll
?>
<hr />
<h2>Delete Poll Responses</h2>
<p>Delete all Poll Responses for a given Poll.  Once deleted, responses cannot be recovered. The hit counter for a poll is also reset.</p>
<form method="post">
    <input type="hidden" name="page" value="<?=(Dimbal_DPM_PRO::buildPageSlug(DimbalPollManager_DPM_PRO::PAGE_SETTINGS))?>">
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
<?php

echo "<hr />";
echo "<h2>Build Sample Data</h2>";
echo "<p>This tool will create several sample Zones, Polls and Responses to help in demo'ing this software.</p>";
echo Dimbal_DPM_PRO::buildButton(array('text'=>'Create Sample Data','params'=>array_merge($defaultParams, array('ac'=>'3'))));


?>

<hr />


<?php
// Close the wrapper
echo Dimbal_DPM_PRO::buildFooter();
