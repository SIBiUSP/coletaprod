<!DOCTYPE html>
<?php
    include('inc/config.php'); 
    include('inc/functions.php');

    $result_get = analisa_get($_GET);
    $query_complete = $result_get['query_complete'];
    $query_aggregate = $result_get['query_aggregate'];
    //$escaped_url = $result_get['escaped_url'];
    $limit = $result_get['limit'];
    $page = $result_get['page'];
    //$new_get = $result_get['new_get'];
    $skip = $result_get['skip'];

    $params = [
        'index' => $index,
        'type' => 'trabalhos',
        'size'=> $limit,
        'from' => $skip,   
        'body' => $query_complete
    ];  
    
    $cursor = $client->search($params);    

    $total = $cursor["hits"]["total"];

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
        <?php include('inc/navbar.php'); ?>        
        
        <div class="uk-container uk-container-center">
            <div class="uk-grid" data-uk-grid>                        
                <div class="uk-width-small-1-2 uk-width-medium-2-6">                    
                    

<div class="uk-panel uk-panel-box">
    <form class="uk-form" method="get" action="result_trabalhos.php">
    <fieldset>
        
        <?php if (!empty($_GET["search"])) : ?>
        <legend>Filtros ativos</legend>
            <div class="uk-form-row">
                <?php foreach($_GET["search"] as $filters): ?>
                    <input type="checkbox" name="search[]" value="<?php print_r(str_replace('"','&quot;',$filters)); ?>" checked><?php print_r($filters); ?><br/>
                <?php endforeach; ?>
            </div>
        <div class="uk-form-row"><button type="submit" class="uk-button-primary">Retirar filtros</button></div>
        <?php endif;?> 
    </fieldset>        
    </form>    
    <hr>
    <h3 class="uk-panel-title">Refinar meus resultados</h3>    
    <ul class="uk-nav uk-nav-side uk-nav-parent-icon uk-margin-top" data-uk-nav="{multiple:true}">
        <hr>
    <?php
        $facets = new facets();
        $facets->query_aggregate = $query_aggregate;
        
        $facets->facet("natureza",10,"Natureza",null);
        $facets->facet("tipo",10,"Tipo de material",null);
        $facets->facet("tag",10,"Tag",null);
        
        $facets->facet("autores.nomeCompletoDoAutor",100,"Nome completo do autor",null);
        $facets->facet("lattes_ids",100,"Número do lattes",null);
        $facets->facet("codpes",100,"Número USP",null);
        $facets->facet("unidadeUSP",100,"Unidade USP",null);
        
        echo '<hr><li>Informações da publicação</li>';
        $facets->facet("pais",200,"País de publicação",null);
        $facets->facet("ano",120,"Ano de publicação","desc");
        $facets->facet("idioma",40,"Idioma",null);
        $facets->facet("meioDeDivulgacao",100,"Meio de divulgação",null);
        $facets->facet("palavras_chave",100,"Palavras-chave",null);
        $facets->facet("agencia_de_fomento",100,"Agências de fomento",null);
        $facets->facet("citacoes_recebidas",100,"Citações recebidas",null);
        
        echo '<hr><li>Área do conhecimento</li>';
        $facets->facet("area_do_conhecimento.nomeGrandeAreaDoConhecimento",100,"Nome da Grande Área do Conhecimento",null);
        $facets->facet("area_do_conhecimento.nomeDaAreaDoConhecimento",100,"Nome da Área do Conhecimento",null);
        $facets->facet("area_do_conhecimento.nomeDaSubAreaDoConhecimento",100,"Nome da Sub Área do Conhecimento",null);
        $facets->facet("area_do_conhecimento.nomeDaEspecialidade",100,"Nome da Especialidade",null);
        
        echo '<hr><li>Eventos</li>';
        $facets->facet("trabalhoEmEventos.classificacaoDoEvento",100,"Classificação do evento",null); 
        $facets->facet("trabalhoEmEventos.nomeDoEvento",100,"Nome do evento",null);
        $facets->facet("trabalhoEmEventos.cidadeDoEvento",100,"Cidade do evento",null);
        $facets->facet("trabalhoEmEventos.anoDeRealizacao",100,"Ano de realização do evento",null);
        $facets->facet("trabalhoEmEventos.tituloDosAnaisOuProceedings",100,"Título dos anais",null);
        $facets->facet("trabalhoEmEventos.isbn",100,"ISBN dos anais",null);
        $facets->facet("trabalhoEmEventos.nomeDaEditora",100,"Editora dos anais",null);
        $facets->facet("trabalhoEmEventos.cidadeDaEditora",100,"Cidade da editora",null);
        
        echo '<hr><li>Periódicos</li>';
        $facets->facet("artigoPublicado.tituloDoPeriodicoOuRevista",100,"Título do periódico",null);   
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
                $( "#date" ).val( "ano:[" + ui.values[ 0 ] + " TO " + ui.values[ 1 ] + "]" );
              }
            });
            $( "#date" ).val( "ano:[" + $( "#limitar-data" ).slider( "values", 0 ) +
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
    <?php if(!empty($_SESSION['oauthuserdata'])): ?>
            <fieldset>
                <legend>Gerar relatório</legend>                  
                <div class="uk-form-row"><a href="<?php echo 'http://'.$_SERVER["SERVER_NAME"].'/~bdpi/report.php?'.$_SERVER["QUERY_STRING"].''; ?>" class="uk-button-primary">Gerar relatório</a>
                </div>
            </fieldset>        
    <?php endif; ?>                
            
</div>
    
                    
                </div>
                <div class="uk-width-small-1-2 uk-width-medium-4-6">
                    
        
                    <div class="uk-grid uk-margin-top">
                        <div class="uk-width-1-3"> 
                            
                        </div>
                        <div class="uk-width-1-3"><p class="uk-text-center"><?php print_r(number_format($total,0,',','.'));?> registros</p></div>
                        <div class="uk-width-1-3">
                            <ul class="uk-pagination" data-uk-pagination="{items:<?php print_r($total);?>,itemsOnPage:<?php print_r($limit);?>,displayedPages:3,edges:1,currentPage:<?php print_r($page-1);?>}"></ul>                         
                        </div>
                    </div>
                    
                    <hr class="uk-grid-divider">
                    <div class="uk-width-1-1 uk-margin-top uk-description-list-line">
                    <ul class="uk-list uk-list-line">   
                    <?php foreach ($cursor["hits"]["hits"] as $r) : ?>
                    
                        <li>                        
                            <div class="uk-grid uk-flex-middle" data-uk-grid-   margin="">
                                <div class="uk-width-medium-2-10 uk-row-first">
                                    <div class="uk-panel uk-h6 uk-text-break">
                                        <a href="result_trabalhos.php?type[]=<?php echo $r["_source"]['tipo'];?>"><?php echo ucfirst(strtolower($r["_source"]['tipo']));?></a>
                                    </div>
                                    
                                </div>
                                <div class="uk-width-medium-8-10 uk-flex-middle">
                                    
                                    <ul class="uk-list">
                                        <li class="uk-margin-top uk-h4">
                                            <strong><?php echo ($r["_source"]['titulo']);?> (<?php echo $r["_source"]['ano']; ?>)</strong>
                                        </li>
                                        <li class="uk-h6">
                                            Autores:
                                            <?php if (!empty($r["_source"]['autores'])) : ?>
                                            <?php foreach ($r["_source"]['autores'] as $autores) {
                                                $authors_array[]='<a href="result_trabalhos.php?search[]=autores.nome_completo_do_autor.keyword:&quot;'.$autores["nomeCompletoDoAutor"].'&quot;">'.$autores["nomeCompletoDoAutor"].'</a>';
                                            } 
                                           $array_aut = implode(", ",$authors_array);
                                            unset($authors_array);
                                            print_r($array_aut);
                                            ?>
                                            
                                           
                                            <?php endif; ?>                           
                                        </li>
                                        
                                        <?php if (!empty($r["_source"]['periodico'])) : ?>
                                            <li class="uk-h6">In: <a href="result_trabalhos.php?search[]=periodico.titulo_do_periodico.keyword:&quot;<?php echo $r["_source"]['periodico']['titulo_do_periodico'];?>&quot;"><?php echo $r["_source"]['periodico']['titulo_do_periodico'];?></a></li>                                        
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($r["_source"]['doi'])) : ?>
                                            <li class="uk-h6">DOI: <a href="https://dx.doi.org/<?php echo $r["_source"]['doi'];?>"><?php echo $r["_source"]['doi'];?></a></li>                                        
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
                                                dadosExternos::query_bdpi($r["_source"]['titulo'],$r["_source"]['ano']);
                                              }
                                        ?>                                       

                                        
                                        <li class="uk-h6">
                                        <?php
                                            
                                            //print_r($r["_source"]);
                                            
                                            $author_number = count($r["_source"]['autores']);
                                            
                                            $record = [];
                                            $record[] = "000000001 FMT   L BK";
                                            $record[] = "000000001 LDR   L ^^^^^nam^^22^^^^^Ia^4500";
                                            $record[] = "000000001 BAS   L \$\$a04";
                                            $record[] = "000000001 008   L ^^^^^^s^^^^^^^^^^^^^^^^^^^^^^000^0^^^^^d";
                                            if (isset($r["_source"]['doi'])){
                                                $record[] = '000000001 0247  L \$\$a'.$r["_source"]["doi"].'\$\$2DOI';         
                                            } 
                                            $record[] = "000000001 040   L \$\$aUSP/SIBI";
                                            $record[] = '000000001 0410  L \$\$a';
                                            $record[] = '000000001 044   L \$\$a';
                                            if ($author_number > 1) {
                                                $record[] = '000000001 1001  L \$\$a'.$r["_source"]['autores'][0]["nomeCompletoDoAutor"].'';
                                                for ($i = 1; $i < $author_number; $i++) {
                                                    $record[] = '000000001 7001  L \$\$a'.$r["_source"]['autores'][$i]["nomeCompletoDoAutor"].'';
                                                }
                                            } else {
                                                $record[] = '000000001 1001  L \$\$a'.$r["_source"]['autores'][0]["nomeCompletoDoAutor"].'';
                                            }                                            
                                            $record[] = '000000001 24510 L \$\$a'.$r["_source"]["titulo"].'';                                            
                                            if (isset($r["_source"]["trabalhoEmEventos"])){  
                                                $record[] = '000000001 260   L \$\$a'.$r["_source"]["trabalhoEmEventos"]["cidadeDaEditora"].'\$\$b'.$r["_source"]["trabalhoEmEventos"]["nomeDaEditora"].'\$\$c'.$r["_source"]["ano"].'';
                                            }
                                            if (isset($r["_source"]["trabalhoEmEventos"])){
                                                $record[] = '000000001 300   L \$\$ap. -, res.';
                                            }                                            
                                            if (isset($r["_source"]["artigoPublicado"])){
                                                $record[] = '000000001 300   L \$\$ap. -';
                                            }
                                            if (isset($r["_source"]["artigoPublicado"])){
                                                $record[] = '000000001 5101  L \$\$aIndexado no:';
                                            }                                               
                                            
                                            $record[] = '000000001 650 7 L \$\$a';
                                            $record[] = '000000001 650 7 L \$\$a';
                                            $record[] = '000000001 650 7 L \$\$a';
                                            $record[] = '000000001 650 7 L \$\$a';
                                            
                                            if (isset($r["_source"]["trabalhoEmEventos"])){
                                                $record[] = '000000001 7112  L \$\$a'.$r["_source"]["trabalhoEmEventos"]["nomeDoEvento"].'\$\$d('.$r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"].'\$\$c'.$r["_source"]["trabalhoEmEventos"]["cidadeDoEvento"].')';
                                                
                                                $record[] = '000000001 7730  L \$\$t'.$r["_source"]["trabalhoEmEventos"]["tituloDosAnaisOuProceedings"].'\$\$x'.$r["_source"]["trabalhoEmEventos"]["isbn"].'\$\$hv. , n. , p.'.$r["_source"]["trabalhoEmEventos"]["paginaInicial"].'-'.$r["_source"]["trabalhoEmEventos"]["paginaFinal"].', '.$r["_source"]["trabalhoEmEventos"]["anoDeRealizacao"].'';
                                            }
                                            
                                            if (isset($r["_source"]["artigoPublicado"])){
                                                $record[] = '000000001 7730  L \$\$t'.$r["_source"]["artigoPublicado"]["tituloDoPeriodicoOuRevista"].'\$\$x'.$r["_source"]["artigoPublicado"]["issn"].'\$\$hv.'.$r["_source"]["artigoPublicado"]["volume"].', n. '.$r["_source"]["artigoPublicado"]["serie"].', p.'.$r["_source"]["artigoPublicado"]["paginaInicial"].'-'.$r["_source"]["artigoPublicado"]["paginaFinal"].', '.$r["_source"]["ano"].'';
                                            }                                            
                                            
                                            
                                            if (isset($r["_source"]['doi'])){                                            
                                                $record[] = '000000001 8564  L \$\$zClicar sobre o botão para acesso ao texto completo\$\$uhttps://dx.doi.org/'.$r["_source"]["doi"].'\$\$3DOI';           
                                            }                           
                                            
                                            if (isset($r["_source"]["trabalhoEmEventos"])){
                                                $record[] = '000000001 945   L \$\$aP\$\$bTRABALHO DE EVENTO\$\$c10\$\$j'.$r["_source"]["ano"].'\$\$l';
                                            }
                                            if (isset($r["_source"]["artigoPublicado"])){
                                                $record[] = '000000001 945   L \$\$aP\$\$bARTIGO DE PERIODICO\$\$c01\$\$j'.$r["_source"]["ano"].'\$\$l';
                                            }                                            
                                            $record[] = '000000001 946   L \$\$a';
                                            
                                            $record_blob = implode("\\n", $record);
                                            
                                            echo '<h4>Exportar</h4>';
                                            echo '<p><button  class="ui blue label" onclick="SaveAsFile(\''.$record_blob.'\',\'aleph.seq\',\'text/plain;charset=utf-8\')">Baixar ALEPH Sequencial</button></p>';
                                            unset($record);
                                            unset($record_blob);
                                            
                                        ?> 
                                        </li>
                                        
                                        
                                        <p><a href="#" class="uk-margin-top" data-uk-toggle="{target:'#citacao<?php echo  $r['_id'];?>'}">Ver todos os dados deste registro</a></p>
                                        <div id="citacao<?php echo  $r['_id'];?>" class="uk-hidden">                                        
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