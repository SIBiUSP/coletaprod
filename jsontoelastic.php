<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
    <head>
        <?php 
            include('inc/config.php');             
            include('inc/meta-header.php');
            include('inc/functions.php');
            
            if(!empty($_SESSION['oauthuserdata'])) { 
                store_user($_SESSION['oauthuserdata'],$client);
            }
        ;      

        ?> 
        <title>Conversor do Lattes para o ElasticSearch - Coleta Produção USP</title>
        <!-- Facebook Tags - START -->
        <meta property="og:locale" content="pt_BR">
        <meta property="og:url" content="http://bdpi.usp.br">
        <meta property="og:title" content="Coleta Produção USP - Página Principal">
        <meta property="og:site_name" content="Coleta Produção USP">
        <meta property="og:description" content="Memória documental da produção científica, técnica e artística gerada nas Unidades da Universidade de São Paulo.">
        <meta property="og:image" content="http://www.imagens.usp.br/wp-content/uploads/USP.jpg">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="800"> 
        <meta property="og:image:height" content="600"> 
        <meta property="og:type" content="website">
        <!-- Facebook Tags - END -->
        
    </head>

    <body>     
        
        <?php include('inc/navbar.php'); ?>
        
        <div class="uk-container uk-container-center uk-margin-large-bottom">
            <div class="uk-width-medium-1-1">

