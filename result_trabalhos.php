<!DOCTYPE html>
<?php

require 'inc/config.php'; 
require 'inc/functions.php';

if (!empty($_POST)) {
    foreach ($_POST as $key=>$value) {            
        $var_concluido["doc"]["concluido"] = $value;
        $var_concluido["doc"]["doc_as_upsert"] = true; 
        elasticsearch::elastic_update($key, $type, $var_concluido);
    }
    sleep(6);
    header("Refresh:0");
}
if (isset($_GET["filter"])) {
    if (!in_array("type:\"Work\"", $_GET["filter"])) {
        $_GET["filter"][] = "type:\"Work\"";
    }
} else {
    $_GET["filter"][] = "type:\"Work\"";
}
$result_get = get::analisa_get($_GET);
$query = $result_get['query'];
$limit = $result_get['limit'];
$page = $result_get['page'];
$skip = $result_get['skip'];

//$query['sort'] = [
//    ['datePublished' => ['order' => 'desc']],
//];

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
	    
        <!-- List of filters - Start -->
        <?php if (!empty($_SERVER["QUERY_STRING"])) : ?>
        <p class="uk-margin-top" uk-margin>
            <a class="uk-button uk-button-default uk-button-small" href="index.php"><?php echo $t->gettext('Começar novamente'); ?></a>	
            <?php 
            if (!empty($_GET["search"])) {
                foreach ($_GET["search"] as $querySearch) {
                    $querySearchArray[] = $querySearch;
                    $name_field = explode(":", $querySearch);
                    $querySearch = str_replace($name_field[0].":", "", $querySearch);
                    $diff["search"] = array_diff($_GET["search"], $querySearchArray);
                    $url_push = $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"].'?'.http_build_query($diff);
                    echo '<a class="uk-button uk-button-default uk-button-small" href="http://'.$url_push.'">'.$querySearch.' <span uk-icon="icon: close; ratio: 1"></span></a>';
                    unset($querySearchArray);
                }
            }
                
            if (!empty($_GET["filter"])) {
                foreach ($_GET["filter"] as $filters) {
                    $filters_array[] = $filters;
                    $name_field = explode(":", $filters);
                    $filters = str_replace($name_field[0].":", "", $filters);
                    $diff["filter"] = array_diff($_GET["filter"], $filters_array);
                    $url_push = $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"].'?'.http_build_query($diff);
                    echo '<a class="uk-button uk-button-primary uk-button-small" href="http://'.$url_push.'">Filtrado por: '.$filters.' <span uk-icon="icon: close; ratio: 1"></span></a>';
                    unset($filters_array);
                }
            }
            
            if (!empty($_GET["notFilter"])) {
                foreach ($_GET["notFilter"] as $notFilters) {
                    $notFiltersArray[] = $notFilters;
                    $name_field = explode(":", $notFilters);
                    $notFilters = str_replace($name_field[0].":", "", $notFilters);
                    $diff["notFilter"] = array_diff($_GET["notFilter"], $notFiltersArray);
                    $url_push = $_SERVER['SERVER_NAME'].$_SERVER["SCRIPT_NAME"].'?'.http_build_query($diff);
                    echo '<a class="uk-button uk-button-danger uk-button-small" href="http://'.$url_push.'">Ocultando: '.$notFilters.' <span uk-icon="icon: close; ratio: 1"></span></a>';
                    unset($notFiltersArray);
                }
            }                 
            ?>
            
        </p>
        <?php endif;?> 
        <!-- List of filters - End -->
	    
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
                    $facets->facet("bdpi.existe", 100, "Está no DEDALUS?", null, "_term", $_GET["search"]);

                ?>
                </ul>
                    <?php if (!empty($_SESSION['oauthuserdata'])) : ?>
                        <h3 class="uk-panel-title uk-margin-top">Informações administrativas</h3>
                        <ul class="uk-nav uk-nav-side uk-nav-parent-icon uk-margin-top" data-uk-nav="{multiple:true}">
                        <hr>
                        <?php         

                        ?>
                        </ul>
                    <?php endif; ?>
                <hr>
                        <!-- Limitar por data - Início -->
                        <form class="uk-text-small">
                            <fieldset>
                                <legend><?php echo 'Limitar por data'; ?></legend>
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
                                <label for="date"><?php echo 'Selecionar período de tempo'; ?>:</label>
                                <input class="uk-input" type="text" id="date" readonly style="border:0; color:#f6931f;" name="search[]">
                                </p>        
                                <div id="limitar-data" class="uk-margin-bottom"></div>
                                <?php if (!empty($_GET["search"])) : ?>
                                    <?php foreach($_GET["search"] as $search_expression): ?>
                                        <input type="hidden" name="search[]" value="<?php echo str_replace('"', '&quot;', $search_expression); ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                <?php if (!empty($_GET["filter"])) : ?>
                                    <?php foreach($_GET["filter"] as $filter_expression): ?>
                                        <input type="hidden" name="filter[]" value="<?php echo str_replace('"', '&quot;', $filter_expression); ?>">
                                    <?php endforeach; ?>
                                <?php endif; ?>                                
                                <button class="uk-button uk-button-primary uk-button-small"><?php echo 'Limitar datas'; ?></button>
                            </fieldset>        
                        </form>
                        <!-- Limitar por data - Fim -->
                <hr>     
                        
            </div>
        </div>
                
        <div class="uk-width-3-4@s uk-width-4-6@m">
        
            <!-- Navegador de resultados - Início -->
            <?php ui::pagination($page, $total, $limit, $t); ?>
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
                                            $authors_array[]='<a href="result_trabalhos.php?filter[]=author.person.name:&quot;'.$autores["person"]["name"].'&quot;">'.$autores["person"]["name"].'</a>';
                                        } 
                                        $array_aut = implode(", ",$authors_array);
                                        unset($authors_array);
                                        print_r($array_aut);
                                        ?>
                                        
                                        
                                        <?php endif; ?>                           
                                    </li>
                                    
                                    <?php if (!empty($r["_source"]['artigoPublicado'])) : ?>
                                        <li class="uk-h6">In: <a href="result_trabalhos.php?filter[]=periodico.titulo_do_periodico:&quot;<?php echo $r["_source"]['artigoPublicado']['tituloDoPeriodicoOuRevista'];?>&quot;"><?php echo $r["_source"]['artigoPublicado']['tituloDoPeriodicoOuRevista'];?></a></li>
                                        <li class="uk-h6">ISSN: <a href="result_trabalhos.php?filter[]=periodico.issn:&quot;<?php echo $r["_source"]['artigoPublicado']['issn'];?>&quot;"><?php echo $r["_source"]['artigoPublicado']['issn'];?></a></li>                                        
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($r["_source"]['doi'])) : ?>
                                        <li class="uk-h6"><p>DOI: <a href="https://doi.org/<?php echo $r["_source"]['doi'];?>"><?php echo $r["_source"]['doi'];?></a></p>
                                        <p><a href="doi_to_elastic.php?doi=<?php echo $r['_source']['doi'];?>&tag=<?php echo $r['_source']['tag'][0];?>">Coletar dados da Crossref</a></p></li>                                        
                                    <?php endif; ?>                                        
                                    
                                    <li class="uk-h6">
                                        Assuntos:
                                        <?php if (!empty($r["_source"]['palavras_chave'])) : ?>
                                        <?php foreach ($r["_source"]['palavras_chave'] as $assunto) : ?>
                                            <a href="result_trabalhos.php?filter[]=palavras_chave:&quot;<?php echo $assunto;?>&quot;"><?php echo $assunto;?></a>
                                        <?php endforeach;?>
                                        <?php endif; ?>
                                    </li>
                                    
                                    <?php if (!empty($r["_source"]['ids_match'])) : ?>  
                                    <?php foreach ($r["_source"]['ids_match'] as $id_match) : ?>
                                        <?php compararRegistros::match_id($id_match["id_match"], $id_match["nota"]);?>
                                    <?php endforeach;?>
                                    <?php endif; ?>
                                    
                                    <?php 
                                    if ($instituicao == "USP") {
                                        DadosExternos::query_bdpi($r["_source"]['name'], $r["_source"]['datePublished'], $r['_id']);
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
                                    
                                    if (isset($dspaceRest)) { 
                                        echo '<form action="dspaceConnect.php" method="get">
                                              <input type="hidden" name="createRecord" value="true" />
                                              <input type="hidden" name="_id" value="'.$r['_id'].'" />
                                              <button class="uk-button uk-button-danger" name="btn_submit">Criar registro no DSpace</button>
                                              </form>';  
                                    }
                                    
                                    ?>
                                    </li>
                                    <li class="uk-h6">
                                        <a href="tools/export.php?search[]=_id:<?php echo $r['_id'] ?>&format=alephseq" class="uk-margin-top">Exportar Alephseq</a>
                                    </li>
                                    <li class="uk-h6">
                                        <a href="editor.php?_id=<?php echo $r['_id'] ?>" class="uk-margin-top">Editar registro</a>
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
                                                                                //echo ''.$valor1.'';
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
                    <!-- Navegador de resultados - Início -->
                    <?php ui::pagination($page, $total, $limit, $t); ?>
                    <!-- Navegador de resultados - Fim --> 
                    
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