<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/13/15
 * Time: 12:37 PM
 * To change this template use File | Settings | File Templates.
 */
class DimbalPollResponse_DPM_FREE extends DimbalStandardObjectRecord_DPM_FREE{
    public $lastModified;
    public $pollId;

    public $responseChoiceId;
    public $responseChoiceText;
    public $responseDate;
    public $responseIpAddress;
    public $responseUserAgent;
    public $responseRequestUri;

    const TABLE_NAME = "poll-response";

    public function __construct($pollId, $responseChoiceId, $responseDate=null){

        global $wpdb;
        $table_name = self::getTableName();

        //Get the Poll object
        $poll = DimbalPollQuestion_DPM_FREE::get($pollId);
        if(empty($poll)){
            // Poll could not be retrieved - do not save the response
            return false;
        }

        //Validate the selected choice is within the poll choices
        if(!array_key_exists($responseChoiceId, $poll->choices)){
            //Logger::debug('Error saving Poll Response.  Selected choice does not exist.');
            return false;
        }

        // Save the response choice text in case it changes
        $responseChoiceText = "";
        if(!empty($poll->choices[$responseChoiceId]->text)){
            $responseChoiceText = $poll->choices[$responseChoiceId]->text;
        }

        // Set the response date if not passed
        if(empty($responseDate)){
            $responseDate = time();
        }

        //Pass it to the parent to setup some common items
        $this->create();

        //Assign other values...
        $this->setLastModified();
        $this->pollId = $pollId;
        $this->responseChoiceId = $responseChoiceId;
        $this->responseChoiceText = $responseChoiceText;
        $this->responseDate = $responseDate;
        $this->responseIpAddress = $_SERVER['REMOTE_ADDR'];
        $this->responseUserAgent = $_SERVER['HTTP_USER_AGENT'];
        $this->responseRequestUri = $_SERVER['REQUEST_URI'];

        //Save in the DB
        try{
            $wpdb->insert(
                $table_name,
                array(
                    'pollId' => $this->pollId,
                    'choiceId' => $this->responseChoiceId,
                    'responseDate' => $this->responseDate,
                    'data' => self::pack($this)
                ),
                array(
                    '%d',
                    '%d',
                    '%d',
                    '%s'
                )
            );
        }catch(Exception $e){
            //Logger::debug('Error creating Response: '.$e->getMessage());
            return false;
        }

        //Get the ID of the inserted row and save it back to the object
        try{
            $this->id = $wpdb->insert_id;
            $this->save();
        }catch(Exception $e){
            //Logger::debug('Error creating Response: '.$e->getMessage());
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
                    'pollId' => $this->pollId,
                    'choiceId' => $this->responseChoiceId,
                    'responseDate' => $this->responseDate,
                    'data' => self::pack($this)
                ),
                array( 'ID' => $this->id ),
                array(
                    '%d',
                    '%d',
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
    //public static function getTableName(){}

    // Controlled by Parent Class
    //public static function get($id){}

    // Controlled by Parent Class
    //public static function getAll($start=0,$limit=500){}

    // Controlled by Parent Class
    //public static function deleteById($id){}

    public static function getAllByPollId($pollId, $start=0, $limit=10000){

        // Setup the variables
        global $wpdb;
        $tableName = self::getTableName();

        // Query the Data
        $sql = $wpdb->prepare(
            "
            SELECT * FROM $tableName
            WHERE pollId = %d
            ORDER BY id DESC
            LIMIT %d,%d
            ",
            $pollId,
            $start,
            $limit
        );

        // Get the results
        $packedObjects = self::executeQuery($sql);

        // Test the results are valid and unpack if so
        $goodObjects = self::unpackObjects($packedObjects);

        // Return the results
        return $goodObjects;
    }

    public static function deleteByPollId($pollId){

        // Setup the variables
        global $wpdb;
        $tableName = self::getTableName();

        // Query the Data
        $sql = $wpdb->prepare(
            "
            DELETE FROM $tableName
            WHERE pollId = %d
            ",
            $pollId
        );

        // Get the results
        $result = self::executeGenericQuery($sql);

        return;
    }

    public static function deleteByPollIdAndChoiceId($pollId, $choiceId){

        // Setup the variables
        global $wpdb;
        $tableName = self::getTableName();

        // Query the Data
        $sql = $wpdb->prepare(
            "
            DELETE FROM $tableName
            WHERE pollId = %d
              AND choiceId = %d
            ",
            $pollId,
            $choiceId
        );

        // Get the results
        $result = self::executeGenericQuery($sql);

        return;
    }
}