<?php 

    $cursor = coleta_json_lattes($_GET["id_lattes"]);
    
    //print_r($cursor);

    $doc_curriculo_array = [];
    $doc_curriculo_array[] = '"source":"Base Lattes"';
    $doc_curriculo_array[] = '"tag": ["'.$_GET["tag"].'"]';
    $doc_curriculo_array[] = '"data_atualizacao": "'.$cursor["docs"][0]["dataAtualizacao"].'"';
    $doc_curriculo_array[] = '"nome_completo": "'.$cursor["docs"][0]["dadosGerais"]["nomeCompleto"].'"';
    $doc_curriculo_array[] = '"nome_em_citacoes_bibliograficas":"'.$cursor["docs"][0]["dadosGerais"]["nomeEmCitacoesBibliograficas"].'"';
    $doc_curriculo_array[] = '"nacionalidade":"'.$cursor["docs"][0]["dadosGerais"]["nacionalidade"].'"';
    $doc_curriculo_array[] = '"pais_de_nascimento":"'.$cursor["docs"][0]["dadosGerais"]["paisDeNascimento"].'"';
    $doc_curriculo_array[] = '"sigla_pais_nacionalidade":"'.$cursor["docs"][0]["dadosGerais"]["siglaPaisNacionalidade"].'"';
    $doc_curriculo_array[] = '"pais_de_nacionalidade":"'.$cursor["docs"][0]["dadosGerais"]["paisDeNacionalidade"].'"';



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

    foreach ($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"]["totalQuadroCitacoes"] as $citacoes) {
        $citacoes_base_array = [];
        if(isset($citacoes["nomeBase"])) {
            $citacoes_base_array[] = '"nome_base":"'.$citacoes["nomeBase"].'"';
        }
        if(isset($citacoes["codigoBase"])) {
            $citacoes_base_array[] = '"codigo_base":"'.$citacoes["codigoBase"].'"';
        }
        if(isset($citacoes["sequencialIndicador"])) {
            $citacoes_base_array[] = '"sequencial_indicador":"'.$citacoes["sequencialIndicador"].'"';
        }
        if(isset($citacoes["numeroCitacoes"])) {
            $citacoes_base_array[] = '"numero_citacoes":"'.$citacoes["numeroCitacoes"].'"';
        }
        if(isset($citacoes["dataCitacao"])) {
            $citacoes_base_array[] = '"data_citacao":"'.$citacoes["dataCitacao"].'"';
        }
        if(isset($citacoes["textoArgumento"])) {
            $citacoes_base_array[] = '"texto_argumento":"'.$citacoes["textoArgumento"].'"';
        }        
        if(isset($citacoes["indiceH"])) {
            $citacoes_base_array[] = '"indice_h":"'.$citacoes["indiceH"].'"';
        }
        if(isset($citacoes["numeroTrabalhos"])) {
            $citacoes_base_array[] = '"numero_trabalhos":"'.$citacoes["numeroTrabalhos"].'"';
        }          
        if(isset($citacoes["uriPesquisadorBase"])) {
            $citacoes_base_array[] = '"uri_pesquisador_base":"'.$citacoes["uriPesquisadorBase"].'"';
        } 
        if(isset($citacoes["uriLogoBase"])) {
            $citacoes_base_array[] = '"uri_logo_base":"'.$citacoes["uriLogoBase"].'"';
        }        
        
        $citacoes_array[] = '{
            "'.str_replace(" ","_",strtolower($citacoes["nomeBase"])).'": 
                { 
                    '.implode(",",$citacoes_base_array).'
                }
        }';
        
        unset ($citacoes_base_array);
    }

    $formacao_graduacao = [];
    $formacao_mestrado = [];
    $formacao_mestradoProfissionalizante = [];
    $formacao_doutorado = [];
    $formacao_livreDocencia = [];
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
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["mestrado"])) {
            foreach ($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["mestrado"] as $mestrado) {
                $formacao_mestrado[] = '{
                    "sequencia_formacao":"'.$mestrado["sequenciaFormacao"].'",
                    "nivel":"'.$mestrado["nivel"].'",
                    "codigo_instituicao":"'.$mestrado["codigoInstituicao"].'",
                    "nome_instituicao":"'.$mestrado["nomeInstituicao"].'",
                    "codigo_curso":"'.$mestrado["codigoCurso"].'",
                    "nome_curso":"'.$mestrado["nomeCurso"].'",
                    "codigo_area_curso":"'.$mestrado["codigoAreaCurso"].'",
                    "status_do_curso":"'.$mestrado["statusDoCurso"].'",
                    "ano_de_inicio":"'.$mestrado["anoDeInicio"].'",
                    "ano_de_conclusao":"'.$mestrado["anoDeConclusao"].'",
                    "ano_de_obtencao_do_titulo":"'.$mestrado["anoDeObtencaoDoTitulo"].'",
                    "nome_completo_do_orientador":"'.$mestrado["nomeCompletoDoOrientador"].'",
                    "id_do_orientador":"'.$mestrado["numeroIdOrientador"].'",
                    "codigo_curso_capes":"'.$mestrado["codigoCursoCapes"].'",
                    "nome_curso_capes":"'.$mestrado["nomeCursoIngles"].'",
                    "conceito_capes":"'.$mestrado["conceitoCapes"].'",
                    "codigo_agencia_financiadora":"'.$mestrado["codigoAgenciaFinanciadora"].'",
                    "nome_agencia":"'.$mestrado["nomeAgencia"].'",
                    "flag_bolsa":"'.$mestrado["flagBolsa"].'"
                }'; 

            }        
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["mestradoProfissionalizante"])) {
            foreach ($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["mestradoProfissionalizante"] as $mestradoProfissionalizante) {
                $formacao_mestradoProfissionalizante[] = '{
                    "sequencia_formacao":"'.$mestradoProfissionalizante["sequenciaFormacao"].'",
                    "nivel":"'.$mestradoProfissionalizante["nivel"].'",
                    "codigo_instituicao":"'.$mestradoProfissionalizante["codigoInstituicao"].'",
                    "nome_instituicao":"'.$mestradoProfissionalizante["nomeInstituicao"].'",
                    "codigo_curso":"'.$mestradoProfissionalizante["codigoCurso"].'",
                    "nome_curso":"'.$mestradoProfissionalizante["nomeCurso"].'",
                    "codigo_area_curso":"'.$mestradoProfissionalizante["codigoAreaCurso"].'",
                    "status_do_curso":"'.$mestradoProfissionalizante["statusDoCurso"].'",
                    "ano_de_inicio":"'.$mestradoProfissionalizante["anoDeInicio"].'",
                    "ano_de_conclusao":"'.$mestradoProfissionalizante["anoDeConclusao"].'",
                    "ano_de_obtencao_do_titulo":"'.$mestradoProfissionalizante["anoDeObtencaoDoTitulo"].'",
                    "nome_completo_do_orientador":"'.$mestradoProfissionalizante["nomeCompletoDoOrientador"].'",
                    "id_do_orientador":"'.$mestradoProfissionalizante["numeroIdOrientador"].'",
                    "codigo_curso_capes":"'.$mestradoProfissionalizante["codigoCursoCapes"].'",
                    "nome_curso_capes":"'.$mestradoProfissionalizante["nomeCursoIngles"].'",
                    "conceito_capes":"'.$mestradoProfissionalizante["conceitoCapes"].'",
                    "codigo_agencia_financiadora":"'.$mestradoProfissionalizante["codigoAgenciaFinanciadora"].'",
                    "nome_agencia":"'.$mestradoProfissionalizante["nomeAgencia"].'",
                    "flag_bolsa":"'.$mestradoProfissionalizante["flagBolsa"].'"
                }'; 

            }        
        }        
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["doutorado"])) {
            foreach ($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["doutorado"] as $doutorado) {
                $formacao_doutorado[] = '{
                    "sequencia_formacao":"'.$doutorado["sequenciaFormacao"].'",
                    "nivel":"'.$doutorado["nivel"].'",
                    "codigo_instituicao":"'.$doutorado["codigoInstituicao"].'",
                    "nome_instituicao":"'.$doutorado["nomeInstituicao"].'",
                    "codigo_curso":"'.$doutorado["codigoCurso"].'",
                    "nome_curso":"'.$doutorado["nomeCurso"].'",
                    "codigo_area_curso":"'.$doutorado["codigoAreaCurso"].'",
                    "status_do_curso":"'.$doutorado["statusDoCurso"].'",
                    "ano_de_inicio":"'.$doutorado["anoDeInicio"].'",
                    "ano_de_conclusao":"'.$doutorado["anoDeConclusao"].'",
                    "ano_de_obtencao_do_titulo":"'.$doutorado["anoDeObtencaoDoTitulo"].'",
                    "nome_completo_do_orientador":"'.$doutorado["nomeCompletoDoOrientador"].'",
                    "tipo_doutorado":"'.$doutorado["tipoDoutorado"].'",
                    "id_do_orientador":"'.$doutorado["numeroIdOrientador"].'",
                    "codigo_curso_capes":"'.$doutorado["codigoCursoCapes"].'",
                    "nome_curso_capes":"'.$doutorado["nomeCursoIngles"].'",
                    "conceito_capes":"'.$doutorado["conceitoCapes"].'",
                    "codigo_agencia_financiadora":"'.$doutorado["codigoAgenciaFinanciadora"].'",
                    "nome_agencia":"'.$doutorado["nomeAgencia"].'",
                    "flag_bolsa":"'.$doutorado["flagBolsa"].'"
                }'; 

            }        
        }
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["livreDocencia"])) {
            foreach ($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["livreDocencia"] as $livreDocencia) {
                $formacao_livreDocencia[] = '{
                    "sequencia_formacao":"'.$livreDocencia["sequenciaFormacao"].'",
                    "nivel":"'.$livreDocencia["nivel"].'",
                    "codigo_instituicao":"'.$livreDocencia["codigoInstituicao"].'",
                    "nome_instituicao":"'.$livreDocencia["nomeInstituicao"].'",
                    "codigo_curso":"'.$livreDocencia["codigoCurso"].'",
                    "nome_curso":"'.$livreDocencia["nomeCurso"].'",
                    "codigo_area_curso":"'.$livreDocencia["codigoAreaCurso"].'",
                    "status_do_curso":"'.$livreDocencia["statusDoCurso"].'",
                    "ano_de_inicio":"'.$livreDocencia["anoDeInicio"].'",
                    "ano_de_conclusao":"'.$livreDocencia["anoDeConclusao"].'",
                    "ano_de_obtencao_do_titulo":"'.$livreDocencia["anoDeObtencaoDoTitulo"].'",
                    "nome_completo_do_orientador":"'.$livreDocencia["nomeCompletoDoOrientador"].'",
                    "tipo_livreDocencia":"'.$livreDocencia["tipolivreDocencia"].'",
                    "id_do_orientador":"'.$livreDocencia["numeroIdOrientador"].'",
                    "codigo_curso_capes":"'.$livreDocencia["codigoCursoCapes"].'",
                    "nome_curso_capes":"'.$livreDocencia["nomeCursoIngles"].'",
                    "conceito_capes":"'.$livreDocencia["conceitoCapes"].'",
                    "codigo_agencia_financiadora":"'.$livreDocencia["codigoAgenciaFinanciadora"].'",
                    "nome_agencia":"'.$livreDocencia["nomeAgencia"].'",
                    "flag_bolsa":"'.$livreDocencia["flagBolsa"].'"
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



        $atuacao_profissional_array = [];
        if ($cursor["docs"][0]["dadosGerais"]["atuacoesProfissionais"]) {
            foreach ($cursor["docs"][0]["dadosGerais"]["atuacoesProfissionais"]["atuacaoProfissional"] as $atuacao_profissional) {
                
                foreach ($atuacao_profissional["vinculos"] as $vinculos) {
                    
                    $vinculos_base_array = [];
                    if(isset($vinculos["tipoDeVinculo"])) {
                        $citacoes_base_array[] = '"tipo_de_vinculo":"'.$vinculos["tipoDeVinculo"].'"';
                    }                    
                    if(isset($vinculos["enquadramentoFuncional"])) {
                        $citacoes_base_array[] = '"enquadramento_funcional":"'.$vinculos["enquadramentoFuncional"].'"';
                    }  
                    if(isset($vinculos["cargaHorariaSemanal"])) {
                        $citacoes_base_array[] = '"carga_horaria_semanal":"'.$vinculos["cargaHorariaSemanal"].'"';
                    }
                    if(isset($vinculos["flagDedicacaoExclusiva"])) {
                        $citacoes_base_array[] = '"flag_dedicacao_exclusiva":"'.$vinculos["flagDedicacaoExclusiva"].'"';
                    }                     
                    if(isset($vinculos["anoInicio"],$vinculos["mesInicio"])) {
                        $citacoes_base_array[] = '"inicio":"'.$vinculos["anoInicio"].$vinculos["mesInicio"].'"';
                    }
                    if(isset($vinculos["anoFim"],$vinculos["mesFim"])) {
                        $citacoes_base_array[] = '"fim":"'.$vinculos["anoFim"].$vinculos["mesFim"].'"';
                    }
                    if(isset($vinculos["flagVinculoEmpregaticio"])) {
                        $citacoes_base_array[] = '"flag_vinculo_empregaticio":"'.$vinculos["flagVinculoEmpregaticio"].'"';
                    }
                    if(isset($vinculos["outroEnquadramentoFuncionalInformado"])) {
                        $citacoes_base_array[] = '"outro_enquadramento_funcional_informado":"'.$vinculos["outroEnquadramentoFuncionalInformado"].'"';
                    }                     
                    
                    $vinculos_array[] = '{
                        '.implode(",",$citacoes_base_array).'
                    }';
                    
                    unset($citacoes_base_array);
                }
                
                $atuacao_profissional_array[] = '{
                    "codigo_instituicao":"'.$atuacao_profissional["codigoInstituicao"].'",
                    "nome_instituicao":"'.$atuacao_profissional["nomeInstituicao"].'",
                    "vinculos":['.implode(",",$vinculos_array).'] 
                }';
             
                unset($vinculos_array);
            }
        }

	$query_lattes = 
			'{
				"doc":{
                    '.implode(",",$doc_curriculo_array).',					
					'.$resumo_cv.'
					"endereco_profissional":{'.implode(",",$endereco_profissional).'},
                    "citacoes":['.implode(",",$citacoes_array).'],
                    "formacao_academica_titulacao_graduacao":['.implode(",",$formacao_graduacao).'],
                    "formacao_academica_titulacao_mestrado":['.implode(",",$formacao_mestrado).'],
                    "formacao_academica_titulacao_mestradoProfissionalizante":['.implode(",",$formacao_mestradoProfissionalizante).'],
                    "formacao_academica_titulacao_doutorado":['.implode(",",$formacao_doutorado).'],
                    "formacao_academica_titulacao_livreDocencia":['.implode(",",$formacao_livreDocencia).'],
                    "atuacao_profissional":['.implode(",",$atuacao_profissional_array).']
				},
				"doc_as_upsert" : true
			}';
    
    
    $resultado_curriculo = store_curriculo ($client,$_GET["id_lattes"],$query_lattes);
    print_r($resultado_curriculo);

