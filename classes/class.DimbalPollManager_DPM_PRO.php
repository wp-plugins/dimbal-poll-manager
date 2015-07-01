<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:26 PM
 * To change this template use File | Settings | File Templates.
 */
class DimbalPollManager_DPM_PRO{

    const CURRENT_VERSION = 1;

    const PAGE_HOME = "home";
    const PAGE_POLLS = "polls";
    const PAGE_ZONES = "zones";
    const PAGE_REPORTS = "reports";
    const PAGE_SETTINGS = "settings";
    const PAGE_SUPPORT = "support";
    const PAGE_PREVIEW = "preview";

    /*
     * WordPress Action Hook for register_activation_hook
     */
    public static function wpActionActivate(){
        error_log("inside: ".__CLASS__."::".__FUNCTION__);
        self::installDatabase();

        // Build sample data if no data is saved.  Don't add sample data if data already exists
        $pollCount = DimbalPollQuestion_DPM_PRO::getCount();
        if(empty($pollCount)){
            self::buildSampleData();
        }
    }

    /*
    * WordPress Action Hook for plugins_loaded
    */
    public static function wpActionPluginsLoaded(){
        Dimbal_DPM_PRO::checkForUpgrade(Dimbal_DPM_PRO::buildDatabaseVersionString(), self::CURRENT_VERSION, array(Dimbal_DPM_PRO::buildAppClassName('DimbalPollManager'),'installDatabase'));
    }

    /*
     * WordPress Action Hook for widgets_init
     */
    public static function wpActionWidgetsInit(){
        register_widget( Dimbal_DPM_PRO::buildAppClassName('DimbalPollWidget') );
    }

    /*
    * WordPress Action Hook for widgets_init
    */
    public static function wpActionEnqueueScripts(){
        self::enqueuePublicResources();
    }

    /*
    * WordPress Action Hook for widgets_init
    */
    public static function wpActionAdminEnqueueScripts(){
        self::enqueuePublicResources();
        self::enqueueAdminResources();
    }

    public static function wpActionAdminMenu(){
        //add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position );
        add_menu_page( 'Dimbal Poll Manager', DIMBAL_CONST_DPM_PRO_PLUGIN_TITLE_SHORT, 'manage_options', Dimbal_DPM_PRO::buildPageSlug(self::PAGE_HOME), array(Dimbal_DPM_PRO::buildAppClassName('Dimbal'),'renderPage') );

        //add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function);
        add_submenu_page( Dimbal_DPM_PRO::buildPageSlug(self::PAGE_HOME), 'Poll Manager', 'Poll Manager', 'manage_options', Dimbal_DPM_PRO::buildPageSlug(self::PAGE_POLLS), array(Dimbal_DPM_PRO::buildAppClassName('Dimbal'),'renderPage'));
        add_submenu_page( Dimbal_DPM_PRO::buildPageSlug(self::PAGE_HOME), 'Zone Manager', 'Zone Manager', 'manage_options', Dimbal_DPM_PRO::buildPageSlug(self::PAGE_ZONES), array(Dimbal_DPM_PRO::buildAppClassName('Dimbal'),'renderPage'));
        add_submenu_page( Dimbal_DPM_PRO::buildPageSlug(self::PAGE_HOME), 'Reports', 'Reports', 'manage_options', Dimbal_DPM_PRO::buildPageSlug(self::PAGE_REPORTS), array(Dimbal_DPM_PRO::buildAppClassName('Dimbal'),'renderPage'));
        add_submenu_page( Dimbal_DPM_PRO::buildPageSlug(self::PAGE_HOME), 'Settings & Tools', 'Settings & Tools', 'manage_options', Dimbal_DPM_PRO::buildPageSlug(self::PAGE_SETTINGS), array(Dimbal_DPM_PRO::buildAppClassName('Dimbal'),'renderPage'));
        add_submenu_page( Dimbal_DPM_PRO::buildPageSlug(self::PAGE_HOME), 'Help & Support', 'Help & Support', 'manage_options', Dimbal_DPM_PRO::buildPageSlug(self::PAGE_SUPPORT), array(Dimbal_DPM_PRO::buildAppClassName('Dimbal'),'renderPage'));

        add_submenu_page( 'fake-slug-does-not-exist', 'Preview', 'Preview', 'manage_options', Dimbal_DPM_PRO::buildPageSlug(self::PAGE_PREVIEW), array(Dimbal_DPM_PRO::buildAppClassName('Dimbal'),'renderPage'));
    }

