<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
    <head>
        <?php 
            include('inc/config.php');             
            include('inc/meta-header.php');
            include('inc/functions.php');
            
            /* Define variables */
            define('authorUSP','authorUSP');
        ?> 
        <title>Coleta Produção USP</title>
        <!-- Facebook Tags - START -->
        <meta property="og:locale" content="pt_BR">
        <meta property="og:url" content="http://coletaprod.sibi.usp.br/coletaprod">
        <meta property="og:title" content="Coleta Produção USP - Página Principal">
        <meta property="og:site_name" content="Coleta Produção USP">
        <meta property="og:description" content="Sistema de coleta de produção em diversas fontes.">
        <meta property="og:image" content="http://www.imagens.usp.br/wp-content/uploads/USP.jpg">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="800"> 
        <meta property="og:image:height" content="600"> 
        <meta property="og:type" content="website">
        <!-- Facebook Tags - END -->
        
    </head>

    <body>     
              
<div class="uk-container">

    <h1 class="uk-heading-line uk-text-center uk-margin-top"><span>Coleta Produção USP</span></h1>
    <p>Coleta produção de diversas fontes para preenchimento do Cadastro de Produção Intelectual, para uso interno das Bibliotecas da Universidade de São Paulo</p>
    <br/><br/>
    <ul class="uk-subnav uk-subnav-pill" uk-switcher>
        <li><a href="#">Pesquisa</a></li>
        <li><a href="#">Inclusão</a></li>
    </ul>

    <ul class="uk-switcher uk-margin">
        <li>
            <form class="uk-form-stacked" action="result_trabalhos.php" method="get">
                <div class="uk-margin" uk-grid>
                    <label class="uk-form-label" for="form-stacked-text">Pesquisa por trabalho - <a href="result_trabalhos.php">Ver todos</a></label>                    
                    <div class="uk-form-controls">
                        <input type="text" placeholder="Pesquise por termo ou título" class="uk-input uk-form-width-large" name="search[]">
                    </div>
                    <div>
                        <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">Buscar</button>
                    </div>
                </div>
            </form>
            <form class="uk-form-stacked" action="result_trabalhos.php" method="get">
                <div class="uk-margin" uk-grid>
                    <label class="uk-form-label" for="form-stacked-text">Pesquisa por TAG</label>
                    <div class="uk-form-controls">
                        <input type="text" placeholder="Pesquise por tag" class="uk-input uk-form-width-large" name="filter[]" value="tag:">
                    </div>
                    <div>
                        <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">Buscar TAG</button>
                    </div>    
            </form>
            <form class="uk-form-stacked" action="result_trabalhos.php" method="get">
                <div class="uk-margin" uk-grid>
                    <label class="uk-form-label" for="form-stacked-text">Pesquisa por Número USP</label>
                    <div class="uk-form-controls">
                        <input type="text" placeholder="Pesquise por Número USP" class="uk-input uk-form-width-large" name="filter[]" value="USP.codpes:">
                    </div>
                    <div>
                        <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">Buscar Número USP</button>
                    </div>    
            </form>            
            <br/>
            <form class="uk-form-stacked" action="result_autores.php" method="get">
                <div class="uk-margin" uk-grid>
                    <label class="uk-form-label" for="form-stacked-text">Pesquisa por autor - <a href="result_autores.php">Ver todos</a></label>
                    <div class="uk-form-controls">
                        <input type="text" placeholder="Pesquise por nome do autor ou número USP" class="uk-input uk-form-width-large" name="search[]">
                        <input type="hidden" name="fields[]" value="nome_completo">                                
                        <input type="hidden" name="fields[]" value="nome_em_citacoes_bibliograficas">
                        <input type="hidden" name="fields[]" value="endereco.endereco_profissional.nomeInstituicaoEmpresa">                                            
                    </div>
                    <div>
                        <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom">Buscar</button>                
                    </div>    
                </div>
            </form> 
            <p><a href="result_trabalhos.php?notFilter[]=doi:%22%22&search[]=-_exists_:bdpi.doi_bdpi&filter[]=bdpi.existe:%22Sim%22">Inconsistência: Trabalhos com DOI no Lattes e não preenchido no DEDALUS</a></p>       
        </li>
        <li>
        
