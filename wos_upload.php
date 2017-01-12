<?php

    include('inc/config.php');             
    include('inc/functions.php');

    if (isset($_FILES['file'])) {    
        $fh = fopen($_FILES['file']['tmp_name'], 'r+');
        while( ($row = fgetcsv($fh, 8192,"\t")) !== FALSE ) {    
            
            //print_r($row);
            //echo '<br/><br/>'; 
            
            // Palavras chave
            $palavras_chave_authors = explode(";",$row[19]);
            $palavras_chave_wos = explode(";",$row[20]);
            $palavras_chave_array = array_merge($palavras_chave_authors, $palavras_chave_wos);
            
            // Autores
            $autores_nome_array = explode(";",$row[5]);
            $autores_citacao_array = explode(";",$row[1]);
            $autores_json_str = [];
            
            for($i=0;$i<count($autores_nome_array);$i++){
                $autores_json_str[] = '{"nome_completo_do_autor":"'.$autores_nome_array[$i].'","nome_para_citacao":"'.$autores_citacao_array[$i].'"}';
            }
            
            // Agência de fomento
            $agencia_de_fomento_array = explode(";",$row[27]);

            if ($row[0] == "J") {
                $container_title = '
					"periodico":{
						"titulo_do_periodico":"'.str_replace('"','',$row[9]).'",
                        "nome_da_editora":"'.$row[35].'",
						"issn":"'.$row[38].'",
						"volume":"'.$row[45].'",
						"fasciculo":"'.$row[46].'",
						"serie":"'.$row[47].'",
						"pagina_inicial":"'.$row[51].'",
						"pagina_final":"'.$row[52].'",
						"local_de_publicacao":"'.$row[36].'"
					},
                ';
            }
            
            if (!empty($row[54])) {
                $sha256 = hash('sha256', ''.$row[54].'');
            } else {
                $sha256 = hash('sha256', ''.$row[60].'');
            }
            
            $query_wos = 
                '{
                    "doc":{
                        "source":"Base Web of Science",  
                        "wos_id": "'.$row[60].'",
                        "tag": ["'.$_POST["tag"].'"],
                        "tipo":"'.$row[0].'",
                        "titulo": "'.str_replace('"','',$row[8]).'",
                        "ano": "'.$row[44].'",
                        "idioma": "'.$row[12].'",
                        "doi":"'.$row[54].'",
                        "citacoes_recebidas": "'.$row[31].'",
                        "agencia_de_fomento":["'.implode('","',str_replace('"','',$agencia_de_fomento_array)).'"],
                        '.$container_title.'
                        "resumo":"'.str_replace('"','',$row[21]).'",
                        "palavras_chave":["'.implode('","',$palavras_chave_array).'"],	
                        "autores":['.implode(',',$autores_json_str).']

                    },
                    "doc_as_upsert" : true
                }';
            
            //print_r($query_wos);
            //echo '<br/><br/>';
            
            $resultado_wos = store_record($sha256,"trabalhos",$query_wos);
            print_r($resultado_wos);  
            
            
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

    
    