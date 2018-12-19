<!DOCTYPE html>
<?php
    // Set directory to ROOT
    chdir('../');
    // Include essencial files
    require 'inc/config.php'; 
    require 'inc/functions.php'; 

    $query["query"]["query_string"]["query"] = "-_exists_:matchTag AND source:\"Base Lattes\"";
    $query['sort'] = [
        ['datePublished.keyword' => ['order' => 'desc']],
    ];      

    $params = [];
    $params["index"] = $index;
    $params["type"] = $type;
    $params["size"] = 9000;
    $params["body"] = $query;

    $cursor = $client->search($params);
    $total = $cursor["hits"]["total"];

    echo 'Registros faltantes: '.$total.'';
    echo '<br/><br/>';

    foreach ($cursor["hits"]["hits"] as $r) {

        //print_r($r);
        unset($doc["doc"]["matchTag"]);
        $doc["doc"]["matchTag"][] = "Lattes";
        $doc["doc_as_upsert"] = true;
        $sysno = $r["_id"];
        $type = "trabalhos";
        $result_elastic = elasticsearch::elastic_update($sysno, $type, $doc);
        print_r($result_elastic); 

    }

?>