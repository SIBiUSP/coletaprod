<?php 
    
require 'inc/config.php';
require 'inc/functions.php';


if (!isset($_POST['codpes'])) {
    $_POST['codpes'] = null;
}            
if (!isset($_GET['unidadeUSP'])) {
    $_POST['unidadeUSP'] = null;
}
if (!isset($_GET['tag'])) {
    $_POST['tag'] = null;
}
if (isset($_FILES['file'])) {
    //$content = file_get_contents($_FILES['file']['tmp_name']);
    $curriculo = simplexml_load_file($_FILES['file']['tmp_name']);
} else {
    echo "Não foi enviado um arquivo XML";
}

function processaAutoresLattes($autores_array) 
{
    $i = 0;
    foreach ($autores_array as $autor) {
        $autor = get_object_vars($autor);
        $array_result["doc"]["autores"][$i]["nomeCompletoDoAutor"] = $autor["@attributes"]["NOME-COMPLETO-DO-AUTOR"];
        $array_result["doc"]["autores"][$i]["nomeParaCitacao"] = $autor["@attributes"]["NOME-PARA-CITACAO"];
        $array_result["doc"]["autores"][$i]["ordemDeAutoria"] = $autor["@attributes"]["ORDEM-DE-AUTORIA"];
        if (isset($autor["@attributes"]["NRO-ID-CNPQ"])) {
            $array_result["doc"]["autores"][$i]["nroIdCnpq"] = $autor["@attributes"]["NRO-ID-CNPQ"];
        }
        
        $i++;
    }

    if (!empty($array_result)) {
        return $array_result;
    } else {
        $array_empty = [];
        return $array_empty;
    }
    unset($array_result);
}

function processaPalavrasChaveLattes($palavras_chave) 
{
    $palavras_chave = get_object_vars($palavras_chave);
    foreach (range(1, 6) as $number) {
        if (!empty($palavras_chave["@attributes"]["PALAVRA-CHAVE-$number"])) {
            $array_result["doc"]["palavras_chave"][] = $palavras_chave["@attributes"]["PALAVRA-CHAVE-$number"];
        }
    }
    if (isset($array_result)) {
        return $array_result;
    }
    unset($array_result); 
}

function processaPalavrasChaveFormacaoLattes($palavras_chave) 
{
    $palavras_chave = get_object_vars($palavras_chave);
    foreach (range(1, 6) as $number) {
        if (!empty($palavras_chave["@attributes"]["PALAVRA-CHAVE-$number"])) {
            $array_result["palavras_chave"][] = $palavras_chave["@attributes"]["PALAVRA-CHAVE-$number"];
        }
    }
    if (isset($array_result)) {
        return $array_result;
    }
    unset($array_result); 
}

function processaAreaDoConhecimentoLattes($areas_do_conhecimento)
{
    $i = 0;
    foreach ($areas_do_conhecimento as $ac) {
        $ac = get_object_vars($ac);
        foreach ($ac as $ac_record) {
            $array_result["doc"]["area_do_conhecimento"][$i]["nomeGrandeAreaDoConhecimento"] = $ac_record["NOME-GRANDE-AREA-DO-CONHECIMENTO"];
            $array_result["doc"]["area_do_conhecimento"][$i]["nomeDaAreaDoConhecimento"] = $ac_record["NOME-DA-AREA-DO-CONHECIMENTO"];
            $array_result["doc"]["area_do_conhecimento"][$i]["nomeDaSubAreaDoConhecimento"] = $ac_record["NOME-DA-SUB-AREA-DO-CONHECIMENTO"];
            $array_result["doc"]["area_do_conhecimento"][$i]["nomeDaEspecialidade"] = $ac_record["NOME-DA-ESPECIALIDADE"];
        } 
        $i++;
    }
    if (!empty($array_result)) {
        return $array_result;
    } else {
        $array_empty = [];
        return $array_empty;
    }
    unset($array_result);
         
} 

function processaAreaDoConhecimentoFormacaoLattes($areas_do_conhecimento)
{
    $i = 0;
    foreach ($areas_do_conhecimento as $ac) {
        $ac = get_object_vars($ac);
        foreach ($ac as $ac_record) {
            $array_result["area_do_conhecimento"][$i]["nomeGrandeAreaDoConhecimento"] = $ac_record["NOME-GRANDE-AREA-DO-CONHECIMENTO"];
            $array_result["area_do_conhecimento"][$i]["nomeDaAreaDoConhecimento"] = $ac_record["NOME-DA-AREA-DO-CONHECIMENTO"];
            $array_result["area_do_conhecimento"][$i]["nomeDaSubAreaDoConhecimento"] = $ac_record["NOME-DA-SUB-AREA-DO-CONHECIMENTO"];
            $array_result["area_do_conhecimento"][$i]["nomeDaEspecialidade"] = $ac_record["NOME-DA-ESPECIALIDADE"];
        } 
        $i++;
    }
    return $array_result;
    unset($array_result);     
}  

