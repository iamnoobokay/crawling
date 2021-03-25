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

    // function to call other functions that get the require fields
    public function parse(){
        $this->object->person_name = $this->getName();
        $this->object->practice_name = $this->practiceName();
        $this->object->phone_number = $this->phoneNumber();
        $this->object->funding_scheme = $this->fundingSchemes();
        $this->object->area_of_practices = $this->areaOfPractices();
        $this->address();
        $this->writeToFile($this->object);
    }

    // function to get name
    public function getName(){
        return $this->result->find(".name")->text();
    }

    // function to get practice name
    public function practiceName(){
        return $this->result->find(".main-contact-content")->find(".title__tag")->text();
    }

    // function to get phone number
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

    // function to get funding schemes
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

    // function to get area of practices
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

    // function to pass address to get country,postal code, state etc.
    public function address(){
        $paragraphs = $this->result->find('p');
        $addressStringUntrimmed = '';
        foreach($paragraphs as $paragraph){
            if(preg_match('/[A-Z]{5}/',$paragraph->{'textContent'}) || preg_match('/AUSTRALIA/',$paragraph->{'textContent'})){
                $addressStringUntrimmed = $paragraph->{'textContent'};
            }
        }
        $addressString = preg_replace('!\s+!', ' ', $addressStringUntrimmed);
        $this->getStreetAndCity($addressString);
        $this->getState($addressString);
        $this->getPostalCode($addressString);
        $this->getCountry($addressString);
        return;
    }

    // function to get postal code
    public function getPostalCode($addressString){
        $postalCode = 'N/A';
        if(preg_match('/,\s[0-9]{4}/',$addressString)){
            preg_match('/,\s[0-9]{4}/',$addressString,$matches);
            $postalCodeUntrimmed = $matches[0];
            $postalCodeRegexArray = preg_match('/[0-9]{4}/',$postalCodeUntrimmed,$match);
            $postalCode = $match[0];
        }
        $this->object->postal_code = $postalCode;
        return;
    }

    // function to get country
    public function getCountry($addressString){
        $country = 'N/A';
        // print_r($addressString);
        if(preg_match('/[A][U][S]?T?R?A?L?I?A?/',$addressString)){
            preg_match('/[A][U][S]?TRALIA/',$addressString,$matches);
            $country = $matches[0];
        }
        $this->object->country = $country;
        return;
    }

    // function to get state
    public function getState($addressString){
        $state = 'N/A';
        $addressParts = explode(",",$addressString);
        foreach($addressParts as $addressPart){
            if(preg_match('/QLD|SA|NSW|VIC|WA|NT|TAS|QUEENSLAND|SOUTHERN AUSTRALIA|NEW SOUTH WALES|VICTORIA|WESTERN AUSTRALIA|TASMANIA|NORTHERN TERRITORY/',$addressPart)){
                preg_match('/QLD|SA|NSW|VIC|WA|NT|TAS|NORTHERN TERRITORY|QUEENSLAND|SOUTHERN AUSTRALIA|NEW SOUTH WALES|VICTORIA|WESTERN AUSTRALIA|TASMANIA/',$addressPart,$match);
                $state = $match[0];
                break;
            };
        }
        $this->object->state = $state;
    }

    // function to get city
    public function getStreetAndCity($addressString){
        // Get street and city
        $streetAndCity = 'N/A';
        if(preg_match('/^.*(?=(, QLD|, SA|, NSW|, VIC|, WA|, NT|, TAS|, QUEENSLAND|, SOUTHERN AUSTRALIA|, NEW SOUTH WALES|, VICTORIA|, WESTERN AUSTRALIA|, TASMANIA|, NORTHERN TERRITORY))/',$addressString)){
            preg_match('/^.*(?=(, QLD|, SA|, NSW|, VIC|, WA|, NT|, TAS|, QUEENSLAND|, SOUTHERN AUSTRALIA|, NEW SOUTH WALES|, VICTORIA|, WESTERN AUSTRALIA|, TASMANIA|, NORTHERN TERRITORY))/',$addressString,$match);
            $streetAndCity = $match[0];
        }

        // Get city
        $city = 'N/A';
        if(preg_match('/\w*[A-Z] ?\w*[A-Z] ?\w*/',$streetAndCity)){
            preg_match('/\w*[A-Z] ?\w*[A-Z] ?\w*/',$streetAndCity,$match);
            $city = $match[0];
        }

        // Get Street
        $street = 'N/A';
        $streetArray = explode($city, $streetAndCity);
        if($streetArray[0] != ' '){
            $street = $streetArray[0];
        }
        $this->object->street = $street;
        $this->object->city = $city;
        return;
    }

    // function to write to file
    public function writeToFile($object){
        $fp = fopen('text.csv', 'a');
        fputcsv($fp,get_object_vars($object));
        fclose($fp);
    }
}
?>