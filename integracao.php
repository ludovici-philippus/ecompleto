<?php
class IntegracaoPagcompleto
{
  public static function get_lojas_com_pagcompleto()
  {
    $SQL = Db::connect()->prepare("SELECT id_loja FROM lojas_gateway WHERE id_gateway=1");
    $SQL->execute();
    $LOJAS_TMP = $SQL->fetchAll();
    $lojas = [];
    foreach ($LOJAS_TMP as $key => $value) {
      $lojas[] = $value["id_loja"];
    }
    return $lojas;
  }

  public static function get_pedidos($lojas)
  {
    $pedidos_aguardando = self::get_pedidos_aguardando($lojas);
    $pedidos_cartao = self::get_pedidos_cartao_credito($pedidos_aguardando);
  }


  private static function get_pedidos_aguardando($lojas)
  {
    $query = "SELECT * FROM pedidos WHERE id_situacao=1 AND id_loja IN (";
    foreach ($lojas as $key => $value) {
      if ($key == 0)
        $query .= '?';
      else
        $query .= ", ?";
    }
    $query .= ')';
    $SQL = Db::connect()->prepare($query);
    $SQL->execute($lojas);
    $PEDIDOS_AGUARDANDO = $SQL->fetchAll();
    return $PEDIDOS_AGUARDANDO;
  }

  private static function get_ids_pedidos($pedidos)
  {
    $ids_pedidos = [];
    foreach ($pedidos as $key => $value) {
      $ids_pedidos[] = $value['id'];
    }
    return $ids_pedidos;
  }

  private static function get_pedidos_cartao_credito($pedidos)
  {
    $ids_pedidos = self::get_ids_pedidos($pedidos);
    $query = "SELECT * FROM pedidos_pagamentos WHERE id_pedido IN (";
    foreach ($ids_pedidos as $key => $value) {
      if ($key == 0)
        $query .= '?';
      else
        $query .= ", ?";
    }
    $query .= ')';
    $SQL = Db::connect()->prepare($query);
    $SQL->execute($ids_pedidos);
    $PEDIDOS_CARTAO_CREDITO = $SQL->fetchAll();
    return $PEDIDOS_CARTAO_CREDITO;
  }
}
