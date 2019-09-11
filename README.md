# Taxa de Resíduos Sólidos Domésticos


## Sobre o Sistema
O sistema nomeado TRSD (Taxa de Resíduos Sólidos Domésticos) tem como objetivo obter dados da quantidade de resíduos sólidos gerados por um imóvel (residencial ou misto) para posterior taxação. É disponibilizado um formulário onde o contribuinte preenche alguns dados como seu endereço, o tipo de uso do imóvel que reside e a quantidade média de resíduos sólidos (lixo) que gera.

O sistema contempla apenas a identificação do imóvel e do contribuinte (tela de identificação), preenchimento de dados em um formulário (cadastro) e impressão de um comprovante do cadastro.

## Implementação
Para a implementação foi utilizado o framework Slim PHP (https://www.slimframework.com/).
Destaque para a utilização do JWT (https://jwt.io/) para incrmentar a segurança das requisições do cliente.
As demais dependências encontram-se no arquivo de configuração package.json.

## Deployment

``` bash
# Instalação de dependências
composer install

# Para execução no ambiente de desenvolvimento

> O comando a seguir inicia o servidor embutido no framework Slim. Sua utilização é facultativa.
cd [txlx-backend]; 
php -S localhost:8080 -t public public/index.php

# Para execução no ambiente de homologação/produção
Apenas descarregue os arquivos no diretório do servidor web.
