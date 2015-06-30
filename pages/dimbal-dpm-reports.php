<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 8:47 PM
 * To change this template use File | Settings | File Templates.
 */


echo Dimbal_DPM_PRO::buildHeader(array(
    'title'=>'Reports and Analysis',
    'icon'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/info_rhombus.png',
    'description'=>'Create custom reports to view your sites poll usage and interactions.',
));

$polls = DimbalPollQuestion_DPM_PRO::getAll();
$zones = DimbalZone_DPM_PRO::getAllByTypeId(DimbalZone_DPM_PRO::TYPE_DPM);
?>

<div style="display:table; width:100%;">
    <div style="display:table-cell; width:50%;">
        <div>
            Single Poll Analysis:
            <br />
            <select id="pollId" name="pollId">
            <?php
            foreach($polls as $poll){
                echo "<option value='".$poll->id."'>".$poll->text."</option>";
            }
            ?>
            </select>
            <input type="button" value="Run Analysis" class="button" onclick="dimbalPoll_DPM_PRO.reportsPollAnalysis();">
        </div>
        <br />
        <div>
            Single Zone Analysis:
            <br />
            <select id="zoneId" name="zoneId">
                <?php
                foreach($zones as $zone){
                    echo "<option value='".$zone->id."'>".$zone->text."</option>";
                }
                ?>
            </select>
            <input type="button" value="Run Analysis" class="button" onclick="dimbalPoll_DPM_PRO.reportsZoneAnalysis();">
        </div>
    </div>
<!--
    <div style="display:table-cell; width:50%;">
        <div>
            <form action="#" method="post">
                <input type="hidden" name="page" value="<?=(Dimbal_DPM_PRO::buildPageSlug(DimbalPollManager_DPM_PRO::PAGE_REPORTS))?>">
                <input type="hidden" name="ac" value="3">
                Application Statistics Report:
                <input type="submit" value="Run Report">
            </form>
        </div>
        <br />
        <div>
            <form action="#" method="post">
                <input type="hidden" name="page" value="<?=(Dimbal_DPM_PRO::buildPageSlug(DimbalPollManager_DPM_PRO::PAGE_REPORTS))?>">
                <input type="hidden" name="ac" value="4">
                Response Timeframe Report:
                <input type="submit" value="Run Report">
            </form>
        </div>
    </div>
-->
</div>
<hr />
<div id="reportResponse">

    <h3 style="text-align: center;">Choose a Report above.</h3>

    <p style="text-align: center;">Empty Polls or Zones will not return any data graphs.</p>

</div>
<?

$ac = Dimbal_DPM_PRO::getRequestVarIfExists('ac');
switch($ac){
    case '1':
        // Poll Analysis
        $pollId = Dimbal_DPM_PRO::getRequestVarIfExists('pollId');
        if(!empty($pollId)){
            $poll = DimbalPollQuestion_DPM_PRO::get($pollId);
            if(!empty($poll)){
                $dataString = $poll->getChartStringPollAnalysis();

            }
        }
        break;
    case '2':
        // Zone Analysis
        $zoneId = Dimbal_DPM_PRO::getRequestVarIfExists('zoneId');
        if(!empty($zoneId)){
            $zone = DimbalPollQuestion_DPM_PRO::get($zoneId);
            if(!empty($zone)){

            }
        }
        break;
    case '3':
        // App Report

        break;
    case '4':
        // Timeframe Report

        break;
}


// Close the wrapper
echo Dimbal_DPM_PRO::buildFooter();

