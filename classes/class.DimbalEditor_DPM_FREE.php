<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 4:44 PM
 * To change this template use File | Settings | File Templates.
 */
class DimbalEditor_DPM_FREE{

    // Object Types
    const OT_BOOLEAN=1;
    const OT_STRING=2;
    const OT_NUMERIC=3;
    const OT_ARRAY=4;
    const OT_DATE=5;
    const OT_SKIP=6;
    const OT_ARRAY_ARRAY=7;
    const OT_OBJECT=8;

    // Editor Types
    const ET_TEXT=1;
    const ET_TEXT_READONLY=2;
    const ET_TEXT_ADDITIONAL=3;
    const ET_MENU=4;
    const ET_MENU_STATUS=5;
    const ET_MENU_MULTIPLE=6;	//Doesn't work :: use ET_CHECKBOX_GROUP instead
    const ET_MENU_BOOLEAN=7;
    const ET_DATE=8;
    const ET_CHECKBOX=9;
    const ET_CHECKBOX_GROUP=10;
    const ET_RADIO=11;
    const ET_TEXTAREA=12;
    const ET_SUBMIT=13;
    const ET_HIDDEN=14;
    const ET_PASSWORD=15;
    const ET_LINK=16;
    const ET_HTML=17;
    const ET_MENU_KEY=18;
    const ET_BUTTON=19;
    const ET_TEXT_ARRAY_ARRAY=20;
    const ET_ZONE_ITEM_PICKER = 21;
    const ET_ITEM_ZONE_PICKER = 22;
    const ET_DPM_ANSWER_CHOICE_PICKER=23;
    const ET_TEXTAREA_HTML=24;
    const ET_DATE_TIME = 25;
    const ET_SKIP = 26;
    const ET_PHOTO_PICKER = 27;
    const ET_IMAGE_UPLOADER=28;

    // Default menus options
    public static $defaultsMenuTrueFalse = array(1=>'True',0=>'False');
    public static $defaultsMenuActive = array('Active','Inactive');
    public static $defaultsOperators = array('>','>=','=','=<','<');

    public static function buildPageTemplate($class, $pageTitle, $forceEditor=false){

        // Make sure the class exists
        if(!class_exists($class)){
            return $class." - class does not exist.";
        }

        $html = '';

        //Dimbal_DPM_FREE::logMessage("Request Vars: ".print_r($_REQUEST, true));

        // See if the editor was passed
        $editor = Dimbal_DPM_FREE::getRequestVarIfExists('formEditor');

        // See if a new was passed ofr an id
        $id = Dimbal_DPM_FREE::getRequestVarIfExists('id');

        if(empty($editor) && empty($id)){
            // Both Editor Submit and Editor Display are empty - we do not belong in here
            return '';
        }

        // Setup the object
        $object = false;
        if($id=='new'){
            $object = false; // Do not create a new object yet
        }elseif(empty($id)){
            $object = new $class();     // Will catch either 0 or ''
        }else{
            $object = $class::get($id);
        }

        // Update or Insert the Choices as appropriate
        $options = $class::editorBuildOptions($object);
        if($object && isset($_REQUEST['formEditor'])){
            // Save the changes but do not build the editor
            $object = DimbalEditor_DPM_FREE::saveEditorChanges($object,$options,$_REQUEST);
            if($forceEditor){
                // Build the editor again (such as in Free Zones)
                $object = $class::get($id);
                $options = $class::editorBuildOptions($object);
                $html .= DimbalEditor_DPM_FREE::buildEditor($options, '#');
            }else{
                unset($_REQUEST['id']);
            }
        }else{
            // Build the editor because the form was not shown
            $html .= DimbalEditor_DPM_FREE::buildEditor($options, '#');
        }

        return $html;
    }

    public static function buildCreateNewButtonOptions($page, $text="Create New"){
        $buttonOptions = array(
            'params'=>array('page'=>$page,'id'=>0),
            'text'=>$text
        );
        return $buttonOptions;
    }