$doc_curriculo_array = [];
$doc_curriculo_array["doc"]["source"] = "Base Lattes";
$doc_curriculo_array["doc"]["type"] = "Curriculum";
$doc_curriculo_array["doc"]["tag"] = $_POST['tag'];
$doc_curriculo_array["doc"]["unidadeUSP"][] = $_POST['unidadeUSP'];
$doc_curriculo_array["doc"]["codpes"] = $_POST['codpes'];            
$doc_curriculo_array["doc"]["data_atualizacao"] = substr((string)$curriculo->attributes()->{'DATA-ATUALIZACAO'}, 4, 4)."-".substr((string)$curriculo->attributes()->{'DATA-ATUALIZACAO'}, 2, 2);
$doc_curriculo_array["doc"]["nome_completo"] = (string)$curriculo->{'DADOS-GERAIS'}->attributes()->{'NOME-COMPLETO'};
$doc_curriculo_array["doc"]["nome_em_citacoes_bibliograficas"] = (string)$curriculo->{'DADOS-GERAIS'}->attributes()->{'NOME-EM-CITACOES-BIBLIOGRAFICAS'};
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'NACIONALIDADE'})) {
    $doc_curriculo_array["doc"]["nacionalidade"] = (string)$curriculo->{'DADOS-GERAIS'}->attributes()->{'NACIONALIDADE'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NASCIMENTO'})) {
    $doc_curriculo_array["doc"]["pais_de_nascimento"] = (string)$curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NASCIMENTO'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'SIGLA-PAIS-NACIONALIDADE'})) {
    $doc_curriculo_array["doc"]["sigla_pais_nacionalidade"] = (string)$curriculo->{'DADOS-GERAIS'}->attributes()->{'SIGLA-PAIS-NACIONALIDADE'};
}
if (isset($curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NACIONALIDADE'})) {
    $doc_curriculo_array["doc"]["pais_de_nacionalidade"] = (string)$curriculo->{'DADOS-GERAIS'}->attributes()->{'PAIS-DE-NACIONALIDADE'};
}                 
if (isset($curriculo->{'DADOS-GERAIS'}->{'RESUMO-CV'})) {
    $doc_curriculo_array["doc"]["resumo_cv"]["texto_resumo_cv_rh"] = str_replace('"', '\"', (string)$curriculo->{'DADOS-GERAIS'}->{'RESUMO-CV'}->attributes()->{'TEXTO-RESUMO-CV-RH'});
    if (isset($cursor["docs"][0]["dadosGerais"]["resumoCv"]["textoResumoCvRhEn"])) {
        $doc_curriculo_array["doc"]["resumo_cv"]["texto_resumo_cv_rh_en"] = str_replace('"', '\"', (string)$curriculo->{'DADOS-GERAIS'}->{'RESUMO-CV'}->attributes()->{'TEXTO-RESUMO-CV-RH-EN'});
    }
}

// // if (isset($cursor["docs"][0]["linksPesquisador"])){
// //     foreach ($cursor["docs"][0]["linksPesquisador"] as $links_pesquisador) {
// //         //print_r($links_pesquisador);
// //         if ($links_pesquisador["origemLink"] == "orcid") {
// //             $doc_curriculo_array["doc"]["orcid"] = $links_pesquisador["link"]["path"];
// //         }
// //     }
// // }      
    
// Endereço profissional atual            
if (isset($curriculo->{'DADOS-GERAIS'}->{'ENDERECO'})) {
    $doc_curriculo_array["doc"]["endereco"]["flagDePreferencia"] = (string)$curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->attributes()->{'FLAG-DE-PREFERENCIA'};
    if (isset($curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'})) {
        $enderecoProfissionalArray = get_object_vars($curriculo->{'DADOS-GERAIS'}->{'ENDERECO'}->{'ENDERECO-PROFISSIONAL'});
        foreach (["CODIGO-INSTITUICAO-EMPRESA","NOME-INSTITUICAO-EMPRESA","CODIGO-ORGAO","NOME-ORGAO","CODIGO-UNIDADE","NOME-UNIDADE","LOGRADOURO-COMPLEMENTO","PAIS","UF","CEP","CIDADE","BAIRRO","HOME-PAGE"] as $endprof_campos) {
            if (!empty($enderecoProfissionalArray["@attributes"][$endprof_campos])) {
                $endprof_campos_corrigido = pregReplaceVariableName(strtolower($endprof_campos));
                $doc_curriculo_array["doc"]["endereco"]["endereco_profissional"][$endprof_campos_corrigido] = $enderecoProfissionalArray["@attributes"][$endprof_campos]; 
            }                    
        }
    }
}  
 
// // // Quadro de citações            
// // if (isset($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"]["totalQuadroCitacoes"])) {
// //     $i = 0;
// //     foreach ($cursor["docs"][0]["producaoBibliografica"]["artigosPublicados"]["totalQuadroCitacoes"] as $citacoes) {
// //         foreach (["nomeBase","codigoBase","sequencialIndicador","numeroCitacoes","dataCitacao","textoArgumento","indiceH","numeroTrabalhos","uriPesquisadorBase","uriLogoBase"] as $citacoes_campos) {
// //             if (isset ($citacoes[$citacoes_campos])) {
// //                 $doc_curriculo_array["doc"]["citacoes"][$citacoes["nomeBase"]][$citacoes_campos] = $citacoes[$citacoes_campos];                   
// //             }                    
// //         }
// //         foreach (["uriPesquisadorBase"] as $identificador_pesquisador) {
// //             if (!empty($citacoes[$identificador_pesquisador])) {
// //                 $doc_curriculo_array["doc"]["uri_pesquisador"][] = $citacoes[$identificador_pesquisador];
// //             }
// //         }             
// //         $i++;
// //     }
// // }           
                
// Formação Acadêmica Titulação            
if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'})) {
    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'GRADUACAO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'GRADUACAO'} as $graduacao) {
            $graduacao = get_object_vars($graduacao);
            $formacao_array["sequenciaFormacao"]=$graduacao['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"]=$graduacao['@attributes']["NIVEL"];
            $formacao_array["tituloDoTrabalhoDeConclusaoDeCurso"]=$graduacao['@attributes']["TITULO-DO-TRABALHO-DE-CONCLUSAO-DE-CURSO"];
            $formacao_array["nomeDoOrientador"]=$graduacao['@attributes']["NOME-DO-ORIENTADOR"];
            $formacao_array["codigoInstituicao"]=$graduacao['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"]=$graduacao['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"]=$graduacao['@attributes']["CODIGO-CURSO"];
            $formacao_array["nomeCurso"]=$graduacao['@attributes']["NOME-CURSO"];
            $formacao_array["codigoAreaCurso"]=$graduacao['@attributes']["CODIGO-AREA-CURSO"];
            $formacao_array["statusDoCurso"]=$graduacao['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"]=$graduacao['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"]=$graduacao['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"]=$graduacao['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"]=$graduacao['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"]=$graduacao['@attributes']["NOME-AGENCIA"];
            if (isset($graduacao['@attributes']["FORMACAO-ACADEMICA-TITULACAO"])) {
                $formacao_array["formacaoAcademicaTitulacao"]=$graduacao['@attributes']["FORMACAO-ACADEMICA-TITULACAO"];
            }            

            $doc_curriculo_array["formacao_academica_titulacao_graduacao"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'ESPECIALIZACAO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'ESPECIALIZACAO'} as $especializacao) {
            $especializacao = get_object_vars($especializacao);
            $formacao_array["sequenciaFormacao"]=$especializacao['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"]=$especializacao['@attributes']["NIVEL"];
            $formacao_array["tituloDaMonografia"]=$especializacao['@attributes']["TITULO-DA-MONOGRAFIA"];
            $formacao_array["nomeDoOrientador"]=$especializacao['@attributes']["NOME-DO-ORIENTADOR"];
            $formacao_array["codigoInstituicao"]=$especializacao['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"]=$especializacao['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"]=$especializacao['@attributes']["CODIGO-CURSO"];
            $formacao_array["nomeCurso"]=$especializacao['@attributes']["NOME-CURSO"];
            $formacao_array["statusDoCurso"]=$especializacao['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"]=$especializacao['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"]=$especializacao['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"]=$especializacao['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"]=$especializacao['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"]=$especializacao['@attributes']["NOME-AGENCIA"];
            $formacao_array["cargaHoraria"]=$especializacao['@attributes']["CARGA-HORARIA"];

            $doc_curriculo_array["formacao_academica_titulacao_graduacao"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO'} as $mestrado) {
            $mestrado = get_object_vars($mestrado);
            $formacao_array["sequenciaFormacao"]=$mestrado['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"]=$mestrado['@attributes']["NIVEL"];
            $formacao_array["tituloDaDissertacaoTese"]=$mestrado['@attributes']["TITULO-DA-DISSERTACAO-TESE"];
            $formacao_array["nomeDoOrientador"]=$mestrado['@attributes']["NOME-COMPLETO-DO-ORIENTADOR"];
            $formacao_array["nomeDoCoOrientador"]=$mestrado['@attributes']["NOME-DO-CO-ORIENTADOR"];
            $formacao_array["codigoInstituicao"]=$mestrado['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"]=$mestrado['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"]=$mestrado['@attributes']["CODIGO-CURSO"];
            $formacao_array["codigoCursoCapes"]=$mestrado['@attributes']["CODIGO-CURSO-CAPES"];
            $formacao_array["nomeCurso"]=$mestrado['@attributes']["NOME-CURSO"];
            $formacao_array["codigoAreaCurso"]=$mestrado['@attributes']["CODIGO-AREA-CURSO"];
            $formacao_array["statusDoCurso"]=$mestrado['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"]=$mestrado['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"]=$mestrado['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"]=$mestrado['@attributes']["FLAG-BOLSA"];
            $formacao_array["tipoMestrado"]=$mestrado['@attributes']["TIPO-MESTRADO"];
            $formacao_array["codigoAgenciaFinanciadora"]=$mestrado['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"]=$mestrado['@attributes']["NOME-AGENCIA"];
            $formacao_array["anoDeObtencaoDoTitulo"]=$mestrado['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];

            if (isset($mestrado["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($mestrado["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }            
            }

            if (isset($mestrado["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($mestrado["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($mestrado["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }            
            }

            $doc_curriculo_array["formacao_academica_titulacao_graduacao"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO-PROFISSIONALIZANTE'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO-PROFISSIONALIZANTE'} as $mestradoProf) {
            $mestradoProf = get_object_vars($mestradoProf);
            $formacao_array["sequenciaFormacao"]=$mestradoProf['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"]=$mestradoProf['@attributes']["NIVEL"];
            $formacao_array["codigoInstituicao"]=$mestradoProf['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"]=$mestradoProf['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"]=$mestradoProf['@attributes']["CODIGO-CURSO"];
            $formacao_array["nomeCurso"]=$mestradoProf['@attributes']["NOME-CURSO"];
            $formacao_array["codigoAreaCurso"]=$mestradoProf['@attributes']["CODIGO-AREA-CURSO"];
            $formacao_array["statusDoCurso"]=$mestradoProf['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"]=$mestradoProf['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"]=$mestradoProf['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"]=$mestradoProf['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"]=$mestradoProf['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"]=$mestradoProf['@attributes']["NOME-AGENCIA"];
            $formacao_array["anoDeObtencaoDoTitulo"]=$mestradoProf['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];
            $formacao_array["tituloDaDissertacaoTese"]=$mestradoProf['@attributes']["TITULO-DA-DISSERTACAO-TESE"];
            $formacao_array["nomeDoOrientador"]=$mestradoProf['@attributes']["NOME-COMPLETO-DO-ORIENTADOR"];
            $formacao_array["nomeDoCoOrientador"]=$mestradoProf['@attributes']["NOME-DO-CO-ORIENTADOR"];

            if (isset($mestradoProf["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($mestradoProf["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }            
            }

            if (isset($mestradoProf["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($mestradoProf["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($mestradoProf["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }            
            }

            $doc_curriculo_array["formacao_academica_titulacao_graduacao"][] = $formacao_array;
            unset($formacao_array);
        }
    }

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'DOUTORADO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'DOUTORADO'} as $doutorado) {
            $doutorado = get_object_vars($doutorado);
            $formacao_array["sequenciaFormacao"]=$doutorado['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"]=$doutorado['@attributes']["NIVEL"];
            $formacao_array["codigoInstituicao"]=$doutorado['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"]=$doutorado['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["codigoCurso"]=$doutorado['@attributes']["CODIGO-CURSO"];
            $formacao_array["nomeCurso"]=$doutorado['@attributes']["NOME-CURSO"];
            $formacao_array["codigoAreaCurso"]=$doutorado['@attributes']["CODIGO-AREA-CURSO"];
            $formacao_array["statusDoCurso"]=$doutorado['@attributes']["STATUS-DO-CURSO"];
            $formacao_array["anoDeInicio"]=$doutorado['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"]=$doutorado['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["flagBolsa"]=$doutorado['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"]=$doutorado['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"]=$doutorado['@attributes']["NOME-AGENCIA"];
            $formacao_array["anoDeObtencaoDoTitulo"]=$doutorado['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];
            $formacao_array["tituloDaDissertacaoTese"]=$doutorado['@attributes']["TITULO-DA-DISSERTACAO-TESE"];
            $formacao_array["nomeDoOrientador"]=$doutorado['@attributes']["NOME-COMPLETO-DO-ORIENTADOR"];
            $formacao_array["tipoDoutorado"]=$doutorado['@attributes']["TIPO-DOUTORADO"];
            $formacao_array["numeroIDOrientador"]=$doutorado['@attributes']["NUMERO-ID-ORIENTADOR"];
            $formacao_array["codigoCursoCapes"]=$doutorado['@attributes']["CODIGO-CURSO-CAPES"];
            $formacao_array["nomeDoCoOrientador"]=$doutorado['@attributes']["NOME-DO-CO-ORIENTADOR"];
            $formacao_array["codigoInstituicaoCoTutela"]=$doutorado['@attributes']["CODIGO-INSTITUICAO-CO-TUTELA"];
            $formacao_array["codigoInstituicaoSanduiche"]=$doutorado['@attributes']["CODIGO-INSTITUICAO-SANDUICHE"];

            if (isset($doutorado["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($doutorado["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }            
            }

            if (isset($doutorado["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($doutorado["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($doutorado["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }            
            }

            $doc_curriculo_array["formacao_academica_titulacao_graduacao"][] = $formacao_array;
            unset($formacao_array);
        }
    }    

    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'POS-DOUTORADO'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'POS-DOUTORADO'} as $posDoutorado) {
            $posDoutorado = get_object_vars($posDoutorado);
            $formacao_array["sequenciaFormacao"]=$posDoutorado['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"]=$posDoutorado['@attributes']["NIVEL"];
            $formacao_array["codigoInstituicao"]=$posDoutorado['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"]=$posDoutorado['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["anoDeInicio"]=$posDoutorado['@attributes']["ANO-DE-INICIO"];
            $formacao_array["anoDeConclusao"]=$posDoutorado['@attributes']["ANO-DE-CONCLUSAO"];
            $formacao_array["anoDeObtencaoDoTitulo"]=$posDoutorado['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];
            $formacao_array["flagBolsa"]=$posDoutorado['@attributes']["FLAG-BOLSA"];
            $formacao_array["codigoAgenciaFinanciadora"]=$posDoutorado['@attributes']["CODIGO-AGENCIA-FINANCIADORA"];
            $formacao_array["nomeAgencia"]=$posDoutorado['@attributes']["NOME-AGENCIA"];
            $formacao_array["statusDoCurso"]=$posDoutorado['@attributes']["STATUS-DO-CURSO"];  
            $formacao_array["numeroIDOrientador"]=$posDoutorado['@attributes']["NUMERO-ID-ORIENTADOR"];
            $formacao_array["tituloDoTrabalho"]=$posDoutorado['@attributes']["TITULO-DO-TRABALHO"];   

            if (isset($posDoutorado["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($posDoutorado["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }            
            }

            if (isset($posDoutorado["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($posDoutorado["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($posDoutorado["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }            
            }

            $doc_curriculo_array["formacao_academica_titulacao_graduacao"][] = $formacao_array;
            unset($formacao_array);
        }
    }
    
    if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'LIVRE-DOCENCIA'})) {
        foreach ($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'LIVRE-DOCENCIA'} as $livreDocencia) {
            $livreDocencia = get_object_vars($livreDocencia);
            $formacao_array["sequenciaFormacao"]=$livreDocencia['@attributes']["SEQUENCIA-FORMACAO"];
            $formacao_array["nivel"]=$livreDocencia['@attributes']["NIVEL"];
            $formacao_array["codigoInstituicao"]=$livreDocencia['@attributes']["CODIGO-INSTITUICAO"];
            $formacao_array["nomeInstituicao"]=$livreDocencia['@attributes']["NOME-INSTITUICAO"];
            $formacao_array["anoDeObtencaoDoTitulo"]=$livreDocencia['@attributes']["ANO-DE-OBTENCAO-DO-TITULO"];
            $formacao_array["tituloDoTrabalho"]=$livreDocencia['@attributes']["TITULO-DO-TRABALHO"];  

            if (isset($livreDocencia["PALAVRAS-CHAVE"])) {
                $array_result_pc = processaPalavrasChaveFormacaoLattes($livreDocencia["PALAVRAS-CHAVE"]);
                if (isset($array_result_pc)) {
                    $formacao_array = array_merge_recursive($formacao_array, $array_result_pc);
                }            
            }

            if (isset($livreDocencia["AREAS-DO-CONHECIMENTO"])) {
                if (!empty($livreDocencia["AREAS-DO-CONHECIMENTO"])) {
                    $array_result_ac = processaAreaDoConhecimentoFormacaoLattes($livreDocencia["AREAS-DO-CONHECIMENTO"]);
                    if (isset($array_result_ac)) {
                        $formacao_array = array_merge_recursive($formacao_array, $array_result_ac);
                    }
                }            
            }

            $doc_curriculo_array["formacao_academica_titulacao_graduacao"][] = $formacao_array;
            unset($formacao_array);
        }
    }     


}
        
   

// Formação máxima
if (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'LIVRE-DOCENCIA'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Livre Docência";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'POS-DOUTORADO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Pós Doutorado";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'DOUTORADO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Doutorado";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO-PROFISSIONALIZANTE'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Mestrado";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'MESTRADO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Mestrado";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'ESPECIALIZACAO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Especialização";
} elseif (isset($curriculo->{'DADOS-GERAIS'}->{'FORMACAO-ACADEMICA-TITULACAO'}->{'GRADUACAO'})) {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Graduação";
} else {
    $doc_curriculo_array["doc"]["formacao_maxima"] = "Sem formação informada";
}
             
                
//     // Vinculos profissionais
    
//     if ($cursor["docs"][0]["dadosGerais"]["atuacoesProfissionais"]) {
//         $i = 0;
//         foreach ($cursor["docs"][0]["dadosGerais"]["atuacoesProfissionais"]["atuacaoProfissional"] as $atuacao_profissional) {
//             $doc_curriculo_array["doc"]["atuacao_profissional"][$i]["codigoInstituicao"] = $atuacao_profissional["codigoInstituicao"];
//             $doc_curriculo_array["doc"]["atuacao_profissional"][$i]["nomeInstituicao"] = $atuacao_profissional["nomeInstituicao"];
//             foreach ($atuacao_profissional["vinculos"] as $vinculos) {
//                 $vinculos_campos = ["tipoDeVinculo","enquadramentoFuncional","cargaHorariaSemanal","flagDedicacaoExclusiva","mesInicio","anoInicio","mesFim","anoFim","flagVinculoEmpregaticio","outroEnquadramentoFuncionalInformado","outroVinculoInformado"];
//                 foreach ($vinculos_campos as $campos) {
//                     if (!empty($vinculos[$campos])) {
//                         $doc_curriculo_array["doc"]["atuacao_profissional"][$i]["vinculos"][$campos] = $vinculos[$campos];
//                     }
//                 }

//             }
//             $i++;
//         }
//     }
            
    $doc_curriculo_array["doc_as_upsert"] = true;
    print_r($doc_curriculo_array);
    //$body =  json_encode($doc_curriculo_array, JSON_UNESCAPED_UNICODE);
                
    ////$resultado_curriculo = elasticsearch::store_record($curriculo->attributes()->{'NUMERO-IDENTIFICADOR'}, "trabalhos", $doc_curriculo_array);
    //print_r($resultado_curriculo);



//Parser de Trabalhos-em-Eventos

if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'TRABALHOS-EM-EVENTOS'})) {

    $trabalhosEmEventosArray = $curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'TRABALHOS-EM-EVENTOS'}->{'TRABALHO-EM-EVENTOS'};
    foreach ($trabalhosEmEventosArray as $obra) {
        $obra = get_object_vars($obra);
        $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DO-TRABALHO"]);
        $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DO-TRABALHO"]);
        $doc["doc"]["type"] = "Work";
        $doc["doc"]["tipo"] = "Trabalhos em eventos";
        $doc["doc"]["source"] = "Base Lattes";
        $doc["doc"]["lattes_ids"][] = (string)$curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
        $doc["doc"]["tag"][] = $_REQUEST['tag'];
        $doc["doc"]["unidadeUSP"][] = $_REQUEST['unidadeUSP'];
        $doc["doc"]["codpes"] = $_REQUEST['codpes'];
        $doc["doc"]["ano"] = $dadosBasicosDoTrabalho['@attributes']["ANO-DO-TRABALHO"];
        $doc["doc"]["titulo"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-TRABALHO"];
        $doc["doc"]["dadosBasicosDoTrabalho"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
        $doc["doc"]["dadosBasicosDoTrabalho"]["tituloDoTrabalho"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-TRABALHO"];
        $doc["doc"]["dadosBasicosDoTrabalho"]["anoDoTrabalho"] = $dadosBasicosDoTrabalho['@attributes']["ANO-DO-TRABALHO"];
        $doc["doc"]["dadosBasicosDoTrabalho"]["paisDoEvento"] = $dadosBasicosDoTrabalho['@attributes']["PAIS-DO-EVENTO"];
        $doc["doc"]["dadosBasicosDoTrabalho"]["idioma"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
        $doc["doc"]["dadosBasicosDoTrabalho"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
        $doc["doc"]["dadosBasicosDoTrabalho"]["homePageDoTrabalho"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
        $doc["doc"]["dadosBasicosDoTrabalho"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
        $doc["doc"]["dadosBasicosDoTrabalho"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
        $doc["doc"]["dadosBasicosDoTrabalho"]["flagDivulgacaoCientifica"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-DIVULGACAO-CIENTIFICA"];

        $doc["doc"]["detalhamentoDoTrabalho"]["classificacaoDoEvento"] = $detalhamentoDoTrabalho['@attributes']["CLASSIFICACAO-DO-EVENTO"];
        $doc["doc"]["detalhamentoDoTrabalho"]["nomeDoEvento"] = $detalhamentoDoTrabalho['@attributes']["NOME-DO-EVENTO"];
        $doc["doc"]["detalhamentoDoTrabalho"]["cidadeDoEvento"] = $detalhamentoDoTrabalho['@attributes']["CIDADE-DO-EVENTO"];
        $doc["doc"]["detalhamentoDoTrabalho"]["anoDeRealizacao"] = $detalhamentoDoTrabalho['@attributes']["ANO-DE-REALIZACAO"];
        $doc["doc"]["detalhamentoDoTrabalho"]["tituloDosAnaisOuProceedings"] = $detalhamentoDoTrabalho['@attributes']["TITULO-DOS-ANAIS-OU-PROCEEDINGS"];
        $doc["doc"]["detalhamentoDoTrabalho"]["paginaInicial"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-INICIAL"];
        $doc["doc"]["detalhamentoDoTrabalho"]["paginaFinal"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-FINAL"];
        $doc["doc"]["detalhamentoDoTrabalho"]["isbn"] = $detalhamentoDoTrabalho['@attributes']["ISBN"];
        $doc["doc"]["detalhamentoDoTrabalho"]["nomeDaEditora"] = $detalhamentoDoTrabalho['@attributes']["NOME-DA-EDITORA"];
        $doc["doc"]["detalhamentoDoTrabalho"]["cidadeDaEditora"] = $detalhamentoDoTrabalho['@attributes']["CIDADE-DA-EDITORA"];
        $doc["doc"]["detalhamentoDoTrabalho"]["volumeDosAnais"] = $detalhamentoDoTrabalho['@attributes']["VOLUME"];
        $doc["doc"]["detalhamentoDoTrabalho"]["fasciculoDosAnais"] = $detalhamentoDoTrabalho['@attributes']["FASCICULO"];
        $doc["doc"]["detalhamentoDoTrabalho"]["serieDosAnais"] = $detalhamentoDoTrabalho['@attributes']["SERIE"];

        if (!empty($obra["AUTORES"])) {
            $array_result = processaAutoresLattes($obra["AUTORES"]);
            $doc = array_merge_recursive($doc, $array_result);
        }

        if (isset($obra["PALAVRAS-CHAVE"])) {
            $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
            if (isset($array_result_pc)) {
                $doc = array_merge_recursive($doc, $array_result_pc);
            } 
            unset($array_result_pc);           
        }

        if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
            $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
            if (isset($array_result_ac)) {
                $doc = array_merge_recursive($doc, $array_result_ac);
            }
            unset($array_result_ac);           
        }

        // Constroi sha256
        $sha_array[] = $doc["doc"]["dadosBasicosDoTrabalho"]["natureza"];
        $sha_array[] = $doc["doc"]["dadosBasicosDoTrabalho"]["tituloDoTrabalho"];
        $sha_array[] = $doc["doc"]["dadosBasicosDoTrabalho"]["anoDoTrabalho"];
        $sha_array[] = $doc["doc"]["dadosBasicosDoTrabalho"]["paisDoEvento"];
        $sha_array[] = $doc["doc"]["detalhamentoDoTrabalho"]["nomeDoEvento"];
        $sha_array[] = $doc["doc"]["detalhamentoDoTrabalho"]["paginaInicial"];
        $sha_array[] = $doc["doc"]["detalhamentoDoTrabalho"]["paginaFinal"];
        $sha256 = hash('sha256', ''.implode("", $sha_array).'');


        $doc["doc"]["concluido"] = "Não";
        $doc["doc_as_upsert"] = true;

        echo '<br/><br/>';
        print_r($doc);
        echo '<br/><br/>';        

        // Armazenar registro
        ////$resultado_evento = elasticsearch::elastic_update($sha256, "trabalhos", $doc);
        ////print_r($resultado_evento);

        unset($doc);
        flush();

    }
}

//Parser de Artigos-Publicados

if (isset($curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'ARTIGOS-PUBLICADOS'})) {

    $artigoPublicadoArray = $curriculo->{'PRODUCAO-BIBLIOGRAFICA'}->{'ARTIGOS-PUBLICADOS'}->{'ARTIGO-PUBLICADO'};
    foreach ($artigoPublicadoArray as $obra) {
        $obra = get_object_vars($obra);
        $dadosBasicosDoTrabalho = get_object_vars($obra["DADOS-BASICOS-DO-ARTIGO"]);
        $detalhamentoDoTrabalho = get_object_vars($obra["DETALHAMENTO-DO-ARTIGO"]);

        $doc["doc"]["type"] = "Work";
        $doc["doc"]["tipo"] = "Artigo publicado";
        $doc["doc"]["source"] = "Base Lattes";
        $doc["doc"]["lattes_ids"][] = (string)$curriculo->attributes()->{'NUMERO-IDENTIFICADOR'};
        $doc["doc"]["tag"][] = $_REQUEST['tag'];
        $doc["doc"]["unidadeUSP"][] = $_REQUEST['unidadeUSP'];
        $doc["doc"]["codpes"] = $_REQUEST['codpes'];
        $doc["doc"]["ano"] = $dadosBasicosDoTrabalho['@attributes']["ANO-DO-ARTIGO"];
        $doc["doc"]["titulo"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-ARTIGO"];
        $doc["doc"]["dadosBasicosDoArtigo"]["natureza"] = $dadosBasicosDoTrabalho['@attributes']['NATUREZA'];
        $doc["doc"]["dadosBasicosDoArtigo"]["tituloDoArtigo"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-ARTIGO"];
        $doc["doc"]["dadosBasicosDoArtigo"]["anoDoArtigo"] = $dadosBasicosDoTrabalho['@attributes']["ANO-DO-ARTIGO"];
        $doc["doc"]["dadosBasicosDoArtigo"]["paisDoEvento"] = $dadosBasicosDoTrabalho['@attributes']["PAIS-DE-PUBLICACAO"];
        $doc["doc"]["dadosBasicosDoArtigo"]["idioma"] = $dadosBasicosDoTrabalho['@attributes']["IDIOMA"];
        $doc["doc"]["dadosBasicosDoArtigo"]["meioDeDivulgacao"] = $dadosBasicosDoTrabalho['@attributes']["MEIO-DE-DIVULGACAO"];
        $doc["doc"]["dadosBasicosDoArtigo"]["homePageDoTrabalho"] = $dadosBasicosDoTrabalho['@attributes']["HOME-PAGE-DO-TRABALHO"];
        $doc["doc"]["dadosBasicosDoArtigo"]["flagRelevancia"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-RELEVANCIA"];
        $doc["doc"]["dadosBasicosDoArtigo"]["doi"] = $dadosBasicosDoTrabalho['@attributes']["DOI"];
        $doc["doc"]["dadosBasicosDoArtigo"]["tituloDoArtigoIngles"] = $dadosBasicosDoTrabalho['@attributes']["TITULO-DO-ARTIGO-INGLES"];
        $doc["doc"]["dadosBasicosDoArtigo"]["flagDivulgacaoCientifica"] = $dadosBasicosDoTrabalho['@attributes']["FLAG-DIVULGACAO-CIENTIFICA"];

        $doc["doc"]["detalhamentoDoArtigo"]["localDePublicacao"] = $detalhamentoDoTrabalho['@attributes']["LOCAL-DE-PUBLICACAO"];
        $doc["doc"]["detalhamentoDoArtigo"]["tituloDoPeriodicoOuRevista"] = $detalhamentoDoTrabalho['@attributes']["TITULO-DO-PERIODICO-OU-REVISTA"];
        $doc["doc"]["detalhamentoDoArtigo"]["paginaInicial"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-INICIAL"];
        $doc["doc"]["detalhamentoDoArtigo"]["paginaFinal"] = $detalhamentoDoTrabalho['@attributes']["PAGINA-FINAL"];
        $doc["doc"]["detalhamentoDoArtigo"]["issn"] = $detalhamentoDoTrabalho['@attributes']["ISSN"];
        $doc["doc"]["detalhamentoDoArtigo"]["volume"] = $detalhamentoDoTrabalho['@attributes']["VOLUME"];
        $doc["doc"]["detalhamentoDoArtigo"]["fasciculo"] = $detalhamentoDoTrabalho['@attributes']["FASCICULO"];
        $doc["doc"]["detalhamentoDoArtigo"]["serie"] = $detalhamentoDoTrabalho['@attributes']["SERIE"];

        if (!empty($obra["AUTORES"])) {
            $array_result = processaAutoresLattes($obra["AUTORES"]);
            $doc = array_merge_recursive($doc, $array_result);
        }

        if (isset($obra["PALAVRAS-CHAVE"])) {
            $array_result_pc = processaPalavrasChaveLattes($obra["PALAVRAS-CHAVE"]);
            if (isset($array_result_pc)) {
                $doc = array_merge_recursive($doc, $array_result_pc);
            }
            unset($array_result_pc);            
        }

        if (isset($obra["AREAS-DO-CONHECIMENTO"])) {
            $array_result_ac = processaAreaDoConhecimentoLattes($obra["AREAS-DO-CONHECIMENTO"]);
            if (isset($array_result_ac)) {
                $doc = array_merge_recursive($doc, $array_result_ac);
            } 
            unset($array_result_ac);           
        }

        // Constroi sha256
            

        if (isset($doc["doc"]["dadosBasicosDoArtigo"]["doi"])) {
            $sha256 = hash('sha256', $doc["doc"]["dadosBasicosDoArtigo"]["doi"]);
        } else {
            $sha_array[] = $doc["doc"]["dadosBasicosDoArtigo"]["natureza"];
            $sha_array[] = $doc["doc"]["dadosBasicosDoArtigo"]["tituloDoArtigo"];
            $sha_array[] = $doc["doc"]["dadosBasicosDoArtigo"]["anoDoArtigo"];
            $sha_array[] = $doc["doc"]["detalhamentoDoArtigo"]["tituloDoPeriodicoOuRevista"];
            $sha_array[] = $doc["doc"]["detalhamentoDoArtigo"]["paginaInicial"];
            $sha_array[] = $doc["doc"]["dadosBasicosDoArtigo"]["homePageDoTrabalho"];
            $sha256 = hash('sha256', ''.implode("", $sha_array).'');
        }



        $doc["doc"]["concluido"] = "Não";
        $doc["doc_as_upsert"] = true;

        echo '<br/><br/>';
        print_r($doc);
        echo '<br/><br/>';

        // Armazenar registro
        ////$resultado_evento = elasticsearch::elastic_update($sha256, "trabalhos", $doc);
        ////print_r($resultado_evento);

        unset($doc);
        flush();

    }
}

?>

