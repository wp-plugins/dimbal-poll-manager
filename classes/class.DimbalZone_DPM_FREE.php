<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/31/15
 * Time: 10:01 AM
 * To change this template use File | Settings | File Templates.
 */
class DimbalZone_DPM_FREE extends DimbalStandardObjectRecord_DPM_FREE{

    public $text;
    public $items=array();
    public $additionalHtml = '';
    public $typeId = 0;
    public $notes = '';             // Generic Notes about the Zone
    public $showTitle=false;

    const TYPE_DPM = 1;
    const TYPE_DFM = 2;
    const TYPE_DBM = 3;
    const TYPE_DTM = 4;

    const TABLE_NAME = "zone";

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
                    'typeId' => $this->typeId,
                    'data' => self::pack($this)
                ),
                array(
                    '%d',
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
                    'typeId' => $this->typeId,
                    'data' => self::pack($this)
                ),
                array( 'ID' => $this->id ),
                array(
                    '%d',
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
    //public static function get($id){}

    // Controlled by Parent Class
    //public static function getAll($start=0,$limit=500){}

    // Controlled by Parent Class
    //public static function deleteById($id){}

    /*
     * A function to retrieve all objects based upon the Type of Zone being passed,
     * Returns by ID desc by default to eliminate the need for a "Most Recent" function
     */
    public static function getAllByTypeId($typeId, $start=0, $limit=500){

        // Setup the variables
        global $wpdb;
        $tableName = self::getTableName();

        // Query the Data
        $sql = $wpdb->prepare(
            "
            SELECT * FROM $tableName
            WHERE typeId = %d
            ORDER BY id DESC
            LIMIT %d,%d
            ",
            $typeId,
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

    /*
     * Deletes all Zone Objects by a certain Type ID
     */
    public static function deleteAllByTypeId($typeId){

        // Setup the variables
        global $wpdb;
        $tableName = self::getTableName();

        $result = $wpdb->delete( $tableName, array( 'typeId' => $typeId ), array( '%d' ) );

        // Return the result
        return $result;

    }

    /*
     * Add a bunch of new items for a given Zone
     */
    public function addItemIds($itemIds){
        foreach($itemIds as $itemId){
            $link = new DimbalZoneItem_DPM_FREE($itemId, $this->id);
        }
    }

    public function getItemIds(){
        $records = DimbalZoneItem_DPM_FREE::getAllForZoneId($this->id);
        $itemIds = array();
        foreach($records as $record){
            $itemIds[] = $record['itemId'];
        }

        error_log("RECORDS: ".print_r($records, true));
        error_log("ITEMIDS: ".print_r($itemIds, true));

        return $itemIds;
    }

    public function removeItemIds($itemIds){
        foreach($itemIds as $itemId){
            DimbalZoneItem_DPM_FREE::deleteSingleRelationship($itemId, $this->id);
        }
    }

    public function removeAllItemIds(){
        DimbalZoneItem_DPM_FREE::deleteAllForZoneId($this->id);
    }

    public static function getZonesForItem($itemId){
        $zones = array();
        $records = DimbalZoneItem_DPM_FREE::getAllForItemId($itemId);
        foreach($records as $record){
            if(array_key_exists('zoneId', $record)){
                $zones[] = $record['zoneId'];
            }
        }
        return $zones;
    }

    public static function getZoneObjectsForItem($itemId){
        $zoneIds = DimbalZoneItem_DPM_FREE::getAllForItemId($itemId);
        $objects = array();
        foreach($zoneIds as $zoneId){
            $zone = self::get($zoneId);
            if(!empty($zone)){
                $objects[]=$zone;
            }
        }
        return $objects;
    }

    public static function addZonesForItem($itemId, $zoneIds){
        foreach($zoneIds as $zoneId){
            $link = new DimbalZoneItem_DPM_FREE($itemId, $zoneId);
        }
    }

    public static function removeZonesForItem($itemId, $zoneIds){
        foreach($zoneIds as $zoneId){
            DimbalZoneItem_DPM_FREE::deleteSingleRelationship($itemId, $zoneId);
        }
    }

    public static function removeAllZonesForItem($itemId){
        DimbalZoneItem_DPM_FREE::deleteAllForItemId($itemId);
    }


    public static function editorBuildOptions($object){
        $zoneTypeId = self::getTypeIdByAppCode(DIMBAL_CONST_DPM_FREE_APP_CODE);
        $options=array();
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Basic Zone Properties',
        );
        $options[]=array(
            'title'=>'ID',
            'objectType'=>DimbalEditor_DPM_FREE::OT_NUMERIC,
            'objectName'=>'id',
            'formType'=>DimbalEditor_DPM_FREE::ET_TEXT_READONLY,
            'value'=>($object)?$object->id:'',
            'help'=>''
        );
        $typeIdToUse = ($object)?$object->typeId:$zoneTypeId;
        $options[]=array(
            'title'=>'Zone Type (HIDDEN)',
            'objectType'=>DimbalEditor_DPM_FREE::OT_NUMERIC,
            'objectName'=>'typeId',
            'formType'=>DimbalEditor_DPM_FREE::ET_HIDDEN,
            'value'=>$typeIdToUse,
            'help'=>'The Type of Zone.  Once created Zones cannot change type.'
        );
        $options[]=array(
            'title'=>'Zone Type',
            'objectType'=>DimbalEditor_DPM_FREE::OT_SKIP,
            'objectName'=>'skip',
            'formType'=>DimbalEditor_DPM_FREE::ET_HTML,
            'value'=>DimbalZone_DPM_FREE::getFormattedTypeString($typeIdToUse),
            'help'=>'The Type of Zone.  Once created Zones cannot change type.'
        );
        $options[]=array(
            'title'=>'Text Name',
            'objectType'=>DimbalEditor_DPM_FREE::OT_STRING,
            'objectName'=>'text',
            'formType'=>DimbalEditor_DPM_FREE::ET_TEXT,
            'value'=>($object)?$object->text:'',
            'help'=>'The Name of the Zone',
            'size'=>100,
        );
        $options[]=array(
            'title'=>'Show Name',
            'objectType'=>DimbalEditor_DPM_FREE::OT_BOOLEAN,
            'objectName'=>'showTitle',
            'formType'=>DimbalEditor_DPM_FREE::ET_CHECKBOX,
            'value'=>($object)?$object->showTitle:DimbalSetting_DPM_FREE::getSetting('dpm_default_show_legend'),
            'help'=>'If Checked, will display the Zone Name above the Item (if item type supports this feature)',
        );
        $options[]=array(
            'title'=>'Status',
            'objectType'=>DimbalEditor_DPM_FREE::OT_NUMERIC,
            'objectName'=>'status',
            'formType'=>DimbalEditor_DPM_FREE::ET_MENU_STATUS,
            'formOptions'=>DimbalStandardObjectRecord_DPM_FREE::getAllStatusMarks(),
            'value'=>($object)?$object->status:DimbalStandardObjectRecord_DPM_FREE::STATUS_ACTIVE,
            'help'=>'Select whether this Poll is active or inactive'
        );
        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Item Selection',
        );
        switch($zoneTypeId){
            case DimbalZone_DPM_FREE::TYPE_DPM:
                $polls = DimbalPollQuestion_DPM_FREE::getAll(0,1000);
                $pollsArray = DimbalStandardObjectRecord_DPM_FREE::getBasicArrayFromObjects($polls);
                $options[]=array(
                    'title'=>'Items',
                    'objectType'=>DimbalEditor_DPM_FREE::OT_ARRAY,
                    'objectName'=>'items',
                    'formOptions'=>$pollsArray,
                    'formType'=>DimbalEditor_DPM_FREE::ET_ZONE_ITEM_PICKER,
                    'value'=>($object)?$object->getItemIds():'',
                    'help'=>'Select the Polls that should be included in this Zone'
                );
                break;
            case DimbalZone_DPM_FREE::TYPE_DTM:
                $tips = DimbalTipItem::getAll(0,1000);
                $tipsArray = DimbalStandardObjectRecord_DPM_FREE::getBasicArrayFromObjects($tips);
                $options[]=array(
                    'title'=>'Items',
                    'objectType'=>DimbalEditor_DPM_FREE::OT_ARRAY,
                    'objectName'=>'items',
                    'formOptions'=>$tipsArray,
                    'formType'=>DimbalEditor_DPM_FREE::ET_ZONE_ITEM_PICKER,
                    'value'=>($object)?$object->getItemIds():'',
                    'help'=>'Select the Tips that should be included in this Zone'
                );
                break;
        }

        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Additional Information',
        );
        $options[]=array(
            'title'=>'Created Date',
            'objectType'=>DimbalEditor_DPM_FREE::OT_DATE,
            'objectName'=>'createdDate',
            'formType'=>DimbalEditor_DPM_FREE::ET_TEXT_READONLY,
            'value'=>($object)?DimbalStandardObjectRecord_DPM_FREE::formatDate($object->createdDate):'',
            'help'=>'The date the Zone was created on'
        );
        $options[]=array(
            'title'=>'Last Hit Date',
            'objectType'=>DimbalEditor_DPM_FREE::OT_DATE,
            'objectName'=>'lastHitDate',
            'formType'=>DimbalEditor_DPM_FREE::ET_TEXT_READONLY,
            'value'=>($object)?DimbalStandardObjectRecord_DPM_FREE::formatDate($object->lastHitDate,"M j, Y, g:i a"):'',
            'help'=>'The date the poll was last accessed.'
        );
        $options[]=array(
            'title'=>'Current Hit Count',
            'objectType'=>DimbalEditor_DPM_FREE::OT_NUMERIC,
            'objectName'=>'hitCount',
            'formType'=>DimbalEditor_DPM_FREE::ET_TEXT,
            'value'=>($object)?$object->hitCount:'',
            'help'=>'The current number of hits (displays) the Poll has received',
            'size'=>10,
        );
        $options[]=array(
            'title'=>'Additional HTML',
            'objectType'=>DimbalEditor_DPM_FREE::OT_STRING,
            'objectName'=>'additionalHtml',
            'formType'=>DimbalEditor_DPM_FREE::ET_TEXTAREA,
            'size'=>100,
            'value'=>($object)?$object->additionalHtml:'',
            'help'=>'You can optionally include any custom html underneath the Item. (if item type supports this feature)'
        );
        return $options;
    }

    public static function managerBuildOptions($objects){
        $rows = array();
        foreach($objects as $object){
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
                'url'=>Dimbal_DPM_FREE::getPageUrl(DIMBAL_CONST_DPM_FREE_PAGE_ZONES, array('id'=>$object->id, 'typeId'=>$object->typeId)),
                'image'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/page_edit.png',
                'image_tooltip'=>'Edit Zone',
            );
            $row[] = array(
                'title'=>'Text',
                'content'=>$object->text,
            );
            $row[] = array(
                'title'=>'Items',
                'content'=>count($object->items).' ',
            );
            /*
            $row[] = array(
                'title'=>'Report',
                'url'=>'reports.php?pollId='.$object->id,
                'image'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/document_layout.png',
                'image_tooltip'=>'Build Report',
            );
            */
            $typesWithPreview = array(
                self::TYPE_DPM,
                self::TYPE_DTM
            );
            if(in_array($object->typeId, $typesWithPreview)){
                $row[] = array(
                    'title'=>'Preview',
                    'url'=>$object->getPreviewUrl(),
                    'image'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/magnifier.png',
                    'image_tooltip'=>'Preview Zone',
                );
            }
            $row[] = array(
                'title'=>'Created Date',
                'content'=>DimbalStandardObjectRecord_DPM_FREE::formatDate($object->createdDate),
            );
            $row[] = array(
                'title'=>'Shortcode',
                'content'=>"<input size='20' onclick='this.focus();this.select();' type='text' value='".$object->getShortcode()."' />",
            );
            $row[] = array(
                'title'=>'Hits',
                'content'=>max(0, $object->hitCount).' ',
            );
            $row[] = array(
                'title'=>'Delete',
                'url'=>Dimbal_DPM_FREE::getPageUrl(DIMBAL_CONST_DPM_FREE_PAGE_ZONES, array('delete'=>1,'id'=>$object->id, 'typeId'=>$object->typeId)),
                'image'=>DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/delete.png',
                'image_tooltip'=>'Delete Poll',
            );
            $rows[]=$row;
        }
        return $rows;
    }

    /*
    * Returns the current TYPE flags as an array for Editors and such
    */
    public static function getAllTypeMarks(){
        $collection = array();
        $collection[self::TYPE_DPM]=self::getFormattedStatusString(self::TYPE_DPM);
        $collection[self::TYPE_DTM]=self::getFormattedStatusString(self::TYPE_DTM);
        return $collection;
    }

    /*
     * Returns a human readable version of the TYPE Flag
     */
    public static function getFormattedTypeString($id){
        $collection = array(
            self::TYPE_DPM=>'Polls',
            self::TYPE_DTM=>'Tips',
        );
        if(array_key_exists($id, $collection)){
            return $collection[$id];
        }
        return '';
    }

    /*
    * Returns a human readable version of the TYPE Flag
    */
    public static function getTypeIdByAppCode($appCode){
        $collection = array(
            'dpm'=>self::TYPE_DPM,
            'dtm'=>self::TYPE_DTM,
        );
        if(array_key_exists($appCode, $collection)){
            return $collection[$appCode];
        }
        return '';
    }

    public function getPreviewUrl(){
        return Dimbal_DPM_FREE::getPageUrl(DIMBAL_CONST_DPM_FREE_PAGE_PREVIEW, array('ac'=>'3','zoneId'=>$this->id));
    }

    public function getDisplayCode(){
        $html = '';
        switch($this->typeId){
            case self::TYPE_DPM:
                $html .= '<div class="DIMBAL_CONST_DPM_FREE_SLUG-WidgetWrapper" dpm_zone="'.$this->id.'">';
                //$html .= '<a href="http://www.dimbal.com">Loading the Dimbal Poll Manager</a>';
                $html .= '</div>';
                break;
            case self::TYPE_DTM:
                $html .= '<div class="dtmWidgetWrapper" dtm_zone="'.$this->id.'">';
                //$html .= '<a href="http://www.dimbal.com">Loading the Dimbal Tips Manager</a>';
                $html .= '</div>';
                break;
        }
        return $html;
    }

    public function getShortcode(){
        return self::buildShortcodeHelper(array('zone_id'=>$this->id));
    }

}
