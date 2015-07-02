<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:26 PM
 * To change this template use File | Settings | File Templates.
 */
class DimbalPollQuestion_DPM_PRO extends DimbalStandardObjectRecord_DPM_PRO{

    public $text = '';
    public $choices = array();
    public $maxResponses=0;
    public $maxResponsesReached=false;
    public $responseCount=0;
    public $ignoredResponses=0;
    public $multipleResponses=false;
    public $viewResults=true;
    public $viewEarlyResults=false;
    public $choiceIdCounter=0;
    public $additionalHtml = '';
    public $showLegend=false;
    public $is3d=false;

    const TABLE_NAME = "poll-question";

    public function __construct(){

        global $wpdb;
        $table_name = self::getTableName();

        //Pass it to the parent to setup some common items
        $this->create();

        //Save in the DB
        try{
            $wpdb->insert(
                $table_name,
                array(
                    'data' => self::pack($this)
                ),
                array(
                    '%s'
                )
            );
        }catch(Exception $e){
            //Logger::debug('Error creating Poll: '.$e->getMessage());
            return false;
        }

        //Get the ID of the inserted row and save it back to the object
        try{
            $this->id = $wpdb->insert_id;
            $this->save();
        }catch(Exception $e){
            //Logger::debug('Error creating Poll: '.$e->getMessage());
            return false;
        }

        //Return the object
        return $this;
    }

    public function save(){
        global $wpdb;
        $table_name = self::getTableName();
        $this->setLastModified();
        try{
            $wpdb->update(
                $table_name,
                array(
                    'data' => self::pack($this)
                ),
                array( 'ID' => $this->id ),
                array(
                    '%s'
                ),
                array( '%d' )
            );
        }catch(Exception $e){
            //Logger::debug('Error saving Object: '.$e->getMessage());
            return false;
        }
        return true;
    }

    // Controlled by Parent Class
    //public static function getTableName(){}

    // Controlled by Parent Class
    //public static function get($id){}

    // Controlled by Parent Class
    //public static function getAll($start=0,$limit=500){}

    // Controlled by Parent Class
    //public static function deleteById($id){}

    public static function getAllByZoneId($zoneId, $start=0, $limit=5000){

        // Setup the variables
        global $wpdb;
        $pollTableName = self::getTableName();
        $zoneItemTableName = DimbalZoneItem_DPM_PRO::getTableName();

        // Query the Data
        $sql = $wpdb->prepare(
            "
            SELECT * FROM $pollTableName poll
              INNER JOIN $zoneItemTableName zoneitem
              ON poll.id = zoneitem.itemId
            WHERE zoneitem.zoneId = %d
            ORDER BY poll.id DESC
            LIMIT %d,%d
            ",
            $zoneId,
            $start,
            $limit
        );

        // Get the results
        $packedObjects = self::executeQuery($sql, ARRAY_A);

        // Test the results are valid and unpack if so
        $goodObjects = self::unpackObjects($packedObjects);

        // Return the results
        return $goodObjects;

    }

