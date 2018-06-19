<?php 

include('inc/config.php');             
include('inc/functions.php');

if (isset($_GET["doi"])) {
    DadosExternos::query_doi($_GET["doi"], $_GET["tag"]);
    sleep(5); 
    echo '<script>window.location = \'http://coletaprod.sibi.usp.br/result_trabalhos.php?search[]=doi.keyword:"'.$_GET["doi"].'"\'</script>';
} else {
    echo '<p>Favor inserir um DOI</p>';
}



?>