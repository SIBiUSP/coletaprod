<!DOCTYPE html>
<html lang="pt-br" dir="ltr">
    <head>
        <?php

        require 'inc/config.php';             
        require 'inc/meta-header.php';
        require 'inc/functions.php'; 
        
        if (isset($_POST["update"])) {
            print_r($_POST);
            $query["doc"]["name"] = $_POST["name"];
            $query["doc"]["datePublished"] = $_POST["datePublished"];
            $query["doc"]["publisher"]["organization"]["name"] = $_POST["publisher_organization_name"];
            $query["doc_as_upsert"] = true;
            $resultado = elasticsearch::elastic_update($_POST["_id"], $type, $query);
            //print_r($resultado);
            sleep(5); 
            echo '<script>window.location = \'result_trabalhos.php?filter[]=name:"'.$_POST["name"].'"\'</script>';
        }

    
        /* Define variables */
        $elasticsearch = new elasticsearch();
        $cursor = $elasticsearch->elastic_get($_REQUEST["_id"], "trabalhos", null);
        ?> 
        <title>Editor - Coleta Produção USP</title>        
    </head>

    <body>
        <div class="uk-container">
        <?php require 'inc/navbar.php'; ?>         

                    
        <!-- < ?php print_r($cursor);?> <br/><br/><br/> -->
        

        <form class="uk-form-horizontal uk-margin-large" method="post" action="editor.php">
            <legend class="uk-legend">Editor</legend>
            <br/>
            <input type="hidden" id="update" name="update" value="true">
            <input type="hidden" id="_id" name="_id" value="<?php echo $_REQUEST["_id"]; ?>">
            <fieldset class="uk-fieldset">

                <legend class="uk-legend">Dados gerais sobre a obra</legend>            
                <div class="uk-margin">
                    <label class="uk-form-label" for="form-horizontal-select">Base</label>
                    <div class="uk-form-controls">
                        <select class="uk-select" id="form-horizontal-select" name="BAS">
                            <option value="04">Produção científica</option>
                        </select>
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="doi">DOI - 024$a</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="doi" type="text" placeholder="Título" name="doi" value="<?php echo $cursor['_source']['doi'] ?>">
                        <?php 
                        if (isset($cursor['_source']['doi'])) {
                            echo '<a href="https://doi.org/'.$cursor['_source']['doi'].'" target="_blank">Resolver DOI</a>';
                        }
                        ?>
                    </div>
                </div>                            
                <div class="uk-margin">
                    <label class="uk-form-label" for="name">Título - 245$a</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="name" type="text" placeholder="Título" name="name" value="<?php echo $cursor['_source']['name'] ?>">
                    </div>
                </div>
            </fieldset>
            <fieldset class="uk-fieldset">

                <legend class="uk-legend">Imprenta</legend>                   
                <div class="uk-margin">
                    <label class="uk-form-label" for="publisher">Editora</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="publisher.organization.name" type="text" placeholder="Editora" name="publisher.organization.name" value="<?php if (isset($cursor['_source']['publisher']['organization']['name'])) { echo $cursor['_source']['publisher']['organization']['name']; } ?>">
                    </div>
                </div>
                <div class="uk-margin">
                    <label class="uk-form-label" for="datePublished">Data de publicação</label>
                    <div class="uk-form-controls">
                        <input class="uk-input" id="datePublished" type="text" placeholder="Data de publicação" name="datePublished" value="<?php if (isset($cursor['_source']['datePublished'])) { echo $cursor['_source']['datePublished']; } ?>">
                    </div>
                </div>                
            </fieldset>
            <button class="uk-button uk-button-primary">Enviar</button>            
        </form>        


        <br/><br/><br/>
        <?php require 'inc/footer.php'; ?> 
        </div>

        
    </body>
</html>