    public static function editorBuildOptions($object){

        $zones = DimbalZone_DPM_PRO::getAllByTypeId(DimbalZone_DPM_PRO::TYPE_DPM);
        $zonesArray = DimbalZone_DPM_PRO::getBasicArrayFromObjects($zones);

        $options=array();
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Basic Poll Properties',
        );
        $options[]=array(
            'title'=>'ID',
            'objectType'=>DimbalEditor_DPM_PRO::OT_NUMERIC,
            'objectName'=>'id',
            'formType'=>DimbalEditor_DPM_PRO::ET_TEXT_READONLY,
            'value'=>($object)?$object->id:'',
            'help'=>''
        );
        $options[]=array(
            'title'=>'Question Text',
            'objectType'=>DimbalEditor_DPM_PRO::OT_STRING,
            'objectName'=>'text',
            'formType'=>DimbalEditor_DPM_PRO::ET_TEXT,
            'value'=>($object)?$object->text:'',
            'help'=>'The Question displayed for the Poll',
            'size'=>100,
        );
        $options[]=array(
            'title'=>'Status',
            'objectType'=>DimbalEditor_DPM_PRO::OT_NUMERIC,
            'objectName'=>'status',
            'formType'=>DimbalEditor_DPM_PRO::ET_MENU_STATUS,
            'formOptions'=>DimbalStandardObjectRecord_DPM_PRO::getAllStatusMarks(),
            'value'=>($object)?$object->status:DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE,
            'help'=>'Select whether this Poll is active or inactive'
        );
        $options[]=array(
            'title'=>'Answer Choices',
            'objectType'=>DimbalEditor_DPM_PRO::OT_ARRAY,
            'objectName'=>'choices',
            'formType'=>DimbalEditor_DPM_PRO::ET_DPM_ANSWER_CHOICE_PICKER,
            'formOptions'=>($object)?$object->getAllAnswerChoices(false):array(),
            'help'=>'Enter the Poll choices for users to vote for. Place a Checkmark next to choices you want to be active.'
        );
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Zone Affiliation',
        );
        $options[]=array(
            'title'=>'Assigned Zones',
            'objectType'=>DimbalEditor_DPM_PRO::OT_ARRAY,
            'objectName'=>'zones',
            'formOptions'=>$zonesArray,
            'formType'=>DimbalEditor_DPM_PRO::ET_ITEM_ZONE_PICKER,
            'value'=>($object)?DimbalZone_DPM_PRO::getZonesForItem($object->id):'',
            'help'=>'Select the Zones that this Poll should be included in.'
        );
        if($object){
            $options[]=array(
                'rowType'=>'SectionHeader',
                'title'=>'Shortcode',
            );
            $options[]=array(
                'title'=>'Shortcode',
                'objectType'=>DimbalEditor_DPM_PRO::OT_SKIP,
                'objectName'=>'skip',
                'formType'=>DimbalEditor_DPM_PRO::ET_TEXT_READONLY,
                'value'=>$object->getShortcode(),
                'help'=>'Include this shortcode in your pages and posts to display this poll'
            );
        }

        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Stats Tracking',
        );
        $options[]=array(
            'title'=>'Created Date',
            'objectType'=>DimbalEditor_DPM_PRO::OT_DATE,
            'objectName'=>'createdDate',
            'formType'=>DimbalEditor_DPM_PRO::ET_TEXT_READONLY,
            'value'=>($object)?DimbalStandardObjectRecord_DPM_PRO::formatDate($object->createdDate):'',
            'help'=>'The date the Zone was created on'
        );
        $options[]=array(
            'title'=>'Last Hit Date',
            'objectType'=>DimbalEditor_DPM_PRO::OT_DATE,
            'objectName'=>'lastHitDate',
            'formType'=>DimbalEditor_DPM_PRO::ET_TEXT_READONLY,
            'value'=>($object)?DimbalStandardObjectRecord_DPM_PRO::formatDate($object->lastHitDate,"M j, Y, g:i a"):'',
            'help'=>'The date the poll was last accessed.'
        );
        $options[]=array(
            'title'=>'Current Hit Count',
            'objectType'=>DimbalEditor_DPM_PRO::OT_NUMERIC,
            'objectName'=>'hitCount',
            'formType'=>DimbalEditor_DPM_PRO::ET_TEXT,
            'value'=>($object)?$object->hitCount:'',
            'help'=>'The current number of hits (displays) the Poll has received',
            'size'=>10,
        );
        $options[]=array(
            'title'=>'Current Response Count',
            'objectType'=>DimbalEditor_DPM_PRO::OT_NUMERIC,
            'objectName'=>'responseCount',
            'formType'=>DimbalEditor_DPM_PRO::ET_TEXT_READONLY,
            'value'=>($object)?$object->responseCount:'',
            'help'=>'The current number of responses the Poll has received.'
        );
        /*
        $options[]=array(
            'title'=>'Clear All Responses',
            'objectType'=>DimbalEditor_DPM_PRO::OT_SKIP,
            'objectName'=>'responseCount',
            'formType'=>DimbalEditor_DPM_PRO::ET_HTML,
            'value'=>($object)?'':'',
            'help'=>'Click this button to reset the saved responses on this Poll.  THIS ACTION CANNOT BE UNDONE.'
        );
        */
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Extended Poll Properties',
        );
        $options[]=array(
            'title'=>'Allow Multiple Responses',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'multipleResponses',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>($object)?$object->multipleResponses:DimbalSetting_DPM_PRO::getSetting('dpm_default_allow_multiple_responses'),
            'help'=>'Check to allow multiple responses from the same user. (based upon saved cookies)'
        );
        $options[]=array(
            'title'=>'Set Maximum Responses',
            'objectType'=>DimbalEditor_DPM_PRO::OT_NUMERIC,
            'objectName'=>'maxResponses',
            'formType'=>DimbalEditor_DPM_PRO::ET_TEXT,
            'value'=>($object)?$object->maxResponses:DimbalSetting_DPM_PRO::getSetting('dpm_default_max_responses'),
            'help'=>'Close the Poll once the maximum number of responses have been recorded. Use 0 for unlimited.',
            'size'=>10,
        );
        $options[]=array(
            'title'=>'Enforce Start and End Dates',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'enforceStartEndDates',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>($object)?$object->enforceStartEndDates:'',
            'help'=>'Check to make this poll active only within the Start and End Dates specified below.'
        );
        $options[]=array(
            'title'=>'Start Date',
            'objectType'=>DimbalEditor_DPM_PRO::OT_DATE,
            'objectName'=>'startDate',
            'formType'=>DimbalEditor_DPM_PRO::ET_DATE,
            'value'=>($object)?$object->startDate:'',
            'help'=>'You can optionally keep a Poll closed until a designated Start Date.  You must check the Enforce option above to turn this on.'
        );
        $options[]=array(
            'title'=>'End Date',
            'objectType'=>DimbalEditor_DPM_PRO::OT_DATE,
            'objectName'=>'endDate',
            'formType'=>DimbalEditor_DPM_PRO::ET_DATE,
            'value'=>($object)?$object->endDate:'',
            'help'=>'You can optionally close a Poll after a designated End Date.   You must check the Enforce option above to turn this on.'
        );
        $options[]=array(
            'title'=>'View Results',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'viewResults',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>($object)?$object->viewResults:DimbalSetting_DPM_PRO::getSetting('dpm_default_view_results'),
            'help'=>'Allow users to see the results of the poll after voting.'
        );
        $options[]=array(
            'title'=>'View Results BEFORE Voting',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'viewEarlyResults',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>($object)?$object->viewEarlyResults:DimbalSetting_DPM_PRO::getSetting('dpm_default_view_results_before_voting'),
            'help'=>'Allow users to see the results of the poll before voting.'
        );
        $options[]=array(
            'title'=>'Show Legend',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'showLegend',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>($object)?$object->showLegend:DimbalSetting_DPM_PRO::getSetting('dpm_default_show_legend'),
            'help'=>'If checked, will display a legend for the answer choices when the poll is displayed.'
        );
        $options[]=array(
            'title'=>'Use 3D Chart',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'is3d',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>($object)?$object->is3d:DimbalSetting_DPM_PRO::getSetting('dpm_default_use_3d_charts'),
            'help'=>'If checked, will display the chart as a 3D chart.'
        );
        $options[]=array(
            'title'=>'Additional HTML',
            'objectType'=>DimbalEditor_DPM_PRO::OT_STRING,
            'objectName'=>'additionalHtml',
            'formType'=>DimbalEditor_DPM_PRO::ET_TEXTAREA,
            'size'=>100,
            'value'=>($object)?$object->additionalHtml:'',
            'help'=>'You can optionally include any custom html underneath the Poll.'
        );
        return $options;
    }

    public static function managerBuildOptions($objects){
        $rows = array();
        foreach($objects as $object){
            //$responses = DimbalPollResponse_DPM_PRO::getAllByPollId($object->id);
            $responses=array();
            //Logger::debug("items: ID (".$object->id.") ".print_r($object->choices,true));
            $row = array();
            $row[] = array(
                'title'=>'ID',
                'content'=>$object->id,
            );
            $row[] = array(
                'title'=>'Status',
                'content'=>$object->getFormattedStatusImage($object->status, '24px'),
            );
            $row[] = array(
                'title'=>'Edit',
                'url'=>Dimbal_DPM_PRO::getPageUrl(DimbalPollManager_DPM_PRO::PAGE_POLLS, array('id'=>$object->id)),
                'image'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/page_edit.png',
                'image_tooltip'=>'Edit Poll',
            );
            $row[] = array(
                'title'=>'Text',
                'content'=>$object->text,
            );
            $row[] = array(
                'title'=>'Choices',
                'content'=>count($object->choices).' ',
            );
            /*
            $row[] = array(
                'title'=>'Report',
                'url'=>'reports.php?pollId='.$object->id,
                'image'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/document_layout.png',
                'image_tooltip'=>'Build Report',
            );
            */

            /*
            $row[] = array(
                'title'=>'Preview',
                'url'=>$object->getPreviewUrl(),
                //'target'=>'_blank',
                'image'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/magnifier.png',
                'image_tooltip'=>'Preview Poll',
            );
            */

            $row[] = array(
                'title'=>'Created Date',
                'content'=>DimbalStandardObjectRecord_DPM_PRO::formatDate($object->createdDate),
            );

            $row[] = array(
                'title'=>'Shortcode',
                'content'=>"<input size='20' onclick='this.focus();this.select();' type='text' value='".$object->getShortcode()."' />",
            );

            if($object->multipleResponses){
                $row[] = array(
                    'title'=>'<img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/comments_add.png" title="Users can submit multiple answers" />',
                    'image'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/comments_add.png',
                    'image_tooltip'=>'Multiple Responses Allowed',
                );
            }else{
                $row[] = array(
                    'title'=>'<img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/comments_add.png" title="Users can submit multiple answers" />',
                    'content'=>''
                );
            }
            if($object->viewResults){
                $row[] = array(
                    'title'=>'<img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/google_custom_search.png" title="Users can view poll results" />',
                    'image'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/google_custom_search.png',
                    'image_tooltip'=>'Users can view polls results',
                );
            }else{
                $row[] = array(
                    'title'=>'<img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/google_custom_search.png" title="Users can view poll results" />',
                    'content'=>''
                );
            }
            $row[] = array(
                'title'=>'Hits',
                'content'=>max(0,$object->hitCount).' ',
            );
            $row[] = array(
                'title'=>'Responses',
                'content'=>max(0,$object->responseCount).' ',
            );

            $row[] = array(
                'title'=>'Delete',
                'url'=>Dimbal_DPM_PRO::getPageUrl(DimbalPollManager_DPM_PRO::PAGE_POLLS, array('delete'=>1,'id'=>$object->id)),
                'image'=>DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/delete.png',
                'image_tooltip'=>'Delete Poll',
            );
            $rows[]=$row;
        }
        return $rows;
    }

    public function getFullUrl(){
        //return DPM_APP_URL."poll/index.php?poll=".$this->id;
    }

    public function getPreviewUrl(){
        return Dimbal_DPM_PRO::getPageUrl(DimbalPollManager_DPM_PRO::PAGE_PREVIEW, array('ac'=>'1','pollId'=>$this->id));
    }

    public function getDisplayCode($options=array()){

        $html = '';
        $html .= '<div class="DIMBAL_CONST_DPM_PRO_SLUG-WidgetWrapper" dpm_poll="'.$this->id.'">';
        //$html .= '<a href="http://www.dimbal.com">Loading the Dimbal Poll Manager</a>';
        $html .= '</div>';
        return $html;

    }

    //Checks whether or not the max number of responses has already been reached
    public function maxResponsesReached(){
        if($this->maxResponses=0 || $this->maxResponses = ''){
            return false;
        }
        if($this->responseCount >= $this->maxResponses){
            return true;
        }
        return false;
    }

    public function recordResponse($responseId,$responseDate=null){

        if(isset($this->maxResponsesReached) && $this->maxResponsesReached){
            //Fail silently
            $this->ignoredResponses++;
            Dimbal_DPM_PRO::logMessage("Response ID passed for a poll that has reached max responses: Poll (".$this->id.") Response(".$responseId.")");
            return false;
        }

        //Ensure that the status is set appropriately
        if($this->status!=self::STATUS_ACTIVE){
            //The poll is something other then active... ignore the response
            $this->ignoredResponses++;
            Dimbal_DPM_PRO::logMessage("Response ID passed for a poll that is not currently active: Poll (".$this->id.") Status (".$this->status.") Response(".$responseId.")");
            return false;
        }

        //Validate the choice passed is inside the poll
        if(!array_key_exists($responseId, $this->choices)){
            //Logger::error("Response ID passed is not in the choice list: Poll (".$this->id.") Response(".$responseId.")");
        }

        //We are able to record this response... proceed
        Dimbal_DPM_PRO::logMessage("Creating Response Record");
        $pollResponse = new DimbalPollResponse_DPM_PRO($this->id,$responseId,$responseDate);

        //Update the poll counter
        $this->responseCount++;
        $this->save();

        try{
            // headers_sent() will return FALSE if no HTTP headers have already been sent or TRUE otherwise.
            if(!headers_sent()){
                setcookie(self::buildCookieString($this->id), true, time()+60*60*24*365);
            }
            //Logger::debug("Cookie Set");
        }catch(Exception $e){
            //Logger::debug("Error setting Cookie: ".$e->getMessage());
        }
        //Logger::debug("COOKIES AFTER SETTING: ".print_r($_COOKIE, true));
        return $pollResponse;

    }

    public static function buildCookieString($pollId){
        return DIMBAL_CONST_DPM_PRO_SLUG."_pr_".$pollId;
    }

    public function hasAlreadyResponded(){
        if(array_key_exists(self::buildCookieString($this->id), $_COOKIE)){
            //Cookie Exists
            return true;
        }
        return false;
    }

    public function saveAnswerChoice($id=0, $text, $status){
        if($status == "DELETE"){
            $this->deleteAnswerChoice($id);
        }elseif($status == self::STATUS_ACTIVE || $status == self::STATUS_INACTIVE){
            if($id != 0 && array_key_exists($id, $this->choices)){
                //Item exists - no need to create new object
                $this->choices[$id]->text=$text;
                $this->choices[$id]->status=$status;
                $this->save();
            }else{
                if(strlen(trim($text))>0){
                    $id = $this->choiceIdCounter + 1;
                    $this->choiceIdCounter = $id;
                    $choice = new DimbalPollAnswerChoice_DPM_PRO($id, $text, $status);
                    $this->choices[$choice->id]=$choice;
                    $this->save();
                }
            }
        }
    }

    /*
     *	Generally used during Bulk import type routines when several choices are being added at once
     */
    public function addNewAnswerChoices($choices){
        foreach($choices as $text){
            $id = $this->choiceIdCounter + 1;
            $this->choiceIdCounter = $id;
            $choice = new DimbalPollAnswerChoice_DPM_PRO($id, $text, self::STATUS_ACTIVE);
            $this->choices[$choice->id]=$choice;
        }
    }

    public function deactivateAnswerChoice($id){
        if(array_key_exists($id, $this->choices)){
            $this->choices[$id]->status=self::STATUS_INACTIVE;
        }
    }

    public function deleteAnswerChoice($id){
        //For now just remove it from the array
        $choices = array();
        $found = false;
        foreach($this->choices as $choice){
            if($choice->id == $id){
                //found the ID to delete
                $found = true;
            }else{
                $choices[$choice->id]=$choice;
            }
        }
        if($found){
            $this->choices = $choices;
            $this->save();
        }
        //Now remove all responses that are using that option (and decrement the hit count)
        DimbalPollResponse_DPM_PRO::deleteByPollIdAndChoiceId($this->id, $id);
    }

    public function getAllAnswerChoices($simpleArray=true){
        $choices = array();
        foreach($this->choices as $choice){
            //if($choice instanceof DimbalPollAnswerChoice){
                if($simpleArray){
                    $choices[$choice->id]=$choice->text;
                }else{
                    $choices[$choice->id]=$choice;
                }
            //}
        }
        //Logger::debug("ANSWER CHOICES: ".print_r($choices,true));
        return $choices;
    }

    public function getActiveAnswerChoices(){
        $choices = array();
        foreach($this->choices as $choice){
            if($choice->status == self::STATUS_ACTIVE){
                $choices[$choice->id]=$choice;
            }
        }
        return $choices;
    }

    public function getShortcode(){
        return self::buildShortcodeHelper(array('poll_id'=>$this->id));
    }

    public function getResponsesCounts(){
        $responses = DimbalPollResponse_DPM_PRO::getAllByPollId($this->id);
        //Format the rests array for bar graph display
        $results = array();
        foreach($this->choices as $key=>$value){
            $data = array();
            $data['text']=$value->text;
            $data['id']=$key;
            $data['answers']=0;
            $results[$key]=$data;
        }
        foreach($responses as $response){
            $results[$response->responseChoiceId]['answers']++;
        }

        return $results;
    }

    public function getResponsesDates(){
        $responses = DimbalPollResponse_DPM_PRO::getAllByPollId($this->id);

        $results = array();
        foreach($responses as $response){
            $responseData = array();
            $date = $response->responseDate;
error_log("Date: ".$date);
            $responseData['date']=$date;
            $responseData['choice']=$response->responseChoiceId;
            $results[$response->id] = $responseData;
        }

        return $results;
    }

    public function getResponsesDatesByDate(){
        $responses = DimbalPollResponse_DPM_PRO::getAllByPollId($this->id);

        $results = array();
        foreach($responses as $response){
            $responseData = array();
            $date = $response->responseDate;
            $date = mktime(0, 0, 0, date('m',$date), date('d',$date), date('Y',$date));
            if(!array_key_exists($date, $results)){
                $results[$date]=0;
            }
            $results[$date]++;
        }

        // Get the highest and lowest dates
        $highest = 0;
        $lowest = 0;
        foreach($results as $date=>$count){
            if($date > $highest || empty($highest)){
                $highest = $date;
            }
            if($date < $lowest || empty($lowest)){
                $lowest = $date;
            }
        }

        // now fill in the blanks between them
        $newDate = $lowest;
        while($newDate < $highest){
            $newDate += 86400;
            if(!array_key_exists($newDate, $results)){
                $results[$newDate]=0;
            }
        }

        return $results;
    }

    public function additionalDeleteSteps(){
        DimbalPollResponse_DPM_PRO::deleteByPollId($this->id);
    }

}