//Parser de Trabalhos-em-Eventos

if (isset($cursor["docs"][0]["producaoBibliografica"]["trabalhosEmEventos"])) {
        
        foreach ($cursor["docs"][0]["producaoBibliografica"]["trabalhosEmEventos"]["trabalhoEmEventos"] as $trab_evento) {
            
        if (isset($trab_evento["dadosBasicosDoTrabalho"]["doi"])) {
            $doi = '"doi": "'.$trab_evento["dadosBasicosDoTrabalho"]["doi"].'",';
        } else {
            $doi = "";
        }
            
            
		foreach ($trab_evento["autores"]  as $autores) {		
			$autores_base_array = [];
            
            if(isset($autores["nomeCompletoDoAutor"])) {
                $autores_base_array[] = '"nome_completo_do_autor":"'.$autores["nomeCompletoDoAutor"].'"';
            }
            if(isset($autores["nomeParaCitacao"])) {
                $autores_base_array[] = '"nome_para_citacao":"'.$autores["nomeParaCitacao"].'"';
            }  
            if(isset($autores["ordemDeAutoria"])) {
                $autores_base_array[] = '"ordem_de_autoria":"'.$autores["ordemDeAutoria"].'"';
            }              
            if(isset($autores["nroIdCnpq"])) {
                $autores_base_array[] = '"nro_id_cnpq":"'.$autores["nroIdCnpq"].'"';
            }  
            
            $autores_array[] = '{ 
                '.implode(",",$autores_base_array).'
            }';
            unset($autores_base_array);
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
                    $area_do_conhecimento_base_array = [];
                    if (isset($area_do_conhecimento["nomeGrandeAreaDoConhecimento"])){
                        $area_do_conhecimento_base_array[] = '"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento["nomeGrandeAreaDoConhecimento"].'"';
                    }
                    if (isset($area_do_conhecimento["nomeDaAreaDoConhecimento"])){
                        $area_do_conhecimento_base_array[] = '"nome_da_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaAreaDoConhecimento"].'"';
                    }
                    if (isset($area_do_conhecimento["nomeDaSubAreaDoConhecimento"])){
                        $area_do_conhecimento_base_array[] = '"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaSubAreaDoConhecimento"].'"';
                    }
                    if (isset($area_do_conhecimento["nomeDaEspecialidade"])){
                        $area_do_conhecimento_base_array[] = '"nome_da_especialidade":"'.$area_do_conhecimento["nomeDaEspecialidade"].'"';
                    }                
					$area_do_conhecimento_array[] = '{
						'.implode(",",$area_do_conhecimento_base_array).'
					}';
                    unset($area_do_conhecimento_base_array);
			}
		} 
            
		$area_set = "";
		if (isset($area_do_conhecimento_array)){
			$area_set = '"area_do_conhecimento":['.implode(",",$area_do_conhecimento_array).'],';
		}
    
//Define variáveis            
        if(!isset($trab_evento["detalhamentoDoTrabalho"]["paginaInicial"])){
            $trab_evento["detalhamentoDoTrabalho"]["paginaInicial"] = "";            
        }
        if(!isset($trab_evento["detalhamentoDoTrabalho"]["paginaFinal"])){
            $trab_evento["detalhamentoDoTrabalho"]["paginaFinal"] = "";            
        }             
        if(!isset($trab_evento["dadosBasicosDoTrabalho"]["doi"])){
            $trab_evento["dadosBasicosDoTrabalho"]["doi"] = "";            
        }
        if(!isset($trab_evento["detalhamentoDoTrabalho"]["isbn"])){
            $trab_evento["detalhamentoDoTrabalho"]["isbn"] = "";            
        }
        if(!isset($trab_evento["dadosBasicosDoTrabalho"]["homePageDoTrabalho"])){
            $trab_evento["dadosBasicosDoTrabalho"]["homePageDoTrabalho"] = "";            
        }
        if(!isset($trab_evento["detalhamentoDoTrabalho"]["nomeDaEditora"])){
            $trab_evento["detalhamentoDoTrabalho"]["nomeDaEditora"] = "";            
        }  
        if(!isset($trab_evento["detalhamentoDoTrabalho"]["cidadeDaEditora"])){
            $trab_evento["detalhamentoDoTrabalho"]["cidadeDaEditora"] = "";            
        }
        if(!isset($trab_evento["detalhamentoDoTrabalho"]["anoDeRealizacao"])){
            $trab_evento["detalhamentoDoTrabalho"]["anoDeRealizacao"] = "";            
        }            
 
// Checar - início            
        if(!isset($trab_evento["detalhamentoDoTrabalho"]["volumeDosAnais"])){
            $trab_evento["detalhamentoDoTrabalho"]["volumeDosAnais"] = "";            
        }              
        if(!isset($trab_evento["detalhamentoDoTrabalho"]["fasciculoDosAnais"])){
            $trab_evento["detalhamentoDoTrabalho"]["fasciculoDosAnais"] = "";            
        }
        if(!isset($trab_evento["detalhamentoDoTrabalho"]["serieDosAnais"])){
            $trab_evento["detalhamentoDoTrabalho"]["serieDosAnais"] = "";            
        }   
