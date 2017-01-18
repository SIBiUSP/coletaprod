<?php


class elasticsearch {

    public static function elastic_get ($_id,$type,$fields) {
        global $index;
        global $client;
        if (!defined('type_constant')) define('type_constant', ''.$type.'');
        //define('fields', ''.$fields.'');
        $params = [];
        $params["index"] = $index;
        $params["type"] = type_constant;
        $params["id"] = $_id;
        $params["_source"] = $fields;
        
        $response = $client->get($params);        
        return $response;    
    }    
    
    public static function elastic_search ($type,$fields,$size,$body) {
        global $index;
        global $client;
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["_source"] = $fields;
        $params["size"] = $size;
        $params["body"] = $body;
        
        $response = $client->search($params);        
        return $response;
    }
    public static function elastic_update ($_id,$type,$body) {
        global $index;
        global $client;
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["id"] = $_id;
        $params["body"] = $body;
        
        $response = $client->update($params);        
        return $response;
    }
}

class compararRegistros {
    
    public static function doi($doi) {
        global $index;
        global $client;        
        $body = '
            {
                "query":{
                    "match" : {
                        "doi": "'.$doi.'"
                    }
                }
            }
        ';        
        $type = "trabalhos";
        $response = elasticsearch::elastic_search($type,NULL,$size,$body);
        return $response; 
    }    
    
