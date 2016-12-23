<?php 
    
    include ('inc/config.php');
	include ('inc/functions.php');

    $cursor = coleta_json_lattes($_GET["id_lattes"]);
    
    //print_r($cursor["docs"][0]);

	$resumo_cv = "";
	if (isset($cursor["docs"][0]["dadosGerais"]["resumoCv"])) {
        $texto_resumo_cv_rh[] = '"texto_resumo_cv_rh": "'.str_replace('"','\"',$cursor["docs"][0]["dadosGerais"]["resumoCv"]["textoResumoCvRh"]).'"';
        if (isset($cursor["docs"][0]["dadosGerais"]["resumoCv"]["textoResumoCvRhEn"])) {
            $texto_resumo_cv_rh[] = '"texto_resumo_cv_rh_en": "'.str_replace('"','\"',$cursor["docs"][0]["dadosGerais"]["resumoCv"]["textoResumoCvRhEn"]).'"';
        }
        
		$resumo_cv = '"resumo_cv": {						
						'.implode(",",$texto_resumo_cv_rh).'
				},';
		
	}

    $formacao_graduacao = [];
    if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"])) {
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["graduacao"])) {
            foreach ($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["graduacao"] as $graduacao) {
                $formacao_graduacao[] = '{
                    "sequencia_formacao":"'.$graduacao["sequenciaFormacao"].'",
                    "nivel":"'.$graduacao["nivel"].'",
                    "codigo_instituicao":"'.$graduacao["codigoInstituicao"].'",
                    "nome_instituicao":"'.$graduacao["nomeInstituicao"].'",
                    "codigo_curso":"'.$graduacao["codigoCurso"].'",
                    "nome_curso":"'.$graduacao["nomeCurso"].'",
                    "codigo_area_curso":"'.$graduacao["codigoAreaCurso"].'",
                    "status_do_curso":"'.$graduacao["statusDoCurso"].'",
                    "ano_de_inicio":"'.$graduacao["anoDeInicio"].'",
                    "ano_de_conclusao":"'.$graduacao["anoDeConclusao"].'",
                    "flag_bolsa":"'.$graduacao["flagBolsa"].'"
                }'; 

            }        
        }
    }

    $endereco_profissional = [];
    if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"])) {
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["codigoInstituicaoEmpresa"])) {
               $endereco_profissional[] = '"codigo_instituicao_empresa": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["codigoInstituicaoEmpresa"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["nomeInstituicaoEmpresa"])) {
               $endereco_profissional[] = '"nome_instituicao_empresa": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["nomeInstituicaoEmpresa"].'"';
        }        
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["codigoOrgao"])) {
               $endereco_profissional[] = '"codigo_orgao": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["codigoOrgao"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["nomeOrgao"])) {
               $endereco_profissional[] = '"nome_orgao": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["nomeOrgao"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["codigoUnidade"])) {
               $endereco_profissional[] = '"codigo_unidade": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["codigoUnidade"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["nomeUnidade"])) {
               $endereco_profissional[] = '"nome_unidade": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["nomeUnidade"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["logradouroComplemento"])) {
               $endereco_profissional[] = '"logradouro_complemento": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["logradouroComplemento"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["pais"])) {
               $endereco_profissional[] = '"pais": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["pais"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["uf"])) {
               $endereco_profissional[] = '"uf": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["uf"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["cep"])) {
               $endereco_profissional[] = '"cep": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["cep"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["cidade"])) {
               $endereco_profissional[] = '"cidade": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["cidade"].'"';
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["bairro"])) {
               $endereco_profissional[] = '"bairro": "'.$cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"]["bairro"].'"';
        }         
        
    }        

	$query_lattes = 
			'{
				"doc":{
                    "source":"Base Lattes",
                    "data_atualizacao": "'.$cursor["docs"][0]["dataAtualizacao"].'",
					"nome_completo": "'.$cursor["docs"][0]["dadosGerais"]["nomeCompleto"].'",
					"nome_em_citacoes_bibliograficas":"'.$cursor["docs"][0]["dadosGerais"]["nomeEmCitacoesBibliograficas"].'",
					"nacionalidade":"'.$cursor["docs"][0]["dadosGerais"]["nacionalidade"].'",	
					"pais_de_nascimento":"'.$cursor["docs"][0]["dadosGerais"]["paisDeNascimento"].'",
					"sigla_pais_nacionalidade":"'.$cursor["docs"][0]["dadosGerais"]["siglaPaisNacionalidade"].'",
					"pais_de_nacionalidade":"'.$cursor["docs"][0]["dadosGerais"]["paisDeNacionalidade"].'",
					'.$resumo_cv.'
					"endereco_profissional":{'.implode(",",$endereco_profissional).'},
                    "formacao_academica_titulacao_graduacao":['.implode(",",$formacao_graduacao).']   
				},
				"doc_as_upsert" : true
			}';
    
    //print_r($query_lattes);
    $resultado_curriculo = store_curriculo ($client,$_GET["id_lattes"],$query_lattes);
    print_r($resultado_curriculo);

    if (isset($cursor["docs"][0]["producaoBibliografica"]["trabalhosEmEventos"])) {
        
        foreach ($cursor["docs"][0]["producaoBibliografica"]["trabalhosEmEventos"]["trabalhoEmEventos"] as $trab_evento) {
            //print_r($trab_evento);
            //echo "<br/><br/>";
            
            
        if (isset($trab_evento["dadosBasicosDoTrabalho"]["doi"])) {
            $doi = '"doi": "'.$trab_evento["dadosBasicosDoTrabalho"]["doi"].'",';
        }
            
		foreach ($trab_evento["autores"]  as $autores) {
		
			$autores_array[] = '{ "nome_completo_do_autor":"'.$autores["nomeCompletoDoAutor"].'", "nome_para_citacao":"'.$autores["nomeParaCitacao"].'", "ordem_de_autoria":"'.$autores["ordemDeAutoria"].'", "nro_id_cnpq":"'.$autores["nroIdCnpq"].'" }';
										
		}

		$palavras_chave = [];
		if (isset($trab_evento["palavrasChave"])){		
		    if (isset($trab_evento["palavrasChave"]["palavraChave1"])){
			$palavras_chave[] = $trab_evento["palavrasChave"]["palavraChave1"];
		    }
		    if (isset($trab_evento["palavrasChave"]["palavraChave2"])){
			$palavras_chave[] = $trab_evento["palavrasChave"]["palavraChave2"];
		    }
		    if (isset($trab_evento["palavrasChave"]["palavraChave3"])){
			$palavras_chave[] = $trab_evento["palavrasChave"]["palavraChave3"];
		    }
		    if (isset($trab_evento["palavrasChave"]["palavraChave4"])){
			$palavras_chave[] = $trab_evento["palavrasChave"]["palavraChave4"];
		    }						
		    if (isset($trab_evento["palavrasChave"]["palavraChave5"])){
			$palavras_chave[] = $trab_evento["palavrasChave"]["palavraChave5"];
		    }
		    if (isset($trab_evento["palavrasChave"]["palavraChave6"])){
			$palavras_chave[] = $trab_evento["palavrasChave"]["palavraChave6"];
		    }
        }
        
        
		if (isset($trab_evento["areasDoConhecimento"])) {
			foreach ($trab_evento["areasDoConhecimento"] as $area_do_conhecimento) {
					$area_do_conhecimento_array[] = '{
						"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento["nomeGrandeAreaDoConhecimento"].'",
						"nome_da_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaAreaDoConhecimento"].'",
						"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaSubAreaDoConhecimento"].'",
						"nome_da_especialidade":"'.$area_do_conhecimento["nomeDaEspecialidade"].'"
					}'; 								
			}
		} 
            
		$area_set = "";
		if (isset($area_do_conhecimento_array)){
			$area_set = '"area_do_conhecimento":['.implode(",",$area_do_conhecimento_array).'],';
		}
            
		$sha256 = hash('sha256', ''.$trab_evento["dadosBasicosDoTrabalho"]["natureza"].$trab_evento["dadosBasicosDoTrabalho"]["tituloDoTrabalho"].$trab_evento["dadosBasicosDoTrabalho"]["anoDoTrabalho"].$trab_evento["dadosBasicosDoTrabalho"]["paisDoEvento"].$trab_evento["detalhamentoDoTrabalho"]["nomeDoEvento"].$trab_evento["detalhamentoDoTrabalho"]["paginaInicial"].$trab_evento["dadosBasicosDoTrabalho"]["homePageDoTrabalho"].$trab_evento["dadosBasicosDoTrabalho"]["doi"].'');
		
		echo 'Evento: '.$sha256.'';
		print_r($titulo);
        echo "<br/>";    
        print_r($nome_do_evento);
        echo "<br/>";

		
		$results =  compararRegistrosLattes($client,$trab_evento["dadosBasicosDoTrabalho"]["anoDoTrabalho"],$trab_evento["dadosBasicosDoTrabalho"]["tituloDoTrabalho"],$trab_evento["detalhamentoDoTrabalho"]["nomeDoEvento"],"TRABALHO-EM-EVENTOS");
		
		foreach ($results["hits"]["hits"] as $result) {			
			$id_match[] = '{"id_match":"'.$result["_id"].'","nota":"'.$result["_score"].'"}';
		}            
            
		$idmatch_set = "";
		if (isset($id_match)){
			$idmatch_set = '"ids_match":['.implode(",",$id_match).'],';
		}            
            
        $query_evento = 
			'{
				"doc":{
                    "source":"Base Lattes", 
					"id_lattes": ["'.$_GET["id_lattes"].'"],
					"tipo":"TRABALHO-EM-EVENTOS",
					"natureza": "'.$trab_evento["dadosBasicosDoTrabalho"]["natureza"].'",
					"titulo": "'.$trab_evento["dadosBasicosDoTrabalho"]["tituloDoTrabalho"].'",
					"ano": "'.$trab_evento["dadosBasicosDoTrabalho"]["anoDoTrabalho"].'",
					"pais": "'.$trab_evento["dadosBasicosDoTrabalho"]["paisDoEvento"].'",
					"idioma": "'.$trab_evento["dadosBasicosDoTrabalho"]["idioma"].'",
					"meio_de_divulgacao": "'.$trab_evento["dadosBasicosDoTrabalho"]["meioDeDivulgacao"].'",
					"url": "'.$trab_evento["dadosBasicosDoTrabalho"]["homePageDoTrabalho"].'",
					'.$doi.'					
					"evento":{
						"classificacao_do_evento": "'.$trab_evento["detalhamentoDoTrabalho"]["classificacaoDoEvento"].'",
						"nome_do_evento": "'.$trab_evento["detalhamentoDoTrabalho"]["nomeDoEvento"].'",
						"cidade_do_evento": "'.$trab_evento["detalhamentoDoTrabalho"]["cidadeDoEvento"].'",
						"ano_de_realizacao_do_evento": "'.$trab_evento["detalhamentoDoTrabalho"]["anoDeRealizacao"].'",
						"titulo_dos_anais": "'.$trab_evento["detalhamentoDoTrabalho"]["tituloDosAnaisOuProceedings"].'",
						"volume_dos_anais": "'.$volume_dos_anais.'",
						"fasciculo_dos_anais": "'.$fasciculo_dos_anais.'",
						"serie_dos_anais": "'.$serie_dos_anais.'",
						"pagina_inicial": "'.$trab_evento["detalhamentoDoTrabalho"]["paginaInicial"].'",
						"pagina_final": "'.$trab_evento["detalhamentoDoTrabalho"]["paginaFinal"].'",
						"isbn": "'.$trab_evento["detalhamentoDoTrabalho"]["isbn"].'",
						"nome_da_editora": "'.$trab_evento["detalhamentoDoTrabalho"]["nomeDaEditora"].'",
						"cidade_da_editora": "'.$trab_evento["detalhamentoDoTrabalho"]["cidadeDaEditora"].'"
					},
					"palavras_chave":["'.implode('","',$palavras_chave).'"],					
					'.$area_set.'					
					'.$idmatch_set.'
					"autores":['.implode(',',$autores_array).']
					
				},
				"doc_as_upsert" : true
			}';            
            
        print_r($query_evento);    
            
        $resultado_evento = store_record($client,$sha256,$query_evento);
        print_r($resultado_evento);     
            
        }
        
        
        
        
    }
    
        
?>