<form class="uk-form" action="lattes_json_to_elastic.php" method="get">
    <fieldset data-uk-margin>
        <legend>Inserir ID do Currículo Lattes que deseja incluir</legend>
        <input type="text" placeholder="Insira o ID do Curriculo" class="uk-form-width-medium" name="id_lattes" data-validation="required">
        <input type="text" placeholder="TAG para formar um grupo" class="uk-form-width-medium" name="tag">
        <input type="text" placeholder="Número USP" class="uk-form-width-medium" name="codpes">
        <input type="text" placeholder="Unidade USP" class="uk-form-width-medium" name="unidadeUSP">
        <button class="uk-button-primary">Incluir</button><br/>                                    
    </fieldset>
</form>
<br/>
<form class="uk-form" action="lattes_xml_to_elastic.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <fieldset data-uk-margin>
        <legend>Inserir XML do Currículo Lattes que deseja incluir</legend>
        <input type="file" name="file">
        <input type="text" placeholder="TAG para formar um grupo" class="uk-form-width-medium" name="tag">
        <input type="text" placeholder="Número USP" class="uk-form-width-medium" name="codpes">
        <input type="text" placeholder="Unidade USP" class="uk-form-width-medium" name="unidadeUSP">
        <button class="uk-button-primary">Incluir</button><br/>                                    
    </fieldset>
