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
        $query = [];

        if (!empty($get['fields'])) {
            $query["query"]["query_string"]["fields"] = $get['fields'];
        } else {
            $query["query"]["query_string"]["fields"][] = "_all";
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
        
        $query['sort'] = [
            ['ano.keyword' => ['order' => 'desc']],
        ];

        if (!empty($get['codpes'])){        
            $get['search'][] = 'codpes:'.$get['codpes'].'';
        }

        if (!empty($get['assunto'])){        
            $get['search'][] = 'subject:\"'.$get['assunto'].'\"';
        }    

        if (!empty($get['search'])){
            $search = implode(" ",$get['search']);
            $query["query"]["query_string"]["query"] = $search;
        } else {
            $query["query"]["query_string"]["query"] = "*";
        }
     
        $query["query"]["query_string"]["default_operator"] = "AND";
        $query["query"]["query_string"]["analyzer"] = "portuguese";
        $query["query"]["query_string"]["phrase_slop"] = 10;
        
        return compact('page','query','limit','skip');
    }    
    
}

class users {
    
    static function store_user ($userdata){
        global $client;
        global $index;

        $query_array[] = '"nomeUsuario" : "'.$userdata->{'nomeUsuario'}.'"';
        $query_array[] = '"tipoUsuario" : "'.$userdata->{'tipoUsuario'}.'"';
        $query_array[] = '"emailPrincipalUsuario" : "'.$userdata->{'emailPrincipalUsuario'}.'"';
        $query_array[] = '"emailAlternativoUsuario" : "'.$userdata->{'emailAlternativoUsuario'}.'"';
        $query_array[] = '"emailUspUsuario" : "'.$userdata->{'emailUspUsuario'}.'"';
        $query_array[] = '"numeroTelefoneFormatado" : "'.$userdata->{'numeroTelefoneFormatado'}.'"';

        foreach ($userdata->{'vinculo'} as $vinculo) {
            $query_vinculo[] = '{
                    "tipoVinculo" : "'.$vinculo->{'tipoVinculo'}.'",
                    "codigoSetor" : "'.$vinculo->{'codigoSetor'}.'",
                    "nomeAbreviadoSetor" : "'.$vinculo->{'nomeAbreviadoSetor'}.'",
                    "nomeSetor" : "'.$vinculo->{'nomeSetor'}.'",
                    "codigoUnidade" : "'.$vinculo->{'codigoUnidade'}.'",
                    "siglaUnidade" : "'.$vinculo->{'siglaUnidade'}.'",
                    "nomeUnidade" : "'.$vinculo->{'nomeUnidade'}.'"
                }';         
        }

        $query = 
                 '{
                    "doc":{
                        "vinculo" : [
                            '.implode(",",$query_vinculo).'
                        ],
                        '.implode(",",$query_array).'
                    },
                    "doc_as_upsert" : true
                }';

        $num_usp = $userdata->{'loginUsuario'};
        $params = [
            'index' => $index,
            'type' => 'users',
            'id' => "$num_usp",
            'body' => $query
        ];
        $response = $client->update($params);   

    }    
    
}

class facets {
    
    public function facet($field,$size,$field_name,$sort) {
        global $type;
        $query = $this->query;
        $query["aggs"]["counts"]["terms"]["field"] = "$field.keyword";
        if (isset($sort)) {
            $query["aggs"]["counts"]["terms"]["order"]["_term"] = $sort;
        }
        $query["aggs"]["counts"]["terms"]["size"] = $size;
        
        $response = elasticsearch::elastic_search($type,null,0,$query);
        
        echo '<li class="uk-parent">';    
        echo '<a href="#" style="color:#333">'.$field_name.'</a>';
        echo ' <ul class="uk-nav-sub">';
        //$count = 1;
        foreach ($response["aggregations"]["counts"]["buckets"] as $facets) {
            echo '<li>';
            echo '<div class="uk-grid">
                    <div class="uk-width-4-5 uk-text-small" style="color:#333">'.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</div>
                    <div class="uk-width-1-5" style="color:#333">
                        <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=+'.$field.'.keyword:&quot;'.$facets['key'].'&quot;"  title="E" style="color:#333">*</a>
                        <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=-'.$field.'.keyword:&quot;'.$facets['key'].'&quot;" title="NÃO" style="color:#333">-</a>
                        <a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=OR '.$field.'.keyword:&quot;'.$facets['key'].'&quot;" title="OU" style="color:#333">+</a>
                    </div>
                </div>';
            echo '</li>';

        };
        echo   '</ul></li>';


    }
    
