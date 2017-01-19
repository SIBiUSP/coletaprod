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
        ;      

        ?> 
        <title>Conversor do arquivo JSON da Plataforma Lattes para o ElasticSearch - Coleta Produção USP</title>
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
        
        <div class="uk-container uk-container-center uk-margin-large-bottom">
            <div class="uk-width-medium-1-1">
            <br/><br/>    

<?php 

$isbn = $_GET["isbn"];

//Consultas                
    z3950::query_z3950($isbn,"dedalus.usp.br:9991/usp01","USP - DEDALUS");    
    //z3950::query_z3950($isbn,"biblioteca2.senado.leg.br:9992/sen01","Biblioteca do Senado");             
    z3950::query_z3950($isbn,"lx2.loc.gov:210/LCDB","Library of Congress");
    z3950::query_z3950($isbn,"marte.biblioteca.upm.es:2200","Universidade Politécnica de Madrid");            
    z3950::query_z3950($isbn,"sirsi.library.utoronto.ca:2200","University of Toronto");
    z3950::query_z3950($isbn,"ilsz3950.nlm.nih.gov:7091/VOYAGER","U.S. National Library of Medicine (NLM)");
    z3950::query_z3950($isbn,"168.176.5.96:9991/SNB01","Universidade Nacional de Colombia(UNAL)");            
    z3950::query_z3950($isbn,"athena.biblioteca.unesp.br:9992/uep01","UNESP - Athena");            
    z3950::query_z3950($isbn,"library.ox.ac.uk:210/aleph","University of Oxford");
    z3950::query_z3950($isbn,"zcat.libraries.psu.edu:2200","Penn State University");
    z3950::query_z3950($isbn,"ringding.law.yale.edu:210/INNOPAC","Yale Law School");
    z3950::query_z3950($isbn,"newton.lib.cam.ac.uk:7090/VOYAGER","University of CambridgeYale Law School");             
                
?>
            <hr class="uk-grid-divider">
            
<?php include('inc/footer.php'); ?>

        </div>
        
        
<?php include('inc/offcanvas.php'); ?>
          </div>  
        
    </body>
</html>                