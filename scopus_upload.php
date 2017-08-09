<?php

    include('inc/config.php');             
    include('inc/functions.php');

    if (isset($_FILES['file'])) {    
        $fh = fopen($_FILES['file']['tmp_name'], 'r+');
        $row = fgetcsv($fh, 8192,",");
        while( ($row = fgetcsv($fh, 8192,",")) !== FALSE ) {    
            
            print_r($row);
            echo '<br/><br/>'; 

            // Monta array
            $doc_obra_array["doc"]["source"] = "Base Scopus";
            $doc_obra_array["doc"]["source_id"] = $row[40];
            $doc_obra_array["doc"]["tag"][] = $_POST["tag"];
            if ($row[38] == "Article") {
                $doc_obra_array["doc"]["tipo"] = "Artigo publicado";
            }
            $doc_obra_array["doc"]["titulo"] = str_replace('"','',$row[1]);
            $doc_obra_array["doc"]["ano"] = $row[2];
            $doc_obra_array["doc"]["idioma"] = $row[36];
            if (isset($row[11])){
                $doc_obra_array["doc"]["doi"] = $row[11];
            }
            if (isset($row[10])){
                $doc_obra_array["doc"]["citacoes_recebidas"] = $row[10];
            }              
            if (isset($row[15])){
                $doc_obra_array["doc"]["resumo"] = str_replace('"','',$row[15]);
            }   
            
            
            
            
            
            // Palavras chave
            $palavras_chave_authors = explode(";",$row[16]);
            $palavras_chave_scopus = explode(";",$row[17]);
            $doc_obra_array["doc"]["palavras_chave"] = $palavras_chave_authors;
            $doc_obra_array["doc"]["palavras_chave"] = array_merge($palavras_chave_authors,$palavras_chave_scopus);          
            
            // Autores
            $autores_nome_array = explode(",",$row[0]);
            $autores_afiliacao_array = explode(";",$row[14]);
            $autores_json_str = [];
            
            for($i=0;$i<count($autores_nome_array);$i++){
                $doc_obra_array["doc"]["autores"][$i]["nomeCompletoDoAutor"] = $autores_nome_array[$i];
                $doc_obra_array["doc"]["autores"][$i]["nomeAfiliacao"] = $autores_afiliacao_array[$i];            
            }
            
            // Agência de fomento
            $agencia_de_fomento_array = explode(";",$row[26]);
            $doc_obra_array["doc"]["agencia_de_fomento"] = $agencia_de_fomento_array;
            
            // Afiliação
            $afiliacao_array = explode(",",$row[13]);
            $doc_obra_array["doc"]["afiliacao"] = $agencia_de_fomento_array;

            if ($row[38] == "Article") {
                $doc_obra_array["doc"]["artigoPublicado"]["tituloDoPeriodicoOuRevista"] = str_replace('"','',$row[3]);
                $doc_obra_array["doc"]["artigoPublicado"]["nomeDaEditora"] = $row[25];
                $doc_obra_array["doc"]["artigoPublicado"]["issn"] = $row[32];                                                                                      
                $doc_obra_array["doc"]["artigoPublicado"]["volume"] = $row[4];
                $doc_obra_array["doc"]["artigoPublicado"]["fasciculo"] = $row[5];                                                                                 
                $doc_obra_array["doc"]["artigoPublicado"]["paginaInicial"] = $row[7];                                                                             $doc_obra_array["doc"]["artigoPublicado"]["paginaFinal"] = $row[8]; 
            }
            
            if (!empty($row[11])) {
                $sha256 = hash('sha256', ''.$row[11].'');
            } else {
                $sha256 = hash('sha256', ''.$row[40].'');
            }

            $doc_obra_array["doc"]["bdpi"] = dadosExternos::query_bdpi_index($doc_obra_array["doc"]["titulo"],$doc_obra_array["doc"]["ano"]);
            
            $doc_obra_array["doc_as_upsert"] = true;            
            $body = json_encode($doc_obra_array, JSON_UNESCAPED_UNICODE); 
            
            //print_r($body);

            $resultado_scopus = elasticsearch::store_record($sha256,"trabalhos",$body);
            print_r($resultado_scopus);            
            
            
            //Limpar variáveis
            unset($palavras_chave_authors);
            unset($palavras_chave_wos);
            unset($palavras_chave_array);
            unset($autores_array);
            unset($autores_json_str);
            unset($doc_obra_array["doc"]["tag"]);
            
        }
    }
    
    //sleep(5); 
    //echo '<script>window.location = \'http://bdpife2.sibi.usp.br/coletaprod/result_trabalhos.php?search[]=tag.keyword:"'.$_POST["tag"].'"\'</script>';

?>

    
    