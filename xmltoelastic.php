<?php 

	include ('functions_xmltoelastic.php');

	/* Endereço do server, sem http:// */ 
	$server = 'localhost'; 
	$hosts = [
		'200.144.183.86' 
	]; 

	/* Load libraries for PHP composer */ 
	require (__DIR__.'/vendor/autoload.php'); 

	/* Load Elasticsearch Client */ 
	$client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build(); 


	
	$curriculo = simplexml_load_file('xml/'.$_GET["codpes"].'.xml');
	
	
	//Pegar os dados do usuário
	
	$id_lattes = $curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
	
	$query_lattes = 
			'{
				"doc":{
					"id_usp": "'.$_GET["codpes"].'",
					"data_atualizacao": "'.$curriculo->attributes()->{'DATA-ATUALIZACAO'}.'",
					"hora_atualizacao": "'.$curriculo->attributes()->{'HORA-ATUALIZACAO'}.'",
					"nome_completo": "'.$curriculo->{'DADOS-GERAIS'}->attributes()->{'NOME-COMPLETO'}.'",
					"nome_em_citacoes_bibliograficas":"'.$curriculo->{'DADOS-GERAIS'}->attributes()->{'NOME-EM-CITACOES-BIBLIOGRAFICAS'}.'",
					"nacionalidade":"'.$curriculo->{'DADOS-GERAIS'}->attributes()->{'NACIONALIDADE'}.'",	
					"pais_de_nascimento":"'.$curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NASCIMENTO'}.'",
					"uf_nascimento":"'.$curriculo->{'DADOS-GERAIS'}->attributes()->{'UF-NASCIMENTO'}.'",
					"cidade_nascimento":"'.$curriculo->{'DADOS-GERAIS'}->attributes()->{'CIDADE-NASCIMENTO'}.'",
					"data_falecimento":"'.$curriculo->{'DADOS-GERAIS'}->attributes()->{'DATA-FALECIMENTO'}.'",
					"sigla_pais_nacionalidade":"'.$curriculo->{'DADOS-GERAIS'}->attributes()->{'SIGLA-PAIS-NACIONALIDADE'}.'",
					"pais_de_nacionalidade":"'.$curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NACIONALIDADE'}.'",
					"resumo_cv": {
						"texto_resumo_cv_rh": "'.$curriculo->{'DADOS-GERAIS'}->{'RESUMO-CV'}->attributes()->{'TEXTO-RESUMO-CV-RH'}.'",
						"texto_resumo_cv_rh_en": "'.$curriculo->{'DADOS-GERAIS'}->{'RESUMO-CV'}->attributes()->{'TEXTO-RESUMO-CV-RH-EN'}.'"
					},
					"endereco_profissional":{
						"codigo_instituicao_empresa": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'CODIGO-INSTITUICAO-EMPRESA'}.'",
						"nome_instituicao_empresa": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'NOME-INSTITUICAO-EMPRESA'}.'",
						"codigo_orgao": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'CODIGO-ORGAO'}.'",
						"nome_orgao": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'NOME-ORGAO'}.'",
						"codigo_unidade": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'CODIGO-UNIDADE'}.'",
						"nome_unidade": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'NOME-UNIDADE'}.'",
						"logradouro_complemento": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'LOGRADOURO-COMPLEMENTO'}.'",
						"pais": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'PAIS'}.'",
						"uf": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'UF'}.'",
						"cep": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'CEP'}.'",
						"cidade": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'CIDADE'}.'",
						"bairro": "'.$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'}->attributes()->{'BAIRRO'}.'"
					}					
					
				},
				"doc_as_upsert" : true
			}';
	
	//print_r($query_lattes);
	store_curriculo ($client,$id_lattes,$query_lattes);
	
	
	foreach ($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'TRABALHOS-EM-EVENTOS'}->{'TRABALHO-EM-EVENTOS'} as $trab_evento) {
		
		// Dados básicos do trabalho
		$natureza = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'NATUREZA'};
		$titulo = str_replace('"','',$trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'TITULO-DO-TRABALHO'});
		$ano = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'ANO-DO-TRABALHO'};
		$pais = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'PAIS-DO-EVENTO'};
		$idioma = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'IDIOMA'};
		$meio_de_divulgacao = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'MEIO-DE-DIVULGACAO'};
		$url = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'HOME-PAGE-DO-TRABALHO'};
		$flag_relevancia = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'FLAG-RELEVANCIA'};
		$doi = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'DOI'};
		$title = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'TITULO-DO-TRABALHO-INGLES'};
		$flag_divulgacao_cientifica = $trab_evento->{'DADOS-BASICOS-DO-TRABALHO'}->attributes()->{'FLAG-DIVULGACAO-CIENTIFICA'};
		
		// Detalhamento do trabalho		
		$classificacao_do_evento = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'CLASSIFICACAO-DO-EVENTO'};
		$nome_do_evento = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'NOME-DO-EVENTO'};
		$cidade_do_evento = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'CIDADE-DO-EVENTO'};
		$ano_de_realizacao_do_evento = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'ANO-DE-REALIZACAO'};
		$titulo_dos_anais = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'TITULO-DOS-ANAIS-OU-PROCEEDINGS'};
		$volume_dos_anais = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'VOLUME'};
		$fasciculo_dos_anais = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'FASCICULO'};
		$serie_dos_anais = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'SERIE'};
		$pagina_inicial = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'PAGINA-INICIAL'};
		$pagina_final = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'PAGINA-FINAL'};
		$isbn = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'ISBN'};
		$nome_da_editora = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'NOME-DA-EDITORA'};
		$cidade_da_editora = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'CIDADE-DA-EDITORA'};
		$nome_do_evento_ingles = $trab_evento->{'DETALHAMENTO-DO-TRABALHO'}->attributes()->{'NOME-DO-EVENTO-INGLES'};
		
		foreach ($trab_evento->{'AUTORES'} as $autores) {
		
			$autores_array[] = '{ "nome_completo_do_autor":"'.$autores->attributes()->{'NOME-COMPLETO-DO-AUTOR'}.'", "nome_para_citacao":"'.$autores->attributes()->{'NOME-PARA-CITACAO'}.'", "ordem_de_autoria":"'.$autores->attributes()->{'ORDEM-DE-AUTORIA'}.'", "nro_id_cnpq":"'.$autores->attributes()->{'NRO-ID-CNPQ'}.'" }';
										
		}
		
		// Palavras chave
		
				
		if (isset($trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-1'})){
			$palavras_chave[] = $trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-1'};
		}
		if (isset($trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-2'})){
			$palavras_chave[] = $trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-2'};
		}
		if (isset($trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-3'})){
			$palavras_chave[] = $trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-3'};
		}
		if (isset($trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-4'})){
			$palavras_chave[] = $trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-4'};
		}						
		if (isset($trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-5'})){
			$palavras_chave[] = $trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-5'};
		}
		if (isset($trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-6'})){
			$palavras_chave[] = $trab_evento->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-6'};
		}	
		

		//print_r($palavras_chave);
		
		if (isset($trab_evento->{'AREAS-DO-CONHECIMENTO'})) {
			foreach ($trab_evento->{'AREAS-DO-CONHECIMENTO'} as $area_do_conhecimento) {
					
					$area_do_conhecimento_array[] = '{
						"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-1'}->attributes()->{'NOME-GRANDE-AREA-DO-CONHECIMENTO'}.'",
						"nome_da_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-1'}->attributes()->{'NOME-DA-AREA-DO-CONHECIMENTO'}.'",
						"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-1'}->attributes()->{'NOME-DA-SUB-AREA-DO-CONHECIMENTO'}.'",
						"nome_da_especialidade":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-1'}->attributes()->{'NOME-DA-ESPECIALIDADE'}.'"
					}';
					if (isset($area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'})){ 
						$area_do_conhecimento_array[] = '{
							"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'}->attributes()->{'NOME-GRANDE-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'}->attributes()->{'NOME-DA-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'}->attributes()->{'NOME-DA-SUB-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_especialidade":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'}->attributes()->{'NOME-DA-ESPECIALIDADE'}.'"
						}';
					}
					if (isset($area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'})){ 	 
						$area_do_conhecimento_array[] = '{
							"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'}->attributes()->{'NOME-GRANDE-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'}->attributes()->{'NOME-DA-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'}->attributes()->{'NOME-DA-SUB-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_especialidade":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'}->attributes()->{'NOME-DA-ESPECIALIDADE'}.'"
						}';
					}	 								
			}
		}
				
		$sha256 = hash('sha256', ''.$natureza.$titulo.$ano.$pais.$nome_do_evento.$pagina_inicial.$url.$doi.'');
		
		
		$results =  compararRegistrosLattes($client,$ano,$titulo,$nome_do_evento,"TRABALHO-EM-EVENTOS");
		
		foreach ($results["hits"]["hits"] as $result) {			
			$id_match[] = '{"id_match":"'.$result["_id"].'","nota":"'.$result["_score"].'"}';
		}
		
		if (isset($area_do_conhecimento_array)){
			$area_set = '"area_do_conhecimento":['.implode(",",$area_do_conhecimento_array).'],';
		}
		
		$idmatch_set = "";
		if (isset($id_match)){
			$idmatch_set = '"ids_match":['.implode(",",$id_match).'],';
		}					
		
		$query = 
			'{
				"doc":{
					"id_usp": ["'.$_GET["codpes"].'"],
					"tipo":"TRABALHO-EM-EVENTOS",
					"natureza": "'.$natureza.'",
					"titulo": "'.$titulo.'",
					"ano": "'.$ano.'",
					"pais": "'.$pais.'",
					"idioma": "'.$idioma.'",
					"meio_de_divulgacao": "'.$meio_de_divulgacao.'",
					"url": "'.$url.'",
					"doi": "'.$doi.'",
					"title": "'.$title.'",
					"evento":{
						"classificacao_do_evento": "'.$classificacao_do_evento.'",
						"nome_do_evento": "'.$nome_do_evento.'",
						"cidade_do_evento": "'.$cidade_do_evento.'",
						"ano_de_realizacao_do_evento": "'.$ano_de_realizacao_do_evento.'",
						"titulo_dos_anais": "'.$titulo_dos_anais.'",
						"volume_dos_anais": "'.$volume_dos_anais.'",
						"fasciculo_dos_anais": "'.$fasciculo_dos_anais.'",
						"serie_dos_anais": "'.$serie_dos_anais.'",
						"pagina_inicial": "'.$pagina_inicial.'",
						"pagina_final": "'.$pagina_final.'",
						"isbn": "'.$isbn.'",
						"nome_da_editora": "'.$nome_da_editora.'",
						"cidade_da_editora": "'.$cidade_da_editora.'",
						"nome_do_evento_ingles": "'.$nome_do_evento_ingles.'"
					},
					"palavras_chave":["'.implode('","',$palavras_chave).'"],					
					'.$area_set.'					
					'.$idmatch_set.'
					"autores":['.implode(',',$autores_array).']
					
				},
				"doc_as_upsert" : true
			}';
		//print_r($query);
		store_record($client,$sha256,$query);

        // Unset
	unset($autor);
	unset($palavras_chave);
	unset($autores_array);
	unset($area_do_conhecimento_array);
	unset($id_match);        
    } 
    
	foreach ($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'ARTIGOS-PUBLICADOS'}->{'ARTIGO-PUBLICADO'} as $artigo_publicado) {
		
		// Dados básicos do trabalho
		$natureza = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'NATUREZA'};
		$titulo = str_replace('"','',$artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'TITULO-DO-ARTIGO'});
		$ano = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'ANO-DO-ARTIGO'};
		$pais = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'PAIS-DE-PUBLICACAO'};
		$idioma = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'IDIOMA'};
		$meio_de_divulgacao = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'MEIO-DE-DIVULGACAO'};
		$url = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'HOME-PAGE-DO-TRABALHO'};
		$flag_relevancia = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'FLAG-RELEVANCIA'};
		$doi = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'DOI'};
		$title = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'TITULO-DO-ARTIGO-INGLES'};
		$flag_divulgacao_cientifica = $artigo_publicado->{'DADOS-BASICOS-DO-ARTIGO'}->attributes()->{'FLAG-DIVULGACAO-CIENTIFICA'};
		
		// Detalhamento do artigo		
		$titulo_do_periodico = $artigo_publicado->{'DETALHAMENTO-DO-ARTIGO'}->attributes()->{'TITULO-DO-PERIODICO-OU-REVISTA'};
		$issn = $artigo_publicado->{'DETALHAMENTO-DO-ARTIGO'}->attributes()->{'ISSN'};
		$volume = $artigo_publicado->{'DETALHAMENTO-DO-ARTIGO'}->attributes()->{'VOLUME'};
		$fasciculo = $artigo_publicado->{'DETALHAMENTO-DO-ARTIGO'}->attributes()->{'FASCICULO'};
		$serie = $artigo_publicado->{'DETALHAMENTO-DO-ARTIGO'}->attributes()->{'SERIE'};
		$pagina_inicial = $artigo_publicado->{'DETALHAMENTO-DO-ARTIGO'}->attributes()->{'PAGINA-INICIAL'};
		$pagina_final = $artigo_publicado->{'DETALHAMENTO-DO-ARTIGO'}->attributes()->{'PAGINA-FINAL'};
		$local_de_publicacao = $artigo_publicado->{'DETALHAMENTO-DO-ARTIGO'}->attributes()->{'LOCAL-DE-PUBLICACAO'};

		
		foreach ($artigo_publicado->{'AUTORES'} as $autores) {
		
			$autores_array[] = '{ "nome_completo_do_autor":"'.$autores->attributes()->{'NOME-COMPLETO-DO-AUTOR'}.'", "nome_para_citacao":"'.$autores->attributes()->{'NOME-PARA-CITACAO'}.'", "ordem_de_autoria":"'.$autores->attributes()->{'ORDEM-DE-AUTORIA'}.'", "nro_id_cnpq":"'.$autores->attributes()->{'NRO-ID-CNPQ'}.'" }';
										
		}
		
		// Palavras chave
		
		if (isset($artigo_publicado->{'PALAVRAS-CHAVE'})) {		
			if (isset($artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-1'})){
				$palavras_chave[] = $artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-1'};
			}
			if (isset($artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-2'})){
				$palavras_chave[] = $artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-2'};
			}
			if (isset($artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-3'})){
				$palavras_chave[] = $artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-3'};
			}
			if (isset($artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-4'})){
				$palavras_chave[] = $artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-4'};
			}						
			if (isset($artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-5'})){
				$palavras_chave[] = $artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-5'};
			}
			if (isset($artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-6'})){
				$palavras_chave[] = $artigo_publicado->{'PALAVRAS-CHAVE'}->attributes()->{'PALAVRA-CHAVE-6'};
			}
		}		
		

		//print_r($palavras_chave);
		
		if (isset($artigo_publicado->{'AREAS-DO-CONHECIMENTO'})) {
			foreach ($artigo_publicado->{'AREAS-DO-CONHECIMENTO'} as $area_do_conhecimento) {
			
					$area_do_conhecimento_array[] = '{
						"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-1'}->attributes()->{'NOME-GRANDE-AREA-DO-CONHECIMENTO'}.'",
						"nome_da_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-1'}->attributes()->{'NOME-DA-AREA-DO-CONHECIMENTO'}.'",
						"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-1'}->attributes()->{'NOME-DA-SUB-AREA-DO-CONHECIMENTO'}.'",
						"nome_da_especialidade":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-1'}->attributes()->{'NOME-DA-ESPECIALIDADE'}.'"
					}';
					if (isset($area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'})){ 
						$area_do_conhecimento_array[] = '{
							"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'}->attributes()->{'NOME-GRANDE-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'}->attributes()->{'NOME-DA-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'}->attributes()->{'NOME-DA-SUB-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_especialidade":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-2'}->attributes()->{'NOME-DA-ESPECIALIDADE'}.'"
						}';
					}
					if (isset($area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'})){	 
						$area_do_conhecimento_array[] = '{
							"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'}->attributes()->{'NOME-GRANDE-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'}->attributes()->{'NOME-DA-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'}->attributes()->{'NOME-DA-SUB-AREA-DO-CONHECIMENTO'}.'",
							"nome_da_especialidade":"'.$area_do_conhecimento->{'AREA-DO-CONHECIMENTO-3'}->attributes()->{'NOME-DA-ESPECIALIDADE'}.'"
						}';
					}	 								
			}
		}
				
		$sha256 = hash('sha256', ''.$natureza.$titulo.$ano.$pais.$titulo_do_periodico.$pagina_inicial.$url.$doi.'');
		
		
		if ($doi != "") {
			$results =  compararDoi($client,$doi);
		} else {
			$results =  compararRegistrosLattesArtigos($client,$ano,$titulo,$titulo_do_periodico,$doi,"ARTIGO-PUBLICADO");
		}
		
		foreach ($results["hits"]["hits"] as $result) {			
			$id_match[] = '{"id_match":"'.$result["_id"].'","nota":"'.$result["_score"].'"}';
		}
		
		$palavras_chave_set = "";
		if (isset($palavras_chave)){
			$palavras_chave_set = '"palavras_chave":["'.implode('","',$palavras_chave).'"],';
		}		
		
		if (isset($area_do_conhecimento_array)){
			$area_set = '"area_do_conhecimento":['.implode(",",$area_do_conhecimento_array).'],';
		}
		
		if (isset($id_match)){
			$idmatch_set = '"ids_match":['.implode(",",$id_match).'],';
		}		
		
		$query = 
			'{
				"doc":{
					"id_usp": ["'.$_GET["codpes"].'"],
					"tipo":"ARTIGO-PUBLICADO",
					"natureza": "'.$natureza.'",
					"titulo": "'.$titulo.'",
					"ano": "'.$ano.'",
					"pais": "'.$pais.'",
					"idioma": "'.$idioma.'",
					"meio_de_divulgacao": "'.$meio_de_divulgacao.'",
					"url": "'.$url.'",
					"doi": "'.$doi.'",
					"title": "'.$title.'",
					"periodico":{
						"titulo_do_periodico":"'.$titulo_do_periodico.'",
						"issn":"'.$issn.'",
						"volume":"'.$volume.'",
						"fasciculo":"'.$fasciculo.'",
						"serie":"'.$serie.'",
						"pagina_inicial":"'.$pagina_inicial.'",
						"pagina_final":"'.$pagina_final.'",
						"local_de_publicacao":"'.$local_de_publicacao.'"
					},
					'.$palavras_chave_set.'					
					'.$area_set.'
					'.$idmatch_set.'		
					"autores":['.implode(',',$autores_array).']
					
				},
				"doc_as_upsert" : true
			}';
		//print_r($query);
		store_record($client,$sha256,$query);

        // Unset
	unset($autor);
	unset($palavras_chave);
	unset($autores_array);
	unset($area_do_conhecimento_array);
	unset($id_match);        
    }     


?>

