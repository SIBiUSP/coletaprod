<?php

function query_one_elastic ($_id,$client) {
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'id' => ''.$_id.''
    ];
    $response = $client->get($params);
    return $response;    
}

function match_id ($_id,$nota,$client) {
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        '_source' => ['titulo','tipo','ano'],
        'id' => ''.$_id.''
    ];
    $response = $client->get($params);
    
    echo '<div class="uk-alert uk-alert-danger">';
    echo '<h3>Registros similares no Coleta Produção USP</h3>';
        echo '<p><a href="http://bdpife2.sibi.usp.br/coletaprod/result_trabalhos.php?&search[]=+_id:&quot;'.$_id.'&quot;">'.$response["_source"]["tipo"].' - '.$response["_source"]["titulo"].' ('.$response["_source"]["ano"].') - Nota de proximidade: '.$nota.'</a></p>';
    echo '</div>';
        
    
    //return $response;    
}

function contar_registros ($client) {
    $query_all = '
        {
            "query": {
                "match_all": {}
            }
        }        
    ';
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'size'=> 0,
        'body' => $query_all
    ];
    $response = $client->search($params);
    return $response['hits']['total'];
    print_r($response);
}

function contar_autores ($client) {
    $query_all = '
        {
            "query": {
                "match_all": {}
            }
        }        
    ';
    $params = [
        'index' => 'lattes',
        'type' => 'curriculo',
        'size'=> 0,
        'body' => $query_all
    ];
    $response = $client->search($params);
    return $response['hits']['total'];
    print_r($response);
}

function contar_registros_match ($client) {
    $query_all = '
        {
            "query": {
                "exists" : { "field" : "ids_match" }
            }
        }          
    ';
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'size'=> 0,
        'body' => $query_all
    ];
    $response = $client->search($params);
    return $response['hits']['total'];
    print_r($response);
}

function store_record ($client,$sha256,$query){
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'id' => "$sha256",
        'body' => $query
    ];
    $response = $client->update($params);
    echo '<br/>Resultado: '.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';   
 
}

