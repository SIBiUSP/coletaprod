<?php

    include('inc/config.php');             
    include('inc/functions.php');

    if (isset($_FILES['file'])) {    
        $fh = fopen($_FILES['file']['tmp_name'], 'r+');
        while( ($row = fgetcsv($fh, 8192,",")) !== FALSE ) {    
            
            //print_r($row);
            //echo '<br/><br/>'; 
            
            // Palavras chave
            $palavras_chave_authors = explode(";",$row[16]);
            $palavras_chave_wos = explode(";",$row[17]);
            $palavras_chave_array = array_merge($palavras_chave_authors, $palavras_chave_wos);
            
            // Autores
            $autores_nome_array = explode(",",$row[0]);
            $autores_afiliacao_array = explode(";",$row[14]);
            $autores_json_str = [];
            
            for($i=0;$i<count($autores_nome_array);$i++){
                $autores_json_str[] = '{"nome_completo_do_autor":"'.$autores_nome_array[$i].'","nome_e_afiliacao":"'.$autores_afiliacao_array[$i].'"}';
            }
            
            // Agência de fomento
            $agencia_de_fomento_array = explode(";",$row[26]);
            
            // Afiliação
            $afiliacao_array = explode(",",$row[13]);            

            if ($row[38] == "Article") {
                $container_title = '
					"periodico":{
						"titulo_do_periodico":"'.str_replace('"','',$row[3]).'",
                        "nome_da_editora":"'.$row[25].'",
						"issn":"'.$row[32].'",
						"volume":"'.$row[4].'",
						"fasciculo":"'.$row[5].'",
						"pagina_inicial":"'.$row[7].'",
						"pagina_final":"'.$row[8].'"
					},
                ';
            }
            
            if (!empty($row[11])) {
                $sha256 = hash('sha256', ''.$row[11].'');
            } else {
                $sha256 = hash('sha256', ''.$row[40].'');
            }
            
            $query_scopus = 
                '{
                    "doc":{
                        "source":"Base Scopus",  
                        "scopus_id": "'.$row[40].'",
                        "tag": ["'.$_POST["tag"].'"],
                        "tipo":"'.$row[38].'",
                        "titulo": "'.str_replace('"','',$row[1]).'",
                        "ano": "'.$row[2].'",
                        "idioma": "'.$row[36].'",
                        "doi":"'.$row[11].'",
                        "citacoes_recebidas": "'.$row[10].'",
                        "afiliacao":["'.implode('","',str_replace('"','',$afiliacao_array)).'"],
                        "agencia_de_fomento":["'.implode('","',str_replace('"','',$agencia_de_fomento_array)).'"],
                        '.$container_title.'
                        "resumo":"'.str_replace('"','',$row[15]).'",
                        "palavras_chave":["'.implode('","',$palavras_chave_array).'"],	
                        "autores":['.implode(',',$autores_json_str).']

                    },
                    "doc_as_upsert" : true
                }';
//            
//            print_r($query_scopus);
//            echo '<br/><br/>';
//            
            $resultado = store_record($sha256,"trabalhos",$query_scopus);
            print_r($resultado);  
            
            
            //Limpar variáveis
            unset($palavras_chave_authors);
            unset($palavras_chave_wos);
            unset($palavras_chave_array);
            unset($autores_array);
            unset($autores_json_str);
            
        }
    }
    
    sleep(5); 
    echo '<script>window.location = \'http://bdpife2.sibi.usp.br/coletaprod/result_trabalhos.php?search[]=tag.keyword:"'.$_POST["tag"].'"\'</script>';

?>

    
    