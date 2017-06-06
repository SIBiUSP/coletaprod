<?php 

include('inc/config.php');             
include('inc/functions.php');

if (isset($_GET["oai"])) {

    $oaiUrl = $_GET["oai"];
    $client_harvester = new \Phpoaipmh\Client(''.$oaiUrl.'');
    $myEndpoint = new \Phpoaipmh\Endpoint($client_harvester);
    // Result will be a SimpleXMLElement object

    $identify = $myEndpoint->identify();
    echo '<pre>';
 
    // Results will be iterator of SimpleXMLElement objects
    $results = $myEndpoint->listMetadataFormats();
    $metadata_formats = [];
    foreach($results as $item) {
        $metadata_formats[] = $item->{"metadataPrefix"};
    }
    if (in_array("nlm", $metadata_formats)) {
        
        $recs = $myEndpoint->listRecords('nlm');
       
     
        foreach($recs as $rec) {
            print_r($rec);
            if ($rec->{'header'}->attributes()->{'status'} != "deleted"){

                $sha256 = hash('sha256', ''.$rec->{'header'}->{'identifier'}.'');
                $query["doc"]["source"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'journal-title'};
                $query["doc"]["tag"] = $_GET['tag'];
                $query["doc"]["harvester_id"] = (string)$rec->{'header'}->{'identifier'};
                $query["doc"]["tipo"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-categories'}->{'subj-group'}->{'subject'};
                $query["doc"]["titulo"] = str_replace('"','',(string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'title-group'}->{'article-title'});
                $query["doc"]["ano"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'pub-date'}[0]->{'year'};                
                $query["doc"]["doi"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-id'}[1];
                $query["doc"]["resumo"] = str_replace('"','',(string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'abstract'}->{'p'});
                // Palavras-chave
                if (isset($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'kwd-group'}[0]->{'kwd'})) {
                    foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'kwd-group'}[0]->{'kwd'} as $palavra_chave) {
                        $palavraschave_array = explode(".", (string)$palavra_chave);
                        foreach ($palavraschave_array  as $pc) {
                            $query["doc"]["palavras_chave"][] = trim($pc);
                        }
                    }
                }
                $i = 0;
                foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'contrib-group'}->{'contrib'} as $autores) {
                    if ($autores->attributes()->{'contrib-type'} == "author"){
                        $query["doc"]["autores"][$i]["nomeCompletoDoAutor"] = (string)$autores->{'name'}->{'given-names'}.' '.$autores->{'name'}->{'surname'};
                        $query["doc"]["autores"][$i]["nomeParaCitacao"] = (string)$autores->{'name'}->{'surname'}.', '.$autores->{'name'}->{'given-names'};
                        if(isset($autores->{'aff'})) {
                            $query["doc"]["autores"][$i]["afiliacao"] = (string)$autores->{'aff'};
                        }
                        if(isset($autores->{'uri'})) {
                            $query["doc"]["autores"][$i]["nroIdCnpq"] = (string)$autores->{'uri'};
                        }
                        $i++;
                    }
                }
                $query["doc"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"] = str_replace('"','',(string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'journal-title'});
                $query["doc"]["trabalhoEmEventos"]["issn"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'issn'};
                $query["doc"]["trabalhoEmEventos"]["volume"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'volume'};
                $query["doc"]["trabalhoEmEventos"]["fasciculo"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-id'};
                $query["doc"]["trabalhoEmEventos"]["nomeDoEvento"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-title'};
                $query["doc"]["url_principal"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'self-uri'}->attributes('http://www.w3.org/1999/xlink');
                $query["doc_as_upsert"] = true;
                foreach ($rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'self-uri'} as $self_uri) {
                    $query["doc"]["relation"][]=(string)$self_uri->attributes('http://www.w3.org/1999/xlink');
                }
                //print_r($query);
                $resultado = elasticsearch::elastic_update($sha256,$type,$query);
                print_r($resultado);
                unset($query);
                flush();
            }
        }
    } else {
        
        $recs = $myEndpoint->listRecords('rfc1807');
        var_dump($recs);
        foreach($recs as $rec) {
            if ($rec->{'header'}->attributes()->{'status'} != "deleted"){
                $sha256 = hash('sha256', ''.$rec->{'header'}->{'identifier'}.'');
                $query["doc"]["source"] = (string)$identify->Identify->repositoryName;
                    $query["doc"]["harvester_id"] = (string)$rec->{'header'}->{'identifier'};
                    if (isset($_GET["qualis2015"])) {
                        $query["doc"]["qualis2015"] = $_GET["qualis2015"];
                    }                   
                    $query["doc"]["tipo"] = "Trabalhos em eventos";
                    $query["doc"]["titulo"] = str_replace('"','',(string)$rec->{'metadata'}->{'rfc1807'}->{'title'});
                    $query["doc"]["ano"] = substr((string)$rec->{'metadata'}->{'rfc1807'}->{'entry'},0,4);
                    print_r($rec->{'metadata'}->{'rfc1807'});
    //                $query["doc"]["doi"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'article-id'}[1];
                    $query["doc"]["resumo"] = str_replace('"','',(string)$rec->{'metadata'}->{'rfc1807'}->{'abstract'});
                    $query["doc"]["evento"]["titulo_dos_anais"] = str_replace('"','',(string)$rec->{'metadata'}->{'rfc1807'}->{'organization'}[0]);
    //
                    // Palavras-chave
                    if (isset($rec->{'metadata'}->{'rfc1807'}->{'keyword'})) {
                        foreach ($rec->{'metadata'}->{'rfc1807'}->{'keyword'} as $palavra_chave) {
                            $pc_array = [];
                            $pc_array = explode(".", (string)$palavra_chave);
                            foreach ($pc_array as $pc_explode){
                                $pc_array_dot = explode("-", $pc_explode);
                            }
                            foreach ($pc_array_dot as $pc_dot){
                                $pc_array_end = explode(".", $pc_dot);
                            }                             
                            foreach ($pc_array_end as $pc) {
                                $query["doc"]["palavras_chave"][] = trim($pc);
                            }                             
                        }
                    }
                    $i = 0;
                    foreach ($rec->{'metadata'}->{'rfc1807'}->{'author'} as $autor) {
                        $autor_array = explode(";", (string)$autor);
                        $autor_nome_array = explode(",", (string)$autor_array[0]);
                            $query["doc"]["autores"][$i]["nomeCompletoDoAutor"] = $autor_nome_array[1].' '.ucwords(strtolower($autor_nome_array[0]));
                            $query["doc"]["autores"][$i]["nomeParaCitacao"] = (string)$autor_array[0];
                            if(isset($autor_array[1])) {
                                $query["doc"]["autores"][$i]["afiliacao"] = (string)$autor_array[1];
                            }
                            $i++;
                    }
                    $query["doc"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"] = (string)$identify->Identify->repositoryName;
    //                $query["doc"]["artigoPublicado"]["nomeDaEditora"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'publisher'}->{'publisher-name'};
    //                $query["doc"]["artigoPublicado"]["issn"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'journal-meta'}->{'issn'};
    //                $query["doc"]["artigoPublicado"]["volume"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'volume'};
    //                $query["doc"]["artigoPublicado"]["fasciculo"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue'};
    //                $query["doc"]["artigoPublicado"]["paginaInicial"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-id'};
    //                $query["doc"]["artigoPublicado"]["serie"] = (string)$rec->{'metadata'}->{'article'}->{'front'}->{'article-meta'}->{'issue-title'};
                    $query["doc"]["url_principal"] = (string)$rec->{'metadata'}->{'rfc1807'}->{'id'};
                    $query["doc"]["relation"][]=(string)$rec->{'metadata'}->{'rfc1807'}->{'id'};
                    $query["doc_as_upsert"] = true;
                    $resultado = elasticsearch::elastic_update($sha256,$type,$query);
                    print_r($resultado);
                    unset($query);
                    flush();
            }
        }        
    } 

} else {
    echo "URL não informada";
}
?>