</form>
<br/>
<form class="uk-form" action="doi_to_elastic.php" method="get">
    <fieldset data-uk-margin>
        <legend>Inserir um DOI de artigo que queira incluir (sem http://doi.org/)</legend>
        <input type="text" placeholder="Insira um DOI" class="uk-form-width-medium" name="doi" data-validation="required">
        <input type="text" placeholder="TAG para formar um grupo" class="uk-form-width-medium" name="tag">
        <button class="uk-button-primary">Incluir</button><br/>                                    
    </fieldset>
</form>
<br/>
<form class="uk-form" action="wos_upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <fieldset data-uk-margin>
        <legend>Enviar um arquivo da Web of Science (UTF-8, separado por tabulações)</legend>
        <input type="file" name="file">
        <input type="text" placeholder="Tag para formar um grupo" class="uk-form-width-medium" name="tag">
        <button class="uk-button-primary" name="btn_submit">Upload</button><br/>                                    
    </fieldset>
</form>                         
<br/>
<form class="uk-form" action="incites_upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <fieldset data-uk-margin>
        <legend>Enviar um arquivo do INCITES (CSV)</legend>
        <input type="file" name="file">
        <input type="text" placeholder="Tag para formar um grupo" class="uk-form-width-medium" name="tag">
        <button class="uk-button-primary" name="btn_submit">Upload</button><br/>                                    
    </fieldset>
</form>
<br/>
<form class="uk-form" action="scopus_upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <fieldset data-uk-margin>
        <legend>Enviar um arquivo do Scopus (CSV - All available information)</legend>
        <input type="file" name="file">
        <input type="text" placeholder="Tag para formar um grupo" class="uk-form-width-medium" name="tag">
        <button class="uk-button-primary" name="btn_submit">Upload</button><br/>                                    
    </fieldset>
</form>
<br/>
<form class="uk-form" action="scival_upload.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <fieldset data-uk-margin>
        <legend>Enviar um arquivo do SCIVAL (CSV - All available information)</legend>
        <input type="file" name="file">
        <input type="text" placeholder="Tag para formar um grupo" class="uk-form-width-medium" name="tag">
        <button class="uk-button-primary" name="btn_submit">Upload</button><br/>                                    
    </fieldset>
</form>
<br/>
<form class="uk-form" action="harvester_oai.php" method="get" accept-charset="utf-8" enctype="multipart/form-data">
    <fieldset data-uk-margin>
        <legend>Incluir um URL OAI-PMH</legend>
        <input type="text" placeholder="Insira um URL OAI válido" class="uk-form-width-medium" name="oai" data-validation="required">
        <input type="text" placeholder="Tag para formar um grupo" class="uk-form-width-medium" name="tag">
        <button class="uk-button-primary" name="btn_submit">Incluir</button><br/>                                    
    </fieldset>
</form>
<br/>
<form class="uk-form-stacked" action="z3950.php" method="get" accept-charset="utf-8">
    <div class="uk-margin" uk-grid>
    <label class="uk-form-label" for="form-stacked-text">Consulta no Z39.50</a></label>
        <div class="uk-form-controls">
            <input type="text" placeholder="Insira um ISBN válido" class="uk-input uk-form-width-large" name="isbn" size="13"><br/>
            <input type="text" placeholder="Ou número do sistema" class="uk-input uk-form-width-large" name="sysno" size="13"><br/>
            <input type="text" placeholder="Ou pesquisar por título" class="uk-input uk-form-width-large" name="title" size="200"><br/>
            <input type="text" placeholder="e autor" class="uk-input uk-form-width-large" name="author" size="100"><br/>
            <input type="text" placeholder="e ano" class="uk-input uk-form-width-large" name="year" size="4"><br/>
        </div>
        <div>    
            <button class="uk-button uk-button-primary uk-width-1-1 uk-margin-small-bottom" name="btn_submit">Pesquisa Z39.50</button><br/>
        </div>
        <div><p>A busca só aceita 2 critérios simultâneos nos campos de titulo, autor e ano</p></div>                                
    </div>
</form>
<br/>
<form class="uk-form" action="grobid.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <fieldset data-uk-margin>
        <legend>PDF para Aleph Sequencial</legend>
        <input type="file" name="file">        
        <button class="uk-button-primary" name="btn_submit">Upload</button><br/>                                    
    </fieldset>
</form>
<br/>
<form class="uk-form" action="grobid.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <fieldset data-uk-margin>
        <legend>URL de PDF para Aleph Sequencial</legend>
        <input type="text" placeholder="Insira um URL de PDF válido" class="uk-form-width-medium" name="url" data-validation="required">
        <button class="uk-button-primary" name="btn_submit">Incluir</button><br/>                         
    </fieldset>
</form>
<br/>
<form class="uk-form" action="grobidtojats.php" method="post" accept-charset="utf-8" enctype="multipart/form-data">
    <fieldset data-uk-margin>
        <legend>PDF para JATS</legend>
        <input type="file" name="file">        
        <button class="uk-button-primary" name="btn_submit">Upload</button><br/>                                    
    </fieldset>
</form>
<br/>                                   


        </li>
    </ul>

</div>

<div class="uk-section uk-container">
    <h1 class="uk-heading-line uk-text-center"><span>Estatísticas</span></h1>
    <div class="uk-child-width-expand@s uk-text-center" uk-grid>
        <div>
            <div class="uk-card">
                <h2 class="uk-h3">Unidade USP</h2>
                <ul class="uk-list uk-list-striped">
                    <?php paginaInicial::unidadeUSP_inicio(); ?>
                </ul>                    
            </div>
        </div>
        <div>
            <div class="uk-card">
                <h2 class="uk-h3">Tipo de material</h2>
                <ul class="uk-list uk-list-striped">
                    <?php paginaInicial::tipo_inicio(); ?>
                </ul>                    
            </div>
        </div>        
        <div>
            <div class="uk-card">
                <h2 class="uk-h3">Fonte</h2>
                <ul class="uk-list uk-list-striped">
                    <?php paginaInicial::fonte_inicio(); ?> 
                </ul>                    
            </div>
        </div>
        <div>
            <div class="uk-card">
                        <h2 class="uk-h3">Alguns números</h2>
                        <ul class="uk-list uk-list-striped">
                            <li><?php echo paginaInicial::contar_tipo_de_registro("Work"); ?> registros</li> 
                            <li><?php echo paginaInicial::contar_tipo_de_registro("Curriculum"); ?> currículos</li>
                        </ul>    
            </div>
        </div>
    </div>           
</div> 
        
        <div class="uk-container uk-container-center uk-margin-large-bottom">
        
        <hr class="uk-grid-divider">

        <?php include('inc/footer.php'); ?>
        
        
<?php include('inc/offcanvas.php'); ?>
            
        
    </body>
</html>