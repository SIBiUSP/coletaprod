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
        'type' => 'teses',
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
        $facets_teses = new facets_teses();
        $facets_teses->query_aggregate = $query_aggregate;
        
        $facets_teses->facet_tese("tese.nivel",10,"Nível",null);
        $facets_teses->facet_tese("tese.nomeInstituicao",10,"Instituição",null);
        $facets_teses->facet_tese("tese.nomeCurso",10,"Nome do Curso",null);
        $facets_teses->facet_tese("tese.anoDeConclusao",10,"Ano de conclusão",null);
        $facets_teses->facet_tese("tese.nomeDoOrientador",10,"Nome do orientador",null);
        $facets_teses->facet_tese("tese.flagBolsa",10,"Teve bolsa?",null);
        $facets_teses->facet_tese("tese.nomeAgencia",10,"Agência de fomento",null);  
        $facets_teses->facet_tese("tese.conceitoCapes",10,"Conceito CAPES",null);

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
                                        <a href="result_trabalhos.php?type[]=<?php echo $r["_source"]["tese"]["nivel"];?>"><?php echo ucfirst(strtolower($r["_source"]["tese"]["nivel"]));?></a>
                                    </div>
                                    
                                </div>
                                <div class="uk-width-medium-8-10 uk-flex-middle">
                                    
                                    <ul class="uk-list">
                                        <li class="uk-margin-top uk-h4">
                                            <strong>
<?php if (isset($r["_source"]["tese"]["tituloDoTrabalhoDeConclusaoDeCurso"])){
            echo ($r["_source"]["tese"]["tituloDoTrabalhoDeConclusaoDeCurso"]);
      } elseif (isset($r["_source"]["tese"]["tituloDaDissertacaoTese"])){
            echo ($r["_source"]["tese"]["tituloDaDissertacaoTese"]);
      } else {
            echo "Sem título cadastrado";
    }                                       
?> (<?php echo $r["_source"]["tese"]["anoDeConclusao"]; ?>)</strong>
                                        </li>
                                        
                                        <?php if (!empty($r["_source"]["tese"]["autor"])) : ?>
                                            <li class="uk-h6">Autor: <a href="#"><?php echo $r["_source"]["tese"]["autor"];?></a></li>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty($r["_source"]["tese"]["nomeInstituicao"])) : ?>
                                            <li class="uk-h6">Instituição: <a href="#"><?php echo $r["_source"]["tese"]["nomeInstituicao"];?></a></li>
                                        <?php endif; ?>
                                        <?php if (!empty($r["_source"]["tese"]["nomeCurso"])) : ?>
                                            <li class="uk-h6">Nome do curso: <a href="#"><?php echo $r["_source"]["tese"]["nomeCurso"];?></a></li>
                                        <?php endif; ?>
                                        <?php if (!empty($r["_source"]["tese"]["nomeDoOrientador"])) : ?>
                                            <li class="uk-h6">Nome do orientador: <a href="#"><?php echo $r["_source"]["tese"]["nomeDoOrientador"];?></a></li>
                                        <?php endif; ?>                                         
                                        <?php if (!empty($r["_source"]["tese"]["nomeAgencia"])) : ?>
                                            <li class="uk-h6">Agência de fomento: <a href="#"><?php echo $r["_source"]["tese"]["nomeAgencia"];?></a></li>
                                        <?php endif; ?>
                                        <?php if (!empty($r["_source"]["tese"]["conceitoCapes"])) : ?>
                                            <li class="uk-h6">Conceito CAPES: <a href="#"><?php echo $r["_source"]["tese"]["conceitoCapes"];?></a></li>
                                        <?php endif; ?>                                             
                         
                                    
                                        
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