    public static function lattesEventos ($ano,$titulo,$nome_do_evento,$tipo) {
        global $index;
        global $client;        
        $body = '
        {
            "min_score": 30,
            "query":{
                "bool": {
                    "should": [
                        {
                            "multi_match" : {
                                "query":      "'.$tipo.'",
                                "type":       "cross_fields",
                                "fields":     [ "tipo" ],
                                "minimum_should_match": "100%" 
                             }
                        },		
                        {
                            "multi_match" : {
                                "query":      "'.$titulo.'",
                                "type":       "cross_fields",
                                "fields":     [ "titulo" ],
                                "minimum_should_match": "90%" 
                             }
                        },
                        {
                            "multi_match" : {
                                "query":      "'.$nome_do_evento.'",
                                "type":       "cross_fields",
                                "fields":     [ "evento.nome_do_evento" ],
                                "minimum_should_match": "80%" 
                             }
                        },		    
                        {
                            "multi_match" : {
                                "query":      "'.$ano.'",
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
        $type = "trabalhos";
        $response = elasticsearch::elastic_search($type,NULL,NULL,$body);
        return $response;
    }
    
    public static function lattesArtigos ($ano,$titulo,$titulo_do_periodico,$doi,$tipo) {
        global $index;
        global $client;        
        $body = '
            {
                "min_score": 10,
                "query":{
                    "bool": {
                        "should": [
                            {
                                "multi_match" : {
                                    "query":      "'.$tipo.'",
                                    "type":       "cross_fields",
                                    "fields":     [ "tipo" ],
                                    "minimum_should_match": "100%" 
                                 }
                            },
                            {
                                "multi_match" : {
                                    "query":      "'.$doi.'",
                                    "type":       "cross_fields",
                                    "fields":     [ "doi" ],
                                    "minimum_should_match": "100%" 
                                 }
                            },			    		
                            {
                                "multi_match" : {
                                    "query":      "'.$titulo.'",
                                    "type":       "cross_fields",
                                    "fields":     [ "titulo" ],
                                    "minimum_should_match": "90%" 
                                 }
                            },
                            {
                                "multi_match" : {
                                    "query":      "'.$titulo_do_periodico.'",
                                    "type":       "cross_fields",
                                    "fields":     [ "periodico.titulo_do_periodico" ],
                                    "minimum_should_match": "80%" 
                                 }
                            },		    
                            {
                                "multi_match" : {
                                    "query":      "'.$ano.'",
                                    "type":       "best_fields",
                                    "fields":     [ "ano" ],
                                    "minimum_should_match": "75%" 
                                }
                            }
                        ],
                        "minimum_should_match" : 3               
                    }
                }
            }
        ';   

        $type = "trabalhos";
        $response = elasticsearch::elastic_search($type,NULL,NULL,$body);
        return $response;
    }

    public static function lattesLivros ($titulo,$isbn,$tipo) {
        global $index;
        global $client;
        $body = '
        {
            "min_score": 10,
            "query":{
                "bool": {
                    "should": [
                        {
                            "multi_match" : {
                                "query":      "'.$tipo.'",
                                "type":       "cross_fields",
                                "fields":     [ "tipo" ],
                                "minimum_should_match": "100%" 
                             }
                        },
                        {
                            "multi_match" : {
                                "query":      "'.$isbn.'",
                                "type":       "cross_fields",
                                "fields":     [ "isbn" ],
                                "minimum_should_match": "100%" 
                             }
                        },			    		
                        {
                            "multi_match" : {
                                "query":      "'.$titulo.'",
                                "type":       "cross_fields",
                                "fields":     [ "titulo" ],
                                "minimum_should_match": "90%" 
                             }
                        }
                    ],
                    "minimum_should_match" : 3               
                }
            }
        }
        '; 
        $type = "trabalhos";
        $response = elasticsearch::elastic_search($type,NULL,NULL,$body);
        return $response;
    }
    
    public static function lattesCapitulos ($titulo,$titulo_do_livro,$tipo) {
        global $index;
        global $client;
        $body = '
            {
                "min_score": 2,
                "query":{
                    "bool": {
                        "should": [
                            {
                                "multi_match" : {
                                    "query":      "'.$tipo.'",
                                    "type":       "cross_fields",
                                    "fields":     [ "tipo" ],
                                    "minimum_should_match": "100%" 
                                 }
                            },		    		
                            {
                                "multi_match" : {
                                    "query":      "'.$titulo.'",
                                    "type":       "cross_fields",
                                    "fields":     [ "titulo" ],
                                    "minimum_should_match": "90%" 
                                 }
                            },
                            {
                                "multi_match" : {
                                    "query":      "'.$titulo_do_livro.'",
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
        $type = "trabalhos";
        $response = elasticsearch::elastic_search($type,NULL,NULL,$body);
        return $response;
    }
    
    public static function lattesMidiaSocial ($titulo,$url,$tipo) {
        global $index;
        global $client;
        $body = '
        {
            "min_score": 3,
            "query":{
                "bool": {
                    "should": [
                        {
                            "multi_match" : {
                                "query":      "'.$tipo.'",
                                "type":       "cross_fields",
                                "fields":     [ "tipo" ],
                                "minimum_should_match": "100%" 
                             }
                        },		    		
                        {
                            "multi_match" : {
                                "query":      "'.$titulo.'",
                                "type":       "cross_fields",
                                "fields":     [ "titulo" ],
                                "minimum_should_match": "90%" 
                             }
                        },
                        {
                            "multi_match" : {
                                "query":      "'.$url.'",
                                "type":       "cross_fields",
                                "fields":     [ "url" ],
                                "minimum_should_match": "100%" 
                             }
                        }                    
                    ],
                    "minimum_should_match" : 3               
                }
            }
        }
        ';
        $type = "trabalhos";
        $response = elasticsearch::elastic_search($type,NULL,NULL,$body);
        return $response;
    }    

    public static function match_id ($_id,$nota) {
        $fields = ['titulo','tipo','ano'];
        $response = elasticsearch::elastic_get($_id,"trabalhos",$fields);

        echo '<div class="uk-alert uk-alert-danger">';
        echo '<h3>Registros similares no Coleta Produção USP</h3>';
            echo '<p><a href="result_trabalhos.php?&search[]=+_id:&quot;'.$_id.'&quot;">'.$response["_source"]["tipo"].' - '.$response["_source"]["titulo"].' ('.$response["_source"]["ano"].') - Nota de proximidade: '.$nota.'</a></p>';
        echo '</div>';

    }    
    
    
}

function contar_tipo_de_registro($type) {
    $body = '
        {
            "query": {
                "match_all": {}
            }
        }        
    ';    
    $size = 0;
    $response = elasticsearch::elastic_search($type,NULL,$size,$body);
    return number_format($response['hits']['total'],0,',','.');
}


function contar_registros_match ($type) {
    $body = '
        {
            "query": {
                "exists" : { "field" : "ids_match" }
            }
        }          
    ';
    $size = 0;
    $response = elasticsearch::elastic_search($type,NULL,$size,$body);
    return number_format($response['hits']['total'],0,',','.');
}

function store_record ($_id,$type,$body){
    $response = elasticsearch::elastic_update($_id,$type,$body);    
    echo '<br/>Resultado: '.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';   
 
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
        global $index;
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
            'index' => $index,
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
        global $index;
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
            'index' => $index,
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
        global $index;
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
            'index' => $index,
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
        global $index;
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
            'index' => $index,
            'type' => 'curriculos',
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
        global $index;
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
            'index' => $index,
            'type' => 'curriculos',
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
        global $index;
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
            'index' => $index,
            'type' => 'curriculos',
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
    global $index;
    $query = '{
        "aggs": {
            "group_by_state": {
                "terms": {
                    "field": "source.keyword",                    
                    "size" : 100
                }
            }
        }
    }';
    
    $params = [
        'index' => $index,
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
    global $index;
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
        'index' => $index,
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
    global $index;
    $url = "https://api.crossref.org/v1/works/http://dx.doi.org/$doi";
    $json = file_get_contents($url);
    $data = json_decode($json, TRUE);
    
    $sha256 = hash('sha256', ''.$doi.'');
    
    $doc_obra_array["doc"]["source"] = "Base DOI - CrossRef";
    $doc_obra_array["doc"]["source_id"] = $doi;    
    $doc_obra_array["doc"]["tag"][] = $tag;    
    $doc_obra_array["doc"]["tipo"] = $data["message"]["type"];
    $doc_obra_array["doc"]["titulo"] = $data["message"]["title"][0];
    if(isset($data["message"]["subtitle"][0])){
        $doc_obra_array["doc"]["subtitulo"] = $data["message"]["subtitle"][0];
    }    
    if(isset($data["message"]["published-online"]["date-parts"][0][0])){
        $doc_obra_array["doc"]["ano"] = $data["message"]["published-online"]["date-parts"][0][0];
    }     
    if(isset($data["message"]["URL"])){
        $doc_obra_array["doc"]["url"] = $data["message"]["URL"];
    }
    $doc_obra_array["doc"]["doi"] = $doi;

    if(isset($data["message"]["container-title"][0])){
        $doc_obra_array["doc"]["artigoPublicado"]["tituloDoPeriodicoOuRevista"] = $data["message"]["container-title"][0];
    }
    if(isset($data["message"]["ISSN"][0])){
        $doc_obra_array["doc"]["artigoPublicado"]["issn"] = $data["message"]["ISSN"][0];
    }      
    if(isset($data["message"]["volume"])){
        $doc_obra_array["doc"]["artigoPublicado"]["volume"] = $data["message"]["volume"];
    }         
    if(isset($data["message"]["issue"])){
        $doc_obra_array["doc"]["artigoPublicado"]["fasciculo"] = $data["message"]["issue"];
    }      
    if(isset($data["message"]["page"])){
        $doc_obra_array["doc"]["artigoPublicado"]["paginaInicial"] = $data["message"]["page"];
    }      
    if(isset($data["message"]["publisher"])){
        $doc_obra_array["doc"]["artigoPublicado"]["nomeDaEditora"] = $data["message"]["publisher"];
    }
    if(isset($data["message"]["publisher"])){
        $doc_obra_array["doc"]["citacoes_recebidas"] = $data["message"]["cited-count"];
    }     
    
    foreach ($data["message"]["subject"]  as $assunto) {	
       $doc_obra_array["doc"]["palavras_chave"][] = $assunto;
    }
    
    
    $i = 0;
    foreach ($data["message"]["author"]  as $autores) {
        $doc_obra_array["doc"]["autores"][$i]["nomeCompletoDoAutor"] = $autores["given"].", ".$autores["family"];
        $doc_obra_array["doc"]["autores"][$i]["nomeParaCitacao"] = $autores["family"].", ".$autores["given"];
        $i++;
    } 
    
    $doc_obra_array["doc_as_upsert"] = true;
    
    // Retorna resultado
    
    $body = json_encode($doc_obra_array, JSON_UNESCAPED_UNICODE); 
    
    $resultado_crossref = store_record($sha256,"trabalhos",$body);
    print_r($resultado_crossref);
}

function processaFormacaoAcaddemica($dados,$nivel,$campos) {  
    $i = 0;
    foreach ($dados as $curso) {
        foreach ($campos as $nivel_campos) {
            if (!empty($curso[$nivel_campos])) {
                $doc_curriculo_array["doc"]["formacao_academica_titulacao_$nivel"][$i][$nivel_campos] = $curso[$nivel_campos];                   
            }                    
        }                      
    $i++;
    }
    return $doc_curriculo_array;
}

function processaObra($obra,$tipo_de_obra,$tag,$id_lattes,$unidadeUSP,$codpes) {
    switch ($tipo_de_obra) {
            
        case "trabalhoEmEventos":
            $tipo_de_obra_nome = "Trabalhos em eventos";
            $campos_dadosBasicosDoTrabalho = ["natureza","tituloDoTrabalho","anoDoTrabalho","paisDoEvento","idioma","meioDeDivulgacao","homePageDoTrabalho","flagRelevancia","flagDivulgacaoCientifica"];
            $campos_detalhamentoDoTrabalho = ["classificacaoDoEvento","nomeDoEvento","cidadeDoEvento","anoDeRealizacao","tituloDosAnaisOuProceedings","paginaInicial","paginaFinal","doi","isbn","nomeDaEditora","cidadeDaEditora","volumeDosAnais","fasciculoDosAnais","serieDosAnais"];
            $resultado_comparador_local = compararRegistros::lattesEventos($obra["dadosBasicosDoTrabalho"]["anoDoTrabalho"],str_replace('"','',$obra["dadosBasicosDoTrabalho"]["tituloDoTrabalho"]),str_replace('"','',$obra["detalhamentoDoTrabalho"]["nomeDoEvento"]),"TRABALHO-EM-EVENTOS");
            $dadosBasicosNomeCampo = "dadosBasicosDoTrabalho";
            $detalhamentoNomeCampo = "detalhamentoDoTrabalho";
            $campos_sha256 = ["natureza","tituloDoTrabalho","anoDoTrabalho","paisDoEvento","nomeDoEvento","paginaInicial","homePageDoTrabalho"];
                
            break;
            
        case "artigoPublicado":
            $tipo_de_obra_nome = "Artigo publicado";
            $campos_dadosBasicosDoTrabalho = ["natureza","tituloDoArtigo","anoDoArtigo","idioma","meioDeDivulgacao","homePageDoTrabalho","flagRelevancia","flagDivulgacaoCientifica"];
            $campos_detalhamentoDoTrabalho = ["tituloDoPeriodicoOuRevista","issn","volume","serie","paginaInicial","paginaFinal","localDePublicacao"];            
            $dadosBasicosNomeCampo = "dadosBasicosDoArtigo";
            $detalhamentoNomeCampo = "detalhamentoDoArtigo";
            if (isset($obra["dadosBasicosDoArtigo"]["doi"])){
                $campos_sha256 = ["doi"];
                $resultado_comparador_local = compararRegistros::lattesArtigos($obra["dadosBasicosDoArtigo"]["anoDoArtigo"],str_replace('"','',$obra["dadosBasicosDoArtigo"]["tituloDoArtigo"]),str_replace('"','',$obra["detalhamentoDoArtigo"]["tituloDoPeriodicoOuRevista"]),$obra["dadosBasicosDoArtigo"]["doi"],"ARTIGO-PUBLICADO");
            } else {
                $campos_sha256 = ["natureza","tituloDoArtigo","anoDoArtigo","tituloDoPeriodicoOuRevista","nomeDoEvento","paginaInicial","homePageDoTrabalho"];
                $resultado_comparador_local = compararRegistros::lattesArtigos($obra["dadosBasicosDoArtigo"]["anoDoArtigo"],str_replace('"','',$obra["dadosBasicosDoArtigo"]["tituloDoArtigo"]),str_replace('"','',$obra["detalhamentoDoArtigo"]["tituloDoPeriodicoOuRevista"]),NULL,"ARTIGO-PUBLICADO");
            }
            break;
            
        case "livrosPublicadosOuOrganizado":       
            $tipo_de_obra_nome = "Livros publicados ou organizados";
            $campos_dadosBasicosDoTrabalho = ["tipo","natureza","tituloDoLivro","ano","paisDePublicacao","idioma","meioDeDivulgacao","homePageDoTrabalho","flagRelevancia","flagDivulgacaoCientifica"];
            $campos_detalhamentoDoTrabalho = ["numeroDeVolumes","numeroDePaginas","isbn","numeroDaEdicaoRevisao","cidadeDaEditora","nomeDaEditora"];            
            $dadosBasicosNomeCampo = "dadosBasicosDoLivro";
            $detalhamentoNomeCampo = "detalhamentoDoLivro";
            if (isset($obra["dadosBasicosDoLivro"]["isbn"])){
                $campos_sha256 = ["isbn"];
                $resultado_comparador_local = compararRegistros::lattesLivros(str_replace('"','',$obra["dadosBasicosDoLivro"]["tituloDoLivro"]),str_replace('"','',$obra["detalhamentoDoLivro"]["isbn"]),"LIVRO-PUBLICADO");
            } else {
                $campos_sha256 = ["natureza","tituloDoLivro"];
                $resultado_comparador_local = compararRegistros::lattesLivros(str_replace('"','',$obra["dadosBasicosDoLivro"]["tituloDoLivro"]),NULL,"LIVRO-PUBLICADO");
            }
            break;
            
        case "capituloDeLivroPublicado":       
            $tipo_de_obra_nome = "Capítulo de livro publicado";
            $campos_dadosBasicosDoTrabalho = ["tipo","tituloDoCapituloDoLivro","ano","paisDePublicacao","idioma","meioDeDivulgacao","homePageDoTrabalho","flagRelevancia","tituloDoCapituloDoLivroIngles","flagDivulgacaoCientifica"];
            $campos_detalhamentoDoTrabalho = ["tituloDoLivro","paginaInicial","paginaFinal","isbn","organizadores","numeroDaEdicaoRevisao","cidadeDaEditora","nomeDaEditora"];            
            $dadosBasicosNomeCampo = "dadosBasicosDoCapitulo";
            $detalhamentoNomeCampo = "detalhamentoDoCapitulo";
            $campos_sha256 = ["natureza","tituloDoCapituloDoLivro","isbn"];
            $resultado_comparador_local = compararRegistros::lattesCapitulos(str_replace('"','',$obra["dadosBasicosDoCapitulo"]["tituloDoCapituloDoLivro"]),str_replace('"','',$obra["detalhamentoDoCapitulo"]["tituloDoLivro"]),"CAPITULO-DE-LIVRO");
            break;

        case "midiaSocialWebsiteBlog":       
            $tipo_de_obra_nome = "Mídia Social ou Website ou Blog";
            $campos_dadosBasicosDoTrabalho = ["natureza","titulo","ano","pais","idioma","homePage","flagRelevancia","flagDivulgacaoCientifica"];
            $campos_detalhamentoDoTrabalho = ["tema"];            
            $dadosBasicosNomeCampo = "dadosBasicosDaMidiaSocialWebsiteBlog";
            $detalhamentoNomeCampo = "detalhamentoDaMidiaSocialWebsiteBlog";
            $campos_sha256 = ["natureza","titulo","homePage"];
            $resultado_comparador_local = compararRegistros::lattesMidiaSocial(str_replace('"','',$obra["dadosBasicosDaMidiaSocialWebsiteBlog"]["titulo"]),$obra["dadosBasicosDaMidiaSocialWebsiteBlog"]["homePage"],"MIDIA-SOCIAL-OU-BLOG");
            break;            
            
    }
    
    $doc_obra_array["doc"]["tipo"] = $tipo_de_obra_nome;
    $doc_obra_array["doc"]["source"] = "Base Lattes";
    $doc_obra_array["doc"]["lattes_ids"][] = $id_lattes;
    $doc_obra_array["doc"]["tag"] = $tag;
    $doc_obra_array["doc"]["unidadeUSP"][] = $unidadeUSP;
    $doc_obra_array["doc"]["codpes"] = $codpes;       
    
    $titulos_array = ["tituloDoTrabalho","tituloDoArtigo","tituloDoLivro","tituloDoCapituloDoLivro"];
    $ano_array = ["anoDoTrabalho","anoDoArtigo"];
    foreach ($campos_dadosBasicosDoTrabalho as $dados_basicos) {
        if (isset($obra[$dadosBasicosNomeCampo][$dados_basicos])) {
            $doc_obra_array["doc"][$dados_basicos] = $obra[$dadosBasicosNomeCampo][$dados_basicos];
        }
        if (in_array($dados_basicos,$titulos_array)) {
            $doc_obra_array["doc"]["titulo"] = $obra[$dadosBasicosNomeCampo][$dados_basicos];
        }
        if (in_array($dados_basicos,$ano_array)) {
            $doc_obra_array["doc"]["ano"] = $obra[$dadosBasicosNomeCampo][$dados_basicos];
        }        
    }
    
    foreach ($campos_detalhamentoDoTrabalho as $detalhamento) {
        if (isset($obra[$detalhamentoNomeCampo][$detalhamento])){
            $doc_obra_array["doc"][$tipo_de_obra][$detalhamento] = $obra[$detalhamentoNomeCampo][$detalhamento];
        }
        
    }
    
    $array_result = processaAutoresLattes ($obra["autores"]);    
    $doc_obra_array = array_merge_recursive($doc_obra_array,$array_result);
    
    if (isset($obra["palavrasChave"])){
        $array_result = processaPalavrasChaveLattes ($obra["palavrasChave"]);
        $doc_obra_array = array_merge_recursive($doc_obra_array,$array_result);
    }
    
    if (isset($obra["areasDoConhecimento"])){
        $array_result = processaAreaDoConhecimentoLattes ($obra["areasDoConhecimento"]);
        $doc_obra_array = array_merge_recursive($doc_obra_array,$array_result);
    }
    
    // Constroi sha256
    $sha256 = constroi_sha256 ($obra,$campos_sha256,$dadosBasicosNomeCampo,$detalhamentoNomeCampo);     
    
    // Comparador Local
    $i = 0;
    foreach ($resultado_comparador_local["hits"]["hits"] as $result1) {
        
        if ($result1["_id"] != $sha256){
            if (!empty($result1["_id"])){
                $doc_obra_array["doc"]["ids_match"][$i]["id_match"] = $result1["_id"];
            }
            if (isset($result1["_score"])){
                $doc_obra_array["doc"]["ids_match"][$i]["nota"] = $result1["_score"];
            }
        }
        $i++;
    }
    
    $doc_obra_array["doc_as_upsert"] = true;
    
    // Retorna resultado
    
    $body = json_encode($doc_obra_array, JSON_UNESCAPED_UNICODE);  

    
    return compact ('body','sha256');
}

function processaAutoresLattes($autores_array) {
    $i = 0;
    foreach ($autores_array as $autor) {
        $autor_campos = ["nomeCompletoDoAutor","nomeParaCitacao","ordemDeAutoria","nroIdCnpq"];
        foreach ($autor_campos as $campos){
            if (isset($autor[$campos])){
                $array_result["doc"]["autores"][$i][$campos] = $autor[$campos];
            }
            if (isset($autor["nroIdCnpq"])){
                $array_result["doc"]["lattes_ids"][] = $autor["nroIdCnpq"];
            }              
        }        
        $i++;
    }
    return $array_result;
}

function processaPalavrasChaveLattes($palavras_chave) {
    foreach (range(1, 6) as $number) {
        if (isset($palavras_chave["palavraChave$number"])){
            $array_result["doc"]["palavras_chave"][] = $palavras_chave["palavraChave$number"];
        }
    }
    return $array_result;
}

function processaAreaDoConhecimentoLattes($areas_do_conhecimento) {
    $campos = ["nomeGrandeAreaDoConhecimento","nomeDaAreaDoConhecimento","nomeDaSubAreaDoConhecimento","nomeDaEspecialidade"];
    $i = 0;
    foreach ($areas_do_conhecimento as $ac) {
        foreach ($campos as $c){
            if (isset($ac[$c])){
                $array_result["doc"]["area_do_conhecimento"][$i][$c] = $ac[$c];
            }
        } 
        $i++;
    }
    return $array_result;     
}

function constroi_sha256 ($obra,$campos_sha256,$dadosBasicosNomeCampo,$detalhamentoNomeCampo) {
    $sha_array = [];
    
    foreach ($campos_sha256 as $campos){
        if (isset($obra[$dadosBasicosNomeCampo][$campos])){
            $sha_array[] = $obra[$dadosBasicosNomeCampo][$campos];
        } elseif (isset($obra[$detalhamentoNomeCampo][$campos])){
            $sha_array[] = $obra[$detalhamentoNomeCampo][$campos];
        }       
    }
    $sha256 = hash('sha256', ''.implode("",$sha_array).'');
    return $sha256;               
}

?>