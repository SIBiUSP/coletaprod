<?php

    /* Exibir erros */ 
    ini_set('display_errors', 1); 
    ini_set('display_startup_errors', 1); 
    error_reporting(E_ALL);

    // Definir Instituição
    $instituicao = "USP";

	/* Endereço do server, sem http:// */ 
	$server = 'localhost'; 
	$hosts = [
		'localhost' 
	];

    /* Endereço da BDPI - Para o comparador */
	$host_bdpi = [
		'locahost'
	];

    /* Configurações do Elasticsearch */
    $index = "coletaprod";
    $type = "trabalhos";

	/* Load libraries for PHP composer */ 
    require (__DIR__.'/../vendor/autoload.php'); 

	/* Load Elasticsearch Client */ 
	$client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build(); 

    /* Load Elasticsearch Client for BDPI */ 
    $client_bdpi = \Elasticsearch\ClientBuilder::create()->setHosts($host_bdpi)->build(); 
    
    /* DSpace Config */

    $dspaceRest = "http//127.1.1.1:8080/:"; /* URL do DSpace */ 
    $dspaceCollection = "351f4026-a43a-4639-be92-a812d26a6919";
    $dspaceAnnonymousID = "2ad3ba80-0db8-40f4-9d49-bd2467f95cff";
    $dspaceRestrictedID = "6d28bcd6-4c62-40eb-b548-839d2f5b589f";
    $dspaceEmail = "email@teste.com.br";
    $dspacePassword = "123456";
    $testDSpace = true;


?>