function store_curriculo ($client,$id_lattes,$query){
    
    $params = [
        'index' => 'lattes',
        'type' => 'curriculo',
        'id' => "$id_lattes",
        'body' => $query
    ];
    $response = $client->update($params);
    return $response;       
 
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

function compararRegistrosLattesLivros($client,$query_title,$query_isbn,$query_tipo) {
 
    $query = '
    {
        "min_score": 3,
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
                            "query":      "'.$query_isbn.'",
                            "type":       "cross_fields",
                            "fields":     [ "isbn" ],
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

function compararRegistrosLattesCapitulos($client,$query_title,$query_titulo_do_livro,$query_tipo) {
 
    $query = '
    {
        "min_score": 2,
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
                            "query":      "'.$query_titulo_do_livro.'",
                            "type":       "cross_fields",
                            "fields":     [ "capitulo_do_livro.titulo_do_livro" ],
                            "minimum_should_match": "90%" 
                         }
                    }                    
                ],
                "minimum_should_match" : 3               
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

function compararRegistrosLattesMidiaSocial($client,$query_title,$query_url,$query_tipo) {
 
    $query = '
    {
        "min_score": 3,
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
                            "query":      "'.$query_url.'",
                            "type":       "cross_fields",
                            "fields":     [ "url" ],
                            "minimum_should_match": "100%" 
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

class facets {   
    
    public function facet($field,$tamanho,$field_name,$sort) {
        global $client;
        $query_aggregate = $this->query_aggregate;
        $sort_query="";
        if (!empty($sort)){
             $sort_query = '"order" : { "_term" : "'.$sort.'" },';  
        }     
        $query = '{
            '.$query_aggregate.'
            "aggs": {
                "counts": {
                    "terms": {
                        "field": "'.$field.'.keyword",
                        '.$sort_query.'
                        "size" : '.$tamanho.'
                    }
                }
            }
        }';
        $params = [
            'index' => 'lattes',
            'type' => 'trabalhos',
            'size'=> 0,          
            'body' => $query
        ];
        $response = $client->search($params);    
        echo '<li class="uk-parent">';    
        echo '<a href="#">'.$field_name.'</a>';
        echo ' <ul class="uk-nav-sub">';
        //$count = 1;
        foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
            echo '<li class="uk-h6 uk-form-controls uk-form-controls-text">';
            echo '<p class="uk-form-controls-condensed">';
            echo '<div class="uk-grid"><div class="uk-width-4-5">'.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</div> <div class="uk-width-1-5"> <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=+'.$field.'.keyword:&quot;'.$facets['key'].'&quot;" class="uk-icon-hover uk-icon-asterisk" data-uk-tooltip title="E"></a> <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=-'.$field.'.keyword:&quot;'.$facets['key'].'&quot;" class="uk-icon-hover uk-icon-minus" data-uk-tooltip title="NÃO"></a>  <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=OR '.$field.'.keyword:&quot;'.$facets['key'].'&quot;" class="uk-icon-hover uk-icon-plus" data-uk-tooltip title="OU"></a></div>';
            echo '</p>';
            echo '</li>';
            //if ($count == 11)
            //    {  
            //         echo '<div id="'.$campo.'" class="uk-hidden">';
            //    }
            //$count++;
        };
        //if ($count > 12) {
            //echo '</div>';
            //echo '<button class="uk-button" data-uk-toggle="{target:\'#'.$campo.'\'}">Ver mais</button>';
        //}
        echo   '</ul></li>';
    }
    
    public function rebuild_facet($field,$tamanho,$nome_do_campo) {
        global $client;
        $query_aggregate = $this->query_aggregate;
        $query = '{
            '.$query_aggregate.'
            "aggs": {
                "counts": {
                    "terms": {
                        "field": "'.$field.'.keyword",
                        "order" : { "_count" : "desc" },
                        "size" : '.$tamanho.'
                    }
                }
            }
        }';    
        $params = [
            'index' => 'lattes',
            'type' => 'trabalhos',
            'size'=> 0, 
            'body' => $query
        ];
        $response = $client->search($params);
        echo '<li class="uk-parent">';
        echo '<a href="#">'.$nome_do_campo.'</a>';
        echo ' <ul class="uk-nav-sub">';
        foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
            echo '<li class="uk-h6">';        
            echo '<a href="autoridades.php?term='.$facets['key'].'">'.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</a>';
            echo '</li>';
        };
        echo   '</ul>
          </li>';
    }
    public function facet_range($campo,$tamanho,$nome_do_campo) {
        global $client;
        $query_aggregate = $this->query_aggregate;
        $query = '
        {
            '.$query_aggregate.'
            "aggs" : {
                "ranges" : {
                    "range" : {
                        "field" : "metrics.'.$campo.'",
                        "ranges" : [
                            { "to" : 1 },
                            { "from" : 1, "to" : 2 },
                            { "from" : 2, "to" : 5 },
                            { "from" : 5, "to" : 10 },
                            { "from" : 10, "to" : 100 },
                            { "from" : 100 }
                        ]
                    }
                }
            }
         }
         ';
        $params = [
            'index' => 'lattes',
            'type' => 'trabalhos',
            'size'=> 0,          
            'body' => $query
        ];
        $response = $client->search($params); 
        //print_r($response);
        echo '<li class="uk-parent">';    
        echo '<a href="#">'.$nome_do_campo.'</a>';
        echo ' <ul class="uk-nav-sub">';
        echo '<form>';
        //$count = 1;
        foreach ($response["aggregations"]["ranges"]["buckets"] as $facets) {
            echo '<li class="uk-h6 uk-form-controls uk-form-controls-text">';
            echo '<p class="uk-form-controls-condensed">';
            echo '<input type="checkbox" name="'.$campo.'[]" value="'.$facets['key'].'"><a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=+metrics.'.$campo.':&quot;'.$facets['key'].'&quot;">Intervalo '.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</a>';
            echo '</p>';
            echo '</li>';
            //if ($count == 11)
            //    {  
            //         echo '<div id="'.$campo.'" class="uk-hidden">';
            //    }
            //$count++;
        };
        //if ($count > 12) {
            //echo '</div>';
            //echo '<button class="uk-button" data-uk-toggle="{target:\'#'.$campo.'\'}">Ver mais</button>';
        //}
        echo '<input type="hidden" checked="checked" name="operator" value="AND">';
        echo '<button type="submit" class="uk-button-primary">Limitar facetas</button>';
        echo '</form>';
        echo   '</ul></li>';    
    }
}

