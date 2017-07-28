<!DOCTYPE html>
<?php
    include('inc/config.php'); 
    include('inc/functions.php');

    $result_get = get::analisa_get($_GET);
    $query = $result_get['query'];
    $limit = $result_get['limit'];
    $page = $result_get['page'];
    $skip = $result_get['skip'];

    $type = 'curriculos';
   
    $params = [];
    $params["index"] = $index;
    $params["type"] = 'curriculos';
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
        <title>Lattes USP - Resultado da busca por autores</title>
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
                
        $facets->facet("id_usp",100,"Número USP",null,"_term",$_GET["search"]);
        $facets->facet("tag",100,"Tag",null,"_term",$_GET["search"]);
        $facets->facet("nacionalidade",100,"Nacionalidade",null,"_term",$_GET["search"]);
        $facets->facet("pais_de_nascimento",100,"País de nascimento",null,"_term",$_GET["search"]);
                
        $facets->facet("endereco.endereco_profissional.nomeInstituicaoEmpresa",100,"Nome da Instituição ou Empresa",null,"_term",$_GET["search"]);
        $facets->facet("endereco.endereco_profissional.nomeOrgao",100,"Nome do orgão",null,"_term",$_GET["search"]);
        $facets->facet("endereco.endereco_profissional.nomeUnidade",100,"Nome da unidade",null,"_term",$_GET["search"]);
        $facets->facet("endereco.endereco_profissional.pais",100,"País do endereço profissional",null,"_term",$_GET["search"]);
        $facets->facet("endereco.endereco_profissional.cidade",100,"Cidade do endereço profissional",null,"_term",$_GET["search"]);
        
        $facets->facet("formacao_academica_titulacao_graduacao.nomeInstituicao",100,"Instituição em que cursou graduação",null,"_term",$_GET["search"]);
        $facets->facet("formacao_academica_titulacao_graduacao.nomeCurso",100,"Nome do curso na graduação",null,"_term",$_GET["search"]);
        $facets->facet("formacao_academica_titulacao_graduacao.statusDoCurso",100,"Status do curso na graduação",null,"_term",$_GET["search"]);
        
        $facets->facet("formacao_academica_titulacao_mestrado.nomeInstituicao",100,"Instituição em que cursou mestrado",null,"_term",$_GET["search"]);
        $facets->facet("formacao_academica_titulacao_mestrado.nomeCurso",100,"Nome do curso no mestrado",null,"_term",$_GET["search"]);
        $facets->facet("formacao_academica_titulacao_mestrado.statusDoCurso",100,"Status do curso no mestrado",null,"_term",$_GET["search"]);
        
        $facets->facet("formacao_academica_titulacao_mestradoProfissionalizante.nomeInstituicao",100,"Instituição em que cursou mestrado profissional",null,"_term",$_GET["search"]);
        $facets->facet("formacao_academica_titulacao_mestradoProfissionalizante.nomeCurso",100,"Nome do curso no mestrado profissional",null,"_term",$_GET["search"]);
        $facets->facet("formacao_academica_titulacao_mestradoProfissionalizante.statusDoCurso",100,"Status do curso no mestrado profissional",null,"_term",$_GET["search"]);
        
        $facets->facet("formacao_academica_titulacao_doutorado.nomeInstituicao",100,"Instituição em que cursou doutorado",null,"_term",$_GET["search"]);
        $facets->facet("formacao_academica_titulacao_doutorado.nomeCurso",100,"Nome do curso no doutorado",null,"_term",$_GET["search"]);
        $facets->facet("formacao_academica_titulacao_doutorado.statusDoCurso",100,"Status do curso no doutorado",null,"_term",$_GET["search"]);
        
        $facets->facet("formacao_academica_titulacao_livreDocencia.nomeInstituicao",100,"Instituição em que cursou livre docência",null,"_term",$_GET["search"]);
        $facets->facet("formacao_maxima",10,"Maior formação que iniciou",null,"_term",$_GET["search"]);         
        $facets->facet("atuacao_profissional.nomeInstituicao",100,"Instituição em que atuou profissionalmente",null,"_term",$_GET["search"]);
        $facets->facet("atuacao_profissional.vinculos.outroEnquadramentoFuncionalInformado",100,"Enquadramento funcional",null,"_term",$_GET["search"]);
        $facets->facet("atuacao_profissional.vinculos.outroVinculoInformado",100,"Vínculo",null,"_term",$_GET["search"]);
        
        $facets->facet("citacoes.SciELO.numeroCitacoes",100,"Citações na Scielo",null,"_term",$_GET["search"]);
        $facets->facet("citacoes.SCOPUS.numeroCitacoes",100,"Citações na Scopus",null,"_term",$_GET["search"]);
        $facets->facet("citacoes.Web of Science.numeroCitacoes",100,"Citações na Web of Science",null,"_term",$_GET["search"]);
        $facets->facet("citacoes.outras.numero_citacoes",100,"Citações em outras bases",null,"_term",$_GET["search"]);        
        
        $facets->facet("data_atualizacao",100,"Data de atualização do currículo",null,"_term",$_GET["search"]);
        
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
                        <li>                        
                        <div class="uk-grid-divider uk-padding-small" uk-grid>
                            <div class="uk-width-1-5@m">  
                                    <div class="uk-panel uk-h6 uk-text-break">

                                    </div>
                                    
                                </div>
                                <div class="uk-width-medium-8-10 uk-flex-middle">
                                    
                                    <ul class="uk-list">
                                        <li class="uk-margin-top uk-h4">
                                            <strong><?php echo '<a href="result_trabalhos.php?search[]=lattes_ids.keyword:&quot;'.$r["_id"].'&quot;">'.$r["_source"]['nome_completo'].'</a>';?></strong>
                                        </li>
                                        <?php if (isset($r["_source"]['resumo_cv'])): ?>
                                        <li>                                            
                                            <p><?php echo '<strong>Resumo informado pelo autor:</strong> '.$r["_source"]['resumo_cv']['texto_resumo_cv_rh'].''; ?></p>
                                        </li>
                                        <?php endif; ?>
                                        <?php if (isset($r["_source"]['orcid'])): ?>
                                        <li>                                            
                                            <p><?php echo '<strong>ORCID: </strong><a href="'.$r["_source"]['orcid'].'">'.$r["_source"]['orcid'].'</a>'; ?></p>
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
                    

                    
                </div>
            </div>
            <hr class="uk-grid-divider">
    <?php include('inc/footer.php'); ?>          
    </div>
         
    <?php include('inc/offcanvas.php'); ?>         
        
    </body>
</html>