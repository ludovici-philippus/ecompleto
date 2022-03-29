<?php
require("./config.php");
require("./integracao.php");
$lojas = IntegracaoPagcompleto::get_lojas_com_pagcompleto();
IntegracaoPagcompleto::get_pedidos($lojas);