    public static function buildEditor($options, $target, $id="", $forceDemoOnly=false){
        /*
          $options = array(
              0=array(
                  'title'='Name',
                  'objectType'=CommonTools::OT_STRING,
                  'formType'=CommonTools::FT_TEXT,
                  'value'='Default Name',
                  'help'='Please fill out the name of the object'
                  ),
              1=array(
                  'title'='Status',
                  'objectType'=CommonTools::OT_BOOLEAN,
                  'formType'=CommonTools::FT_MENU,
                  'formOptions'=CommonTools::$defaultsMenuActive
                  'value'='active',
                  'help'='Select whether this object is active or inactive'
                  ),
              );
          */

        $html ='';
        $html .= '<div class="contentSectionWrapper">';
        $html .= '<form action="'.$target.'" method="post" id="'.$id.'" enctype="multipart/form-data">';
        $html .= '<input type="hidden" name="formEditor" value="1" />';
        $html .= '<table class="contentEditor">';

        foreach($options as $optionRow){
            if($forceDemoOnly){
                $html .= self::buildEditorRowForDemo($optionRow);
            }else{
                $html .= self::buildEditorRow($optionRow);
            }
        }
        $html .= '</table>';
        $html .= '<div style="text-align:center; padding:5px;"><input type="submit" class="button" name="submit2" value="Save Changes" /></div>';
        $html .= '</form>';
        $html .= '</div>';
        return $html;

    }

    public static function buildEditorRow($options){
        $html = '';
        $defaults = array(
            'formType'=>'',
            'objectName'=>'',
            'value'=>'',
            'formOptions'=>array(),
            'maxlength'=>'',
            'rows'=>'',
            'cols'=>'',
            'size'=>''
        );
        $options = array_merge($defaults,$options);
        if($options['formType'] == self::ET_HIDDEN){
            $html .= '<input type="hidden" name="'.$options['objectName'].'" value="'.$options['value'].'" />';
        }elseif(array_key_exists('rowType', $options) && $options['rowType']=='SectionHeader'){
            $html .= '<tr>';
            $html .= '<th colspan="2">'.$options['title'].'</th>';
            $html .= '</tr>';
        }else{
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<div class="contentEditorTitle">'.$options['title'].'</div>';
            $html .= '<div class="contentEditorHelp">'.$options['help'].'</div>';
            $html .= '</td>';
            $html .= '<td>';
            $html .= self::buildEditorCell($options);
            $html .= '</td>';
            $html .= '</tr>';
        }
        return $html;
    }

    public static function buildEditorRowForDemo($options){
        $html = '';
        if(array_key_exists('rowType', $options) && $options['rowType']=='SectionHeader'){
            $html .= '<tr>';
            $html .= '<th colspan="2">'.$options['title'].'</th>';
            $html .= '</tr>';
        }else{
            $html .= '<tr>';
            $html .= '<td>';
            $html .= '<div class="contentEditorTitle">'.$options['title'].'</div>';
            $html .= '</td>';
            $html .= '<td>';
            $html .= '<div class="contentEditorHelp">'.$options['help'].'</div>';
            $html .= '</td>';
            $html .= '</tr>';
        }
        return $html;

    }

