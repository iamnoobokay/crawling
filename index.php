<?php 
    require("phpQuery/phpQuery/phpQuery.php");

    $ch = curl_init();
    curl_setopt($ch,CURLOPT_URL,'https://www.otaus.com.au/find-an-ot');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    $result = curl_exec($ch);
    $result = phpQuery::newDocument($result);
    $form = $result->find('#frmDesktopMemberSearch');

    // $type_text_value = $form->find("#memberSearch_ServiceType")->contents()->slice(3)->getAttribute('value');
    // $type_text_value=2;
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

        foreach($plottings as $plotting){
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

            $practice_name = $result->find(".main-contact-content")->find(".title__tag")->text();
            $person_name = $result->find(".name")->text();

            $phone;
            if($result->find("a")->attr('target')){
                $phone = $result->find(".main-contact-content")->find("a")->text();
            }
            else{
                $phone = "N/A";
            }
            $phoneArray = str_split($phone,13);
            $phone_number = str_replace(' ','',$phoneArray[0]);

            $data_object = new stdClass();

            $data_object->person_name = $person_name;
            $data_object->practice_name = $practice_name;
            // $data_object->phone_number = $phone_number;
            fputcsv($fp,get_object_vars($data_object));
        }
    }
    fclose($fp);
    

    // text.txt contains all the unfiltered data scraped from the website.
?>










<?php 
    // UNCOMMENT THIS BLOCK FOR SCRAPING IN CHUNKS USED TO WRITE TO TXT FILE

    //     $chunked_plottings = array_chunk($plottings,40);

    //     foreach($chunked_plottings as $chunked_plotting){
    //         $url = 'https://otaus.com.au/search/getcontacts?';
    //         foreach($chunked_plotting as $plotting){
    //             $url = $url.'ids='.$plotting.'&';
    //         }
    //         $ch = curl_init();
    
    //         curl_setopt_array($ch, array(
    //             CURLOPT_URL => $url,
    //             CURLOPT_TIMEOUT => 30,
    //         ));

    //         curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
    //         $result = curl_exec($ch);
    //         $result = phpQuery::newDocument($result);
    //         // echo($result);
    //         $result_items = $result->find("div.results__item");
    //         echo($result_items);
    //         foreach($result_items as $result_item){
    //             var_dump($result_item);
    //             break;
    //         }
    //         break;
    //     }
    //     break; 
    // }
?>