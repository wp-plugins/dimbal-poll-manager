<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/13/15
 * Time: 12:33 PM
 *
 * Base class for the Common Data Objects stored within this Plugin
 */
class DimbalStandardObjectRecord_DPM_PRO{

    public $id;                             // The ID of the Object
    public $status=self::STATUS_ACTIVE;     // Status flag whether the object is active or inactive
    public $lastModified = 0;               // The date the object was last saved
    public $startDate=0;                    // The start date for the object to be considered public
    public $endDate=0;                      // The end date for the object to be considered public
    public $enforceStartEndDates=false;     // Whether or not to enforce the start and end dates
    public $createdDate=0;                  // The date the object was created on
    public $hitCount=0;                     // How many hits the object has received
    public $lastHitDate=0;                  // The date of the last hit for this object

    // Child classes should overwrite this constant
    const TABLE_NAME="";

    // Status markers
    const STATUS_ACTIVE=1;
    const STATUS_INACTIVE=2;

    // Delete Salt - Helps to protect against unintended delete requests
    const DELETE_SALT='SAFIASHIUHFLK#(#O#P!@123nklnlj123e';

    /*
     * Some common setup routines across all objects
     */
    public function create(){
        $this->createdDate = time();
        $this->status = self::STATUS_INACTIVE;
        $this->setLastModified();
    }

    /*
     * Pack the contents of the Object into a single field for DB Storage
     */
    public static function pack($object){
        return base64_encode(serialize($object));
    }

    /*
     * Unpack the contents of the Object from the Database
     */
    public static function unpack($object){
        return unserialize(base64_decode($object));
    }

    /*
     * Function to validate and unpack a single object
     */
    public static function unpackObject($packedObject){
        $goodObject = false;
        if(is_array($packedObject) && array_key_exists('data',$packedObject)){
            // Good Object - unpack
            $goodObject = self::unpack($packedObject['data']);
        }
        return $goodObject;
    }

    /*
     * Function to validate and unpack a group of objects in bulk
     */
    public static function unpackObjects($packedObjects){
        $goodObjects = array();
        foreach($packedObjects as $packedObject){
            if(is_array($packedObject) && array_key_exists('data',$packedObject)){
                // Good Object - unpack
                $goodObjects[] = self::unpack($packedObject['data']);
            }
        }
        return $goodObjects;
    }

    /*
     * Returns a single object from the calling Database Table
     */
    public static function get($id){

        // Setup the variables
        global $wpdb;
        $tableName = static::getTableName();

        // Query the Data
        $sql = $wpdb->prepare(
            "
            SELECT * FROM $tableName
            WHERE id=%d
            ",
            $id
        );

        // Get the results
        $packedObject = self::executeRowQuery($sql, ARRAY_A);

        // Test the results are valid and unpack if so
        $goodObject = self::unpackObject($packedObject);

        // Return the results
        return $goodObject;
    }

