<?php 
    require("phpQuery/phpQuery/phpQuery.php");

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,'https://www.otaus.com.au/find-an-ot');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    $result = curl_exec($ch);
    $result = phpQuery::newDocument($result);
    $form = $result->find('#frmDesktopMemberSearch');

    // $type_text_value = $form->find("#memberSearch_ServiceType")->contents()->slice(3)->getAttribute('value');
    $type_text_value=2;
    $area_of_practices_option = $form->find("#memberSearch_AreaOfPracticeId");
    $area_of_practices = $area_of_practices_option->children('option')->slice(1);
    curl_close($ch);

    $fp = fopen('text.csv', 'a');
    foreach($area_of_practices as $area){
        $area_of_practice = $area->getAttribute('value');
        $data = array(
            'Distance'=>0,
            'ServiceType'=>2,
            'AreaOfPracticeId'=>$area_of_practice,
            'State' => 0,
        );

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
        $chunked_plottings = array_chunk($plottings,40);

        foreach($chunked_plottings as $chunked_plotting){
            $url = 'https://otaus.com.au/search/getcontacts?';
            foreach($chunked_plotting as $plotting){
                $url = $url.'ids='.$plotting.'&';
            }
            $ch = curl_init();
    
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_TIMEOUT => 30,
            ));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            $result = curl_exec($ch);
            $result = phpQuery::newDocument($result);
            // echo($result);
            $result_items = $result->find("div.results__item");
            echo($result_items);
            foreach($result_items as $result_item){
                var_dump($result_item);
                break;
            }
            break;
        }
        break; 
    }

    // text.txt contains all the unfiltered data scraped from the website.
?>