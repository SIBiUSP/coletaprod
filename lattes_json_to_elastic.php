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
        <title>Conversor do arquivo JSON da Plataforma Lattes para o ElasticSearch - Coleta Produção USP</title>
        
    </head>

    <body>     
        
        <?php include('inc/navbar.php'); ?>
        
        <div class="uk-container uk-container-center uk-margin-large-bottom">
            <div class="uk-width-medium-1-1">

<?php 

                
    if (isset($_GET["id_lattes"])) {
        $cursor = coleta_json_lattes($_GET["id_lattes"]);
    } elseif (isset($_GET["path_download"])) {
        $cursor = coleta_json_download_lattes($_GET["path_download"]);
    } else {
        echo '<p>Não foi informado nenhum ID</p>';
    }            
    
// Armazenar currículo Lattes            
                
    $doc_curriculo_array = [];
    $doc_curriculo_array["doc"]["source"] = "Base Lattes";
    $doc_curriculo_array["doc"]["tag"] = $_GET['tag'];
    $doc_curriculo_array["doc"]["data_atualizacao"] = substr($cursor["docs"][0]["dataAtualizacao"],4,4)."-".substr($cursor["docs"][0]["dataAtualizacao"],2,2);
    $doc_curriculo_array["doc"]["nome_completo"] = $cursor["docs"][0]["dadosGerais"]["nomeCompleto"];
    $doc_curriculo_array["doc"]["nome_em_citacoes_bibliograficas"] = $cursor["docs"][0]["dadosGerais"]["nomeEmCitacoesBibliograficas"];
    if (isset($cursor["docs"][0]["dadosGerais"]["nacionalidade"])){
        $doc_curriculo_array["doc"]["nacionalidade"] = $cursor["docs"][0]["dadosGerais"]["nacionalidade"];
    }
    if (isset($cursor["docs"][0]["dadosGerais"]["paisDeNascimento"])){
        $doc_curriculo_array["doc"]["pais_de_nascimento"] = $cursor["docs"][0]["dadosGerais"]["paisDeNascimento"];
    }
    if (isset($cursor["docs"][0]["dadosGerais"]["siglaPaisNacionalidade"])){
        $doc_curriculo_array["doc"]["sigla_pais_nacionalidade"] = $cursor["docs"][0]["dadosGerais"]["siglaPaisNacionalidade"];
    }
    if (isset($cursor["docs"][0]["dadosGerais"]["paisDeNacionalidade"])){
        $doc_curriculo_array["doc"]["pais_de_nacionalidade"] = $cursor["docs"][0]["dadosGerais"]["paisDeNacionalidade"];
    }                 
    if (isset($cursor["docs"][0]["dadosGerais"]["resumoCv"])) {
        $doc_curriculo_array["doc"]["resumo_cv"]["texto_resumo_cv_rh"] = str_replace('"','\"',$cursor["docs"][0]["dadosGerais"]["resumoCv"]["textoResumoCvRh"]);
        if (isset($cursor["docs"][0]["dadosGerais"]["resumoCv"]["textoResumoCvRhEn"])) {
            $doc_curriculo_array["doc"]["resumo_cv"]["texto_resumo_cv_rh_en"] = str_replace('"','\"',$cursor["docs"][0]["dadosGerais"]["resumoCv"]["textoResumoCvRhEn"]);
        }
    }
    
    // Endereço profissional atual            
    if (isset($cursor["docs"][0]["dadosGerais"]["endereco"])) {
        $doc_curriculo_array["doc"]["endereco"]["flagDePreferencia"] = $cursor["docs"][0]["dadosGerais"]["endereco"]["flagDePreferencia"];
        if (isset($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"])) {
            foreach (["codigoInstituicaoEmpresa","nomeInstituicaoEmpresa","codigoOrgao","nomeOrgao","codigoUnidade","nomeUnidade","logradouroComplemento","pais","uf","cep","cidade","bairro","home_page"] as $endprof_campos) {
                if (!empty($cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"][$endprof_campos])) {
                    $doc_curriculo_array["doc"]["endereco"]["endereco_profissional"][$endprof_campos] = $cursor["docs"][0]["dadosGerais"]["endereco"]["enderecoProfissional"][$endprof_campos];                
                }                    
            }
        }
    }              
 
    // Quadro de citações            
    if (isset($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"]["totalQuadroCitacoes"])) {
        $i = 0;
        foreach ($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"]["totalQuadroCitacoes"] as $citacoes) {
            foreach (["nomeBase","codigoBase","sequencialIndicador","numeroCitacoes","dataCitacao","textoArgumento","indiceH","numeroTrabalhos","uriPesquisadorBase","uriLogoBase"] as $citacoes_campos) {
                if (isset ($citacoes[$citacoes_campos])) {
                    $doc_curriculo_array["doc"]["citacoes"][$citacoes["nomeBase"]][$citacoes_campos] = $citacoes[$citacoes_campos];                   
                }                    
            }
            foreach (["uriPesquisadorBase"] as $identificador_pesquisador) {
                if (!empty($citacoes[$identificador_pesquisador])) {
                    $doc_curriculo_array["doc"]["uri_pesquisador"][] = $citacoes[$identificador_pesquisador];
                }
            }             
            $i++;
        }
    }           
                
    // Formação Acadêmica Titulação            
    if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"])) {
        
        
        // Graduação
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["graduacao"])){
            $graduacao_campos = ["sequencia_formacao","nivel","tituloDoTrabalhoDeConclusaoDeCurso","nomeDoOrientador","codigo_instituicao","nomeInstituicao","codigoCurso","nomeCurso","codigoAreaCurso","statusDoCurso","anoDeInicio","anoDeConclusao","flagBolsa"];
            $array_result = processaFormacaoAcaddemica($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["graduacao"],"graduacao",$graduacao_campos);
            $doc_curriculo_array = array_merge_recursive($doc_curriculo_array,$array_result);
        }
        
        // Mestrado
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["mestrado"])){
            $mestrado_campos = ["sequenciaFormacao","nivel","codigoInstituicao","nomeInstituicao","codigoCurso","nomeCurso","codigoAreaCurso","statusDoCurso","anoDeInicio","anoDeConclusao","anoDeObtencaoDoTitulo","tituloDaDissertacaoTese","nomeCompletoDoOrientador","tipoMestrado","numeroIdOrientador","codigoCursoCapes","nomeCursoIngles","conceitoCapes","codigoAgenciaFinanciadora","nomeAgencia","flagBolsa"];
            $array_result = processaFormacaoAcaddemica($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["mestrado"],"mestrado",$mestrado_campos);
            $doc_curriculo_array = array_merge_recursive($doc_curriculo_array,$array_result);
        } 
        
        // Mestrado Profissional
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["mestradoProfissionalizante"])){
            $mestradoProfissionalizante_campos = ["sequenciaFormacao","nivel","codigoInstituicao","nomeInstituicao","codigoCurso","nomeCurso","codigoAreaCurso","statusDoCurso","anoDeInicio","anoDeConclusao","anoDeObtencaoDoTitulo","nomeCompletoDoOrientador","tituloDaDissertacaoTese","numeroIdOrientador","codigoCursoCapes","nomeCursoIngles","conceitoCapes","codigoAgenciaFinanciadora","nomeAgencia","flagBolsa"];
            $array_result = processaFormacaoAcaddemica($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["mestradoProfissionalizante"],"mestradoProfissionalizante",$mestradoProfissionalizante_campos);
            $doc_curriculo_array = array_merge_recursive($doc_curriculo_array,$array_result);
        }
        
        // Doutorado
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["doutorado"])){
            $doutorado_campos = ["sequenciaFormacao","nivel","codigoInstituicao","nomeInstituicao","codigoCurso","nomeCurso","codigoAreaCurso","statusDoCurso","anoDeInicio","anoDeConclusao","anoDeObtencaoDoTitulo","nomeCompletoDoOrientador","tituloDaDissertacaoTese","tipoDoutorado","numeroIdOrientador","codigoCursoCapes","nomeCursoIngles","conceitoCapes","codigoAgenciaFinanciadora","nomeAgencia","flagBolsa"];
            $array_result = processaFormacaoAcaddemica($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["doutorado"],"doutorado",$doutorado_campos);
            $doc_curriculo_array = array_merge_recursive($doc_curriculo_array,$array_result);
        }

        // Pós Doutorado
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["posDoutorado"])){
            $posDoutorado_campos = ["sequenciaFormacao","nivel","codigoInstituicao","nomeInstituicao","codigoCurso","nomeCurso","codigoAreaCurso","statusDoCurso","anoDeInicio","anoDeConclusao","anoDeObtencaoDoTitulo","nomeCompletoDoOrientador","tituloDaDissertacaoTese","tipoDoutorado","numeroIdOrientador","codigoCursoCapes","nomeCursoIngles","conceitoCapes","codigoAgenciaFinanciadora","nomeAgencia","flagBolsa"];
            $array_result = processaFormacaoAcaddemica($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["posDoutorado"],"posDoutorado",$posDoutorado_campos);
            $doc_curriculo_array = array_merge_recursive($doc_curriculo_array,$array_result);
        }        
        
        // Livre docência
        if (isset($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["livreDocencia"])){
            $livreDocencia_campos = ["sequenciaFormacao","nivel","codigoInstituicao","nomeInstituicao","codigoCurso","nomeCurso","codigoAreaCurso","statusDoCurso","anoDeInicio","anoDeConclusao","anoDeObtencaoDoTitulo","tituloDoTrabalho","nomeCompletoDoOrientador","tipolivreDocencia","numeroIdOrientador","codigoCursoCapes","nomeCursoIngles","conceitoCapes","codigoAgenciaFinanciadora","nomeAgencia","flagBolsa"];
            $array_result = processaFormacaoAcaddemica($cursor["docs"][0]["dadosGerais"]["formacaoAcademicaTitulacao"]["livreDocencia"],"livreDocencia",$livreDocencia_campos);
            $doc_curriculo_array = array_merge_recursive($doc_curriculo_array,$array_result);
        }          

        
    }         
                
    // Vinculos profissionais
    
    if ($cursor["docs"][0]["dadosGerais"]["atuacoesProfissionais"]) {
        $i = 0;
        foreach ($cursor["docs"][0]["dadosGerais"]["atuacoesProfissionais"]["atuacaoProfissional"] as $atuacao_profissional) {
            $doc_curriculo_array["doc"]["atuacao_profissional"][$i]["codigoInstituicao"] = $atuacao_profissional["codigoInstituicao"];
            $doc_curriculo_array["doc"]["atuacao_profissional"][$i]["nomeInstituicao"] = $atuacao_profissional["nomeInstituicao"];
            foreach ($atuacao_profissional["vinculos"] as $vinculos) {
                $vinculos_campos = ["tipoDeVinculo","enquadramentoFuncional","cargaHorariaSemanal","flagDedicacaoExclusiva","mesInicio","anoInicio","mesFim","anoFim","flagVinculoEmpregaticio","outroEnquadramentoFuncionalInformado","outroVinculoInformado"];
                foreach ($vinculos_campos as $campos) {
                    if (!empty($vinculos[$campos])) {
                        $doc_curriculo_array["doc"]["atuacao_profissional"][$i]["vinculos"][$campos] = $vinculos[$campos];
                    }
                }

            }
            $i++;
        }
    }
            
    $doc_curriculo_array["doc_as_upsert"] = true;
    $body =  json_encode($doc_curriculo_array, JSON_UNESCAPED_UNICODE);
                
    $resultado_curriculo = store_record($cursor["docs"][0]["numeroIdentificador"],"curriculos",$body);
    print_r($resultado_curriculo);

                
    //Parser de Trabalhos-em-Eventos

    if (isset($cursor["docs"][0]["producaoBibliografica"]["trabalhosEmEventos"])) {
        foreach ($cursor["docs"][0]["producaoBibliografica"]["trabalhosEmEventos"]["trabalhoEmEventos"] as $obra) {
            $resultadoProcessaObra = processaObra($obra,"trabalhoEmEventos",$_GET['tag']);            
            // Armazenar registro
            $resultado_evento = store_record($resultadoProcessaObra["sha256"],"trabalhos",$resultadoProcessaObra["body"]);
            print_r($resultado_evento);
            
            flush();
        }
    }
                
    //Parser de Artigos-Publicados

    if (isset($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"])) {
        foreach ($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"]["artigoPublicado"] as $obra) {
            $resultadoProcessaObra = processaObra($obra,"artigoPublicado",$_GET['tag']);
            // Armazenar registro
            $resultado_artigo = store_record($resultadoProcessaObra["sha256"],"trabalhos",$resultadoProcessaObra["body"]);
            print_r($resultado_artigo);
            
            flush();
        }
    }                
                
    //Parser de Livros publicados ou organizados

    if (isset($cursor["docs"][0]["producaoBibliografica"]["livrosECapitulos"]["livrosPublicadosOuOrganizados"])) {
        foreach ($cursor["docs"][0]["producaoBibliografica"]["livrosECapitulos"]["livrosPublicadosOuOrganizados"]["livroPublicadoOuOrganizado"] as $obra) {
            $resultadoProcessaObra = processaObra($obra,"livrosPublicadosOuOrganizado",$_GET['tag']);
            // Armazenar registro
            $resultado_livro = store_record($resultadoProcessaObra["sha256"],"trabalhos",$resultadoProcessaObra["body"]);
            print_r($resultado_livro);
            
            flush();
        }
    } 

    //Parser de Capítulos de livros

    if (isset($cursor["docs"][0]["producaoBibliografica"]["livrosECapitulos"]["capitulosDeLivrosPublicados"])) {
        foreach ($cursor["docs"][0]["producaoBibliografica"]["livrosECapitulos"]["capitulosDeLivrosPublicados"]["capituloDeLivroPublicado"] as $obra) {
            $resultadoProcessaObra = processaObra($obra,"capituloDeLivroPublicado",$_GET['tag']);
            // Armazenar registro
            $resultado_livro = store_record($resultadoProcessaObra["sha256"],"trabalhos",$resultadoProcessaObra["body"]);
            print_r($resultado_livro);
            
            flush();
        }
    } 

    print_r($cursor["docs"][0]["producaoTecnica"]["demaisTiposDeProducaoTecnica"]);            
                
    //Parser de Mídica Social Website Blog

    if (isset($cursor["docs"][0]["producaoTecnica"]["demaisTiposDeProducaoTecnica"]["midiaSocialWebsiteBlog"])) {
        foreach ($cursor["docs"][0]["producaoTecnica"]["demaisTiposDeProducaoTecnica"]["midiaSocialWebsiteBlog"] as $obra) {
            $resultadoProcessaObra = processaObra($obra,"midiaSocialWebsiteBlog",$_GET['tag']);
            // Armazenar registro
            $resultado_livro = store_record($resultadoProcessaObra["sha256"],"trabalhos",$resultadoProcessaObra["body"]);
            print_r($resultado_livro);
            
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
<!--
< ?php if (!isset($_GET["path_download"])) :?>
    < ?php sleep(5); echo '<script>window.location = \'http://bdpife2.sibi.usp.br/coletaprod/result_trabalhos.php?search[]=id_lattes.keyword:"'.$cursor["docs"][0]["numeroIdentificador"].'"\'</script>'; ?>
< ?php endif; ?>
-->