    public static function buildEditorCell($options){
        $html = '';
        switch ($options['formType']){
            case self::ET_TEXT:
                $html .= '<input type="text" id="'.$options['objectName'].'" name="'.$options['objectName'].'" value="'.$options['value'].'" size="'.$options['size'].'" /> <label for="'.$options['objectName'].'"></label>';
                //Logger::debug("Common Editor TEXT: Value:".print_r($options['value'],true));
                break;
            case self::ET_TEXT_READONLY:
                $html .= $options['value'];
                $html .= '<input type="hidden" id="'.$options['objectName'].'" name="'.$options['objectName'].'" value="'.$options['value'].'" /> <label for="'.$options['objectName'].'"></label>';
                break;
            case self::ET_TEXT_ADDITIONAL:
                /*
                 * // NO LONGER USED -- Needs JS Customization
                $first = true;
                foreach($options['value'] as $key=>$value){
                    if(!$first){ $html.='<br />'; }
                    $html .= '<input type="checkbox" name="'.$options['objectName'].$key.'_chk" checked="checked"> <input type="text" id="'.$options['objectName'].$key.'" name="'.$options['objectName'].$key.'" value="'.$value.'" size="'.$options['size'].'"  />';
                    $first = false;
                }
                $html .= '<br /><div id="addMoreRows'.$options['objectName'].'"><a href="javascript:dimbal.addBlankRow(\'moreRows'.$options['objectName'].'\',\'addMoreRows'.$options['objectName'].'\');">add row</a></div>';
                break;
                */
            case self::ET_TEXT_ARRAY_ARRAY:
                $first = true;
                $html .= '<table>';
                $extraRowNeeded = true;
                $item = $options['defaultArray'];
                foreach($options['value'] as $key=>$value){
                    $html .= '<tr>';
                    $item = $value;

                    foreach($value as $k=>$v){
                        $html .= '<td>'.$k.': <input type="text" name="'.$options['objectName'].$key.$k.'" value="'.$v.'" size="'.$options['size'].'" /></td>';
                        if($extraRowNeeded){

                        }
                    }
                    $html .= '</tr>';

                }
                //4 Extra Blank Rows by Default
                for($i=0;$i<4;$i++){
                    $html .= '<tr>';
                    foreach($item as $k=>$v){
                        $html .= '<td><input type="text" name="moreRows'.$options['objectName'].'_blank_'.$k.'_'.$i.'" size="'.$options['size'].'" /></td>';
                    }
                    $html .= '</tr>';
                }
                $html .= '</table>';
                break;
            case self::ET_MENU:
                $html .= '<select id="'.$options['objectName'].'" name="'.$options['objectName'].'"> <label for="'.$options['objectName'].'"></label>';
                $html .= '<option value="">--</option>';
                foreach($options['formOptions'] as $key=>$option){
                    $selected = '';
                    if($option==$options['value']){ $selected=' selected="selected"'; }
                    $html .= '<option value="'.$option.'"'.$selected.'>'.$option.'</option>';
                }
                $html .= '</select>';
                break;
            case self::ET_MENU_KEY:
                $html .= '<select id="'.$options['objectName'].'" name="'.$options['objectName'].'"> <label for="'.$options['objectName'].'"></label>';
                $html .= '<option value="">--</option>';
                foreach($options['formOptions'] as $key=>$option){
                    $selected = '';
                    if($key==$options['value']){ $selected=' selected="selected"'; }
                    $html .= '<option value="'.$key.'"'.$selected.'>'.$option.'</option>';
                }
                $html .= '</select>';
                break;
            case self::ET_MENU_STATUS:
                $html .= '<select id="'.$options['objectName'].'" name="'.$options['objectName'].'"> <label for="'.$options['objectName'].'"></label>';
                $html .= '<option value="">--</option>';
                foreach(DimbalStandardObjectRecord_DPM_FREE::getAllStatusMarks() as $key=>$value){
                    $selected = '';
                    if($key==$options['value']){ $selected=' selected="selected"'; }
                    $html .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
                }
                $html .= '</select>';
                break;
            case self::ET_MENU_MULTIPLE:
                $html .= '<select id="'.$options['objectName'].'" name="'.$options['objectName'].'" multiple="multiple" size="10"> <label for="'.$options['objectName'].'"></label>';
                $html .= '<option value="">--</option>';
                foreach($options['formOptions'] as $key=>$value){
                    $selected = '';
                    if($value==$options['value']){ $selected=' selected="selected"'; }
                    $html .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
                }
                $html .= '</select>';
                break;
            case self::ET_MENU_BOOLEAN:
                $html .= '<select id="'.$options['objectName'].'" name="'.$options['objectName'].'"> <label for="'.$options['objectName'].'"></label>';
                $html .= '<option value="">--</option>';
                foreach(self::$defaultsMenuTrueFalse as $key=>$value){
                    $selected = '';
                    if($key==$options['value']){ $selected=' selected="selected"'; }
                    $html .= '<option value="'.$key.'"'.$selected.'>'.$value.'</option>';
                }
                $html .= '</select>';
                break;
            case self::ET_DATE:
                //$html .= '<script>';
                //$html .= '$(function() { $( "#'.$options['objectName'].'" ).datepicker(); });';
                //$html .= '</script>';
                $html .= '<input type="text" id="'.$options['objectName'].'" class="datePicker" name="'.$options['objectName'].'" value="'.DimbalStandardObjectRecord_DPM_FREE::formatJQueryDate($options['value']).'" />';
                $html .= ' '.self::renderAjaxInsertTodaysDate($options['objectName']).' <label for="'.$options['objectName'].'"></label>';
                break;
            case self::ET_DATE_TIME:
                //$html .= '<script>';
                //$html .= '$(function() { $( "#'.$options['objectName'].'" ).dateTimepicker(); });';
                //$html .= '</script>';
                $html .= '<input type="text" id="'.$options['objectName'].'" class="dateTimePicker" name="'.$options['objectName'].'" value="'.DimbalStandardObjectRecord_DPM_FREE::formatJQueryDateTime($options['value']).'" />';
                $html .= ' '.self::renderAjaxInsertTodaysDate($options['objectName']).' <label for="'.$options['objectName'].'"></label>';
                break;
            case self::ET_CHECKBOX:
                $first = true;
                $checked = '';
                if($options['value']==true){ $checked=' checked="checked"'; }
                $html .= '<input type="checkbox" id="'.$options['objectName'].'" name="'.$options['objectName'].'"'.$checked.'> <label for="'.$options['objectName'].'"></label>';
                break;
            case self::ET_ZONE_ITEM_PICKER:
            case self::ET_ITEM_ZONE_PICKER:
            case self::ET_CHECKBOX_GROUP:
                $first = true;
                $html .= '<div class="contentScrollCheckboxes">';
                foreach($options['formOptions'] as $key=>$value){
                    $checked = '';
                    if(is_array($options['value']) && in_array($key, $options['value'])){ $checked=' checked="checked"'; }
                    if(!$first){ $html.='<br />'; }
                    $html .= '<input type="checkbox" name="'.$options['objectName'].$key.'" value="'.$key.'"'.$checked.'>'.$value;
                    $first = false;
                }
                $html .= '</div>';

                if(($options['formType'] == self::ET_ITEM_ZONE_PICKER) && (empty($options['formOptions']))){
                    $html .= '<div>Create zones using the Zone Manager.</div>';
                }

                break;
            case self::ET_DPM_ANSWER_CHOICE_PICKER:
                // Specific to DPM
                foreach($options['formOptions'] as $key=>$value){
                    $checked = '';
                    if($value->status == DimbalStandardObjectRecord_DPM_FREE::STATUS_ACTIVE){ $checked=' checked="checked"'; }
                    $html .= '<div id="'.$options['objectName'].$key.'_div"><input type="checkbox" name="'.$options['objectName'].$key.'_chk"'.$checked.'> <input type="text" name="'.$options['objectName'].$key.'_txt" value="'.$value->text.'" /> <input type="button" name="'.$options['objectName'].$key.'_dlt" value="Delete" onClick="javascript:dimbalPoll_DPM_FREE.confirmChoiceDelete(\''.$options['objectName'].$key.'\')" /></div>';
                }
                $html .= '<div id="addMoreRows'.$options['objectName'].'"><a href="javascript:dimbalPoll_DPM_FREE.addBlankRow(\''.$options['objectName'].'\', \'addMoreRows'.$options['objectName'].'\');">add choice</a></div>';
                break;
            case self::ET_RADIO:
                $first = true;
                foreach($options['formOptions'] as $option){
                    if(!$first){ $html.='<br />'; }
                    $checked = '';
                    if($option==$options['value']){ $checked=' checked="checked"'; }
                    $html .= '<input type="radio" name="'.$options['objectName'].'" value="'.$option.'"'.$checked.'>'.$option;
                    $first = false;
                }
                break;
            case self::ET_TEXTAREA:
                $maxlength = '';
                if(array_key_exists('maxlength', $options)){
                    $maxlength = ' maxlength="'.$options['maxlength'].'"';
                }
                $cols = ' cols="70"';
                if(array_key_exists('cols', $options) && !empty($options['cols'])){
                    $cols = ' cols="'.$options['cols'].'"';
                }
                $rows = ' rows="5"';
                if(array_key_exists('rows', $options) && !empty($options['rows'])){
                    $rows = ' rows="'.$options['rows'].'"';
                }
                $html .= '<textarea id="'.$options['objectName'].'" name="'.$options['objectName'].'" '.$rows.''.$cols.''.$maxlength.'>'.$options['value'].'</textarea> <label for="'.$options['objectName'].'"></label>';
                break;
            case self::ET_TEXTAREA_HTML:
                $html .= '<div style="display:table; width:500px; text-align:left;"><div style="display:table-cell; width:500px;"><textarea name="'.$options['objectName'].'" class="tinyMCE">'.$options['value'].'</textarea></div></div>';
                break;
            case self::ET_SUBMIT:
                $html .= '<input type="submit" id="'.$options['objectName'].'" name="'.$options['objectName'].'" value="'.$options['title'].'" /> <label for="'.$options['objectName'].'"></label>';
                break;
            case self::ET_PASSWORD:
                $html .= '<input type="password" id="'.$options['objectName'].'" name="'.$options['objectName'].'" /> <label for="'.$options['objectName'].'"></label>';
                break;
            case self::ET_LINK:
                $html .= '<a href="'.$options['value'].'">'.$options['title'].'</a>';
                break;
            case self::ET_HTML:
                $html .= $options['value'];
                break;
        }
        return $html;
    }

