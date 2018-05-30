<!DOCTYPE html>
<?php
    include('inc/config.php'); 
    include('inc/functions.php');

    if (!empty($_POST)) {
        foreach ($_POST as $key=>$value) {            
            $var_concluido["doc"]["concluido"] = $value;
            $var_concluido["doc"]["doc_as_upsert"] = true; 
            elasticsearch::elastic_update($key, $type, $var_concluido);
        }
        sleep(6);
        header("Refresh:0");
    }
    $_GET["filter"][] = "type:\"Work\"";
    $result_get = get::analisa_get($_GET);
    $query = $result_get['query'];
    $limit = $result_get['limit'];
    $page = $result_get['page'];
    $skip = $result_get['skip'];

    $query['sort'] = [
        ['datePublished.keyword' => ['order' => 'desc']],
    ];
    
    $params = [];
    $params["index"] = $index;
    $params["type"] = $type;
    $params["size"] = $limit;
    $params["from"] = $skip;
    $params["body"] = $query;
    
    $cursor = $client->search($params);
    $total = $cursor["hits"]["total"];

    /*pagination - start*/
    $get_data = $_GET;    
    /*pagination - end*/      

?>
<html>
    <head>
        <?php
            include('inc/meta-header.php'); 
        ?>        
        <title>Lattes USP - Resultado da busca por trabalhos</title>
        <script src="inc/uikit/js/components/accordion.min.js"></script>
        <script src="inc/uikit/js/components/pagination.min.js"></script>
        <script src="inc/uikit/js/components/datepicker.min.js"></script>
        <script src="inc/uikit/js/components/tooltip.min.js"></script>
        
        <script src="http://cdn.jsdelivr.net/g/filesaver.js"></script>
        <script>
              function SaveAsFile(t,f,m) {
                    try {
                        var b = new Blob([t],{type:m});
                        saveAs(b, f);
                    } catch (e) {
                        window.open("data:"+m+"," + encodeURIComponent(t), '_blank','');
                    }
                }
        </script>         
        
    </head>
    <body>
        <div class="uk-container">
            <?php include('inc/navbar.php'); ?>        
	        <div class="uk-width-1-1@s uk-width-1-1@m">	    
                <nav class="uk-navbar-container uk-margin" uk-navbar>
                    <div class="nav-overlay uk-navbar-left">
                        <a class="uk-navbar-item uk-logo" uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#">Clique para uma nova pesquisa</a>        
                    </div>
                    <div class="nav-overlay uk-navbar-right">
                        <a class="uk-navbar-toggle" uk-search-icon uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"></a>
                    </div>

                    <div class="nav-overlay uk-navbar-left uk-flex-1" hidden>
                    <div class="uk-navbar-item uk-width-expand">
                        <form class="uk-search uk-search-navbar uk-width-1-1">
                        <input type="hidden" name="fields[]" value="name">
                        <input type="hidden" name="fields[]" value="author.person.name">
                        <input type="hidden" name="fields[]" value="authorUSP.name">
                        <input type="hidden" name="fields[]" value="about">
                        <input type="hidden" name="fields[]" value="description"> 	    
                        <input class="uk-search-input" type="search" name="search[]" placeholder="Nova pesquisa" autofocus>
                        </form>
                    </div>
                        <a class="uk-navbar-toggle" uk-close uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"></a>
                    </div>
                </nav>
	    </div>

	    <div class="uk-width-1-1@s uk-width-1-1@m">
	    
		    <?php if (!empty($_SERVER["QUERY_STRING"])) : ?>
		    				    
			<p class="uk-margin-top" uk-margin>
				<a class="uk-button uk-button-default uk-button-small" href="index.php">Começar novamente</a>	
				<?php 
				
					if (!empty($_GET["search"])){
                        foreach($_GET["search"] as $filters) {
                            $filters_array[] = $filters;
                            $name_field = explode(":",$filters);	
                            $filters = str_replace($name_field[0].":","",$filters);				
                            $diff["search"] = array_diff($_GET["search"],$filters_array);						
                            $url_push = $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"].'?'.http_build_query($diff);
                            echo '<a class="uk-button uk-button-default uk-button-small" href="http://'.$url_push.'">'.$filters.' <span uk-icon="icon: close; ratio: 1"></span></a>';
                            unset($filters_array); 	
                        }
                    }	
	
				?>
				
			</p>
		    <?php endif;?> 
	    
	    </div>	
        <div class="uk-grid-divider" uk-grid>
        <div class="uk-width-1-4@s uk-width-2-6@m">  
        
            <div class="uk-panel uk-panel-box">
                
                <hr>
                <h3 class="uk-panel-title">Refinar meus resultados</h3>    
                <ul class="uk-nav uk-nav-side uk-nav-parent-icon uk-margin-top" data-uk-nav="{multiple:true}">
                    <hr>
                <?php
                    $facets = new facets();
                    $facets->query = $query;

                    if (!isset($_GET["search"])) {
                        $_GET["search"] = null;                                    
                    }                       
                    
                    $facets->facet("Lattes.natureza", 100, "Natureza", null, "_term", $_GET["search"]);
                    $facets->facet("tipo", 100, "Tipo de material", null, "_term", $_GET["search"]);
                    $facets->facet("tag", 100, "Tag", null, "_term", $_GET["search"]);
                    
                    $facets->facet("author.person.name", 100, "Nome completo do autor", null, "_term", $_GET["search"]);
                    $facets->facet("lattes_ids", 100, "Número do lattes", null, "_term", $_GET["search"]);
                    $facets->facet("USP.codpes",100,"Número USP",null,"_term",$_GET["search"]);
                    $facets->facet("USP.unidadeUSP",100,"Unidade USP",null,"_term",$_GET["search"]);
                    
                    echo '<hr><li>Informações da publicação</li>';
                    $facets->facet("country",200,"País de publicação",null,"_term",$_GET["search"]);
                    $facets->facet("datePublished",120,"Ano de publicação","desc","_term",$_GET["search"]);
                    $facets->facet("language",40,"Idioma",null,"_term",$_GET["search"]);
                    $facets->facet("Lattes.meioDeDivulgacao",100,"Meio de divulgação",null,"_term",$_GET["search"]);
                    $facets->facet("about",100,"Palavras-chave",null,"_term",$_GET["search"]);
                    $facets->facet("agencia_de_fomento",100,"Agências de fomento",null,"_term",$_GET["search"]);

                    echo '<hr><li>Lattes</li>';
                    $facets->facet("Lattes.flagRelevancia",100,"Relevância",null,"_term",$_GET["search"]);
                    $facets->facet("Lattes.flagDivulgacaoCientifica",100,"Divulgação científica",null,"_term",$_GET["search"]);
                    
                    echo '<hr><li>Área do conhecimento</li>';
                    $facets->facet("area_do_conhecimento.nomeGrandeAreaDoConhecimento", 100, "Nome da Grande Área do Conhecimento", null, "_term", $_GET["search"]);
                    $facets->facet("area_do_conhecimento.nomeDaAreaDoConhecimento", 100, "Nome da Área do Conhecimento", null, "_term", $_GET["search"]);
                    $facets->facet("area_do_conhecimento.nomeDaSubAreaDoConhecimento", 100, "Nome da Sub Área do Conhecimento", null, "_term", $_GET["search"]);
                    $facets->facet("area_do_conhecimento.nomeDaEspecialidade", 100, "Nome da Especialidade", null, "_term", $_GET["search"]);
                    
                    echo '<hr><li>Eventos</li>';
                    $facets->facet("trabalhoEmEventos.classificacaoDoEvento", 100, "Classificação do evento", null, "_term", $_GET["search"]); 
                    $facets->facet("EducationEvent.name", 100, "Nome do evento", null, "_term", $_GET["search"]);
                    $facets->facet("publisher.organization.location", 100, "Cidade do evento", null, "_term", $_GET["search"]);
                    $facets->facet("trabalhoEmEventos.anoDeRealizacao", 100, "Ano de realização do evento", null, "_term", $_GET["search"]);
                    $facets->facet("trabalhoEmEventos.tituloDosAnaisOuProceedings", 100, "Título dos anais", null, "_term", $_GET["search"]);
                    $facets->facet("trabalhoEmEventos.isbn", 100, "ISBN dos anais", null, "_term", $_GET["search"]);
                    $facets->facet("trabalhoEmEventos.nomeDaEditora", 100, "Editora dos anais", null, "_term", $_GET["search"]);
                    $facets->facet("trabalhoEmEventos.cidadeDaEditora", 100, "Cidade da editora", null, "_term", $_GET["search"]);

                    echo '<hr><li>Mídias sociais e blogs</li>';
                    $facets->facet("midiaSocialWebsiteBlog.formacao_maxima", 100, "Formação máxima - Blogs e mídias sociais", null, "_term", $_GET["search"]);
                    
                    echo '<hr><li>Periódicos</li>';
                    $facets->facet("isPartOf.name", 100, "Título do periódico", null, "_term", $_GET["search"]);

                    echo '<hr><li>Concluído</li>';
                    $facets->facet("concluido", 100, "Concluído", null, "_term", $_GET["search"]);
                    $facets->facet("bdpi.existe", 100, "Está na BDPI?", null, "_term", $_GET["search"]);

                ?>
                </ul>
                    <?php if(!empty($_SESSION['oauthuserdata'])): ?>
                        <h3 class="uk-panel-title uk-margin-top">Informações administrativas</h3>
                        <ul class="uk-nav uk-nav-side uk-nav-parent-icon uk-margin-top" data-uk-nav="{multiple:true}">
                        <hr>
                        <?php         

                        ?>
                        </ul>
                    <?php endif; ?>
                <hr>
                <form class="uk-form">
                <fieldset>
                    <legend>Limitar datas</legend>

                    <script>
                        $( function() {
                        $( "#limitar-data" ).slider({
                        range: true,
                        min: 1900,
                        max: 2030,
                        values: [ 1900, 2030 ],
                        slide: function( event, ui ) {
                            $( "#date" ).val( "datePublished:[" + ui.values[ 0 ] + " TO " + ui.values[ 1 ] + "]" );
                        }
                        });
                        $( "#date" ).val( "datePublished:[" + $( "#limitar-data" ).slider( "values", 0 ) +
                        " TO " + $( "#limitar-data" ).slider( "values", 1 ) + "]");
                        } );
                    </script>
                    <p>
                    <label for="date">Selecionar período de tempo:</label>
                    <input type="text" id="date" readonly style="border:0; color:#f6931f; font-weight:bold;" name="search[]">
                    </p>        
                    <div id="limitar-data" class="uk-margin-bottom"></div>        
                    <?php if(!empty($_GET["search"])): ?>
                        <?php foreach($_GET["search"] as $search_expression): ?>
                            <input type="hidden" name="search[]" value="<?php echo str_replace('"','&quot;',$search_expression); ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <div class="uk-form-row"><button class="uk-button-primary">Limitar datas</button></div>
                </fieldset>        
                </form>
                <hr>     
                        
            </div>
        </div>
                
        <div class="uk-width-3-4@s uk-width-4-6@m">
        
            <!-- Navegador de resultados - Início -->
            <div class="uk-child-width-expand@s uk-grid-divider" uk-grid>
                <div>
                    <ul class="uk-pagination">
                        <?php if ($page == 1) :?>
                            <li><a href="#"><span class="uk-margin-small-right" uk-pagination-previous></span>Anterior</a></li>
                        <?php else :?>
                            <?php $get_data["page"] = $page-1 ; ?>
                            <li><a href="result_trabalhos.php?<?php echo http_build_query($get_data); ?>"><span class="uk-margin-small-right" uk-pagination-previous></span> Anterior</a></li>
                        <?php endif; ?>
                    </ul>    
                </div>
                <div>
                    <p class="uk-text-center"><?php print_r(number_format($total,0,',','.'));?> registros</p>
                </div>
                <div>
                    <ul class="uk-pagination">
                        <?php if ($total/$limit > $page): ?>
                            <?php $get_data["page"] = $page+1 ; ?>
                            <li class="uk-margin-auto-left"><a href="result_trabalhos.php?<?php echo http_build_query($get_data); ?>">Próxima <span class="uk-margin-small-left" uk-pagination-next></span></a></li>
                        <?php else :?>
                            <li class="uk-margin-auto-left"><a href="#">Próxima <span class="uk-margin-small-left" uk-pagination-next></span></a></li>
                        <?php endif; ?>
                    </ul>                            
                </div>
            </div>
            <!-- Navegador de resultados - Fim -->                    
                    
            <hr class="uk-grid-divider">           
                    
            <div class="uk-width-1-1 uk-margin-top uk-description-list-line">                        
                <ul class="uk-list uk-list-divider">
                   
                <?php foreach ($cursor["hits"]["hits"] as $r) : ?>
                    <?php if (empty($r["_source"]['datePublished'])) {
                        $r["_source"]['datePublished'] = "";
                    }
                    ?>
                    <li>
                        <div class="uk-grid-divider uk-padding-small" uk-grid>
                            <div class="uk-width-1-5@m">                        
                                <div class="uk-panel uk-h6 uk-text-break">
                                    <a href="result_trabalhos.php?type[]=<?php echo $r["_source"]['tipo'];?>"><?php echo ucfirst(strtolower($r["_source"]['tipo']));?></a>
                                </div>
                                <form class="uk-form" method="post">
                                    <?php if(isset($r["_source"]["concluido"])) : ?>
                                        <?php if($r["_source"]["concluido"]== "Sim") : ?>    
                                            
                                                <label><input type='hidden' value='Não' name="<?php echo $r['_id'];?>"></label>                                     
                                                <label><input type="checkbox" name="<?php echo $r['_id'];?>" value='Sim' checked>Concluído</label>
                                           
                                        <?php else : ?>
                                            
                                                <label><input type='hidden' value='Não' name="<?php echo $r['_id'];?>"></label>                                     
                                                <label><input type="checkbox" name="<?php echo $r['_id'];?>" value='Sim'>Concluído</label>
                                            
                                        <?php endif; ?>                                    
                                    <?php else : ?>
                                            
                                                <label><input type='hidden' value='Não' name="<?php echo $r['_id'];?>"></label>                                     
                                                <label><input type="checkbox" name="<?php echo $r['_id'];?>" value='Sim'>Concluído</label>
                                            
                                    <?php endif; ?>
                                    <button class="uk-button-primary">Marcar como concluído</button>
                                </form>                                     
                            </div>
                            <div class="uk-width-4-5@m">
                                <article class="uk-article">
                                <p class="uk-text-lead uk-margin-remove" style="font-size:115%"><?php echo ($r["_source"]['name']);?> (<?php echo $r["_source"]['datePublished']; ?>)</p> 
                                <ul class="uk-list">
                                    <li class="uk-h6">
                                        Autores:
                                        <?php if (!empty($r["_source"]['author'])) : ?>
                                        <?php foreach ($r["_source"]['author'] as $autores) {
                                            $authors_array[]='<a href="result_trabalhos.php?search[]=author.person.name.keyword:&quot;'.$autores["person"]["name"].'&quot;">'.$autores["person"]["name"].'</a>';
                                        } 
                                        $array_aut = implode(", ",$authors_array);
                                        unset($authors_array);
                                        print_r($array_aut);
                                        ?>
                                        
                                        
                                        <?php endif; ?>                           
                                    </li>
                                    
                                    <?php if (!empty($r["_source"]['artigoPublicado'])) : ?>
                                        <li class="uk-h6">In: <a href="result_trabalhos.php?search[]=periodico.titulo_do_periodico.keyword:&quot;<?php echo $r["_source"]['artigoPublicado']['tituloDoPeriodicoOuRevista'];?>&quot;"><?php echo $r["_source"]['artigoPublicado']['tituloDoPeriodicoOuRevista'];?></a></li>
                                        <li class="uk-h6">ISSN: <a href="result_trabalhos.php?search[]=periodico.issn.keyword:&quot;<?php echo $r["_source"]['artigoPublicado']['issn'];?>&quot;"><?php echo $r["_source"]['artigoPublicado']['issn'];?></a></li>                                        
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($r["_source"]['doi'])) : ?>
                                        <li class="uk-h6">DOI: <a href="https://dx.doi.org/<?php echo $r["_source"]['doi'];?>"><?php echo $r["_source"]['doi'];?></a> - <a href="doi_to_elastic.php?doi=<?php echo $r['_source']['doi'];?>&tag=<?php echo $r['_source']['tag'][0];?>">Coletar dados da Crossref</a></li>                                        
                                    <?php endif; ?>                                        
                                    
                                    <li class="uk-h6">
                                        Assuntos:
                                        <?php if (!empty($r["_source"]['palavras_chave'])) : ?>
                                        <?php foreach ($r["_source"]['palavras_chave'] as $assunto) : ?>
                                            <a href="result_trabalhos.php?search[]=palavras_chave.keyword:&quot;<?php echo $assunto;?>&quot;"><?php echo $assunto;?></a>
                                        <?php endforeach;?>
                                        <?php endif; ?>
                                    </li>
                                    
                                    <?php if (!empty($r["_source"]['ids_match'])) : ?>  
                                    <?php foreach ($r["_source"]['ids_match'] as $id_match) : ?>
                                        <?php compararRegistros::match_id($id_match["id_match"],$id_match["nota"]);?>
                                    <?php endforeach;?>
                                    <?php endif; ?>
                                    
                                    <?php if ($instituicao == "USP") {
                                            dadosExternos::query_bdpi($r["_source"]['name'],$r["_source"]['datePublished'],$r['_id']);
                                            }
                                    ?>        
                                    
                                    <li class="uk-h6">
                                        <!-- This is a button toggling the modal -->
                                        <button uk-toggle="target: #<?php echo $r['_id']; ?>" type="button">Ver em tabela</button>

                                        <!-- This is the modal -->
                                        <div id="<?php echo $r['_id']; ?>" uk-modal>
                                            <div class="uk-modal-dialog uk-modal-body">
                                                <h2 class="uk-modal-title">Tabela</h2>
                                                <table class="uk-table">
                                                    <caption></caption>
                                                    <thead>
                                                        <tr>
                                                            <th>Titulo</th>
                                                            <th>Autores</th>
                                                            <th>Ano</th>
                                                            <th>Idioma</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td><?php echo ($r["_source"]['name']);?></td>
                                                            <td><?php echo ($array_aut);?></td>
                                                            <td><?php echo $r["_source"]['datePublished']; ?></td>
                                                            <td><?php echo $r["_source"]['language']; ?></td>
                                                        </tr>
                                                    </tbody>
                                                </table>                                                
                                                <button class="uk-modal-close" type="button"></button>
                                            </div>
                                        </div>                                    
                                    
                                    </li>


                                    <li class="uk-h6">
                                    <?php
                                        
                                        //print_r($r["_source"]);
                                        
                                        $author_number = count($r["_source"]['author']);
                                        
                                        $record = [];
                                        $record[] = "000000001 FMT   L BK";
                                        $record[] = "000000001 LDR   L ^^^^^nab^^22^^^^^Ia^4500";
                                        $record[] = "000000001 BAS   L \$\$a04";
                                        $record[] = "000000001 008   L ^^^^^^s^^^^^^^^^^^^^^^^^^^^^^000^0^^^^^d";
                                        if (isset($r["_source"]['doi'])){
                                            $record[] = '000000001 0247  L \$\$a'.$r["_source"]["doi"].'\$\$2DOI';         
                                        } else {
                                            $record[] = '000000001 0247  L \$\$a\$\$2DOI';
                                        }
                                        $record[] = "000000001 040   L \$\$aUSP/SIBI";
                                        $record[] = '000000001 0410  L \$\$a';
                                        $record[] = '000000001 044   L \$\$a';
                                        if ($author_number > 1) {
                                            if (isset($r["_source"]['author'][0]["person"]["name"])) {
                                                $record[] = '000000001 1001  L \$\$a'.$r["_source"]['author'][0]["nomeParaCitacao"].'';
                                            } else {
                                                $record[] = '000000001 1001  L \$\$a'.$r["_source"]['author'][0]["person"]["name"].'';
                                            }                                            
                                            for ($i = 1; $i < $author_number; $i++) {
                                                if (isset($r["_source"]['author'][$i]["person"]["name"])) {
                                                    $record[] = '000000001 7001  L \$\$a'.$r["_source"]['author'][$i]["nomeParaCitacao"].'';
                                                } else {
                                                    $record[] = '000000001 7001  L \$\$a'.$r["_source"]['author'][$i]["person"]["name"].'';
                                                }
                                            }
                                        } else {
                                            if (isset($r["_source"]['author'][0]["person"]["name"])) {
                                                $record[] = '000000001 1001  L \$\$a'.$r["_source"]['author'][0]["nomeParaCitacao"].'';
                                            } else {
                                                $record[] = '000000001 1001  L \$\$a'.$r["_source"]['author'][0]["person"]["name"].'';
                                            }
                                        }                                            
                                        $record[] = '000000001 24510 L \$\$a'.$r["_source"]["name"].'';                                            
                                        if (isset($r["_source"]["trabalhoEmEventos"])){  
                                            $record[] = '000000001 260   L \$\$a'.((isset($r["_source"]["trabalhoEmEventos"]["cidadeDaEditora"]) && $r["_source"]["trabalhoEmEventos"]["cidadeDaEditora"])? $r["_source"]["trabalhoEmEventos"]["cidadeDaEditora"] : '').'\$\$b'.((isset($r["_source"]["trabalhoEmEventos"]["nomeDaEditora"]) && $r["_source"]["trabalhoEmEventos"]["nomeDaEditora"])? $r["_source"]["trabalhoEmEventos"]["nomeDaEditora"] : '').'\$\$c'.$r["_source"]["datePublished"].'';
                                        } else {
                                            $record[] = '000000001 260   L \$\$a\$\$b\$\$c';
                                        }
                                        if (isset($r["_source"]["trabalhoEmEventos"])){
                                            $record[] = '000000001 300   L \$\$ap. -, res.';
                                        } elseif (isset($r["_source"]["artigoPublicado"])){
                                            $record[] = '000000001 300   L \$\$ap. -';
                                        } else {
                                            $record[] = '000000001 300   L \$\$a';
                                        }

                                        $record[] = '000000001 500   L \$\$a';

                                        if (isset($r["_source"]["artigoPublicado"])){
                                            $record[] = '000000001 5101  L \$\$aIndexado no:';
                                        }                                               
                                        
                                        $record[] = '000000001 650 7 L \$\$a';
                                        $record[] = '000000001 650 7 L \$\$a';
                                        $record[] = '000000001 650 7 L \$\$a';
                                        $record[] = '000000001 650 7 L \$\$a';
                                        
                                        if (isset($r["_source"]["trabalhoEmEventos"])){
                                            if (empty($r["_source"]["trabalhoEmEventos"]["cidadeDoEvento"])) {
                                                $r["_source"]["trabalhoEmEventos"]["cidadeDoEvento"] = "Não informado";
                                            }

                                            $record[] = '000000001 7112  L \$\$a'.$r["_source"]["trabalhoEmEventos"]["nomeDoEvento"].'\$\$d('.((isset($r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"]) && $r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"])? $r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"] : '').'\$\$c'.$r["_source"]["trabalhoEmEventos"]["cidadeDoEvento"].')';
                                            
                                            $record[] = '000000001 7730  L \$\$t'.((isset($r["_source"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"]) && $r["_source"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"])? $r["_source"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"] : '').'\$\$x'.((isset($r["_source"]["trabalhoEmEventos"]["isbn"]) && $r["_source"]["trabalhoEmEventos"]["isbn"])? $r["_source"]["trabalhoEmEventos"]["isbn"] : '').'\$\$hv. , n. , p.'.((isset($r["_source"]["trabalhoEmEventos"]["paginaInicial"]) && $r["_source"]["trabalhoEmEventos"]["paginaInicial"])? $r["_source"]["trabalhoEmEventos"]["paginaInicial"] : '').'-'.((isset($r["_source"]["trabalhoEmEventos"]["paginaFinal"]) && $r["_source"]["trabalhoEmEventos"]["paginaFinal"])? $r["_source"]["trabalhoEmEventos"]["paginaFinal"] : '').', '.((isset($r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"]) && $r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"])? $r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"] : '').'';
                                        }
                                        
                                        if (isset($r["_source"]["artigoPublicado"])){
                                            $record[] = '000000001 7730  L \$\$t'.$r["_source"]["artigoPublicado"]["tituloDoPeriodicoOuRevista"].'\$\$x'.$r["_source"]["artigoPublicado"]["issn"].'\$\$hv.'.((isset($r["_source"]["artigoPublicado"]["volume"]) && $r["_source"]["artigoPublicado"]["volume"])? $r["_source"]["artigoPublicado"]["volume"] : '').', n. '.((isset($r["_source"]["artigoPublicado"]["serie"]) && $r["_source"]["artigoPublicado"]["serie"])? $r["_source"]["artigoPublicado"]["serie"] : '').', p.'.((isset($r["_source"]["artigoPublicado"]["paginaInicial"]) && $r["_source"]["artigoPublicado"]["paginaInicial"])? $r["_source"]["artigoPublicado"]["paginaInicial"] : '').'-'.((isset($r["_source"]["artigoPublicado"]["paginaFinal"]) && $r["_source"]["artigoPublicado"]["paginaFinal"])? $r["_source"]["artigoPublicado"]["paginaFinal"] : '').', '.$r["_source"]["datePublished"].'';
                                        }                                            
                                        
                                        
                                        if (isset($r["_source"]['doi'])){                                            
                                            $record[] = '000000001 8564  L \$\$zClicar sobre o botão para acesso ao texto completo\$\$uhttps://dx.doi.org/'.$r["_source"]["doi"].'\$\$3DOI';           
                                        } else {
                                            $record[] = '000000001 8564  L \$\$zClicar sobre o botão para acesso ao texto completo\$\$u\$\$3DOI';
                                        }                          
                                        
                                        if (isset($r["_source"]["trabalhoEmEventos"])){
                                            $record[] = '000000001 945   L \$\$aP\$\$bTRABALHO DE EVENTO\$\$c10\$\$j'.$r["_source"]["datePublished"].'\$\$l';
                                        }
                                        if (isset($r["_source"]["artigoPublicado"])){
                                            $record[] = '000000001 945   L \$\$aP\$\$bARTIGO DE PERIODICO\$\$c01\$\$j'.$r["_source"]["datePublished"].'\$\$l';
                                        }                                            
                                        $record[] = '000000001 946   L \$\$a';
                                        
                                        $record_blob = implode("\\n", $record);
                                        
                                        echo '<h4>Exportar</h4>';
                                        echo '<p><button  class="ui blue label" onclick="SaveAsFile(\''.$record_blob.'\',\'aleph.seq\',\'text/plain;charset=utf-8\')">Baixar ALEPH Sequencial</button></p>';
                                        unset($record);
                                        unset($record_blob);
                                        
                                    ?> 
                                    </li>
                                    <li class="uk-h6">
                                        <a href="tools/export.php?search[]=_id:<?php echo $r['_id'] ?>&format=alephseq" class="uk-margin-top">Exportar Alephseq</a>
                                    </li>
                                    
                                    <p><a href="#" class="uk-margin-top" uk-toggle="target: #citacao<?php echo  $r['_id'];?>">Ver todos os dados deste registro</a></p>
                                    <div id="citacao<?php echo  $r['_id'];?>" hidden>                                        
                                        <li class="uk-h6"> 
                                            <table class="uk-table">
                                                <thead>
                                                    <tr>
                                                        <th>Nome do campo</th>
                                                        <th>Valor</th>
                                                    </tr>
                                                </thead>    
                                                <tbody>
                                                    <?php foreach ($r["_source"] as $key => $value) {
                                                            echo '<tr><td>'.$key.'</td><td>';
                                                            if (is_array($value)) {
                                                                foreach ($value as $valor) {
                                                                    if (is_array($valor)) {
                                                                            foreach ($valor as $valor1) {
                                                                                echo ''.$valor1.'';
                                                                            }
                                                                        } else {
                                                                            echo ''.$valor.''; 
                                                                        }
                                                                    }

                                                            } else {
                                                                echo ''.$value.'';
                                                            }
                                                            echo '</td>';
                                                            echo '</tr>';
                                                    };?>
                                                </tbody>
                                            </table>
                                        </li>
                                    </div>    
                                        
                                </ul>
                                </div>
                            </div>
                        </li>
                    <?php endforeach;?>
                    </ul>
                    </div>
                    <hr class="uk-grid-divider">
                    <div class="uk-grid uk-margin-top">
                        <div class="uk-width-1-2"><p class="uk-text-center"><?php print_r($total);?> registros</p></div>
                        <div class="uk-width-1-2">
                            <ul class="uk-pagination" data-uk-pagination="{items:<?php print_r($total);?>,itemsOnPage:<?php print_r($limit);?>,displayedPages:3,edges:1,currentPage:<?php print_r($page-1);?>}"></ul>                         
                        </div>
                    </div>                   
                    

                    
                </div>
            </div>
            <hr class="uk-grid-divider">
<?php include('inc/footer.php'); ?>          
        </div>
                


        <script>
        $('[data-uk-pagination]').on('select.uk.pagination', function(e, pageIndex){
            var url = window.location.href.split('&page')[0];
            window.location=url +'&page='+ (pageIndex+1);
        });
        </script>    

<?php include('inc/offcanvas.php'); ?>         
        
    </body>
</html>