    public function rebuild_facet($field,$size,$nome_do_campo) {
        global $type;
        $query = $this->query;
        $query["aggs"]["counts"]["terms"]["field"] = "$field.keyword";
        if (isset($sort)) {
            $query["aggs"]["counts"]["terms"]["order"]["_count"] = "desc";
        }
        $query["aggs"]["counts"]["terms"]["size"] = $size;        
        
        $response = elasticsearch::elastic_search("producao",null,0,$query);

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

    public function facet_range($field,$size,$nome_do_campo) {
        global $type;
        $query = $this->query;
        $query["aggs"]["ranges"]["range"]["field"] = "metrics.$field";
        $query["aggs"]["ranges"]["range"]["ranges"][0]["to"] = 1;
        $query["aggs"]["ranges"]["range"]["ranges"][1]["from"] = 1;
        $query["aggs"]["ranges"]["range"]["ranges"][1]["to"] = 2;
        $query["aggs"]["ranges"]["range"]["ranges"][2]["from"] = 2;
        $query["aggs"]["ranges"]["range"]["ranges"][2]["to"] = 5;
        $query["aggs"]["ranges"]["range"]["ranges"][3]["from"] = 5;
        $query["aggs"]["ranges"]["range"]["ranges"][3]["to"] = 10;
        $query["aggs"]["ranges"]["range"]["ranges"][4]["from"] = 10;
        //$query["aggs"]["counts"]["terms"]["size"] = $size;               
        
        $response = elasticsearch::elastic_search($type,null,0,$query);

        echo '<li class="uk-parent">';    
        echo '<a href="#">'.$nome_do_campo.'</a>';
        echo ' <ul class="uk-nav-sub">';
        echo '<form>';
        foreach ($response["aggregations"]["ranges"]["buckets"] as $facets) {
            echo '<li class="uk-h6 uk-form-controls uk-form-controls-text">';
            echo '<p class="uk-form-controls-condensed">';
            echo '<input type="checkbox" name="'.$field.'[]" value="'.$facets['key'].'"><a href="http://'.$_SERVER["SERVER_NAME"].$_SERVER["SCRIPT_NAME"].'?'.$_SERVER["QUERY_STRING"].'&search[]=+metrics.'.$field.':&quot;'.$facets['key'].'&quot;">Intervalo '.$facets['key'].' ('.number_format($facets['doc_count'],0,',','.').')</a>';
            echo '</p>';
            echo '</li>';

        };

        echo '<input type="hidden" checked="checked" name="operator" value="AND">';
        echo '<button type="submit" class="uk-button-primary">Limitar facetas</button>';
        echo '</form>';
        echo   '</ul></li>';    


    }
    
    
}

class citation {
    
    /* Pegar o tipo de material */
    static function get_type($material_type){
        switch ($material_type) {
            case "ARTIGO DE JORNAL":
                return "article-newspaper";
            break;
            case "ARTIGO DE PERIODICO":
                return "article-journal";
            break;
            case "PARTE DE MONOGRAFIA/LIVRO":
                return "chapter";
            break;
            case "APRESENTACAO SONORA/CENICA/ENTREVISTA":
                return "interview";
            break;
            case "TRABALHO DE EVENTO-RESUMO":
                return "paper-conference";
            break;
            case "TRABALHO DE EVENTO":
                return "paper-conference";
            break;     
            case "TESE":
                return "thesis";
            break;          
            case "TEXTO NA WEB":
                return "post-weblog";
            break;
        }
    }    
    
    static function citation_query($citacao) {        
        $array_citation = [];
        $array_citation["type"] = citation::get_type($citacao["type"]);
        $array_citation["title"] = $citacao["title"];
        
        if (!empty($citacao["authors"])) {
            $i = 0;
            foreach ($citacao["authors"] as $authors){
                $array_authors = explode(',', $authors);
                $array_citation["author"][$i]["family"] = $array_authors[0];
                $array_citation["author"][$i]["given"] = $array_authors[1];
                $i++;
            }
        }
        
        if (!empty($citacao["ispartof"])) {
            $array_citation["container-title"] = $citacao["ispartof"];
        }
        if (!empty($citacao["doi"])) {
            $array_citation["DOI"] = $citacao["doi"][0];
        }        
        if (!empty($citacao["url"])) {
            $array_citation["URL"] = $citacao["url"][0];
        }           
        if (!empty($citacao["publisher"])) {
            $array_citation["publisher"] = $citacao["publisher"];
        }
        if (!empty($citacao["publisher_place"])) {
            $array_citation["publisher_place"] = $citacao["publisher_place"];
        }
        if (!empty($citacao["year"])) {
            $array_citation["issued"]["date-parts"][] = $citacao["year"];
        }        
        if (!empty($citacao["ispartof_data"])) {
            foreach ($citacao["ispartof_data"] as $ispartof_data) {
                if (strpos($ispartof_data, 'v.') !== false) {
                    $array_citation["volume"] = str_replace("v.","",$ispartof_data);
                } elseif (strpos($ispartof_data, 'n.') !== false) {
                    $array_citation["issue"] = str_replace("n.","",$ispartof_data);
                } elseif (strpos($ispartof_data, 'p.') !== false) {
                    $array_citation["page"] = str_replace("p.","",$ispartof_data);
                }
            }
        }
        
        $json = json_encode($array_citation);
        $data = json_decode($json);
        return $data;    
    }
    
}

?>