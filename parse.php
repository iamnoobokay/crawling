<?php 
require("phpQuery/phpQuery/phpQuery.php");
class Parse{
    var $result;
    var $object; 
    function __construct($res){
        $this->result = $res;
        $this->object = new stdClass();
        $this->parse();
    }

    public function parse(){
        $this->object->person_name = $this->getName();
        $this->object->practice_name = $this->practiceName();
        $this->object->phone_number = $this->phoneNumber();
        $this->object->funding_scheme = $this->fundingSchemes();
        $this->object->area_of_practices = $this->areaOfPractices();
        $this->writeToFile($this->object);
    }

    public function getName(){
        return $this->result->find(".name")->text();
    }

    public function practiceName(){
        return $this->result->find(".main-contact-content")->find(".title__tag")->text();
    }

    public function phoneNumber(){
        $spans = $this->result->find("span");  
        $phone_number="N/A";
        foreach($spans as $span){
            if($span->{'textContent'} == "Mobile:"){
                $parentNode = ($span->{'parentNode'});
                $textContent = $parentNode->{'textContent'};
                $phone_number = substr($textContent,7);
                $phone_number = str_replace(' ', '', $phone_number);
            }
        }
        return $phone_number;
    }

    public function fundingSchemes(){
        $strongFields = $this->result->find("strong");

        $fundingSchemes = "N/A";
        foreach($strongFields as $strongField){
            if($strongField->{'textContent'} == "Funding Scheme(s):"){
                $fundingSchemesUntrimmed = $strongField->{'nextSibling'}->{'textContent'};
                $fundingSchemes =  trim($fundingSchemesUntrimmed,"\n\r\t\v\0");
            }
        }
        return $fundingSchemes;
    }

    public function areaOfPractices(){
        $strongFields = $this->result->find("strong");
        
        $areaOfPractices = "N/A";
        foreach($strongFields as $strongField){
            if($strongField->{'textContent'} == "Area(s) of Practice:"){
                $areaOfPracticesUntrimmed = $strongField->{'nextSibling'}->{'textContent'};
                $areaOfPractices =  trim($areaOfPracticesUntrimmed,"\n\r\t\v\0");
            }
        }
        return $areaOfPractices;
    }

    public function writeToFile($object){
        $fp = fopen('text.csv', 'a');
        fputcsv($fp,get_object_vars($object));
        fclose($fp);
    }
}
?>