// Checar - fim            
            
		$sha256 = hash('sha256', ''.$trab_evento["dadosBasicosDoTrabalho"]["natureza"].$trab_evento["dadosBasicosDoTrabalho"]["tituloDoTrabalho"].$trab_evento["dadosBasicosDoTrabalho"]["anoDoTrabalho"].$trab_evento["dadosBasicosDoTrabalho"]["paisDoEvento"].$trab_evento["detalhamentoDoTrabalho"]["nomeDoEvento"].$trab_evento["detalhamentoDoTrabalho"]["paginaInicial"].$trab_evento["dadosBasicosDoTrabalho"]["homePageDoTrabalho"].$trab_evento["dadosBasicosDoTrabalho"]["doi"].'');
		
		$results =  compararRegistrosLattes($client,$trab_evento["dadosBasicosDoTrabalho"]["anoDoTrabalho"],str_replace('"','',$trab_evento["dadosBasicosDoTrabalho"]["tituloDoTrabalho"]),str_replace('"','',$trab_evento["detalhamentoDoTrabalho"]["nomeDoEvento"]),"TRABALHO-EM-EVENTOS");
		
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
                    "tag": ["'.$_GET["tag"].'"],
					"tipo":"TRABALHO-EM-EVENTOS",
					"natureza": "'.$trab_evento["dadosBasicosDoTrabalho"]["natureza"].'",
					"titulo": "'.str_replace('"','',$trab_evento["dadosBasicosDoTrabalho"]["tituloDoTrabalho"]).'",
					"ano": "'.$trab_evento["dadosBasicosDoTrabalho"]["anoDoTrabalho"].'",
					"pais": "'.$trab_evento["dadosBasicosDoTrabalho"]["paisDoEvento"].'",
					"idioma": "'.$trab_evento["dadosBasicosDoTrabalho"]["idioma"].'",
					"meio_de_divulgacao": "'.$trab_evento["dadosBasicosDoTrabalho"]["meioDeDivulgacao"].'",
					"url": "'.$trab_evento["dadosBasicosDoTrabalho"]["homePageDoTrabalho"].'",
					'.$doi.'					
					"evento":{
						"classificacao_do_evento": "'.$trab_evento["detalhamentoDoTrabalho"]["classificacaoDoEvento"].'",
						"nome_do_evento": "'.str_replace('"','',$trab_evento["detalhamentoDoTrabalho"]["nomeDoEvento"]).'",
						"cidade_do_evento": "'.$trab_evento["detalhamentoDoTrabalho"]["cidadeDoEvento"].'",
						"ano_de_realizacao_do_evento": "'.$trab_evento["detalhamentoDoTrabalho"]["anoDeRealizacao"].'",
						"titulo_dos_anais": "'.str_replace('"','',$trab_evento["detalhamentoDoTrabalho"]["tituloDosAnaisOuProceedings"]).'",
						"volume_dos_anais": "'.$trab_evento["detalhamentoDoTrabalho"]["volumeDosAnais"].'",
						"fasciculo_dos_anais": "'.$trab_evento["detalhamentoDoTrabalho"]["fasciculoDosAnais"].'",
						"serie_dos_anais": "'.$trab_evento["detalhamentoDoTrabalho"]["serieDosAnais"].'",
						"pagina_inicial": "'.$trab_evento["detalhamentoDoTrabalho"]["paginaInicial"].'",
						"pagina_final": "'.$trab_evento["detalhamentoDoTrabalho"]["paginaFinal"].'",
						"isbn": "'.$trab_evento["detalhamentoDoTrabalho"]["isbn"].'",
						"nome_da_editora": "'.str_replace('\\','',$trab_evento["detalhamentoDoTrabalho"]["nomeDaEditora"]).')",
						"cidade_da_editora": "'.$trab_evento["detalhamentoDoTrabalho"]["cidadeDaEditora"].'"
					},
					"palavras_chave":["'.implode('","',$palavras_chave).'"],					
					'.$area_set.'					
					'.$idmatch_set.'
					"autores":['.implode(',',$autores_array).']
					
				},
				"doc_as_upsert" : true
			}';            
            
        $resultado_evento = store_record($client,$sha256,$query_evento);
        print_r($resultado_evento);     

        unset($autor);
        unset($palavras_chave);
        unset($autores_array);
        unset($area_do_conhecimento_array);
        unset($id_match);
        
        flush();    
            
        }

        
        
    }

   if (isset($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"])) {
        
        foreach ($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"]["artigoPublicado"] as $artigo_publicado) {
            
        if (isset($artigo_publicado["dadosBasicosDoArtigo"]["doi"])) {
            $doi = '"doi": "'.$artigo_publicado["dadosBasicosDoArtigo"]["doi"].'",';
        }
            
		foreach ($artigo_publicado["autores"]  as $autores) {		
			$autores_base_array = [];
            
            if(isset($autores["nomeCompletoDoAutor"])) {
                $autores_base_array[] = '"nome_completo_do_autor":"'.$autores["nomeCompletoDoAutor"].'"';
            }
            if(isset($autores["nomeParaCitacao"])) {
                $autores_base_array[] = '"nome_para_citacao":"'.$autores["nomeParaCitacao"].'"';
            }  
            if(isset($autores["ordemDeAutoria"])) {
                $autores_base_array[] = '"ordem_de_autoria":"'.$autores["ordemDeAutoria"].'"';
            }              
            if(isset($autores["nroIdCnpq"])) {
                $autores_base_array[] = '"nro_id_cnpq":"'.$autores["nroIdCnpq"].'"';
            }  
            
            $autores_array[] = '{ 
                '.implode(",",$autores_base_array).'
            }';
            unset($autores_base_array);
		}

		$palavras_chave = [];
		if (isset($artigo_publicado["palavrasChave"])){		
		    if (isset($artigo_publicado["palavrasChave"]["palavraChave1"])){
			$palavras_chave[] = $artigo_publicado["palavrasChave"]["palavraChave1"];
		    }
		    if (isset($artigo_publicado["palavrasChave"]["palavraChave2"])){
			$palavras_chave[] = $artigo_publicado["palavrasChave"]["palavraChave2"];
		    }
		    if (isset($artigo_publicado["palavrasChave"]["palavraChave3"])){
			$palavras_chave[] = $artigo_publicado["palavrasChave"]["palavraChave3"];
		    }
		    if (isset($artigo_publicado["palavrasChave"]["palavraChave4"])){
			$palavras_chave[] = $artigo_publicado["palavrasChave"]["palavraChave4"];
		    }						
		    if (isset($artigo_publicado["palavrasChave"]["palavraChave5"])){
			$palavras_chave[] = $artigo_publicado["palavrasChave"]["palavraChave5"];
		    }
		    if (isset($artigo_publicado["palavrasChave"]["palavraChave6"])){
			$palavras_chave[] = $artigo_publicado["palavrasChave"]["palavraChave6"];
		    }
        }
        
        
		if (isset($artigo_publicado["areasDoConhecimento"])) {
			foreach ($artigo_publicado["areasDoConhecimento"] as $area_do_conhecimento) {
                    $area_do_conhecimento_base_array = [];
                    if (isset($area_do_conhecimento["nomeGrandeAreaDoConhecimento"])){
                        $area_do_conhecimento_base_array[] = '"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento["nomeGrandeAreaDoConhecimento"].'"';
                    }
                    if (isset($area_do_conhecimento["nomeDaAreaDoConhecimento"])){
                        $area_do_conhecimento_base_array[] = '"nome_da_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaAreaDoConhecimento"].'"';
                    }
                    if (isset($area_do_conhecimento["nomeDaSubAreaDoConhecimento"])){
                        $area_do_conhecimento_base_array[] = '"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaSubAreaDoConhecimento"].'"';
                    }
                    if (isset($area_do_conhecimento["nomeDaEspecialidade"])){
                        $area_do_conhecimento_base_array[] = '"nome_da_especialidade":"'.$area_do_conhecimento["nomeDaEspecialidade"].'"';
                    }                
					$area_do_conhecimento_array[] = '{
						'.implode(",",$area_do_conhecimento_base_array).'
					}';
                    unset($area_do_conhecimento_base_array);
			}
		} 
            
		$area_set = "";
		if (isset($area_do_conhecimento_array)){
			$area_set = '"area_do_conhecimento":['.implode(",",$area_do_conhecimento_array).'],';
		}
            
// Define variáveis
        if(!isset($artigo_publicado["dadosBasicosDoArtigo"]["anoDoTrabalho"])){
            $artigo_publicado["dadosBasicosDoArtigo"]["anoDoTrabalho"] = "";            
        }
        if(!isset($artigo_publicado["dadosBasicosDoArtigo"]["homePageDoTrabalho"])){
            $artigo_publicado["dadosBasicosDoArtigo"]["homePageDoTrabalho"] = "";            
        }
        if(!isset($artigo_publicado["detalhamentoDoArtigo"]["fasciculo"])){
            $artigo_publicado["detalhamentoDoArtigo"]["fasciculo"] = "";            
        } 
        if(!isset($artigo_publicado["detalhamentoDoArtigo"]["serie"])){
            $artigo_publicado["detalhamentoDoArtigo"]["serie"] = "";            
        }
        if(!isset($artigo_publicado["detalhamentoDoArtigo"]["paginaInicial"])){
            $artigo_publicado["detalhamentoDoArtigo"]["paginaInicial"] = "";            
        } 
        if(!isset($artigo_publicado["detalhamentoDoArtigo"]["paginaFinal"])){
            $artigo_publicado["detalhamentoDoArtigo"]["paginaFinal"] = "";            
        }
        if(!isset($artigo_publicado["detalhamentoDoArtigo"]["localDePublicacao"])){
            $artigo_publicado["detalhamentoDoArtigo"]["localDePublicacao"] = "";            
        }
        if(!isset($artigo_publicado["dadosBasicosDoArtigo"]["doi"])){
            $artigo_publicado["dadosBasicosDoArtigo"]["doi"] = "";            
        }             
//Define variáveis - fim            
            
		$sha256 = hash('sha256', ''.$artigo_publicado["dadosBasicosDoArtigo"]["natureza"].$artigo_publicado["dadosBasicosDoArtigo"]["tituloDoArtigo"].$artigo_publicado["dadosBasicosDoArtigo"]["anoDoTrabalho"].$artigo_publicado["detalhamentoDoArtigo"]["tituloDoPeriodicoOuRevista"].$artigo_publicado["detalhamentoDoArtigo"]["paginaInicial"].$artigo_publicado["dadosBasicosDoArtigo"]["homePageDoTrabalho"].$artigo_publicado["dadosBasicosDoArtigo"]["doi"].'');
		
		$results =  compararRegistrosLattes($client,$artigo_publicado["dadosBasicosDoArtigo"]["anoDoTrabalho"],str_replace('"','',$artigo_publicado["dadosBasicosDoArtigo"]["tituloDoArtigo"]),str_replace('"','',$artigo_publicado["detalhamentoDoArtigo"]["tituloDoPeriodicoOuRevista"]),"TRABALHO-EM-EVENTOS");
		
		foreach ($results["hits"]["hits"] as $result) {			
			$id_match[] = '{"id_match":"'.$result["_id"].'","nota":"'.$result["_score"].'"}';
		}            
            
		$idmatch_set = "";
		if (isset($id_match)){
			$idmatch_set = '"ids_match":['.implode(",",$id_match).'],';
		}            
            
        $query_artigo = 
			'{
				"doc":{
                    "source":"Base Lattes", 
					"id_lattes": ["'.$_GET["id_lattes"].'"],
                    "tag": ["'.$_GET["tag"].'"],
					"tipo":"ARTIGO-PUBLICADO",
					"natureza": "'.$artigo_publicado["dadosBasicosDoArtigo"]["natureza"].'",
					"titulo": "'.str_replace('"','',$artigo_publicado["dadosBasicosDoArtigo"]["tituloDoArtigo"]).'",
					"ano": "'.$artigo_publicado["dadosBasicosDoArtigo"]["anoDoArtigo"].'",
					"idioma": "'.$artigo_publicado["dadosBasicosDoArtigo"]["idioma"].'",
					"meio_de_divulgacao": "'.$artigo_publicado["dadosBasicosDoArtigo"]["meioDeDivulgacao"].'",
					"url": "'.$artigo_publicado["dadosBasicosDoArtigo"]["homePageDoTrabalho"].'",
					'.$doi.'
					"periodico":{
						"titulo_do_periodico":"'.str_replace('"','',$artigo_publicado["detalhamentoDoArtigo"]["tituloDoPeriodicoOuRevista"]).'",
						"issn":"'.$artigo_publicado["detalhamentoDoArtigo"]["issn"].'",
						"volume":"'.$artigo_publicado["detalhamentoDoArtigo"]["volume"].'",
						"fasciculo":"'.$artigo_publicado["detalhamentoDoArtigo"]["fasciculo"].'",
						"serie":"'.$artigo_publicado["detalhamentoDoArtigo"]["serie"].'",
						"pagina_inicial":"'.$artigo_publicado["detalhamentoDoArtigo"]["paginaInicial"].'",
						"pagina_final":"'.$artigo_publicado["detalhamentoDoArtigo"]["paginaFinal"].'",
						"local_de_publicacao":"'.$artigo_publicado["detalhamentoDoArtigo"]["localDePublicacao"].'"
					},
					"palavras_chave":["'.implode('","',$palavras_chave).'"],					
					'.$area_set.'					
					'.$idmatch_set.'
					"autores":['.implode(',',$autores_array).']
					
				},
				"doc_as_upsert" : true
			}';            
            
        $resultado_artigo = store_record($client,$sha256,$query_artigo);
        print_r($resultado_artigo);    
            
        unset($autor);
        unset($palavras_chave);
        unset($autores_array);
        unset($area_do_conhecimento_array);
        unset($id_match);  
            
        flush();    
            
        }
       
 
        
        
    }

    if (isset($cursor["docs"][0]["producaoBibliografica"]["livrosECapitulos"])) {

        if (isset($cursor["docs"][0]["producaoBibliografica"]["livrosECapitulos"]["livrosPublicadosOuOrganizados"])) {
            foreach ($cursor["docs"][0]["producaoBibliografica"]["livrosECapitulos"]["livrosPublicadosOuOrganizados"]["livroPublicadoOuOrganizado"] as $livro_publicado) {
                                        
                if (isset($livro_publicado["dadosBasicosDoLivro"]["doi"])) {
                    $doi = '"doi": "'.$livro_publicado["dadosBasicosDoLivro"]["doi"].'",';
                } else {
                    $doi = "";
                }

                foreach ($livro_publicado["autores"]  as $autores) {		
                    $autores_base_array = [];

                    if(isset($autores["nomeCompletoDoAutor"])) {
                        $autores_base_array[] = '"nome_completo_do_autor":"'.$autores["nomeCompletoDoAutor"].'"';
                    }
                    if(isset($autores["nomeParaCitacao"])) {
                        $autores_base_array[] = '"nome_para_citacao":"'.$autores["nomeParaCitacao"].'"';
                    }  
                    if(isset($autores["ordemDeAutoria"])) {
                        $autores_base_array[] = '"ordem_de_autoria":"'.$autores["ordemDeAutoria"].'"';
                    }              
                    if(isset($autores["nroIdCnpq"])) {
                        $autores_base_array[] = '"nro_id_cnpq":"'.$autores["nroIdCnpq"].'"';
                    }  

                    $autores_array[] = '{ 
                        '.implode(",",$autores_base_array).'
                    }';
                    unset($autores_base_array);
                }

                $palavras_chave = [];
                if (isset($livro_publicado["palavrasChave"])){		
                    if (isset($livro_publicado["palavrasChave"]["palavraChave1"])){
                    $palavras_chave[] = $livro_publicado["palavrasChave"]["palavraChave1"];
                    }
                    if (isset($livro_publicado["palavrasChave"]["palavraChave2"])){
                    $palavras_chave[] = $livro_publicado["palavrasChave"]["palavraChave2"];
                    }
                    if (isset($livro_publicado["palavrasChave"]["palavraChave3"])){
                    $palavras_chave[] = $livro_publicado["palavrasChave"]["palavraChave3"];
                    }
                    if (isset($livro_publicado["palavrasChave"]["palavraChave4"])){
                    $palavras_chave[] = $livro_publicado["palavrasChave"]["palavraChave4"];
                    }						
                    if (isset($livro_publicado["palavrasChave"]["palavraChave5"])){
                    $palavras_chave[] = $livro_publicado["palavrasChave"]["palavraChave5"];
                    }
                    if (isset($livro_publicado["palavrasChave"]["palavraChave6"])){
                    $palavras_chave[] = $livro_publicado["palavrasChave"]["palavraChave6"];
                    }
                }
        
        
                if (isset($livro_publicado["areasDoConhecimento"])) {
                    foreach ($livro_publicado["areasDoConhecimento"] as $area_do_conhecimento) {
                            $area_do_conhecimento_base_array = [];
                            if (isset($area_do_conhecimento["nomeGrandeAreaDoConhecimento"])){
                                $area_do_conhecimento_base_array[] = '"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento["nomeGrandeAreaDoConhecimento"].'"';
                            }
                            if (isset($area_do_conhecimento["nomeDaAreaDoConhecimento"])){
                                $area_do_conhecimento_base_array[] = '"nome_da_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaAreaDoConhecimento"].'"';
                            }
                            if (isset($area_do_conhecimento["nomeDaSubAreaDoConhecimento"])){
                                $area_do_conhecimento_base_array[] = '"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaSubAreaDoConhecimento"].'"';
                            }
                            if (isset($area_do_conhecimento["nomeDaEspecialidade"])){
                                $area_do_conhecimento_base_array[] = '"nome_da_especialidade":"'.$area_do_conhecimento["nomeDaEspecialidade"].'"';
                            }                
                            $area_do_conhecimento_array[] = '{
                                '.implode(",",$area_do_conhecimento_base_array).'
                            }';
                            unset($area_do_conhecimento_base_array);
                    }
                }  
            
                $area_set = "";
                if (isset($area_do_conhecimento_array)){
                    $area_set = '"area_do_conhecimento":['.implode(",",$area_do_conhecimento_array).'],';
                }
            
// Define variáveis
        if(!isset($livro_publicado["detalhamentoDoLivro"]["numeroDaEdicaoRevisao"])){
            $livro_publicado["detalhamentoDoLivro"]["numeroDaEdicaoRevisao"] = "";            
        }           
//Define variáveis - fim   
                
                $sha256 = hash('sha256', ''.$livro_publicado["dadosBasicosDoLivro"]["natureza"].$livro_publicado["dadosBasicosDoLivro"]["tituloDoLivro"].$livro_publicado["detalhamentoDoLivro"]["isbn"].'');              
                $results =  compararRegistrosLattesLivros($client,str_replace('"','',$livro_publicado["dadosBasicosDoLivro"]["tituloDoLivro"]),str_replace('"','',$livro_publicado["detalhamentoDoLivro"]["isbn"]),"LIVRO-PUBLICADO");
		
                foreach ($results["hits"]["hits"] as $result) {			
                    $id_match[] = '{"id_match":"'.$result["_id"].'","nota":"'.$result["_score"].'"}';
                }            
            
                $idmatch_set = "";
                if (isset($id_match)){
                    $idmatch_set = '"ids_match":['.implode(",",$id_match).'],';
                }
                
                $query_livro = 
                    '{
                        "doc":{
                            "source":"Base Lattes", 
                            "id_lattes": ["'.$_GET["id_lattes"].'"],
                            "tag": ["'.$_GET["tag"].'"],
                            "tipo":"LIVRO-PUBLICADO",
                            "natureza": "'.$livro_publicado["dadosBasicosDoLivro"]["natureza"].'",
                            "titulo": "'.str_replace('"','',$livro_publicado["dadosBasicosDoLivro"]["tituloDoLivro"]).'",
                            "ano": "'.$livro_publicado["dadosBasicosDoLivro"]["ano"].'",
                            "pais": "'.$livro_publicado["dadosBasicosDoLivro"]["paisDePublicacao"].'",
                            "idioma": "'.$livro_publicado["dadosBasicosDoLivro"]["idioma"].'",
                            "meio_de_divulgacao": "'.$livro_publicado["dadosBasicosDoLivro"]["meioDeDivulgacao"].'",
                            "isbn":"'.$livro_publicado["detalhamentoDoLivro"]["isbn"].'",
                            '.$doi.'
                            "livro":{
                                "numero_de_paginas":"'.$livro_publicado["detalhamentoDoLivro"]["numeroDePaginas"].'",						
                                "numero_da_edicao_revisao":"'.$livro_publicado["detalhamentoDoLivro"]["numeroDaEdicaoRevisao"].'",
                                "cidade_da_editora":"'.$livro_publicado["detalhamentoDoLivro"]["cidadeDaEditora"].'",
                                "nome_da_editora":"'.$livro_publicado["detalhamentoDoLivro"]["nomeDaEditora"].'"
                            },
                            "palavras_chave":["'.implode('","',$palavras_chave).'"],					
                            '.$area_set.'					
                            '.$idmatch_set.'
                            "autores":['.implode(',',$autores_array).']

                        },
                        "doc_as_upsert" : true
                    }';
                
            
                $resultado_livro = store_record($client,$sha256,$query_livro);
                print_r($resultado_livro);    
            
                unset($autor);
                unset($palavras_chave);
                unset($autores_array);
                unset($area_do_conhecimento_array);
                unset($id_match);  

                flush();                 
                
            }
        }

        if (isset($cursor["docs"][0]["producaoBibliografica"]["livrosECapitulos"]["capitulosDeLivrosPublicados"])) {
            foreach ($cursor["docs"][0]["producaoBibliografica"]["livrosECapitulos"]["capitulosDeLivrosPublicados"]["capituloDeLivroPublicado"] as $capitulo_publicado) { 

                if (isset($capitulo_publicado["dadosBasicosDoCapitulo"]["doi"])) {
                    $doi = '"doi": "'.$capitulo_publicado["dadosBasicosDoCapitulo"]["doi"].'",';
                } else {
                    $doi = "";
                }

                foreach ($capitulo_publicado["autores"]  as $autores) {		
                    $autores_base_array = [];

                    if(isset($autores["nomeCompletoDoAutor"])) {
                        $autores_base_array[] = '"nome_completo_do_autor":"'.$autores["nomeCompletoDoAutor"].'"';
                    }
                    if(isset($autores["nomeParaCitacao"])) {
                        $autores_base_array[] = '"nome_para_citacao":"'.$autores["nomeParaCitacao"].'"';
                    }  
                    if(isset($autores["ordemDeAutoria"])) {
                        $autores_base_array[] = '"ordem_de_autoria":"'.$autores["ordemDeAutoria"].'"';
                    }              
                    if(isset($autores["nroIdCnpq"])) {
                        $autores_base_array[] = '"nro_id_cnpq":"'.$autores["nroIdCnpq"].'"';
                    }  

                    $autores_array[] = '{ 
                        '.implode(",",$autores_base_array).'
                    }';
                    unset($autores_base_array);
                }

                $palavras_chave = [];
                if (isset($capitulo_publicado["palavrasChave"])){		
                    if (isset($capitulo_publicado["palavrasChave"]["palavraChave1"])){
                    $palavras_chave[] = $capitulo_publicado["palavrasChave"]["palavraChave1"];
                    }
                    if (isset($capitulo_publicado["palavrasChave"]["palavraChave2"])){
                    $palavras_chave[] = $capitulo_publicado["palavrasChave"]["palavraChave2"];
                    }
                    if (isset($capitulo_publicado["palavrasChave"]["palavraChave3"])){
                    $palavras_chave[] = $capitulo_publicado["palavrasChave"]["palavraChave3"];
                    }
                    if (isset($capitulo_publicado["palavrasChave"]["palavraChave4"])){
                    $palavras_chave[] = $capitulo_publicado["palavrasChave"]["palavraChave4"];
                    }						
                    if (isset($capitulo_publicado["palavrasChave"]["palavraChave5"])){
                    $palavras_chave[] = $capitulo_publicado["palavrasChave"]["palavraChave5"];
                    }
                    if (isset($capitulo_publicado["palavrasChave"]["palavraChave6"])){
                    $palavras_chave[] = $capitulo_publicado["palavrasChave"]["palavraChave6"];
                    }
                }
        
        
                if (isset($capitulo_publicado["areasDoConhecimento"])) {
                    foreach ($capitulo_publicado["areasDoConhecimento"] as $area_do_conhecimento) {
                            $area_do_conhecimento_base_array = [];
                            if (isset($area_do_conhecimento["nomeGrandeAreaDoConhecimento"])){
                                $area_do_conhecimento_base_array[] = '"nome_grande_area_do_conhecimento":"'.$area_do_conhecimento["nomeGrandeAreaDoConhecimento"].'"';
                            }
                            if (isset($area_do_conhecimento["nomeDaAreaDoConhecimento"])){
                                $area_do_conhecimento_base_array[] = '"nome_da_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaAreaDoConhecimento"].'"';
                            }
                            if (isset($area_do_conhecimento["nomeDaSubAreaDoConhecimento"])){
                                $area_do_conhecimento_base_array[] = '"nome_da_sub_area_do_conhecimento":"'.$area_do_conhecimento["nomeDaSubAreaDoConhecimento"].'"';
                            }
                            if (isset($area_do_conhecimento["nomeDaEspecialidade"])){
                                $area_do_conhecimento_base_array[] = '"nome_da_especialidade":"'.$area_do_conhecimento["nomeDaEspecialidade"].'"';
                            }                
                            $area_do_conhecimento_array[] = '{
                                '.implode(",",$area_do_conhecimento_base_array).'
                            }';
                            unset($area_do_conhecimento_base_array);
                    }
                } 
            
                $area_set = "";
                if (isset($area_do_conhecimento_array)){
                    $area_set = '"area_do_conhecimento":['.implode(",",$area_do_conhecimento_array).'],';
                }

