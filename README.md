# Teste Técnico ECompleto
O presente repositório guarda o código feito em PHP puro e PostgreSQL do teste técnico para a ECompleto.

## Condições
- Abrangir todas as lojas que utilizam o gateway PAGCOMPLETO.
- Somente pedidos realizados com a forma de pagamento “Cartão de crédito” e na situação
- “Aguardando Pagamento” devem ser processados.
- A situação do pedido deverá ser atualizada conforme o retorno da API de transação.
- Transações recusadas devem resultar no cancelamento do pedido.
- O retorno da API deverá ser salvo na coluna “retorno_intermediador” da tabela "pedidos_pagamentos”.
- Disponibilizar desenvolvimento em repositório online (Github, Bitbucket, etc).

## Método de Realização
Decidi manter o desenvolvimento simples, descentralizei as responsabilidades em quatro arquivos:
- Db.php, responsável por criar a conexão com o banco de dados.
- config.php, responsável por criar algumas configurações básicas do projeto e ativar a visualização de erros em tempo de execução.
- index.php, responsável por chamar o arquivo integracao.php ligando-o com a página sem dar acesso ao arquivo em si.
- integracao.php, responsável por realizar toda a integração com a API do Pagcompleto.

### Integracao.php e Index.php
O index.php conversa com a classe IntegracaoPagcompleto mediante o arquivo dela, isto é, o integracao.php, utilizando-se de suas 3 funções públicas:
- get_lojas_com_pagcompleto, responsável por retornar apenas as lojas que utilizar o gateway de pagamento Pagcompleto.
- get_pedidos, utilizando-se de duas funções privadas (get_pedidos_aguardando e get_pedidos_cartao_credito) essa função retorna os pedidos das lojas que utilizam o gateway de pagamento do Pagcompleto e que estão com status de aguardando e foram feitos com o cartão de crédito.
- verifica_situacao_api, por fim essa função faz a requisição para a API utilizando-se dos pedidos advindos do get_pedidos, após feita a requisição, seu valor é armazenado dentro da variável RETORNO e usando-se dessa variável em conjunto com a EXTERNAL_ORDER_ID (id do pedido), são feitas as atualizações dos pedidos, conforme o retorno da API e a adição do retorno na coluna retorno_intermediador.
