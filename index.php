<?php
require_once './parse.php';

class Scrape extends Parse{
    var $baseUrl = 'https://www.otaus.com.au/find-an-ot';
    var $counter = 1;

    function __construct(){
        $counter = $this->counter;
        $baseUrl = $this->baseUrl;
        $this->getBaseUrl($baseUrl);
    }

    // This function gets the baseurl which contains ot form
    public function getBaseUrl($counter){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,'https://www.otaus.com.au/find-an-ot');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        $result = phpQuery::newDocument($result);
        $form = $result->find('#frmDesktopMemberSearch');
        $area_of_practices_option = $form->find("#memberSearch_AreaOfPracticeId");
        $area_of_practices = $area_of_practices_option->children('option')->slice(1);
        curl_close($ch);

        $this->setOptionData($area_of_practices);
        echo("Scraping Completed");
    }

    // This function sets the form option data
    public function setOptionData($area_of_practices){
        foreach($area_of_practices as $area){
            $area_of_practice = $area->getAttribute('value');
            $data = array(
                'Distance'=>0,
                'ServiceType'=>2,
                'AreaOfPracticeId'=>$area_of_practice,
                'State' => 0,
            );
            $this->getOptionData($data);
        }
        return;
    }

    // This function submits the form and gets the data
    public function getOptionData($data){
        $ch = curl_init();
    
        curl_setopt_array($ch, array(
            CURLOPT_URL => 'https://www.otaus.com.au//search/membersearchdistance',
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array('Content-Type:application/json')
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        $list = json_decode($result);
        $arrayed = (array)$list;

        curl_close($ch);
        
        $plottings = $arrayed['mainlist'];

        foreach($plottings as $plotting){
            $this->getIndividualValue($plotting);
            $this->counter++;
        }
        return;
    }

    // This function gets individual values and passes it to the parser
    public function getIndividualValue($plotting){
        // $dataToWrite = array();
        $url = 'https://otaus.com.au/search/getcontacts?ids='.$plotting;

        $ch = curl_init();
    
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 30,
        ));

        curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
        $result = curl_exec($ch);
        $result = phpQuery::newDocument($result);

        parent::__construct($result);

        echo($this->counter." data scraped\n");
        return;
    }
}

$scrape = new Scrape;
?>