<?php
/**
 * Arquivo de classes e funções do principais do sistema
 */

/**
 * Classe de interação com o Elasticsearch
 */
class elasticsearch {

    /**
     * Executa o commando get no Elasticsearch
     * 
     * @param string $_id ID do documento
     * @param string $type Tipo de documento no índice do Elasticsearch                         
     * @param string[] $fields Informa quais campos o sistema precisa retornar. Se nulo, o sistema retornará tudo.
     * 
     */
    public static function elastic_get ($_id,$type,$fields) {
        global $index;
        global $client;
        $params = [];
        $params["index"] = $index;
        $params["type"] = $type;
        $params["id"] = $_id;
        $params["_source"] = $fields;
        
        $response = $client->get($params);        
        return $response;    
    }    

    /**
     * Executa o commando search no Elasticsearch
     * 
     * @param string $type Tipo de documento no índice do Elasticsearch                         
     * @param string[] $fields Informa quais campos o sistema precisa retornar. Se nulo, o sistema retornará tudo.
     * @param int $size Quantidade de registros nas respostas
     * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch
     * 
     */    
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
    
    /**
     * Executa o commando update no Elasticsearch
     * 
     * @param string $_id ID do documento
     * @param string $type Tipo de documento no índice do Elasticsearch
     * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch  
     * 
     */     
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

    
    /**
     * Executa o commando update no Elasticsearch e retorna uma resposta em html
     * 
     * @param string $_id ID do documento
     * @param string $type Tipo de documento no índice do Elasticsearch
     * @param resource $body Arquivo JSON com os parâmetros das consultas no Elasticsearch  
     * 
     */     
    static function store_record ($_id,$type,$body){
        $response = elasticsearch::elastic_update($_id,$type,$body);    
        echo '<br/>Resultado: '.($response["_id"]).', '.($response["result"]).', '.($response["_shards"]['successful']).'<br/>';   

    }
    
}

class get {
    
    static function analisa_get($get) {

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
    
}


?>