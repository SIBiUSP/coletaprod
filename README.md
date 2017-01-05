# coletaprod
Extração e navegação de registros para a Coleta de Produção Científica das Instituições. 

Fontes possíveis: 

+ Base Lattes
+ Web of Science
+ CrossRef (DOI)

## Dependencias

1. Elasticsearch 5 ou superior

2. Dependências do PHP

⋅⋅1. php5-cgi

⋅⋅2. php5-curl


## Instalação

curl -s http://getcomposer.org/installer | php

php composer.phar install --no-dev

php5-cgi -f jsontoelastic.php id_lattes=XXXXXXXX

php5-cgi -f jsontoelastic.php path_download=XXXXXXXX


Autores:

+ Tiago Rodrigo Marçal Murakami
+ Jan Leduc de Lara




Para citar, use o DOI: 
<a href="https://zenodo.org/badge/latestdoi/77038207"><img src="https://zenodo.org/badge/77038207.svg" alt="DOI"></a>

MURAKAMI, Tiago Rodrigo Marçal & LARA, Jan Leduc de. Coletaprod. Disponível em: < https://zenodo.org/badge/latestdoi/77038207 >, Acesso em: 