<?php 

include('inc/config.php');             
include('inc/functions.php');

if (isset($_GET["doi"])) {
    dadosExternos::query_doi($_GET["doi"],$_GET["tag"]);
    sleep(5); 
    echo '<script>window.location = \'http://bdpife2.sibi.usp.br/dev_coletaprod/result_trabalhos.php?search[]=doi.keyword:"'.$_GET["doi"].'"\'</script>';
} else {
    echo '<p>Favor inserir um DOI</p>';
}



?>