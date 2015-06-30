<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/9/15
 * Time: 11:14 PM
 * To change this template use File | Settings | File Templates.
 */
class Dimbal_DPM_FREE{

    const USER_MESSAGES_KEY = "DIMBAL_CONST_DPM_FREE_USER_MESSAGES_COOKIE";

    /*
    * The version string for the Database
    */
    public static function buildDatabaseVersionString(){
        return DIMBAL_CONST_DPM_FREE_SLUG . '-version';
    }

    /*
    * Routine to check the Database version and upgrade the schema as needed.
    */
    public static function checkForUpgrade($versionString, $currentVersion, $installCallback){
        global $wpdb;
        $installedVersion = Dimbal_DPM_FREE::getInstalledVersionNumber($versionString);
        if ( $currentVersion != $installedVersion) {
            // Upgrade the Database
            call_user_func($installCallback);

            // Update the version number in the Options Database
            update_option( $versionString, $currentVersion );
        }
    }

    /*
    * Gets the Installed Version of the Dimbal Plugin and formats it for numeric values, etc
    */
    public static function getInstalledVersionNumber($versionString){
        $installed_ver = get_option( $versionString );
        if(empty($installed_ver)){
            $installed_ver = 0;
        }
        return $installed_ver;
    }

    /*
    * Checks the user defined settings to see if the Framework was disabled
    */
    public static function isPluginEnabled(){
        return DimbalSetting_DPM_FREE::getSetting('plugin_enabled');
    }

    /*
     * Called via the register function.  Use the $plugin_page variable to get the page slug.  The page must exist within the pages folder named according to the designated slug
     */
    public static function renderPage(){
        global $plugin_page;
        $plugin_page = str_replace("-free", "", $plugin_page);
        $plugin_page = str_replace("-pro", "", $plugin_page);
        include WP_PLUGIN_DIR.'/'.DIMBAL_CONST_DPM_FREE_SLUG.'/pages/'.$plugin_page.'.php';
    }

    /*
     * Builds a full localized slug from a page name
     */
    public static function buildPageSlug($page){
        return DIMBAL_CONST_DPM_FREE_SLUG.'-'.$page;
    }

    /*
     * Builds a page url for use in admin links and so forth.  Appended via a query string parameter.
     */
    public static function getPageUrl($page, $params=array()){
        $url = '?page=' . DIMBAL_CONST_DPM_FREE_SLUG.'-'.$page;
        if(!empty($params)){
            $url = add_query_arg($params, $url);
        }
        return $url;
    }

    public static function redirect($url){
        wp_redirect($url);
        exit();
    }

    public static function ajaxRegisterPublicHandlers($handlers, $callback){
        foreach($handlers as $action=>$handler){
            add_action( 'wp_ajax_nopriv_'.$action, $callback );     // Non logged in users
        }
    }

    public static function ajaxRegisterAdminHandlers($handlers, $callback){
        foreach($handlers as $action=>$handler){
            add_action( 'wp_ajax_'.$action, $callback );            // Logged in users
        }
    }

    public static function ajaxProcessHandler($handlers){

        // Setup the response Object -- ALWAYS JSON
        header( "Content-Type: application/json" );

        // Route the call as appropriate
        $response = '';
        $action = Dimbal_DPM_FREE::getRequestVarIfExists('action');
        if(array_key_exists($action, $handlers)){
            $call = $handlers[$action];
            $response = call_user_func($call);
        }

        echo json_encode($response);

        // Always exit after processing the AJAX
        exit;
    }

    /*
     * Takes in a class name such as "Dimbal" and returns an app formatted name such as "Dimbal_DPM"
     */
    public static function buildAppClassName($className){
        $newClassName = $className . '_' . strtoupper(DIMBAL_CONST_DPM_FREE_APP_CODE) . '_' . strtoupper(DIMBAL_CONST_DPM_FREE_PURCHASE_LEVEL);
        //error_log("Old Class Name [$className] New Class Name [$newClassName]");
        return $newClassName;
    }

