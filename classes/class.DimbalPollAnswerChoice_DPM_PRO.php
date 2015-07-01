<?php

class DimbalPollAnswerChoice_DPM_PRO{

    public $id;
    public $text;
    public $status;

    public function __construct($id=false, $text="", $status=DimbalStandardObjectRecord_DPM_PRO::STATUS_ACTIVE){
        if($id==false){
            //ID must not be false
            //Logger::error("Choice Creation Failed :: ID Cannot Be False");
        }elseif(trim($text)==""){
            //Logger::error("Choice Creation Failed :: Text Cannot Be False");
        }else{
            $this->id = $id;
            $this->text = $text;
            $this->status = $status;
            return $this;
        }
        return false;
    }

}
