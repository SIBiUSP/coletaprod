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
        'index' => 'lattes',
        'type' => 'curriculo',
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
        <title>Lattes USP - Resultado da busca</title>
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
        $facets_users = new facets_users();
        $facets_users->query_aggregate = $query_aggregate;
                
        $facets_users->facet_user("id_usp",100,"Número USP",null);
        $facets_users->facet_user("nacionalidade",100,"Nacionalidade",null);
        $facets_users->facet_user("pais_de_nascimento",100,"País de nascimento",null);
                
        $facets_users->facet_user("endereco_profissional.nome_instituicao_empresa",100,"Nome da Instituição ou Empresa",null);
        $facets_users->facet_user("endereco_profissional.nome_orgao",100,"Nome do orgão",null);
        $facets_users->facet_user("endereco_profissional.nome_unidade",100,"Nome da unidade",null);
        $facets_users->facet_user("endereco_profissional.pais",100,"País do endereço profissional",null);
        $facets_users->facet_user("endereco_profissional.cidade",100,"Cidade do endereço profissional",null);
        
        $facets_users->facet_user("data_atualizacao",100,"Data de atualização do currículo",null);
        
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

                                    </div>
                                    
                                </div>
                                <div class="uk-width-medium-8-10 uk-flex-middle">
                                    
                                    <ul class="uk-list">
                                        <li class="uk-margin-top uk-h4">
                                            <strong><?php echo '<a href="result_trabalhos.php?search[]=id_usp.keyword:&quot;'.$r["_source"]["id_usp"].'&quot;">'.$r["_source"]['nome_completo'].'</a>';?></strong>
                                        </li>
                                        <?php if (isset($r["_source"]['resumo_cv'])): ?>
                                        <li>                                            
                                            <p><?php echo '<strong>Resumo informado pelo autor:</strong> '.$r["_source"]['resumo_cv']['texto_resumo_cv_rh'].''; ?></p>
                                        </li>
                                        <?php endif; ?>
                                                                                
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