class facets_users {   
    
    public function facet_user($field,$tamanho,$field_name,$sort) {
        global $client;
        $query_aggregate = $this->query_aggregate;
        $sort_query="";
        if (!empty($sort)){
             $sort_query = '"order" : { "_term" : "'.$sort.'" },';  
        }     
        $query = '{
            '.$query_aggregate.'
            "aggs": {
                "counts": {
                    "terms": {
                        "field": "'.$field.'.keyword",
                        '.$sort_query.'
                        "size" : '.$tamanho.'
                    }
                }
            }
        }';
        $params = [
            'index' => 'lattes',
            'type' => 'curriculo',
            'size'=> 0,          
            'body' => $query
        ];
        $response = $client->search($params);    
        echo '<li class="uk-parent">';    
        echo '<a href="#">'.$field_name.'</a>';
        echo ' <ul class="uk-nav-sub">';
        //$count = 1;
        foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
            echo '<li class="uk-h6 uk-form-controls uk-form-controls-text">';
            echo '<p class="uk-form-controls-condensed">';
            echo '<div class="uk-grid"><div class="uk-width-4-5">'.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</div> <div class="uk-width-1-5"> <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=+'.$field.'.keyword:&quot;'.$facets['key'].'&quot;" class="uk-icon-hover uk-icon-asterisk" data-uk-tooltip title="E"></a> <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=-'.$field.'.keyword:&quot;'.$facets['key'].'&quot;" class="uk-icon-hover uk-icon-minus" data-uk-tooltip title="NÃO"></a>  <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=OR '.$field.'.keyword:&quot;'.$facets['key'].'&quot;" class="uk-icon-hover uk-icon-plus" data-uk-tooltip title="OU"></a></div>';
            echo '</p>';
            echo '</li>';
            //if ($count == 11)
            //    {  
            //         echo '<div id="'.$campo.'" class="uk-hidden">';
            //    }
            //$count++;
        };
        //if ($count > 12) {
            //echo '</div>';
            //echo '<button class="uk-button" data-uk-toggle="{target:\'#'.$campo.'\'}">Ver mais</button>';
        //}
        echo   '</ul></li>';
    }
    
    public function rebuild_facet_user($field,$tamanho,$nome_do_campo) {
        global $client;
        $query_aggregate = $this->query_aggregate;
        $query = '{
            '.$query_aggregate.'
            "aggs": {
                "counts": {
                    "terms": {
                        "field": "'.$field.'.keyword",
                        "order" : { "_count" : "desc" },
                        "size" : '.$tamanho.'
                    }
                }
            }
        }';    
        $params = [
            'index' => 'lattes',
            'type' => 'curriculo',
            'size'=> 0, 
            'body' => $query
        ];
        $response = $client->search($params);
        echo '<li class="uk-parent">';
        echo '<a href="#">'.$nome_do_campo.'</a>';
        echo ' <ul class="uk-nav-sub">';
        foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
            echo '<li class="uk-h6">';        
            echo '<a href="autoridades.php?term='.$facets['key'].'">'.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</a>';
            echo '</li>';
        };
        echo   '</ul>
          </li>';
    }
    public function facet_range_curriculo($campo,$tamanho,$nome_do_campo) {
        global $client;
        $query_aggregate = $this->query_aggregate;
        $query = '
        {
            '.$query_aggregate.'
            "aggs" : {
                "ranges" : {
                    "range" : {
                        "field" : "'.$campo.'",
                        "ranges" : [
                            { "to" : 1 },
                            { "from" : 1, "to" : 2 },
                            { "from" : 2, "to" : 5 },
                            { "from" : 5, "to" : 10 },
                            { "from" : 10, "to" : 100 },
                            { "from" : 100 }
                        ]
                    }
                }
            }
         }
         ';
        $params = [
            'index' => 'lattes',
            'type' => 'curriculo',
            'size'=> 0,          
            'body' => $query
        ];
        //print_r($query);
        
        $response = $client->search($params); 
        //print_r($response);
        echo '<li class="uk-parent">';    
        echo '<a href="#">'.$nome_do_campo.'</a>';
        echo ' <ul class="uk-nav-sub">';
        echo '<form>';
        //$count = 1;
        foreach ($response["aggregations"]["ranges"]["buckets"] as $facets) {
            echo '<li class="uk-h6 uk-form-controls uk-form-controls-text">';
            echo '<p class="uk-form-controls-condensed">';
            echo '<input type="checkbox" name="'.$campo.'[]" value="'.$facets['key'].'"><a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=+metrics.'.$campo.':&quot;'.$facets['key'].'&quot;">Intervalo '.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</a>';
            echo '</p>';
            echo '</li>';
            //if ($count == 11)
            //    {  
            //         echo '<div id="'.$campo.'" class="uk-hidden">';
            //    }
            //$count++;
        };
        //if ($count > 12) {
            //echo '</div>';
            //echo '<button class="uk-button" data-uk-toggle="{target:\'#'.$campo.'\'}">Ver mais</button>';
        //}
        echo '<input type="hidden" checked="checked" name="operator" value="AND">';
        echo '<button type="submit" class="uk-button-primary">Limitar facetas</button>';
        echo '</form>';
        echo   '</ul></li>';    
    }
    
    
}


