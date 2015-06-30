<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/18/15
 * Time: 5:49 PM
 * To change this template use File | Settings | File Templates.
 */
class DimbalManager_DPM_FREE{

    public static function buildManagerTable($rows){
        //Logger::debug("Manager Table Rows: ".print_r($rows,true));
        /*
          $row = array();
          $row[] = array(
              'title'='Edit',
              'target'=>'zones_edit.php?id=%%ID%%',
              'image'=>URL_IMAGES.'/error.png',
              'image_tooltip'=>'Edit Zone',
              'content'=>'Text Next To Image',
          );
          $row[] = array(
              'title'='Name',
              'target'=>'',
              'image'=>'',
              'image_tooltip'=>'',
              'content'=>'Zone Name',
          );
          $rows[]=$row;
          */
        $rand = rand(0,99999);
        $html = '';
        if(count($rows)==0){
            $html .= '<p style="text-align:center;"><img src="'.DIMBAL_CONST_DPM_FREE_URL_IMAGES.'/no_saved_objects.png" /></p>';
        }else{

            //Get Headers:
            $headers = array();
            foreach($rows[0] as $hk=>$headerCell){
                //Logger::debug("HEADER CELL: HK($hk) ".print_r($headerCell, true));
                if($hk === 'onclick' || $hk === 'style'){
                }else{
                    $headers[]=$headerCell['title'];
                }
            }
            //Logger::debug("HEADERS: ".print_r($headers, true));
            $html .= '';
            $html .= '<table id="managerTable_'.$rand.'" class="contentManager" style="clear:both;">';
            $html .= '<thead>';
            $html .= '<tr>';
            foreach($headers as $keyname=>$header){
                $html .= '<th>'.$header.'</th>';
            }
            $html .= '</tr>';
            $html .= '</thead>';

            $html .= '<tbody>';
            foreach($rows as $row){
                $rowOnClick = "";
                $rowStyle = "";
                if(array_key_exists('style', $row)){
                    $rowStyle = $row['style'];
                    unset($row['style']);
                }
                if(array_key_exists('onclick', $row)){
                    $rowOnClick = $row['onclick'];
                    $rowStyle = "cursor:pointer;" . $rowStyle;	// Add to the front so it can be overwritten
                    unset($row['onclick']);
                }
                $html .= '<tr onclick="'.$rowOnClick.'" style="'.$rowStyle.'">';
                foreach($row as $cell){
                    $html .= '<td';
                    if(array_key_exists('id', $cell)){
                        $html .= ' id="'.$cell['id'].'"';
                    }
                    if(array_key_exists('class', $cell)){
                        $html .= ' class="'.$cell['class'].'"';
                    }
                    if(array_key_exists('style', $cell)){
                        $html .= ' style="'.$cell['style'].'"';
                    }
                    $html .= '>';
                    if(array_key_exists('multiple', $cell) && $cell['multiple']==true){
                        $firstRow = true;
                        foreach($cell['rows'] as $cellRow){
                            /*	if(!$firstRow){
                                           $html .= '<br />';
                                       }
                                       $firstRow = false;
                                   */
                            $styleString="";
                            if(array_key_exists('style', $cellRow)){
                                $styleString=$cellRow['style'];
                            }
                            $html .= '<div style="'.$styleString.'">';
                            if(array_key_exists('subTitle', $cellRow)){
                                $html .= '<span style="font-weight:bold;">'.$cellRow['subTitle'].'</span> : '.self::buildManagerCell($cellRow);
                            }else{
                                $html .= self::buildManagerCell($cellRow);
                            }
                            $html .= '</div>';

                        }
                    }else{
                        $html .= self::buildManagerCell($cell);
                    }
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody>';
            $html .= '</table>';
            $html .= '<br />';
        }
        return $html;

    }

    public static function buildManagerCell($cell){
        $html = '';

        if(!empty($cell['url']) && self::validateArrayEntry($cell['url'])){
            $target = '';
            if(!empty($cell['target']) && self::validateArrayEntry($cell['target'])){
                $target = ' target="'.$cell['target'].'"';
            }
            $html .= '<a href="'.$cell['url'].'"'.$target.'>';
        }

        if(!empty($cell['image']) && self::validateArrayEntry($cell['image'])){
            $html .= '<img src="'.$cell['image'].'" align="middle" style="border:0;"';	//Image is present - display it...
            if(!empty($cell['image_tooltip']) && self::validateArrayEntry($cell['image_tooltip'])){
                $html .= ' title="'.$cell['image_tooltip'].'"';	//Tooltip is present - include it...
            }
            $html .= ' /> '; //Close the image tag
        }

        if(!empty($cell['content']) && self::validateArrayEntry($cell['content'])){
            $html .= htmlspecialchars_decode($cell['content']); //Content is present - Include the content...
        }

        if(!empty($cell['span']) && self::validateArrayEntry($cell['span'])){
            $html .= '<span>'.$cell['span'].'</span>'; //Content is present - Include the content...
        }

        if(!empty($cell['target']) && self::validateArrayEntry($cell['target'])){
            $html .= '</a>';	//Target is present - End the link...
        }

        return $html;
    }

    public static function validateArrayEntry($entry=''){
        if(strlen($entry)>=1){
            return true;
        }
        return false;
    }

}