// Define variáveis
        if(!isset($capitulo_publicado["dadosBasicosDoCapitulo"]["natureza"])){
            $capitulo_publicado["dadosBasicosDoCapitulo"]["natureza"] = "";            
        }
        if(!isset($capitulo_publicado["dadosBasicosDoCapitulo"]["homePageDoTrabalho"])){
            $capitulo_publicado["dadosBasicosDoCapitulo"]["homePageDoTrabalho"] = "";            
        }
        if(!isset($capitulo_publicado["detalhamentoDoCapitulo"]["numeroDeVolumes"])){
            $capitulo_publicado["detalhamentoDoCapitulo"]["numeroDeVolumes"] = "";            
        }
        if(!isset($capitulo_publicado["detalhamentoDoCapitulo"]["numeroDaEdicaoRevisao"])){
            $capitulo_publicado["detalhamentoDoCapitulo"]["numeroDaEdicaoRevisao"] = "";            
        }                 
//Define variáveis - fim                   
            
		        $sha256 = hash('sha256', ''.$capitulo_publicado["dadosBasicosDoCapitulo"]["natureza"].$capitulo_publicado["dadosBasicosDoCapitulo"]["tituloDoCapituloDoLivro"].$capitulo_publicado["detalhamentoDoCapitulo"]["isbn"].'');                
		
                $results =  compararRegistrosLattesCapitulos($client,str_replace('"','',$capitulo_publicado["dadosBasicosDoCapitulo"]["tituloDoCapituloDoLivro"]),str_replace('"','',$capitulo_publicado["detalhamentoDoCapitulo"]["tituloDoLivro"]),"CAPITULO-DE-LIVRO");
		
                foreach ($results["hits"]["hits"] as $result) {			
                    $id_match[] = '{"id_match":"'.$result["_id"].'","nota":"'.$result["_score"].'"}';
                }            
            
                $idmatch_set = "";
                if (isset($id_match)){
                    $idmatch_set = '"ids_match":['.implode(",",$id_match).'],';
                }
                
                $query_capitulo = 
                    '{
                        "doc":{
                            "source":"Base Lattes", 
                            "id_lattes": ["'.$_GET["id_lattes"].'"],
                            "tag": ["'.$_GET["tag"].'"],
                            "tipo":"CAPITULO-DE-LIVRO",
                            "natureza": "'.$capitulo_publicado["dadosBasicosDoCapitulo"]["natureza"].'",
                            "titulo": "'.str_replace('"','',$capitulo_publicado["dadosBasicosDoCapitulo"]["tituloDoCapituloDoLivro"]).'",
                            "ano": "'.$capitulo_publicado["dadosBasicosDoCapitulo"]["ano"].'",
                            "pais": "'.$capitulo_publicado["dadosBasicosDoCapitulo"]["paisDePublicacao"].'",
                            "idioma": "'.$capitulo_publicado["dadosBasicosDoCapitulo"]["idioma"].'",
                            "meio_de_divulgacao": "'.$capitulo_publicado["dadosBasicosDoCapitulo"]["meioDeDivulgacao"].'",                            
                            "url": "'.$capitulo_publicado["dadosBasicosDoCapitulo"]["homePageDoTrabalho"].'",
                            '.$doi.'
                            "capitulo_do_livro":{
                                "titulo_do_livro":"'.$capitulo_publicado["detalhamentoDoCapitulo"]["tituloDoLivro"].'",
                                "numero_de_volumes":"'.$capitulo_publicado["detalhamentoDoCapitulo"]["numeroDeVolumes"].'",
                                "pagina_inicial":"'.$capitulo_publicado["detalhamentoDoCapitulo"]["paginaInicial"].'",
                                "pagina_final":"'.$capitulo_publicado["detalhamentoDoCapitulo"]["paginaFinal"].'",
                                "isbn":"'.$capitulo_publicado["detalhamentoDoCapitulo"]["isbn"].'",                                
                                "numero_da_edicao_revisao":"'.$capitulo_publicado["detalhamentoDoCapitulo"]["numeroDaEdicaoRevisao"].'",
                                "cidade_da_editora":"'.$capitulo_publicado["detalhamentoDoCapitulo"]["cidadeDaEditora"].'",
                                "nome_da_editora":"'.$capitulo_publicado["detalhamentoDoCapitulo"]["nomeDaEditora"].'"
                            },
                            "palavras_chave":["'.implode('","',$palavras_chave).'"],					
                            '.$area_set.'					
                            '.$idmatch_set.'
                            "autores":['.implode(',',$autores_array).']

                        },
                        "doc_as_upsert" : true
                    }';
                
 
            
                $resultado_capitulo = store_record($client,$sha256,$query_capitulo);
                print_r($resultado_capitulo);    
            
                unset($autor);
                unset($palavras_chave);
                unset($autores_array);
                unset($area_do_conhecimento_array);
                unset($id_match);  

                flush();                 
                                
            }
        }            
        
        
} 
    