    /*
     * A function to retrieve all objects of the calling type,
     * Returns by ID desc by default to eliminate the need for a "Most Recent" function
     */
    public static function getAll($start=0, $limit=5000){

        // Setup the variables
        global $wpdb;
        $tableName = static::getTableName();

        // Query the Data
        $sql = $wpdb->prepare(
            "
            SELECT * FROM $tableName
            ORDER BY id DESC
            LIMIT %d,%d
            ",
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

    public static function getCount(){

        // Setup the variables
        global $wpdb;
        $tableName = static::getTableName();

        // Query the Data
        $sql = "SELECT count(*) as count FROM $tableName";

        // Get the results
        $return = self::executeRowQuery($sql, ARRAY_A);

        // get the count
        $count = 0;
        if(array_key_exists('count', $return)){
            $count = $return['count'];
        }

        return $count;

    }

    /*
     * Delete a single object by ID reference -- currently only supporting deleting one by one
     */
    public static function deleteById($id){

        // Setup the variables
        global $wpdb;
        $tableName = static::getTableName();

        // Get the results
        $result = $wpdb->delete( $tableName, array( 'ID' => $id ), array( '%d' ) );

        // Return the result
        return $result;
    }

    /*
     * A generic Query wrapper to get a single result from the DB
     */
    public static function executeRowQuery( $sql, $outputType = ARRAY_A, $offset=0 ){
        global $wpdb;
        return $wpdb->get_row( $sql, $outputType, $offset );
    }

    /*
     * A generic query wrapper to execute a query that returns results
     */
    public static function executeQuery( $sql, $outputType = ARRAY_A ){
        global $wpdb;
        return $wpdb->get_results( $sql, $outputType );
    }

    /*
    * A generic query wrapper to execute a query, returns a numeric value indicating number of rows effected
    */
    public static function executeGenericQuery( $sql){
        global $wpdb;
        return $wpdb->query( $sql );
    }

    /*
     * Child classes should implement their own SAVE routines
     */
    public function save(){
        // Nothing to do in the parent
    }

    /*
     * Return the table name -- Child classes override this method
     */
    public static function getTableName(){
        global $wpdb;
        $name = $wpdb->prefix . DIMBAL_CONST_DPM_PRO_SLUG . '-' . static::TABLE_NAME;
        $name = str_replace("-","_",$name);
        return $name;
    }

    /*
     * Saves the current timestamp as the last modified timestamp
     */
    public function setLastModified(){
        $this->lastModified = time();
    }

    /*
     * Increase the internal hit counter for this object
     */
    public function increaseHitCount($hitNumber=1){
        $this->hitCount=$this->hitCount+$hitNumber;
        $this->lastHitDate = time();
    }

    public static function buildShortcodeHelper($params){

        if(empty($params)){
            return '';
        }

        $paramString = '';
        foreach($params as $key=>$value){
            $paramString .= ' '.$key.'="'.$value.'"';
        }

        $html = '['.DIMBAL_CONST_DPM_PRO_SLUG.$paramString.']';
        return $html;
    }

    /*
     * Returns the current Status flags as an array for Editors and such
     */
    public static function getAllStatusMarks(){
        $statusCollection = array();
        $statusCollection[self::STATUS_ACTIVE]=self::getFormattedStatusString(self::STATUS_ACTIVE);
        $statusCollection[self::STATUS_INACTIVE]=self::getFormattedStatusString(self::STATUS_INACTIVE);
        return $statusCollection;
    }

    /*
     * Returns a human readable version of the Status Flag
     */
    public static function getFormattedStatusString($status){
        $statusCollection = array(
            self::STATUS_ACTIVE=>'Active',
            self::STATUS_INACTIVE=>'Inactive',
        );
        return $statusCollection[$status];
    }

    /*
     * Returns an image representing the status
     */
    public static function getFormattedStatusImage($status, $width='32px'){
        $statusCollection = array(
            self::STATUS_ACTIVE=>'<img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/accept.png" style="width:'.$width.';" title="Active" />',
            self::STATUS_INACTIVE=>'<img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/cancel.png" style="width:'.$width.';" title="Inactive" />',
        );
        if(array_key_exists($status, $statusCollection)){
            return $statusCollection[$status];
        }
        return '';
    }

    /*
     * Gets a date variable according to the default format
     */
    public function getFormattedDate($variable){
        return self::formatDate($this->$variable);
    }

    /*
     * Format a timestamp or string according to the JQuery default Date format
     */
    public static function formatJQueryDate($date){
        return self::formatDate($date, "m/j/Y");
    }

    /*
     * Format a timestamp or string according to the JQuery default Date/Time format
     */
    public static function formatJQueryDateTime($date){
        return self::formatDate($date, "m/j/Y H:i");
    }

    /*
     * Format a timestamp or string into a given passed format
     */
    public static function formatDate($date, $format="M j, Y"){
        if($date==''){
            return '';
        }elseif(is_numeric($date)){
            return date($format,$date);
        }else{
            $date = strtotime($date);
            return date($format,$date);
        }
    }

    /*
     * Format a timestamp or string into a given passed format
     */
    public static function formatDateTime($date, $format="M j, Y g:i:sa"){
        if($date==''){
            return '';
        }elseif(is_numeric($date)){
            return date($format,$date);
        }else{
            $date = strtotime($date);
            return date($format,$date);
        }
    }

    /*
     * Format an income date string from various formats into a unix timestamp
     */
    public static function formatIncomingDateString($date="now"){
        //Formats the date for save into the object
        $nullDates = array(null,0,"0","");
        if(in_array($date,$nullDates)){
            $date=time();
        }else{
            $date=strtotime($date);
        }
        return $date;
    }

    /*
     * Returns a unix timestamp formatted for the Start of Day for a given unix timestamp
     */
    public static function standardizeForStartOfDay($date){
        return mktime(0, 0, 0, date("m", $date), date("d", $date), date("Y", $date));
    }

    /*
     * Returns a unix timestamp formatted for the End of Day for a given unix timestamp
     */
    public static function standardizeForEndOfDay($date){
        return mktime(23, 59, 59, date("m", $date), date("d", $date), date("Y", $date));
    }

    /*
     * Returns a simple Key=>Value pair array for a batch of objects
     */
    public static function getBasicArrayFromObjects($objects,$fieldValue='text',$fieldKey='id'){
        $items = array();
        foreach($objects as $object){
            $items[$object->$fieldKey]=$object->$fieldValue;
        }
        return $items;
    }


    /*
     * Editor and Manager functions -- in most cases they will be overriden
     */
    public static function editorBuildOptions($object){
        return array();
    }

    public static function managerBuildOptions($objects){
        return array();
    }



    public function getDeleteObjectHash(){
        $hash = md5($this->id.__CLASS__.self::DELETE_SALT);
        return $hash;
    }

    public function getObjectHashId(){
        return spl_object_hash($this);
    }

    public static function getObjectByClassAndId($className, $id){
        $object = call_user_func(array($className, 'get'), $id);
        return $object;
    }

    public static function checkForDelete($options){
        $defaults = array(
            'class'=>get_called_class(),
            'requestVars'=>$_REQUEST
        );
        $options = array_merge($defaults, $options);
        $return=self::checkForDeleteConfirmation($options);
        if(!$return){
            $return = self::checkForDeleteRequest($options);
        }
        return $return;
    }

    public static function checkForDeleteRequest($options){
        //Logger::debug("Inside ".__CLASS__.__FUNCTION__);
        $requestVars = $options['requestVars'];
        $return = false;
        //Logger::debug("Delete(".$requestVars['delete'].") ID(".$requestVars['id'].")");
        if(isset($requestVars['delete'])){
            if(isset($requestVars['id'])){
                $objectToDelete = self::getObjectByClassAndId($options['class'],$requestVars['id']);
                if($objectToDelete){
                    $return = $objectToDelete->prepareDeleteConfirmation($options['class']);
                }else{
                    // Bad Delete request -- go to the redirect url
                }
            }else{
                // Bad Delete request -- go to the redirect url
            }
        }
        return $return;
    }

    public static function checkForDeleteConfirmation($options){
        //Logger::debug("Inside ".__CLASS__.__FUNCTION__);
        $requestVars = $options['requestVars'];
        $return = false;
        if(isset($requestVars['deleteHash'])){
            if(isset($requestVars['id'])){
                $objectToDelete = self::getObjectByClassAndId($options['class'],$requestVars['id']);
                if($objectToDelete){
                    return $objectToDelete->processDeleteRequest($requestVars);
                }
            }
        }
        return $return;
    }

    public function prepareDeleteConfirmation($type){
        //Logger::debug("Inside ".__CLASS__.__FUNCTION__);
        // A delete request was made and an ID was set... show the confirmation box for this delete object
        $hash = $this->getDeleteObjectHash();
        $msg = '';
        $msg .= '
			<div id="confirmationDeleteDialog" class="pBox3" style="">
				<img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/error.png" style="float:left; width:50px; padding-right:25px;" />

					<p>Please confirm that you would like to delete the following entry.  This action cannot be undone.</p>
					<div>Type: '.$type.' (ID: '.$this->id.')</div>
					<div>Created Date: '.$this->formatDate($this->createdDate).'</div>
					<br />
					<form method="post" action="#">
						<input type="hidden" name="deleteHash" value="'.$hash.'" />
						<input type="hidden" name="id" value="'.$this->id.'" />
						<input type="submit" class="button" name="submit" value="Confirm Delete" />
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="#" onclick="javascript:$(\'#confirmationDeleteDialog\').fadeOut();">Cancel</a>
					</form>
			</div>
		';
        return $msg;
    }

    public function processDeleteRequest($requestVars){
        //Logger::debug("Inside ".__CLASS__.__FUNCTION__);
        if(!isset($requestVars['deleteHash'])){
            //Logger::error('Delete Attempt made without a Delete Hash');
            return '';
        }

        $hash = $this->getDeleteObjectHash();
        if($requestVars['deleteHash']!=$hash){
            //Logger::error('Delete Attempt made with invalid Delete Hash Passed('.$requestVars['deleteHash'].') Generated('.$hash.')');
            return '';
        }

        //If it got this far then we are good to go... go ahead and do the delete
        try{
            $this->deleteById($this->id);
            $this->additionalDeleteSteps();
            unset($_REQUEST['id']); // Unset this var so we can go back to the manager
            //UserMessages::add('Object successfully deleted.', UserMessages::L_SUCCESS, true);
        }catch(Exception $e){
            //Logger::error("Error Caught Processing Delete command - were we acting upon the Standard Object? Message: ".$e->getMessage());
        }

        return '';

    }

    public function additionalDeleteSteps(){

    }


}
