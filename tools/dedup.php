<!DOCTYPE html>
<?php
    // Set directory to ROOT
    chdir('../');
    // Include essencial files
    require 'inc/config.php'; 
    require 'inc/functions.php'; 

    $query["query"]["query_string"]["query"] = "-_exists_:match.string AND _exists_:matchTag AND datePublished:[2012 TO 2016]";
    $query['sort'] = [
        ['datePublished.keyword' => ['order' => 'desc']],
    ];      

    $params = [];
    $params["index"] = $index;
    $params["type"] = $type;
    $params["size"] = 300;
    $params["_source"] = ["doi","matchTag","name"];   
    $params["body"] = $query;

    $cursor = $client->search($params);
    $total = $cursor["hits"]["total"];

    echo 'Registros faltantes: '.$total.'';
    echo '<br/><br/>';

    foreach ($cursor["hits"]["hits"] as $r) {

        //print_r($r);
        query_wos($r);
        // echo 'Título: '.$r["_source"]["name"].' e ano: '.$r["_source"]["datePublished"].' e sysno: '.$r["_id"].'';
        // echo '<br/>';
        // query_bdpi($r["_source"]["name"], $r["_source"]["datePublished"], $r["_id"], $r["_source"]["type"]);


    }

    function query_wos($record) {
        if (!empty($record["_source"]["doi"])) {
            query_coletaprod_doi($record["_source"]["doi"], $record["_id"], $record["_source"]["matchTag"]);
        } else {
            //echo "<br/>Não tem doi<br/>";
            $name = str_replace('"', '', $record["_source"]["name"]);
            $name = str_replace('\\', '', $name);
            query_coletaprod_title($name, $record["_id"], $record["_source"]["matchTag"]);
        }

    }

    function query_coletaprod_doi($doi, $original_id, $matchTagArray) {
        //echo "<br/><br/><br/>TEM DOI<br/>";
        global $index;
        global $type;
        global $client;
        $query["query"]["query_string"]["query"] = "doi:\"$doi\" AND _exists_:matchTag";    
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["size"] = 100;     
        $params["body"] = $query;    
        $cursor = $client->search($params);
        $total = $cursor["hits"]["total"]; 
        //echo "Resultado total com DOI: $total";
        
        $result_matchTag = $matchTagArray;
        foreach ($cursor["hits"]["hits"] as $r) {
            if (isset($r["_source"]["matchTag"])) {
                $result_matchTag = array_merge($result_matchTag, $r["_source"]["matchTag"]);
            } else {
                $result_matchTag = $result_matchTag;
            }            
        }
        $result_matchTag_final = array_unique($result_matchTag);
        sort($result_matchTag_final);

        $doc["doc"]["matchTag"] = $result_matchTag_final;
        $doc["doc"]["match"]["data"] = date("Ymd");
        $doc["doc"]["match"]["count"] = count($result_matchTag_final);
        $doc["doc"]["match"]["string"] = implode(" - ",$result_matchTag_final);
        $doc["doc_as_upsert"] = true;
        //echo "<br/><br/><br/><br/>";
        //print_r($doc);
        $result_elastic = elasticsearch::elastic_update($original_id, $type, $doc);
        //print_r($result_elastic);          

    }

    function query_coletaprod_title($title, $original_id, $matchTagArray) {
        //echo "<br/><br/><br/>Sim<br/>";
        global $index;
        global $type;
        global $client;
        $query["query"]["query_string"]["query"] = "datePublished:[2012 TO 2016] AND name:\"$title\" AND _exists_:matchTag";    
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["size"] = 10;
        $params["body"] = $query;    
        $cursor = $client->search($params);
        $total = $cursor["hits"]["total"]; 


        $result_matchTag = $matchTagArray;
        foreach ($cursor["hits"]["hits"] as $r) {
            if (isset($r["_source"]["matchTag"])) {
                $result_matchTag = array_merge($result_matchTag, $r["_source"]["matchTag"]);
            } else {
                $result_matchTag = $result_matchTag;
            }            
        }
        $result_matchTag_final = array_unique($result_matchTag);
        sort($result_matchTag_final);

        $doc["doc"]["matchTag"] = $result_matchTag_final;
        $doc["doc"]["match"]["data"] = date("Ymd");
        $doc["doc"]["match"]["count"] = count($result_matchTag_final);
        $doc["doc"]["match"]["string"] = implode(" - ",$result_matchTag_final);
        $doc["doc_as_upsert"] = true;
        //echo "<br/><br/>Título<br/><br/>";
        //print_r($doc);
        $result_elastic = elasticsearch::elastic_update($original_id, $type, $doc);
        //print_r($result_elastic);    
    } 

    function query_bdpi($query_title,$query_year,$sysno,$type) { 
        global $client;
        global $index;
        global $type;       
        $query = '
        {
            "min_score": 80,
            "query":{
                "bool": {
                    "should": [	
                        {
                            "multi_match" : {
                                "query":      "'.str_replace('"', '', $query_title).'",
                                "type":       "cross_fields",
                                "fields":     [ "name" ],
                                "minimum_should_match": "90%" 
                             }
                        },
                        {
                            "multi_match" : {
                                "query":      "'.$type.'",
                                "type":       "cross_fields",
                                "fields":     [ "type" ],
                                "minimum_should_match": "90%" 
                             }
                        },	                        	    
                        {
                            "multi_match" : {
                                "query":      "'.$query_year.'",
                                "type":       "best_fields",
                                "fields":     [ "datePublished" ],
                                "minimum_should_match": "75%" 
                            }
                        }
                    ],
                    "must_not" : {
                        "term" : { "sysno" : "'.$sysno.'" }
                      },                    
                    "minimum_should_match" : 2               
                }
            }
        }
        ';

        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["size"] = 100;
        $params["body"] = $query; 
    
        $cursor = $client->search($params);

        //print_r($cursor);

        if ($cursor["hits"]["total"] > 0) {

            echo "Sim";
            print_r($cursor);


            // echo '<div class="uk-alert">';
            // echo '<h3>Registros similares na BDPI</h3>';
            // foreach ($data["hits"]["hits"] as $match){
            //     echo '<p>Nota de proximidade: '.$match["_score"].' - <a href="http://bdpi.usp.br/single.php?_id='.$match["_id"].'">'.$match["_source"]["type"].' - '.$match["_source"]["name"].' ('.$match["_source"]["datePublished"].')</a><br/> Autores: ';   
            //     foreach ($match["_source"]['author'] as $autores) {
            //         echo ''.$autores['person']['name'].', ';
            //     }
            //     if (isset($match["_source"]["doi"])){
            //         $doc["doc"]["bdpi"]["doi_bdpi"] = $match["_source"]["doi"];
            //     } else {
                    
            //     }
            //     echo '</p>';
            // }
            // echo '</div>';            

            // $doc["doc"]["bdpi"]["existe"] = "Sim";
            // $doc["doc_as_upsert"] = true;
            // //$result_elastic = elasticsearch::elastic_update($sha256,"trabalhos",$doc);
        } else {
            $doc["doc"]["dedup"]["data"] = date("Ymd");
            $doc["doc_as_upsert"] = true;
            $result_elastic = elasticsearch::elastic_update($sysno, $type, $doc);
            print_r($result_elastic);            

        }
    } 
    
    header("Refresh: 0");

?>