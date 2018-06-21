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
        
            /* Define variables */
            define('authorUSP','authorUSP');
            $elasticsearch = new elasticsearch();
            $cursor = $elasticsearch->elastic_get($_GET["_id"],"trabalhos",NULL);
        ?> 
        <title>Editor - Coleta Produção USP</title>
        <!-- Facebook Tags - START -->
        <meta property="og:locale" content="pt_BR">
        <meta property="og:url" content="http://bdpi.usp.br">
        <meta property="og:title" content="Coleta Produção USP - Página Principal">
        <meta property="og:site_name" content="Coleta Produção USP">
        <meta property="og:description" content="Memória documental da produção científica, técnica e artística gerada nas Unidades da Universidade de São Paulo.">
        <meta property="og:image" content="http://www.imagens.usp.br/wp-content/uploads/USP.jpg">
        <meta property="og:image:type" content="image/jpeg">
        <meta property="og:image:width" content="800"> 
        <meta property="og:image:height" content="600"> 
        <meta property="og:type" content="website">
        <!-- Facebook Tags - END -->
        
    </head>

    <body>     
        
        <?php include('inc/navbar.php'); ?>
        
        <div class="uk-container uk-container-center uk-margin-large-bottom">
            <div class="uk-width-medium-1-1">
                
                <?php print_r($cursor);?>
                <br/><br/><br/>
 
                <form class="uk-form uk-form-horizontal" action="result_trabalhos.php" method="get">
                    <div class="uk-form-row">                
                
                        
                        <label class="uk-form-label" for="form-h-s">Selecione a Base</label>
                        <div class="uk-form-controls">
                            <select id="form-h-s">
                                <option name="base" value="01">Base 01 (Livros)</option>
                                <option name="base" value="02">Base 02 (Seriados)</option>
                                <option name="base" value="03">Base 03 (Teses)</option>
                                <option name="base" value="04">Base 04 (Produção)</option>
                            </select>
                        </div>                                


                        <div class="uk-form-row">
                            <label class="uk-form-label" for="form-h-t">Textarea</label>
                            <div class="uk-form-controls">
                                <textarea name="textarea" id="form-h-t" cols="30" rows="5" placeholder="Textarea text"></textarea>
                            </div>
                        </div>                              


                        <div class="uk-form-row">
                            <label class="uk-form-label" for="Título">Título</label>
                            <div class="uk-form-controls">
                                <textarea class="uk-width-1-1" name="245a" id="form-h-it" type="text"><?php print_r($cursor["_source"]["titulo"]);?></textarea>
                            </div>
                        </div>    
                        
<div class="form-group row list-inline">
  <label for="references" class="col-sm-2 form-control-label">ID das citações</label>
  <div class="col-sm-10">
  <div class="input_fields_citation form-group">
    <?php
    if (!empty($cursor['citation'])) {
        foreach ($cursor['citation'] as $ct) {
            echo '<div><input type="text" class="form-control" id="exampleTextarea" name="citation[]" placeholder="ID da citação" value="'.$ct.'"><a href="#" class="remove_field">Remover</a></div>';
        }
    } else {
        echo '<div><input type="text" class="form-control" id="exampleTextarea" name="citation[]" placeholder="ID da citação"><a href="#" class="remove_field">Remover</a></div>';
    }
    ?>
  </div>
</div>
</div>
<button class="add_field_citation">+ citações</button>                        


                    </div>
                
                    <button class="uk-button-primary">Enviar</button><br/>

                </form>
                <br/><br/><br/>

            </div>    
            <?php include('inc/footer.php'); ?>
        </div>
        
        
        <?php include('inc/offcanvas.php'); ?>


<script type="text/javascript">
$(document).ready(function() {
    var max_fields      = 100; //maximum input boxes allowed
    var wrapper         = $(".input_fields_citation"); //Fields wrapper
    var add_button      = $(".add_field_citation"); //Add button ID
    var x = 1; //initlal text box count
    $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
        if(x < max_fields){ //max input box allowed
            x++; //text box increment
            $(wrapper).append('<div><input type="text" class="form-control" id="exampleTextarea" name="citation[]" placeholder="ID da citação"><a href="#" class="remove_field">Remover</a></div>'); //add input box
        }
    });
    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
        e.preventDefault(); $(this).parent('div').remove(); x--;
    })
});
</script>        
        
    </body>
</html>