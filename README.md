# coletaprod
Extração e navegação de registros para a Coleta de Produção Científica das Instituições. 

Fontes possíveis: 

+ Base Lattes
+ Web of Science
+ CrossRef (DOI)

## Dependencias

1. Elasticsearch 5 ou superior
* Dependências do PHP: php5-cgi | php5-curl

## Clonar repositórios

git clone https://github.com/SIBiUSP/coletaprod.git

## Instalação

curl -s http://getcomposer.org/installer | php

php composer.phar install --no-dev

## Configurar o arquivo inc/config.php

Editar suas configurações em config.php

## Rodar os comandos inc/mappings.elasticsearch

Rodar os comandos de criação do índice e mapeamento dos tipos disponíveis no arquivo inc/mappings.elasticsearch

## Comandos para rodar via linha de comando (Se necessário)

php5-cgi -f lattes_json_to_elastic.php id_lattes=XXXXXXXX

php5-cgi -f lattes_json_to_elastic.php path_download=XXXXXXXX

## Autores:

+ Tiago Rodrigo Marçal Murakami
+ Jan Leduc de Lara


## Como citar

Para citar, use o DOI: 
<a href="https://zenodo.org/badge/latestdoi/77038207"><img src="https://zenodo.org/badge/77038207.svg" alt="DOI"></a>

MURAKAMI, Tiago Rodrigo Marçal & LARA, Jan Leduc de. Coletaprod. Disponível em: < https://zenodo.org/badge/latestdoi/77038207 >, Acesso em: 