    // Database Install Routine
    public static function installDatabase(){
        global $wpdb;

        error_log("inside: ".__CLASS__."::".__FUNCTION__);

        // Get the DB CharSet
        $charset_collate = $wpdb->get_charset_collate();

        // Setup the Poll Question table
        $question_table_name = DimbalPollQuestion_DPM_PRO::getTableName();
        $response_table_name = DimbalPollResponse_DPM_PRO::getTableName();
        $sql = "
            CREATE TABLE $question_table_name (
                id int(11) NOT NULL AUTO_INCREMENT,
                data mediumblob,
                UNIQUE KEY id (id)
            ) $charset_collate;
            CREATE TABLE $response_table_name (
                id int(11) unsigned NOT NULL AUTO_INCREMENT,
                pollId int(11) NOT NULL,
                choiceId int(11) NOT NULL,
                responseDate int(15) NOT NULL,
                data mediumblob,
                UNIQUE KEY id (id)
            ) $charset_collate;
            ";

        // Run the SQL
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // Setup the Settings Table
        DimbalSetting_DPM_PRO::installDatabase();

        // Setup the Zones Table
        DimbalZoneManager_DPM_PRO::installDatabase();

        // Add an option to store the version number
        add_option( Dimbal_DPM_PRO::buildDatabaseVersionString(), self::CURRENT_VERSION );
    }

    /*
    * The version string for the Database
    */
    public static function buildDatabaseVersionString(){
        return DIMBAL_CONST_DPM_PRO_SLUG . '-version';
    }

