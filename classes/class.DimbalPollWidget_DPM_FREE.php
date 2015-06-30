<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ben
 * Date: 3/12/15
 * Time: 11:23 PM
 *
 * This file controls the Widget Options for the Dimbal Software Poll Widgets
 *
 */
class DimbalPollWidget_DPM_FREE extends WP_Widget {

    public static $displayOptions = array(
        1=>'Poll',
        2=>'Zone'
    );

    public function __construct() {
        // widget actual processes
        parent::__construct(
            DIMBAL_CONST_DPM_FREE_SLUG, // Base ID
            DIMBAL_CONST_DPM_FREE_PLUGIN_TITLE, // Name
            array( 'description' => __( 'Add this widget to display Polls from the '.DIMBAL_CONST_DPM_FREE_PLUGIN_TITLE.'.  Select either a single poll or a group of polls.', 'text_domain' ), ) // Args
        );
    }

    public function form( $instance ) {
        // outputs the options form on admin
        $polls = DimbalPollQuestion_DPM_FREE::getAll();
        $zones = DimbalZone_DPM_FREE::getAllByTypeId(DimbalZone_DPM_FREE::TYPE_DPM);

        $pollId = false;
        $zoneId = false;
        $displayId = false;
        if ( $instance ) {
            $title = esc_attr( $instance['title'] );
            $displayId = $instance['display_id'];
            $pollId = $instance['poll_id'];
            $zoneId = $instance['zone_id'];
        }
        else {
            $title = __( 'User Polls' );
        }
        if(empty($displayId)){
            $displayId = 1;
        }

        $displayIdFieldName = $this->get_field_name( 'display_id' );
        $displayIdFieldName = str_replace("[","_",$displayIdFieldName);
        $displayIdFieldName = str_replace("]","_",$displayIdFieldName);
        $displayIdFieldName = str_replace("-","_",$displayIdFieldName);

        ?>
    <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" />
        <br />
        <br />
        <p><?php _e( 'Choose a Display Type:' ); ?></p>
        <br />
        <?php
        foreach(self::$displayOptions as $displayOptionKey=>$displayOptionName){
            $selected='';
            if($displayId == $displayOptionKey){
                $selected=' checked="checked"';
            }
            echo '<input type="radio" name="'.$displayIdFieldName.'" value="'.$displayOptionKey.'"'.$selected.' onclick="dimbalPoll_DPM_FREE.widgetChangeType(\''.$displayIdFieldName.'\','.$displayOptionKey.')" /> '.$displayOptionName.'&nbsp;&nbsp;&nbsp;';
        }
        ?>
        <br />
        <hr />
        <br />
    <?php

    ?>
        <div id="<?=($displayIdFieldName)?>_1" class="dimbal_dpm_widget_wrapper_<?=($displayIdFieldName)?> <?=($displayIdFieldName)?>_1">
            <label for="<?php echo $this->get_field_id( 'poll_id' ); ?>"><?php _e( 'Poll:' ); ?></label>
            <select id="<?php echo $this->get_field_id( 'poll_id' ); ?>" name="<?php echo $this->get_field_name( 'poll_id' ); ?>">
                <?php
                foreach($polls as $poll){
                    $selected='';
                    if($pollId == $poll->id){
                        $selected=' selected="selected"';
                    }
                    echo '<option value="'.$poll->id.'"'.$selected.'>'.$poll->text.'</option>';
                }
                ?>
            </select>
        </div>
        <div id="<?=($displayIdFieldName)?>_2" class="dimbal_dpm_widget_wrapper_<?=($displayIdFieldName)?> <?=($displayIdFieldName)?>_2">
            <label for="<?php echo $this->get_field_id( 'zone_id' ); ?>"><?php _e( 'Zone:' ); ?></label>
            <select id="<?php echo $this->get_field_id( 'zone_id' ); ?>" name="<?php echo $this->get_field_name( 'zone_id' ); ?>">
                <?php
                foreach($zones as $zone){
                    $selected='';
                    if($zoneId == $zone->id){
                        $selected=' selected="selected"';
                    }
                    echo '<option value="'.$zone->id.'"'.$selected.'>'.$zone->text.'</option>';
                }
                ?>
            </select>
        </div>
        <script>
            dimbalPoll_DPM_FREE.widgetChangeType('<?=($displayIdFieldName)?>',<?=($displayId)?>);
        </script>
    </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        // processes widget options to be saved
        $title = (array_key_exists('title',$new_instance))?$new_instance['title']:"";
        $instance['title'] = strip_tags($title);

        $pollId = (array_key_exists('poll_id',$new_instance))?$new_instance['poll_id']:0;
        $instance['poll_id'] = strip_tags($pollId);

        $zoneId = (array_key_exists('zone_id',$new_instance))?$new_instance['zone_id']:0;
        $instance['zone_id'] = strip_tags($zoneId);

        $displayId = (array_key_exists('display_id',$new_instance))?$new_instance['display_id']:1;
        $instance['display_id'] = strip_tags($displayId);
        return $instance;
    }

    public function widget( $args, $instance ) {
        // outputs the content of the widget

        // Make sure the framework is enabled (this will prevent the widget from display entirely)
        if(!Dimbal_DPM_FREE::isPluginEnabled()){
            return;
        }

        // Make sure the Dimbal Scripts are enqueued
        DimbalPollManager_DPM_FREE::enqueuePublicResources();

        $html = "";

        // Now display the result based upon display id
        // Grabbing the html first -- so that we can just skip the display of the widget as a whole if there are problems
        $displayId = $instance['display_id'];
        switch($displayId){
            case 1:
                // Poll
                $pollId = $instance['poll_id'];
                $poll = DimbalPollQuestion_DPM_FREE::get($pollId);
                if(!empty($poll)){
                    $html = $poll->getDisplayCode();
                }
                break;
            case 2:
                // Zone
                $zoneId = $instance['zone_id'];
                $zone = DimbalZone_DPM_FREE::get($zoneId);
                if(!empty($zone)){
                    $html = $zone->getDisplayCode();
                }
                break;
        }


        // Now display everything if the HTML variable has proper data in it.
        if(!empty($html)){

            // Before the widget
            echo $args['before_widget'];

            // Title information
            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'];
                echo esc_html( $instance['title'] );
                echo $args['after_title'];
            }

            // Html for Poll Display
            echo $html;

            // After the widget
            echo $args['after_widget'];

        }



    }
}