function query_bdpi($query_title,$query_year) {

    $query = '
    {
        "min_score": 5,
        "query":{
            "bool": {
                "should": [	
                    {
                        "multi_match" : {
                            "query":      "'.$query_title.'",
                            "type":       "cross_fields",
                            "fields":     [ "title" ],
                            "minimum_should_match": "80%" 
                         }
                    },	    
                    {
                        "multi_match" : {
                            "query":      "'.$query_year.'",
                            "type":       "best_fields",
                            "fields":     [ "year" ],
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
    //172.31.0.90
        
    $ch = curl_init();
    $method = "POST";
    $url = "http://172.31.0.90/sibi/producao/_search";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PORT, 9200);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
    $result = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($result, TRUE);
    
    if ($data["hits"]["total"] > 0){
        echo '<div class="uk-alert">';
        echo '<h3>Registros similares na BDPI</h3>';
        foreach ($data["hits"]["hits"] as $match){
            //var_dump($match);
            echo '<p><a href="http://bdpi.usp.br/single.php?_id='.$match["_id"].'">'.$match["_source"]["type"].' - '.$match["_source"]["title"].' ('.$match["_source"]["year"].')</a><br/> Autores: ';   
            foreach ($match["_source"]['authors'] as $autores) {
                echo ''.$autores.', ';
            }
            echo '</p>';
            
            
        }
        echo '</div>';
    }
    return $data;
}

function coleta_json_lattes($id_lattes) {
    
    $ch = curl_init();
    $method = "GET";
    $url = "http://buscacv.cnpq.br/buscacv/rest/espelhocurriculo/$id_lattes";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    $result = curl_exec($ch);
    $info = curl_getinfo($ch);
    if ($info["http_code"] == 200) {
        var_dump($info);    
        curl_close($ch);
        $data = json_decode($result, TRUE);
        return $data;        
    } else {
        echo '<br/><br/><br/><h2>Erro '.$info["http_code"].' ao obter o arquivo da Base do Lattes, favor tentar novamente. <a href="index.php">Clique aqui para voltar a página inicial</a></h2>';
        //var_dump($info);    
        curl_close($ch);
        exit();
    }
    

    
}

function coleta_json_download_lattes($id_lattes) {
    
    $result = file_get_contents($id_lattes);
    $data = json_decode($result, TRUE);
    return $data;
    
}

function fonte_inicio($client) {
    $query = '{
        "aggs": {
            "group_by_state": {
                "terms": {
                    "field": "source.keyword",                    
                    "size" : 5
                }
            }
        }
    }';
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'size'=> 0,
        'body' => $query
    ];    
    
    $response = $client->search($params);
    foreach ($response["aggregations"]["group_by_state"]["buckets"] as $facets) {
        echo '<li><a href="result_trabalhos.php?search[]=source.keyword:&quot;'.$facets['key'].'&quot;">'.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</a></li>';
    }   
    
}

function tipo_inicio($client) {
    $query = '{
        "aggs": {
            "group_by_state": {
                "terms": {
                    "field": "tipo.keyword",                    
                    "size" : 5
                }
            }
        }
    }';
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'size'=> 0,
        'body' => $query
    ];    
    
    $response = $client->search($params);
    foreach ($response["aggregations"]["group_by_state"]["buckets"] as $facets) {
        echo '<li><a href="result_trabalhos.php?search[]=tipo.keyword:&quot;'.$facets['key'].'&quot;">'.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</a></li>';
    }   
    
}

function query_doi($doi,$tag,$client) {
    $url = "https://api.crossref.org/v1/works/http://dx.doi.org/$doi";
    $json = file_get_contents($url);
    $data = json_decode($json, TRUE);
    
    //print_r($data);
    
    $sha256 = hash('sha256', ''.$doi.''); 
    
    foreach ($data["message"]["subject"]  as $assunto) {	
        $palavras_chave[] = $assunto;
    }
    
    foreach ($data["message"]["author"]  as $autores) {		
        $autores_base_array = [];
        $autores_base_array[] = '"nome_completo_do_autor":"'.$autores["given"]." ".$autores["family"].'"';
        $autores_base_array[] = '"nome_para_citacao":"'.$autores["family"].', '.$autores["given"].'"';

        $autores_array[] = '{ 
            '.implode(",",$autores_base_array).'
        }';
        unset($autores_base_array);
    }    
    
    
    $insert_doi = 
        '{
            "doc":{
                "source":"Base DOI - CrossRef", 
                "tag": ["'.$tag.'"],
                "tipo":"'.$data["message"]["type"].'",
                "titulo": "'.$data["message"]["title"][0].'",
                "subtitulo": "'.$data["message"]["subtitle"][0].'",
                "titulo_original": "'.$data["message"]["original-title"][0].'",
                "ano": "'.$data["message"]["published-online"]["date-parts"][0][0].'",
                "url": "'.$data["message"]["URL"].'",
                "doi":"'.$data["message"]["DOI"].'",
                "periodico":{
                    "titulo_do_periodico":"'.$data["message"]["container-title"][0].'",
                    "issn":"'.$data["message"]["ISSN"][0].'",
                    "volume":"'.$data["message"]["volume"].'",
                    "fasciculo":"'.$data["message"]["issue"].'",
                    "pagina_inicial":"'.$data["message"]["page"].'",
                    "nome_da_editora":"'.$data["message"]["publisher"].'"
                },
                "palavras_chave":["'.implode('","',$palavras_chave).'"],
                "autores":['.implode(',',$autores_array).']

            },
            "doc_as_upsert" : true
        }';     
    
    
    //print_r($insert_doi);
    
    $params = [
        'index' => 'lattes',
        'type' => 'trabalhos',
        'id' => "$sha256",
        'body' => $insert_doi
    ];
    $response = $client->update($params);
    echo '<br/>Resultado: '.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';       
    

}

?>