    public static function buildSampleData(){
        // Add back in the Sample Zones
        $zone1 = new DimbalZone_DPM_PRO();
        $zone1->typeId = DimbalZone_DPM_PRO::TYPE_DPM;
        $zone1->text = "Music";
        $zone1->status = DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE;
        $zone1->save();

        $zone2 = new DimbalZone_DPM_PRO();
        $zone2->typeId = DimbalZone_DPM_PRO::TYPE_DPM;
        $zone2->text = "Movies";
        $zone2->status = DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE;
        $zone2->save();

        $timeEnd = time();
        $timeStart = $timeEnd - 604800; // Last 7 days

        // Add back in the Polls
        $poll1 = new DimbalPollQuestion_DPM_PRO();
        $poll1->text = "Which music genre is the best?";
        $poll1->hitCount = 123;
        $poll1->lastHitDate = rand($timeStart, $timeEnd);
        $poll1->multipleResponses = true;
        $poll1->status = DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE;
        $poll1->save();
        $poll1->saveAnswerChoice(0, "Classic Rock", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll1->saveAnswerChoice(1, "Popular", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll1->saveAnswerChoice(2, "Country", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll1->saveAnswerChoice(3, "Heavy Metal", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll1->saveAnswerChoice(4, "Rap", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll1->saveAnswerChoice(5, "R&B", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        self::sampleDataHelperCreateResponses($poll1, 5, 10);

        $poll2 = new DimbalPollQuestion_DPM_PRO();
        $poll2->text = "Which performer puts on a better show?";
        $poll2->hitCount = 254;
        $poll2->lastHitDate = rand($timeStart, $timeEnd);
        $poll2->multipleResponses = true;
        $poll2->status = DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE;
        $poll2->save();
        $poll2->saveAnswerChoice(0, "Beyonce", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll2->saveAnswerChoice(1, "Kid Rock", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll2->saveAnswerChoice(2, "U2", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll2->saveAnswerChoice(3, "Eminem", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll2->saveAnswerChoice(4, "Snoop Dogg", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll2->saveAnswerChoice(5, "Keith McGraw", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        self::sampleDataHelperCreateResponses($poll2, 5, 16);

        $poll3 = new DimbalPollQuestion_DPM_PRO();
        $poll3->text = "Which movie franchise is more entertaining?";
        $poll3->hitCount = 182;
        $poll3->lastHitDate = rand($timeStart, $timeEnd);
        $poll3->multipleResponses = true;
        $poll3->status = DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE;
        $poll3->save();
        $poll3->saveAnswerChoice(0, "Batman", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll3->saveAnswerChoice(1, "Lord of the Rings", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll3->saveAnswerChoice(2, "Jason Bourne", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll3->saveAnswerChoice(3, "X-Men", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll3->saveAnswerChoice(4, "Fast and Furious", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        self::sampleDataHelperCreateResponses($poll3, 4, 8);

        $poll4 = new DimbalPollQuestion_DPM_PRO();
        $poll4->text = "Which movie genre is the best?";
        $poll4->hitCount = 123;
        $poll4->lastHitDate = rand($timeStart, $timeEnd);
        $poll4->multipleResponses = true;
        $poll4->status = DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE;
        $poll4->save();
        $poll4->saveAnswerChoice(0, "Action", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll4->saveAnswerChoice(1, "Comedy", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll4->saveAnswerChoice(2, "Horror", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll4->saveAnswerChoice(3, "Drama", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        $poll4->saveAnswerChoice(4, "Musical", DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE);
        self::sampleDataHelperCreateResponses($poll4, 4, 124);

        $zone1Items = array($poll1->id, $poll2->id);
        $zone1->addItemIds($zone1Items);
        $zone1->items = $zone1Items;
        $zone1->hitCount = 243;
        $zone1->save();

        $zone2Items = array($poll1->id, $poll2->id, $poll3->id, $poll4->id);
        $zone2->addItemIds($zone2Items);
        $zone2->items = $zone2Items;
        $zone2->hitCount = 165;
        $zone2->save();
    }

    public static function sampleDataHelperCreateResponses($pollObject, $highestKey, $numResponses){
        //Make the date random within the last ~14 days
        $dateNow = time();
        for($i=0; $i<$numResponses; $i++){
            $daysRand = rand(0,14);
            $daysRand = $daysRand * 86400;
            $secondsRand = rand(0,86400);
            $datePrevious = $dateNow - $daysRand - $secondsRand;
            $rand = rand(0,$highestKey);
            $pollObject->recordResponse($rand, $datePrevious);
        }
    }

    public static function enqueuePublicResources(){
        wp_enqueue_style( DIMBAL_CONST_DPM_PRO_SLUG.'-main-css', DIMBAL_CONST_DPM_PRO_URL.'/css/dimbal-main.css' );
        wp_enqueue_script( DIMBAL_CONST_DPM_PRO_SLUG.'-main-js', DIMBAL_CONST_DPM_PRO_URL.'/js/dimbal-dpm.js', array( 'jquery' ) );

        // Don't forget to localize the data objects we want
        $dimbalVars = array(
            'slug' => DIMBAL_CONST_DPM_PRO_SLUG,
            'page' => DIMBAL_CONST_DPM_PRO_PAGE_PREFIX,
            'url' => DIMBAL_CONST_DPM_PRO_URL,
            'ajax_url' => admin_url('admin-ajax.php'),
        );
        wp_localize_script( DIMBAL_CONST_DPM_PRO_SLUG.'-main-js', 'dimbal_dpm_vars', $dimbalVars );
    }

    public static function enqueueAdminResources(){
        wp_enqueue_style( DIMBAL_CONST_DPM_PRO_SLUG.'-admin-css', DIMBAL_CONST_DPM_PRO_URL.'/css/dimbal-admin.css' );

        /*
        wp_enqueue_script( DIMBAL_CONST_DPM_PRO_SLUG.'-admin-js', DIMBAL_CONST_DPM_PRO_URL.'/js/dimbal-admin.js', array( 'jquery' ) );

        // Don't forget to localize the data objects we want
        $dimbalVars = array(
            'slug' => DIMBAL_CONST_DPM_PRO_SLUG,
            'page' => DIMBAL_CONST_DPM_PRO_PAGE_PREFIX,
            'url' => DIMBAL_CONST_DPM_PRO_URL,
            'ajax_url' => admin_url('admin-ajax.php'),
        );
        wp_localize_script( DIMBAL_CONST_DPM_PRO_SLUG.'-admin-js', 'dimbal_admin_vars', $dimbalVars );
        */
    }

    /*
     * Called via the register function.  Use the $plugin_page variable to get the page slug.  The page must exist within the pages folder named according to the designated slug
     */
    public static function renderPage(){
        Dimbal_DPM_PRO::renderPage();
    }

    public static function buildSettingsEditorOptions(){

        $object = DimbalSetting_DPM_PRO::getSettingsObject();

        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Global Framework Settings',
        );

        $options[]=array(
            'title'=>'Plugin Framework Enabled',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'plugin_enabled',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>(isset($object->plugin_enabled))?$object->plugin_enabled:true,
            'help'=>'True to enable the Dimbal Poll Manager Plugin, False to disable it without uninstalling it.  If False, will prevent the display of all user facing polls, zones, etc... Use this feature to disable the plugin globally without having to uninstall it.'
        );

        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Poll Manager Settings',
        );

        $options[]=array(
            'title'=>'New Poll Default - Allow Multiple Responses',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'dpm_default_allow_multiple_responses',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>(isset($object->dpm_default_allow_multiple_responses))?$object->dpm_default_allow_multiple_responses:false,
            'help'=>'If checked, new Polls will allow Multiple Responses by default.'
        );
        $options[]=array(
            'title'=>'New Poll Default - Maximum Responses',
            'objectType'=>DimbalEditor_DPM_PRO::OT_NUMERIC,
            'objectName'=>'dpm_default_max_responses',
            'formType'=>DimbalEditor_DPM_PRO::ET_TEXT,
            'value'=>(isset($object->dpm_default_max_responses))?$object->dpm_default_max_responses:0,
            'help'=>'The default number that new polls should use as the maximum responses to allow a poll to receive before auto closing the poll.  Use 0 for unlimited.'
        );
        $options[]=array(
            'title'=>'New Poll Default - View Results',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'dpm_default_view_results',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>(isset($object->dpm_default_view_results))?$object->dpm_default_view_results:true,
            'help'=>'If checked, new Polls will allow users to see results after voting by default.'
        );
        $options[]=array(
            'title'=>'New Poll Default - View Results Before Voting',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'dpm_default_view_results_before_voting',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>(isset($object->dpm_default_view_results_before_voting))?$object->dpm_default_view_results_before_voting:false,
            'help'=>'If checked, new Polls will allow users to see results before voting by default.'
        );
        $options[]=array(
            'title'=>'New Poll Default - Show Legend',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'dpm_default_show_legend',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>(isset($object->dpm_default_show_legend))?$object->dpm_default_show_legend:false,
            'help'=>'If checked, new Polls will show the legend by default.'
        );
        $options[]=array(
            'title'=>'New Poll Default - Use 3D Charts',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'dpm_default_use_3d_charts',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>(isset($object->dpm_default_use_3d_charts))?$object->dpm_default_use_3d_charts:false,
            'help'=>'If checked, new Polls will use the 3D chart option by default.'
        );

        $options[]=array(
            'rowType'=>'SectionHeader',
            'title'=>'Zone Manager Settings',
        );

        $options[]=array(
            'title'=>'New Zone Default - Show Name',
            'objectType'=>DimbalEditor_DPM_PRO::OT_BOOLEAN,
            'objectName'=>'dzm_default_show_name',
            'formType'=>DimbalEditor_DPM_PRO::ET_CHECKBOX,
            'value'=>(isset($object->dzm_default_show_name))?$object->dzm_default_show_name:false,
            'help'=>'If checked, new Zones will show the Zone name above the content.'
        );

        return $options;

    }

    public static function ajaxGetAllHandlerMappings(){
        $public = self::ajaxGetPublicHandlerMappings();
        $private = self::ajaxGetAdminHandlerMappings();
        $handlers = array_merge($public, $private);
        return $handlers;
    }

    public static function ajaxGetPublicHandlerMappings(){
        $handlers = array(
            'DIMBAL_CONST_DPM_PRO_SLUG-display-poll'=>array(Dimbal_DPM_PRO::buildAppClassName('DimbalPollManager'),'ajaxDisplayPoll'),
            'DIMBAL_CONST_DPM_PRO_SLUG-submit-poll'=>array(Dimbal_DPM_PRO::buildAppClassName('DimbalPollManager'),'ajaxSubmitPoll'),
            'DIMBAL_CONST_DPM_PRO_SLUG-view-results'=>array(Dimbal_DPM_PRO::buildAppClassName('DimbalPollManager'),'ajaxViewResults')
        );
        return $handlers;
    }

    public static function ajaxGetAdminHandlerMappings(){
        $handlers = array(
            'DIMBAL_CONST_DPM_PRO_SLUG-admin-poll-analysis'=>array(Dimbal_DPM_PRO::buildAppClassName('DimbalPollManager'),'ajaxViewAdminPollAnalysis'),
            'DIMBAL_CONST_DPM_PRO_SLUG-admin-zone-analysis'=>array(Dimbal_DPM_PRO::buildAppClassName('DimbalPollManager'),'ajaxViewAdminZoneAnalysis'),
            'DIMBAL_CONST_DPM_PRO_SLUG-admin-report'=>array(Dimbal_DPM_PRO::buildAppClassName('DimbalPollManager'),'ajaxViewAdminReport')
        );
        return $handlers;
    }

    public static function ajaxProcessHandlerWrapper(){
        Dimbal_DPM_PRO::ajaxProcessHandler(self::ajaxGetAllHandlerMappings());
    }

    /*
     * The handler for the AJAX hit to display a poll
     */
    public static function ajaxDisplayPoll(){

        //error_log("Inside :: ".__CLASS__."::".__FUNCTION__);

        $response = array();

        // Make sure the framework is enabled (this will catch pages that are loaded but the setting changes
        if(!Dimbal_DPM_PRO::isPluginEnabled()){
            $response['error'] = "Plugin Disabled";
            return $response;
        }

        // Prepare the poll
        $poll = false;
        $dpmEId = Dimbal_DPM_PRO::getRequestVarIfExists('dpmEId');
        $response['dpmEId'] = $dpmEId;
        $pollId = Dimbal_DPM_PRO::getRequestVarIfExists('pollId');
        $zoneId = Dimbal_DPM_PRO::getRequestVarIfExists('zoneId');
        //$zoneDisplayAll = Dimbal_DPM_PRO::getRequestVarIfExists('zoneDisplayAll');

        $html = '';
        $extraHtml = '';

        if(!empty($pollId)){
            $poll = DimbalPollQuestion_DPM_PRO::get($pollId);
        }elseif(!empty($zoneId)){
            $zone = DimbalZone_DPM_PRO::get($zoneId);
            if(!empty($zone)){
                // Increase zone hit counter
                $zone->increaseHitCount();
                $zone->save();

                // Zone is good -- check some other settings.
                if($zone->showTitle){
                    $html .= "<p>".$zone->text."</p>";
                }

                if(!empty($zone->additionalHtml)){
                    $extraHtml .= "<br /><div>".$zone->additionalHtml."</div>";
                }

                // let's get all polls according to that Zone
                $polls = DimbalPollQuestion_DPM_PRO::getAllByZoneId($zoneId);
                if(!empty($polls)){
                    shuffle($polls);
                    $validated = false;
                    while($validated==false && !empty($polls)){
                        $poll = array_shift($polls);
                        if($poll->status != DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE){
                            // Poll is not considered active - get another poll
                        }elseif($poll->hasAlreadyResponded() && $poll->multipleResponses==false){
                            // User has already responded -- loop so we try to find another poll
                        }else{
                            // Poll looks good - let's use it for display
                            $validated = true;
                        }
                    }
                    //error_log("Found a Poll from a Zone: ".print_r($poll, true));
                }else{
                    $error = "Zone returned no polls";
                }
            }else{
                $error = "Could not retrieve zone";
            }
        }else{
            $error = "Invalid parameters passed from client";
        }

        if(!empty($poll)){
            // A specific Poll ID was passed - let's display that
            $html.=self::ajaxHelperDisplaySinglePoll($poll, $dpmEId);

            if(!empty($poll->additionalHtml)){
                $extraHtml = "<br /><div>".$poll->additionalHtml."</div>".$extraHtml;
            }
        }else{
            if(empty($error)){
                $error = 'Could not retrieve poll';
            }
            $response['error'] = $error;
        }

        $response['html'] = $html.$extraHtml;

        return $response;

    }

    /*
     * A helper function to display a single poll
     */
    public static function ajaxHelperDisplaySinglePoll($poll, $dpmEId, $createWrapper=false){

        //Dimbal_DPM_PRO::logMessage("Inside ".__FUNCTION__);

        $html = '';

        // Make sure the framework is enabled (this will catch pages that are loaded but the setting changes
        if(!Dimbal_DPM_PRO::isPluginEnabled()){
            return '';
        }

        if($poll){
            if($poll->status != DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE){
                //Poll is no longer active
                $html .= '<p>We could not find the poll you specified.</p>';
            }elseif($poll->hasAlreadyResponded() && $poll->multipleResponses==false){
                //UhOh - User has already responded (we should display the graph then
                if($poll->viewResults){
                    $html .= self::ajaxHelperDisplayPollResults($poll, $dpmEId);
                }else{
                    $html = '';
                    $html .= '<p>Response for this poll is already on file.</p>';
                }
            }else{
                $poll->increaseHitCount();
                $poll->save();
                $html = '';
                $html .= '<p>'.$poll->text.'</p>';
                $html .= '<form action="#" method="post">';
                $html .= '<input type="hidden" name="pollSubmit" value="1" />';
                $html .= '<input type="hidden" id="dpmFormPollId_'.$dpmEId.'" name="dpmFormPollId_'.$dpmEId.'" value="'.$poll->id.'" />';
                foreach($poll->choices as $key=>$value){
                    $html .= '<input type="radio" name="dpmFormPollChoice_'.$dpmEId.'" value="'.$key.'" /> '.$value->text.'<br />';
                }
                $html .= '<br />';

                $html .= '<input type="button" id="dpmSubmit_'.$dpmEId.'" onClick="javascript:dimbalPoll_DPM_PRO.submitPoll(\''.$dpmEId.'\');" value="Submit" />';
                $html .= '</form>';

                if($poll->viewEarlyResults || $poll->hasAlreadyResponded()){
                    $html .= '<p><a href="javascript:dimbalPoll_DPM_PRO.viewEarlyResults(\''.$dpmEId.'\')">View Results</a></p>';
                }

                if($createWrapper){
                    //Doa  reset of the variable to use the wrapper not a .=
                    $html = '<div id="'.$dpmEId.'">'.$html.'</div>';
                }
            }
        }else{
            //Let's die silently
        }
        return $html;
    }

    public static function ajaxSubmitPoll(){
        //Dimbal_DPM_PRO::logMessage("Inside ".__FUNCTION__);

        $response = array();

        // Make sure the framework is enabled (this will catch pages that are loaded but the setting changes
        if(!Dimbal_DPM_PRO::isPluginEnabled()){
            $response['error'] = "Plugin Disabled";
            return $response;
        }

        $dpmEId = Dimbal_DPM_PRO::getRequestVarIfExists('dpmEId');
        $response['dpmEId'] = $dpmEId;
        $pollId = Dimbal_DPM_PRO::getRequestVarIfExists('pollId');
        $poll = DimbalPollQuestion_DPM_PRO::get($pollId);
        $pollChoice = Dimbal_DPM_PRO::getRequestVarIfExists('pollChoice');

        $html = '';
        $html .= '<div style="max-width:250px;">';
        if(empty($poll)){
            $html .= '<p><img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/error.png" style="vertical-align:middle" /> Error validating Poll.  Please try again.</p>';
        }elseif(empty($pollChoice)){
            $html .= '<p><img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/error.png" style="vertical-align:middle" /> Error validating Poll Choice.  Please try again.</p>';
        }elseif($poll->recordResponse($pollChoice)){
            if($poll->viewResults){
                $html .= self::ajaxHelperDisplayPollResults($poll, $dpmEId);
            }else{
                $html .= '<p><img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/tick.png" style="vertical-align:middle" /> Response submitted.</p>';
            }
        }else{
            $html .= '<p><img src="'.DIMBAL_CONST_DPM_PRO_URL_IMAGES.'/error.png" style="vertical-align:middle" /> There was an error submitting your response.  Please try again.</p>';
        }
        $html .= '</div>';
        $response['html']=$html;

        return $response;

    }

    public static function ajaxHelperDisplayPollResults($poll, $dpmEId, $createWrapper=false){
        $html = '';

        // Make sure the framework is enabled (this will catch pages that are loaded but the setting changes
        if(!Dimbal_DPM_PRO::isPluginEnabled()){
            return '';
        }

        if($poll->viewResults){
            $responses = DimbalPollResponse_DPM_PRO::getAllByPollId($poll->id);

            if(empty($responses)){
                $html .= '<p>'.$poll->text.'</p>';
                $html .= '<p>No results on file.</p>';
            }else{


                //Build the Results Array
                $results = array();
                foreach($poll->choices as $key=>$value){
                    $data = array();
                    $data['text']=$value->text;
                    $data['id']=$key;
                    $data['answers']=0;
                    $results[$key]=$data;
                }
                foreach($responses as $response){
                    $results[$response->responseChoiceId]['answers']++;
                }

                //GOOGLE CHARTS
                $chartOptions = array(
                    'showLegend'=>$poll->showLegend,
                    'is3d'=>$poll->is3d,
                );
                $html .= '<p>'.$poll->text.'</p>';
                $html .= '
                <div class="dpmChartContainer_'.$dpmEId.'" dpmchartdata=\''.json_encode($results).'\' dpmchartoptions=\''.json_encode($chartOptions).'\'>
                    <div id="dpmGoogleChart_'.$dpmEId.'" style="width: 250px; height: 250px;">Loading</div>
                </div>
                ';
            }

            if($createWrapper){
                $html = '<div id="'.$dpmEId.'">'.$html.'</div>';
            }

        }
        return $html;
    }

    public static function ajaxViewResults(){

        $response = array();

        // Make sure the framework is enabled (this will catch pages that are loaded but the setting changes
        if(!Dimbal_DPM_PRO::isPluginEnabled()){
            $response['error'] = "Framework Disabled";
            return $response;
        }

        // Get the params for this call
        $dpmEId = Dimbal_DPM_PRO::getRequestVarIfExists('dpmEId');
        $response['dpmEId'] = $dpmEId;
        $pollId = Dimbal_DPM_PRO::getRequestVarIfExists('pollId');
        $poll = DimbalPollQuestion_DPM_PRO::get($pollId);

        // Build the HTML
        $html = '';
        $html .= '<div style="max-width:250px;">';
        $html .= self::ajaxHelperDisplayPollResults($poll, $dpmEId);
        $html .= '<div><a href="javascript:dimbalPoll_DPM_PRO.displayPoll('.$pollId.',\''.$dpmEId.'\');">Back to Poll Voting</a></div>
		';
        $html .= '</div>';
        $response['html']=$html;

        return $response;

    }

    public static function ajaxViewAdminPollAnalysis(){
        $response = array();

        // Make sure the framework is enabled (this will catch pages that are loaded but the setting changes
        if(!Dimbal_DPM_PRO::isPluginEnabled()){
            $response['error'] = "Framework Disabled";
            return $response;
        }

        $pollId = Dimbal_DPM_PRO::getRequestVarIfExists('pollId');
        if(!empty($pollId)){
            $poll = DimbalPollQuestion_DPM_PRO::get($pollId);
            if(!empty($poll)){

                // The poll Object for data display
                $response['pollObject'] = $poll;

                // This one can do a Pie and Bar chart - Distribution and Counts
                $response['responseCounts'] = $poll->getResponsesCounts();

                // This one can do a Pie and Bar chart - Distribution and Counts
                $response['responseDates'] = $poll->getResponsesDatesByDate();

            }
        }

        return $response;

    }

    public static function ajaxViewAdminZoneAnalysis(){
        $response = array();

        // Make sure the framework is enabled (this will catch pages that are loaded but the setting changes
        if(!Dimbal_DPM_PRO::isPluginEnabled()){
            $response['error'] = "Framework Disabled";
            return $response;
        }

        $zoneId = Dimbal_DPM_PRO::getRequestVarIfExists('zoneId');
        if(!empty($zoneId)){
            $zone = DimbalPollQuestion_DPM_PRO::get($zoneId);
            if(!empty($zone)){

                $polls = DimbalPollQuestion_DPM_PRO::getAllByZoneId($zone->id);

                // The poll Object for data display
                $response['zoneObject'] = $zone;

                // This one can do a Pie and Bar chart - Distribution and Counts
                $pollCounts = array();
                foreach($polls as $poll){
                    $countData = array();
                    $countData['text']=$poll->text;
                    $countData['hitCount']=$poll->hitCount;
                    $countData['responseCount']=$poll->responseCount;
                    $pollCounts[$poll->id] = $countData;
                }
                $response['pollCounts'] = $pollCounts;

                // This one can do a Pie and Bar chart - Distribution and Counts
                //$response['responseDates'] = $zone->getResponsesDates();

            }
        }

        return $response;
    }

    public static function ajaxViewAdminReport(){
        $response = array();

        // Make sure the framework is enabled (this will catch pages that are loaded but the setting changes
        if(!Dimbal_DPM_PRO::isPluginEnabled()){
            $response['error'] = "Framework Disabled";
            return $response;
        }

        $reportType = Dimbal_DPM_PRO::getRequestVarIfExists('reportType');
        switch($reportType){
            case '1':

                break;
            case '2':

                break;
        }

        return $response;
    }

    /*
     * Entry point for all ShortCodes used by the Dimbal Poll Manager
     */
    public static function shortcodeHandler($atts){

        // Make sure the Dimbal Scripts are enqueued
        DimbalPollManager_DPM_PRO::enqueuePublicResources();

        // Parse the data out
        $poll_id = 0;
        $zone_id = 0;
        extract( shortcode_atts( array(
            'poll_id' => 0,
            'zone_id' => 0,
        ), $atts ) );

        $html = "";

        if(!empty($poll_id)){
            $poll = DimbalPollQuestion_DPM_PRO::get($poll_id);
            if(!empty($poll)){
                $html = $poll->getDisplayCode();
            }
        }elseif(!empty($zone_id)){
            $zone = DimbalZone_DPM_PRO::get($zone_id);
            if(!empty($zone)){
                $html = $zone->getDisplayCode();
            }
        }

        return $html;
    }
}