    public static function saveArrayChanges($originalArray, $options, $requestVars){
        $changes = array();
        try{
            foreach($options as $option){
                $value = "";
                $processValue = false;
                switch ($option['objectType']){
                    case self::OT_DATE:
                        $enteredDate = $requestVars[$option['objectName']];
                        if(strlen(trim($enteredDate))>1 || $option['objectName']=="createdDate"){
                            $value = DimbalStandardObjectRecord_DPM_FREE::formatIncomingDateString($requestVars[$option['objectName']]);
                        }else{
                            $value = "";
                        }
                        $processValue = true;
                        break;
                    case self::OT_NUMERIC:
                        $value = $requestVars[$option['objectName']];
                        if(!is_numeric($value)){
                            $value=0;
                        }
                        $processValue = true;
                        break;
                    case self::OT_STRING:
                        $value = $requestVars[$option['objectName']];
                        $value = str_replace("\\", "", $value);
                        $value = trim($value);
                        $processValue = true;
                        break;
                    case self::OT_BOOLEAN:
                        //Logger::debug("Inside the Editor with Object Type String");
                        if(array_key_exists($option['objectName'], $requestVars)){
                            $value = $requestVars[$option['objectName']];
                            if($value=='checked' || $value=='selected' || $value=='on'){
                                $value = true;
                            }else{
                                $value = false;
                            }
                        }else{
                            //Then it means it was a checkmark and was not selected...  will not be included in responseVars
                            $value = false;
                        }
                        $processValue = true;
                        break;
                    case self::OT_SKIP:
                        //Skip checking this variable :: useful for displaying entities only outside of the form
                        break;
                }//Switch Block
                if($processValue){
                    $changes[$option['objectName']]=$value;
                }
            }//For Loop
        }catch(Exception $e){

        }
        return $changes;

    }

