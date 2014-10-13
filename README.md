Correios SRO to JSON
====================

Este script lê a página de rastreamento dos Correios com base em código de rastreio informado via GET ou POST e a transforma em JSON ou JSONP


##Exemplo
Hospede o código PHP em seu servidor e chame a URL passando o código de rastreio como parametro:
`http://localhost/qd-correios-sro-2-json.php?tracking=LZ640818275US`

Para utilizar no formato JSONP, basta informa o `callback` na querystring:
`http://localhost/qd-correios-sro-2-json.php?tracking=LZ640818275US&callback=my_function`