    public static function buildButton($options){
        $defaults = array(
            'url'=>'?',
            'params'=>array(),
            'method'=>'post',
            'text'=>'New Button'
        );
        $options = array_merge($defaults, $options);

        $params = '';
        foreach($options['params'] as $k=>$v){
            if(!empty($params)){
                $params .= "&";
            }
            $params .= $k . "=" . $v;
        }
        $url = $options['url'].$params;

        $html = '
            <div style="display:inline-block; padding:2px;">
                <a href="'.$url.'"><div class="button">'.$options['text'].'</div></a>
            </div>
        ';

        return $html;
    }

    public static function buildHeader($options=array()){

        $defaults = array(
            'title'=>'Dimbal Software',
            'icon'=>null,
            'description'=>null,
            'buttons'=>array(),
        );
        $options = array_merge($defaults, $options);

        $html = '';

        $html .= '<div id="dimbalWrapper" class="dimbalWrapper">';   // Wrapper for all our pages

            $html .= '<div style="display:table; width:inherit;">';
                $html .= '<div style="display:table-cell; vertical-align:middle; width:57px;"><img src="'.$options['icon'].'" alt="'.$options['title'].'" style="vertical-align:middle; margin: 5px 20px 5px 5px;" /></div>';
                $html .= '<div style="display:table-cell; vertical-align:middle;"><h1 style="vertical-align:middle;">'.$options['title'].'</a></div>';
                $html .= '<div style="display:table-cell; vertical-align:middle; text-align:right;">';
                    $html .= '<p style="font-style:italic;">'.$options['description'].'</p>';
                    $html .= '<div>';
                    foreach($options['buttons'] as $buttonOptions){
                        $html .= self::buildButton($buttonOptions);
                    }
                    $html .= '</div>';
                $html .= '</div>';
            $html .= '</div>';

            $html .= '<br />';

        return $html;
    }

    public static function buildFooter(){

        $html = '';

        $html .= self::displayAllUserMessages();    // User Messages display - if any

        $html .= '</div>'; // Close the common wrapper

        return $html;
    }

    /*
      * Checks if the variable exists - does not change the value
      */
    public static function getRequestVarIfExists($fieldName){
        if(array_key_exists($fieldName, $_REQUEST)){
            return $_REQUEST[$fieldName];
        }
        return null;
    }

    public static function logMessage($msg){
        error_log($msg);
    }

    public static function logError($msg){
        error_log($msg);
    }

    public static function getUserMessages(){
        $messages = array();
        if(array_key_exists(self::USER_MESSAGES_KEY, $_SESSION)){
            $messages = $_SESSION[self::USER_MESSAGES_KEY];
        }
        if(empty($messages) || !is_array($messages)){
            $messages = array();
        }
        return $messages;
    }

    public static function addUserMessage($msg){
        self::startSession();   // make sure the session has started
        $messages = self::getUserMessages();
        $messages[] = $msg;
        $_SESSION[self::USER_MESSAGES_KEY] = $messages;
    }

    public static function displayAllUserMessages(){
        $messages = self::getUserMessages();

        $html = '';
        if(!empty($messages)){
            $html .= '<div id="dimbal-user-messages-tmp" style="display:none;">';
            foreach($messages as $message){
                $rand = rand(100000,999999);
                $html .= '<div class="dimbal-user-message" id="dimbal_user_message_'.$rand.'" onclick="dimbalUserMessages_DPM_FREE.remove('.$rand.')">'.$message.'</div>';
            }
            $html .= '</div>';
        }

        unset($_SESSION[self::USER_MESSAGES_KEY]);

        return $html;

    }

    /*
     * Start the PHP Session Object if it has not yet started
     */
    public static function startSession(){
        if(!session_id()) {
            session_start();
        }
    }



}