if (isset($cursor["docs"][0]["producaoTecnica"]["demaisTiposDeProducaoTecnica"]["midiaSocialWebsiteBlog"])) {
    foreach ($cursor["docs"][0]["producaoTecnica"]["demaisTiposDeProducaoTecnica"]["midiaSocialWebsiteBlog"] as $midiasocialwebsiteblog) { 

            foreach ($midiasocialwebsiteblog["autores"]  as $autores) {

                $autores_array[] = '{ "nome_completo_do_autor":"'.$autores["nomeCompletoDoAutor"].'", "nome_para_citacao":"'.$autores["nomeParaCitacao"].'", "ordem_de_autoria":"'.$autores["ordemDeAutoria"].'", "nro_id_cnpq":"'.$autores["nroIdCnpq"].'" }';

            }

            $palavras_chave = [];
            if (isset($midiasocialwebsiteblog["palavrasChave"])){		
                if (isset($midiasocialwebsiteblog["palavrasChave"]["palavraChave1"])){
                $palavras_chave[] = $midiasocialwebsiteblog["palavrasChave"]["palavraChave1"];
                }
                if (isset($artigo_publicado["palavrasChave"]["palavraChave2"])){
                $palavras_chave[] = $midiasocialwebsiteblog["palavrasChave"]["palavraChave2"];
                }
                if (isset($artigo_publicado["palavrasChave"]["palavraChave3"])){
                $palavras_chave[] = $midiasocialwebsiteblog["palavrasChave"]["palavraChave3"];
                }
                if (isset($artigo_publicado["palavrasChave"]["palavraChave4"])){
                $palavras_chave[] = $midiasocialwebsiteblog["palavrasChave"]["palavraChave4"];
                }						
                if (isset($artigo_publicado["palavrasChave"]["palavraChave5"])){
                $palavras_chave[] = $midiasocialwebsiteblog["palavrasChave"]["palavraChave5"];
                }
                if (isset($artigo_publicado["palavrasChave"]["palavraChave6"])){
                $palavras_chave[] = $midiasocialwebsiteblog["palavrasChave"]["palavraChave6"];
                }
            }


            if (isset($midiasocialwebsiteblog["areasDoConhecimento"])) {
                foreach ($midiasocialwebsiteblog["areasDoConhecimento"] as $area_do_conhecimento) {
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

            $sha256 = hash('sha256', ''.$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["natureza"].$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["titulo"].$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["homePage"].'');                

            $results =  compararRegistrosLattesMidiaSocial($client,str_replace('"','',$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["titulo"]),$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["homePage"],"MIDIA-SOCIAL-OU-BLOG");

            foreach ($results["hits"]["hits"] as $result) {			
                $id_match[] = '{"id_match":"'.$result["_id"].'","nota":"'.$result["_score"].'"}';
            }            

            $idmatch_set = "";
            if (isset($id_match)){
                $idmatch_set = '"ids_match":['.implode(",",$id_match).'],';
            }        
        
            $query_midiasocial = 
                '{
                    "doc":{
                        "source":"Base Lattes", 
                        "id_lattes": ["'.$_GET["id_lattes"].'"],
                        "tag": ["'.$_GET["tag"].'"],
                        "tipo":"MIDIA-SOCIAL-OU-BLOG",
                        "natureza": "'.$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["natureza"].'",
                        "titulo": "'.str_replace('"','',$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["titulo"]).'",
                        "ano": "'.$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["ano"].'",
                        "pais": "'.$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["paisDePublicacao"].'",
                        "idioma": "'.$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["idioma"].'",
                        "url": "'.$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["homePage"].'",
                        "midia_social_ou_blog":{
                            "tema":"'.$midiasocialwebsiteblog["dadosBasicosDaMidiaSocialWebsiteBlog"]["homePage"].'"
                        },
                        "palavras_chave":["'.implode('","',$palavras_chave).'"],					
                        '.$area_set.'					
                        '.$idmatch_set.'
                        "autores":['.implode(',',$autores_array).']

                    },
                    "doc_as_upsert" : true
                }';

            //print_r($query_midiasocial);    

            $resultado_midiasocial = store_record($client,$sha256,$query_midiasocial);
            print_r($resultado_midiasocial);    

            unset($autor);
            unset($palavras_chave);
            unset($autores_array);
            unset($area_do_conhecimento_array);
            unset($id_match);  

            flush();         
        
    }
}
        
?>
            </div>
                
            <?php include('inc/footer.php'); ?>
        </div>
        
        
        <?php include('inc/offcanvas.php'); ?>
        
    </body>
</html>

<?php sleep(5); echo '<script>window.location = \'http://bdpife2.sibi.usp.br/coletaprod/result_trabalhos.php?search[]=id_lattes.keyword:"'.$_GET["id_lattes"].'"\'</script>'; ?>