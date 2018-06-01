<?php

require 'inc/config.php'; 
require 'inc/functions.php';

print_r($_REQUEST);
echo "<br/><br/>";

$cookies = DSpaceREST::loginREST();

$cursor = elasticsearch::elastic_get($_REQUEST['_id'], $type, null);

print_r($cursor);
echo "<br/><br/>";

if (isset($_REQUEST["createRecord"])) {
    if ($_REQUEST["createRecord"] == "true") {
        echo "SIM";
        print_r($cookies);
        
        $dataString = DSpaceREST::buildDC($cursor, $_REQUEST['_id']);
        $resultCreateItemDSpace = DSpaceREST::createItemDSpace($dataString, $dspaceCollection, $cookies);
        
        // echo "<script type='text/javascript'>
        // $(document).ready(function(){  
        //         //Reload the page
        //         window.location = window.location.href;
        // });
        // </script>";
    } 
}

DSpaceREST::logoutREST($cookies);


?>
