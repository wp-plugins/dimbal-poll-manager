<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 5/4/15
 * Time: 10:45 PM
 * To change this template use File | Settings | File Templates.
 */
class DimbalSetting_DPM_FREE{

    public static $settings = null;

    // Database Install Routine
    public static function installDatabase(){
        global $wpdb;

        // Get the DB CharSet
        $charset_collate = $wpdb->get_charset_collate();

        // Setup the Poll Question table
        $table_name = self::getTableName();
        $sql = "
            CREATE TABLE $table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                data mediumblob,
                UNIQUE KEY id (id)
            ) $charset_collate;
            ";

        // Run the SQL
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

    }

    public function __construct(){

        global $wpdb;
        $table_name = self::getTableName();

        //Save in the DB
        try{
            $wpdb->insert(
                $table_name,
                array(
                    'data' => DimbalStandardObjectRecord_DPM_FREE::pack($this)
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
        try{
            $wpdb->update(
                $table_name,
                array(
                    'data' => DimbalStandardObjectRecord_DPM_FREE::pack($this)
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

    /*
    * A function to retrieve all objects of the calling type,
    * Returns by ID desc by default to eliminate the need for a "Most Recent" function
    */
    public static function getFromDb(){

        // Setup the variables
        global $wpdb;
        $settingsObject = null;
        $tableName = static::getTableName();

        // Query the Data
        $sql = "SELECT * FROM $tableName ORDER BY id DESC";

        // Get the results
        $packedObjects = self::executeQuery($sql, ARRAY_A);

        // Test the results are valid and unpack if so
        $goodObjects = DimbalStandardObjectRecord_DPM_FREE::unpackObjects($packedObjects);

        // Get the first object
        if(!empty($goodObjects)){
            $settingsObject = $goodObjects[0];
        }

        // Return the results
        return $settingsObject;
    }

    public static function init(){
        if(!empty(self::$settings)){
            // Settings have already been loaded...
            return;
        }

        // Get the settings out
        $settings = self::getFromDb();

        if(empty($settings)){
            // Make sure this wasn't just a transient network error
            $settings = self::getFromDb();
        }

        // Check to see if the settings are empty (meaning never been setup)
        if(empty($settings)){
            // Need to give the Admin User a Message -- or maybe not -- just consider it deactivated
            // Need to exit out of here though so that settings are not auto loaded in case of re-write
            // Might need to create a default single setting for activated the framework
            $settings = new DimbalSetting_DPM_FREE();
            $settings->plugin_enabled = true;
            $settings->save();
            self::$settings = $settings;
            return;
        }

        // Set the object into static cache (this will prevent future chick / egg problems below)
        // I.E. from here on in the Static Cache is used and updated
        self::$settings = $settings;

    }

    public static function getSettingsObject(){
        self::init();
        return self::$settings;
    }

    /*
     * Returns a named setting from the static settings object
     */
    public static function getSetting($settingName){
        self::init();

        // Get the setting
        if(!isset(self::$settings->$settingName)){
            // Setting is missing - return a null
            // This could happen if not yet setup
            return null;
        }

        // Check for the presence of the setting
        if(isset(self::$settings->$settingName)){
            return self::$settings->$settingName;
        }

        // The setting is missing or not setup properly - return a null
        return null;
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
     * Return the table name -- Child classes override this method
     */
    public static function getTableName(){
        global $wpdb;
        $name = $wpdb->prefix . DIMBAL_CONST_DPM_FREE_SLUG . '-settings';
        $name = str_replace("-","_",$name);
        return $name;
    }

}
