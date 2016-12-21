<?php

	/* Endereço do server, sem http:// */ 
	$server = 'localhost'; 
	$hosts = [
		'200.144.183.86' 
	]; 

	/* Load libraries for PHP composer */ 
    require (__DIR__.'/../vendor/autoload.php'); 

	/* Load Elasticsearch Client */ 
	$client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build(); 

?>