    public static function saveEditorChanges($object, $options, $requestVars){

        error_log("Inside saveEditorChanges");

        /*
          $options = array(
              0=array(
                  'title'='Name',
                  'objectType'=CommonTools::OT_STRING,
                  'objectName'='name',
                  'formType'=CommonTools::FT_TEXT,
                  'value'='Default Name',
                  'help'='Please fill out the name of the object'
                  ),
              1=array(
                  'title'='Status',
                  'objectType'=CommonTools::OT_BOOLEAN,
                  'formType'=CommonTools::FT_MENU,
                  'formOptions'=CommonTools::$defaultsMenuActive
                  'value'='active',
                  'help'='Select whether this object is active or inactive'
                  ),
              );
          */
        try{
            foreach($options as $option){
                $value = false;
                $processValue = false;
                if($option['title']=='ID'){
                    //Skip the ID Field
                }elseif(array_key_exists('rowType', $option) && $option['rowType']=='SectionHeader'){
                    // Skip the Section Headers
                }elseif(array_key_exists('skipIfEmpty', $option) && $option['skipIfEmpty']==true && $requestVars[$option['objectName']]==""){
                    //Skip the field if blank and marked to skip
                    $object->$option['objectName']="";
                }else{
                    switch ($option['objectType']){
                        case self::OT_DATE:
                            $enteredDate = $requestVars[$option['objectName']];
                            if(strlen(trim($enteredDate))>1 || $option['objectName']=="createdDate"){
                                $value = DimbalStandardObjectRecord_DPM_FREE::formatIncomingDateString($requestVars[$option['objectName']]);
                            }else{
                                $value = "";
                            }
                            break;
                        case self::OT_NUMERIC:
                            switch ($option['formType']){
                                case self::ET_MENU_STATUS:
                                    $value = $requestVars[$option['objectName']];
                                    if(!is_numeric($value) || empty($value)){
                                        $value=DimbalStandardObjectRecord_DPM_FREE::STATUS_ACTIVE;
                                    }
                                    $processValue = true;
                                    break;
                                default:
                                    $value = $requestVars[$option['objectName']];
                                    if(!is_numeric($value)){
                                        $value=0;
                                    }
                                    $processValue = true;
                                    break;
                            }
                            break;
                        case self::OT_ARRAY_ARRAY:
                            switch ($option['formType']){
                                case self::ET_TEXT_ARRAY_ARRAY:
                                    $items = array();
                                    $newItem = $option['defaultArray'];
                                    if(is_array($option['value'])){
                                        foreach($option['value'] as $key=>$value){
                                            $newItem = $option['value'][$key];
                                            foreach($value as $k=>$v){
                                                if(array_key_exists($option['objectName'].$key.$k,$requestVars)){
                                                    $val = $requestVars[$option['objectName'].$key.$k];
                                                    $val = str_replace("\\", "", $val);
                                                    $newItem[$k]=$val;
                                                    $items[$key] = $newItem;
                                                }
                                            }

                                        }
                                    }
                                    for($i=0;$i<=50;$i++){
                                        $newlyMadeItem = array();
                                        foreach($newItem as $k=>$v){
                                            $fieldString = 'moreRows'.$option['objectName'].'_blank_'.$k.'_'.$i;
                                            //Logger::debug("Field String :: ".$fieldString);
                                            if(array_key_exists($fieldString,$requestVars)){
                                                $val = $requestVars[$fieldString];
                                                if(strlen($val)>=1){
                                                    $val = str_replace("\\", "", $val);
                                                    $newlyMadeItem[$k]=$val;
                                                    //Logger::debug("Newly Made Item: ".print_r($newlyMadeItem, true));
                                                }
                                            }
                                        }
                                        if(count($newlyMadeItem)>0){
                                            $items[] = $newlyMadeItem;
                                        }
                                    }
                                    $value = $items;
                                    break;
                            }
                            break;
                        case self::OT_ARRAY:
                            switch ($option['formType']){
                                case self::ET_CHECKBOX_GROUP:
                                    $items = array();
                                    foreach($option['formOptions'] as $key=>$value){
                                        $rvKeyname = $option['objectName'].$key;
                                        if(array_key_exists($rvKeyname, $requestVars) && $key==$requestVars[$rvKeyname]){
                                            $items[] = $key;
                                        }
                                    }
                                    $value = $items;
                                    break;
                                case self::ET_ZONE_ITEM_PICKER:
                                    $items = array();
                                    foreach($option['formOptions'] as $key=>$value){
                                        $rvKeyname = $option['objectName'].$key;
                                        error_log("ZONE_ITEM_PICKER: Key[$key] Value[$value] rvKeyname[$rvKeyname]");
                                        if(array_key_exists($rvKeyname, $requestVars) && $key==$requestVars[$rvKeyname]){
                                            $items[] = $key;
                                        }
                                    }
                                    $object->removeAllItemIds();
                                    $object->addItemIds($items);
                                    $value = $items;
                                    break;
                                case self::ET_ITEM_ZONE_PICKER:
                                    $zones = array();
                                    foreach($option['formOptions'] as $key=>$value){
                                        $rvKeyname = $option['objectName'].$key;
                                        if(array_key_exists($rvKeyname, $requestVars) && $key==$requestVars[$rvKeyname]){
                                            $zones[] = $key;
                                        }
                                    }
                                    DimbalZone_DPM_FREE::removeAllZonesForItem($object->id);
                                    DimbalZone_DPM_FREE::addZonesForItem($object->id, $zones);
                                    $value = false;		//Skip the save below
                                    break;
                                case self::ET_TEXT_ADDITIONAL:
                                    if(is_array($option['value'])){
                                        foreach($option['value'] as $key=>$value){
                                            $chkString = $option['objectName'].$key.'_chk';
                                            if(array_key_exists($chkString,$requestVars)){
                                                $value = $requestVars[$chkString];
                                                if($value=='checked' || $value=='on'){
                                                    $rvKeyname = $option['objectName'].$key;
                                                    if(array_key_exists($rvKeyname, $requestVars) && $value==$requestVars[$rvKeyname]){
                                                        $value = str_replace("\\", "", $value);
                                                        if(strlen(trim($value))>0){
                                                            $items[$key] = $value;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    for($i=0;$i<=50;$i++){
                                        // now loop through and find any added entries
                                        $chkString = 'moreRows'.$option['objectName'].'_blankChk_'.$i;
                                        if(array_key_exists($chkString,$requestVars)){
                                            $value = $requestVars[$chkString];
                                            if($value=='checked' || $value=='on'){
                                                $fieldString = 'moreRows'.$option['objectName'].'_blankTxt_'.$i;
                                                //Logger::debug("Field String :: ".$fieldString);
                                                if(array_key_exists($fieldString,$requestVars)){
                                                    $val = $requestVars[$fieldString];
                                                    $val = str_replace("\\", "", $val);
                                                    if(strlen(trim($val))>0){
                                                        $items[] = $val;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    $value = $items;
                                    //Logger::debug("ITEMS: ".print_r($items));
                                    break;
                                case self::ET_DPM_ANSWER_CHOICE_PICKER:
                                    if(is_array($option['formOptions'])){
                                        foreach($option['formOptions'] as $key=>$value){
                                            $fieldString = $option['objectName'].$key.'_txt';
                                            if(array_key_exists($fieldString, $requestVars)){
                                                $chkString = $option['objectName'].$key.'_chk';
                                                $status = DimbalStandardObjectRecord_DPM_FREE::STATUS_INACTIVE;
                                                if(array_key_exists($chkString,$requestVars)){
                                                    $value = $requestVars[$chkString];
                                                    if($value == 'checked' || $value=='on'){
                                                        $status = DimbalStandardObjectRecord_DPM_FREE::STATUS_ACTIVE;
                                                    }elseif($value == 'deleted'){
                                                        $status = "DELETE";
                                                    }
                                                }
                                                $val = $requestVars[$fieldString];
                                                $val = str_replace("\\", "", $val);
                                                $object->saveAnswerChoice($key, $val, $status);
                                            }
                                        }
                                    }
                                    for($i=0;$i<=50;$i++){
                                        $fieldString = $option['objectName'].'_blankTxt_'.$i;
                                        //Logger::debug("Field String :: ".$fieldString);
                                        if(array_key_exists($fieldString,$requestVars)){
                                            $chkString = $option['objectName'].'_blankChk_'.$i;
                                            $status = DimbalStandardObjectRecord_DPM_FREE::STATUS_INACTIVE;
                                            if(array_key_exists($chkString,$requestVars)){
                                                $value = $requestVars[$chkString];
                                                if($value == 'checked' || $value=='on'){
                                                    $status = DimbalStandardObjectRecord_DPM_FREE::STATUS_ACTIVE;
                                                }
                                            }
                                            $val = $requestVars[$fieldString];
                                            $val = str_replace("\\", "", $val);
                                            if(strlen(trim($val))>0){
                                                $object->saveAnswerChoice(0, $val, $status);
                                            }
                                        }
                                    }
                                    $value = false;
                                    //Logger::debug("ITEMS: ".print_r($items));
                                    break;
                            }
                            break;
                        case self::OT_STRING:
                            switch ($option['formType']){
                                case self::ET_PASSWORD:
                                    $password = $requestVars[$option['objectName']];
                                    if(strlen(trim($password))>2){
                                        $object->updatePassword($password);
                                        $value = false;
                                    }
                                    break;
                                default:
                                    $value = $requestVars[$option['objectName']];
                                    $value = str_replace("\\", "", $value);
                                    $value = trim($value);
                                    $processValue = true;
                                    //Logger::debug("Common Save Editor TEXT: Value:".print_r($value,true));
                                    break;
                            }
                            break;
                        case self::OT_BOOLEAN:
                            //Logger::debug("Inside the Editor with Object Type String");
                            if(array_key_exists($option['objectName'], $requestVars)){
                                $value = $requestVars[$option['objectName']];
                                if($value=='checked' || $value=='selected' || $value=='on' || $value=='1'){
                                    $value = true;
                                }else{
                                    $value = false;
                                    $processValue = true;
                                }
                            }else{
                                //Then it means it was a checkmark and was not selected...  will not be included in responseVars
                                $value = false;
                                $processValue = true;
                            }
                            break;
                        case self::OT_SKIP:
                            //Skip checking this variable :: useful for displaying entities only outside of the form
                            break;
                        default:
                            $value = $requestVars[$option['objectName']];
                            break;
                    }
                    if($value || $processValue){
                        $object->$option['objectName'] = $value;
                    }
                }

            }

            $object->save();
            Dimbal_DPM_FREE::addUserMessage('Object Saved Successfully');
        }catch(Exception $e){
            error_log("Exception Caught: ".$e->getMessage());
        }


        return $object;

    }

    public static function renderAjaxInsertTodaysDate($formIds){
        if(!is_array($formIds)){
            $formIds = array($formIds);
        }
        $html = "<a href='";
        foreach($formIds as $formId){
            $html .= "javascript:setFormValue(\"".$formId."\",\"".DimbalStandardObjectRecord_DPM_FREE::formatJQueryDate(time())."\");";
        }
        $html .= "'>insert today</a>";
        return $html;
    }

}
