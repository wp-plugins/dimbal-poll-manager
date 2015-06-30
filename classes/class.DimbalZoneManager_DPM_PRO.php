<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:26 PM
 * To change this template use File | Settings | File Templates.
 */
class DimbalZoneManager_DPM_PRO{

    // General Settings and Constants

    // Database Install Routine
    public static function installDatabase(){
        global $wpdb;

        // Get the DB CharSet
        $charset_collate = $wpdb->get_charset_collate();

        // Setup the Poll Question table
        $zone_table_name = DimbalZone_DPM_PRO::getTableName();
        $zone_item_table_name = DimbalZoneItem_DPM_PRO::getTableName();
        $sql = "
            CREATE TABLE $zone_table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                typeId int(11) NOT NULL,
                data mediumblob,
                UNIQUE KEY id (id)
            ) $charset_collate;
            CREATE TABLE $zone_item_table_name (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                itemId int(11) NOT NULL,
                zoneId int(11) NOT NULL,
                UNIQUE KEY id (id)
            ) $charset_collate;
            ";

        // Run the SQL
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public static function shortcodeHandlerZone($atts){
        $html = "";
        $zone_id = 0;
        extract( shortcode_atts( array(
            'zone_id' => 0
        ), $atts ) );

        $zone = DimbalZone_DPM_PRO::get($zone_id);
        if(!empty($zone)){
            $html = $zone->getDisplayCode();
        }

        return $html;
    }

    public static function validateFreeZone($typeId){
        global $_POST;
        $zones = DimbalZone_DPM_PRO::getAllByTypeId($typeId);
        $zoneId = null;
        foreach($zones as $zone){
            if(!empty($zoneId)){
                DimbalZone_DPM_PRO::deleteById($zone->id);
            }else{
                $zoneId = $zone->id;
            }
        }

        if(empty($zoneId)){
            $zone = new DimbalZone_DPM_PRO();
            $zone->typeId = $typeId;
            $zone->text = "Default Zone";
            $zone->save();
            $zoneId = $zone->id;
        }

        if(empty($_REQUEST['id'])){
            $_REQUEST['id']=$zoneId;
        }

        return $zoneId;
    }

}
