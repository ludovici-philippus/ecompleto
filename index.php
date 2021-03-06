<?php
require("./config.php");
require("./integracao.php");

try {
  $LOJAS = IntegracaoPagcompleto::get_lojas_com_pagcompleto();
  $PEDIDOS = IntegracaoPagcompleto::get_pedidos($LOJAS);
  IntegracaoPagcompleto::verifica_situacao_api($PEDIDOS);
  echo "Tudo certo!";
} catch (Throwable $th) {
  echo "Ocorreu um erro!";
}
