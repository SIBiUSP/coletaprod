<?php

    /* Exibir erros */ 
    ini_set('display_errors', 1); 
    ini_set('display_startup_errors', 1); 
    error_reporting(E_ALL);

    // Definir Instituição
    $instituicao = "";

	/* Endereço do server, sem http:// */ 
	$server = 'localhost'; 
	$hosts = [
		'localhost' 
	];

    /* Endereço da BDPI - Para o comparador */
	// $host_bdpi = [
	// 	''
	// ];

    /* Configurações do Elasticsearch */
    $index = "";
    $type = "trabalhos";

	/* Load libraries for PHP composer */ 
    require (__DIR__.'/../vendor/autoload.php'); 

	/* Load Elasticsearch Client */ 
	$client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build(); 

    /* Load Elasticsearch Client for BDPI */ 
	//$client_bdpi = \Elasticsearch\ClientBuilder::create()->setHosts($host_bdpi)->build(); 

