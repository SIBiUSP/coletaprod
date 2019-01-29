<!DOCTYPE html>
<?php
    // Set directory to ROOT
    chdir('../');
    // Include essencial files
    require 'inc/config.php';
    require 'inc/functions.php';

    $query["query"]["query_string"]["query"] = "-_exists_:match.string AND _exists_:match.tag AND datePublished:[2007 TO 2016]";
    $query['sort'] = [
        ['datePublished.keyword' => ['order' => 'desc']],
    ];

    $params = [];
    $params["index"] = $index;
    $params["type"] = $type;
    $params["size"] = 100;
    $params["_source"] = ["doi","match.tag","name","author","datePublished"];
    $params["body"] = $query;

    $cursor = $client->search($params);
    $total = $cursor["hits"]["total"];

    echo 'Registros faltantes: '.$total.'';
    echo '<br/><br/>';

    foreach ($cursor["hits"]["hits"] as $record) {
        //print_r($record["_id"]);
        if (!empty($record["_source"]["doi"])) {
            query_coletaprod_doi($record["_source"]["doi"], $record["_id"], $record["_source"]["match"]["tag"]);
        } else {
            $name = str_replace('"', '', $record["_source"]["name"]);
            $name = str_replace('\\', '', $name);
            $author_name = "";
            //query_coletaprod_title($name, $record["_id"], $record["_source"]["match"]["tag"]);
            comparaprod($name, $author_name, $record["_source"]["datePublished"], $record["_id"], $record["_source"]["match"]["tag"]);
        }
        //echo "<br/><br/><br/>";


    }

    function query_coletaprod_doi($doi, $original_id, $matchTagArray)
    {
        //echo "<br/><br/><br/>TEM DOI<br/>";
        global $index;
        global $type;
        global $client;
        $query["query"]["query_string"]["query"] = "doi:\"$doi\" AND _exists_:match.tag";
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
            if (isset($r["_source"]["match"]["tag"])) {
                $result_matchTag = array_merge($result_matchTag, $r["_source"]["match"]["tag"]);
            } else {
                $result_matchTag = $result_matchTag;
            }
        }
        $result_matchTag_final = array_unique($result_matchTag);
        sort($result_matchTag_final);

        $doc["doc"]["match"]["tag"] = $result_matchTag_final;
        $doc["doc"]["match"]["data"] = date("Ymd");
        $doc["doc"]["match"]["count"] = count($result_matchTag_final);
        $doc["doc"]["match"]["string"] = implode(" - ", $result_matchTag_final);
        $doc["doc_as_upsert"] = true;
        //echo "<br/><br/><br/><br/>";
        //print_r($doc);
        $result_elastic = elasticsearch::elastic_update($original_id, $type, $doc);
        //print_r($result_elastic);

    }

    function query_coletaprod_title($title, $original_id, $matchTagArray)
    {
        //echo "<br/><br/><br/>Sim<br/>";
        global $index;
        global $type;
        global $client;
        $query["query"]["query_string"]["query"] = "datePublished:[2013 TO 2016] AND name:\"$title\" AND _exists_:match.tag";
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["size"] = 10;
        $params["body"] = $query;
        $cursor = $client->search($params);
        $total = $cursor["hits"]["total"];


        $result_matchTag = $matchTagArray;
        foreach ($cursor["hits"]["hits"] as $r) {
            if (isset($r["_source"]["match"]["tag"])) {
                $result_matchTag = array_merge($result_matchTag, $r["_source"]["match"]["tag"]);
            } else {
                $result_matchTag = $result_matchTag;
            }
        }
        $result_matchTag_final = array_unique($result_matchTag);
        sort($result_matchTag_final);

        $doc["doc"]["match"]["tag"] = $result_matchTag_final;
        $doc["doc"]["match"]["data"] = date("Ymd");
        $doc["doc"]["match"]["count"] = count($result_matchTag_final);
        $doc["doc"]["match"]["string"] = implode(" - ", $result_matchTag_final);
        $doc["doc_as_upsert"] = true;
        $result_elastic = elasticsearch::elastic_update($original_id, $type, $doc);
    }

    function comparaprod($title, $author_name, $year, $original_id, $matchTagArray)
    {
        global $index;
        global $type;
        global $client;

        $query = '
        {
            "min_score": 10,
            "query":{
                "bool": {
                    "should": [
                        {
                            "multi_match" : {
                                "query":      "'.str_replace('"', '', $title).'",
                                "type":       "cross_fields",
                                "fields":     [ "name" ],
                                "minimum_should_match": "90%"
                             }
                        },
                        {
                            "multi_match" : {
                                "query":      "'.$year.'",
                                "type":       "best_fields",
                                "fields":     [ "datePublished" ],
                                "minimum_should_match": "75%"
                            }
                        }
                    ],
                    "minimum_should_match" : 1
                }
            }
        }
        ';


        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["size"] = 1000;
        $params["body"] = $query;
        $cursor = $client->search($params);
        $total = $cursor["hits"]["total"];


        $result_matchTag = $matchTagArray;
        foreach ($cursor["hits"]["hits"] as $r) {
            if (isset($r["_source"]["match"]["tag"])) {
                $result_matchTag = array_merge($result_matchTag, $r["_source"]["match"]["tag"]);
            } else {
                $result_matchTag = $result_matchTag;
            }
        }
        $result_matchTag_final = array_unique($result_matchTag);
        sort($result_matchTag_final);

        $doc["doc"]["match"]["tag"] = $result_matchTag_final;
        $doc["doc"]["match"]["data"] = date("Ymd");
        $doc["doc"]["match"]["count"] = count($result_matchTag_final);
        $doc["doc"]["match"]["string"] = implode(" - ", $result_matchTag_final);
        $doc["doc_as_upsert"] = true;
        $result_elastic = elasticsearch::elastic_update($original_id, $type, $doc);
    }

    header("Refresh: 0");

?>
