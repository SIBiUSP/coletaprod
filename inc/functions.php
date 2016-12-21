<?php

function store_record ($client,$sha256,$query){
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'id' => "$sha256",
        'body' => $query
    ];
    $response = $client->update($params);
    echo ''.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';   
 
}

function store_curriculo ($client,$id_lattes,$query){
    
    $params = [
        'index' => 'lattes',
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
        'index' => 'lattes',
        'type' => 'trabalhos',   
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
        'index' => 'lattes',
        'type' => 'trabalhos',   
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
        'index' => 'lattes',
        'type' => 'trabalhos',   
        'body' => $query
    ];
     
    $response = $client->search($params);
    
    //print_r($response); 
    
    return $response;

}

function analisa_get($get) {
    
    $search_fields = "";
    if (!empty($get['fields'])) {
        $search_fields = implode('","',$get['fields']);  
    } else {            
        $search_fields = "_all";
    }    
    
    if (!empty($get['search'])){
        $get['search'] = str_replace('"','\"',$get['search']);
    }
    
    /* Pagination */
    if (isset($get['page'])) {
        $page = $get['page'];
        unset($get['page']);
    } else {
        $page = 1;
    }
    
    /* Pagination variables */
    $limit = 20;
    $skip = ($page - 1) * $limit;
    $next = ($page + 1);
    $prev = ($page - 1);
    $sort = array('year' => -1);       
    
    if (!empty($get['codpes'])){        
        $get['search'][] = 'codpes:'.$get['codpes'].'';
    }
    
    if (!empty($get['assunto'])){        
        $get['search'][] = 'subject:\"'.$get['assunto'].'\"';
    }    
    
    if (!empty($get['search'])){
        $query = implode(" ", $get['search']); 
    } else {
        $query = "*";
    }
    
    $search_term = '
        "query_string" : {
            "fields" : ["'.$search_fields.'"],
            "query" : "'.$query.'",
            "default_operator": "AND",
            "analyzer":"portuguese",
            "phrase_slop":10
        }                
    ';    
    
    $query_complete = '{
        "sort" : [
                { "ano.keyword" : "desc" }
            ],    
        "query": {
        '.$search_term.'
        }
    }';
    $query_aggregate = '
        "query": {
            '.$search_term.'
        },
    ';
 
    return compact('page','get','new_get','query_complete','query_aggregate','url','escaped_url','limit','termo_consulta','data_inicio','data_fim','skip');
}

?>