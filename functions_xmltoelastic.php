<?php

function store_record ($client,$sha256,$query){
    
    $params = [
        'index' => 'trabalhos',
        'type' => 'lattes',
        'id' => "$sha256",
        'body' => $query
    ];
    $response = $client->update($params);
    echo ''.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';   
 
}

function store_curriculo ($client,$id_lattes,$query){
    
    $params = [
        'index' => 'trabalhos',
        'type' => 'curriculo',
        'id' => "$id_lattes",
        'body' => $query
    ];
    $response = $client->update($params);
    echo ''.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';   
 
}

function compararRegistrosLattes ($client,$query_year,$query_title,$query_nome_do_evento,$query_tipo) {
 
    $query = '
    {
        "min_score": 30,
        "query":{
            "bool": {
                "should": [
                    {
                        "multi_match" : {
                            "query":      "'.$query_tipo.'",
                            "type":       "cross_fields",
                            "fields":     [ "tipo" ],
                            "minimum_should_match": "100%" 
                         }
                    },		
                    {
                        "multi_match" : {
                            "query":      "'.$query_title.'",
                            "type":       "cross_fields",
                            "fields":     [ "titulo" ],
                            "minimum_should_match": "90%" 
                         }
                    },
                    {
                        "multi_match" : {
                            "query":      "'.$query_nome_do_evento.'",
                            "type":       "cross_fields",
                            "fields":     [ "evento.nome_do_evento" ],
                            "minimum_should_match": "80%" 
                         }
                    },		    
                    {
                        "multi_match" : {
                            "query":      "'.$query_year.'",
                            "type":       "best_fields",
                            "fields":     [ "ano" ],
                            "minimum_should_match": "75%" 
                        }
                    }
                ],
                "minimum_should_match" : 1               
            }
        }
    }
    ';
    
    //print_r($query);
    
    $params = [
        'index' => 'trabalhos',
        'type' => 'lattes',   
        'body' => $query
    ];
     
    $response = $client->search($params);
    
    //print_r($response); 
    
    return $response;

}

function compararRegistrosLattesArtigos($client,$query_year,$query_title,$query_titulo_do_periodico,$query_doi,$query_tipo) {
 
    $query = '
    {
        "min_score": 0,
        "query":{
            "bool": {
                "should": [
                    {
                        "multi_match" : {
                            "query":      "'.$query_tipo.'",
                            "type":       "cross_fields",
                            "fields":     [ "tipo" ],
                            "minimum_should_match": "100%" 
                         }
                    },
                    {
                        "multi_match" : {
                            "query":      "'.$query_doi.'",
                            "type":       "cross_fields",
                            "fields":     [ "doi" ],
                            "minimum_should_match": "100%" 
                         }
                    },			    		
                    {
                        "multi_match" : {
                            "query":      "'.$query_title.'",
                            "type":       "cross_fields",
                            "fields":     [ "titulo" ],
                            "minimum_should_match": "90%" 
                         }
                    },
                    {
                        "multi_match" : {
                            "query":      "'.$query_titulo_do_periodico.'",
                            "type":       "cross_fields",
                            "fields":     [ "periodico.titulo_do_periodico" ],
                            "minimum_should_match": "80%" 
                         }
                    },		    
                    {
                        "multi_match" : {
                            "query":      "'.$query_year.'",
                            "type":       "best_fields",
                            "fields":     [ "ano" ],
                            "minimum_should_match": "75%" 
                        }
                    }
                ],
                "minimum_should_match" : 2               
            }
        }
    }
    ';
    
    //print_r($query);
    
    $params = [
        'index' => 'trabalhos',
        'type' => 'lattes',   
        'body' => $query
    ];
     
    $response = $client->search($params);
    
    //print_r($response); 
    
    return $response;

}

function compararDoi($client,$query_doi) {
 
    $query = '
    {
        "min_score": 0,
        "query":{
		"match" : {
			"doi": "'.$query_doi.'"
		}
	}
    }
    ';
    
    //print_r($query);
    
    $params = [
        'index' => 'trabalhos',
        'type' => 'lattes',   
        'body' => $query
    ];
     
    $response = $client->search($params);
    
    //print_r($response); 
    
    return $response;

}

?>