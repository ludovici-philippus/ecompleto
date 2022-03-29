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
    self::verifica_situacao_api($pedidos_cartao);
  }

  public static function verifica_situacao_api($pedidos)
  {
    foreach ($pedidos as $key => $value) {
      $EXTERNAL_ORDER_ID = $value['id_pedido'];
      $AMOUNT = self::get_pedido_info($EXTERNAL_ORDER_ID)['valor_total'];
      $CARD_NUMBER = $value['num_cartao'];
      $CARD_CVV = $value['codigo_verificacao'];
      $CARD_EXPIRATION_DATE = $value['vencimento'];
      $CARD_HOLDER_NAME = $value['nome_portador'];

      $EXTERNAL_ID = self::get_pedido_info($EXTERNAL_ORDER_ID)['id_cliente'];
      $CLIENTE_INFO = self::get_cliente_info($EXTERNAL_ID);

      $NAME = $CLIENTE_INFO['nome'];
      $TYPE_CUSTOMER = $CLIENTE_INFO['tipo_pessoa'] == 'F' ? "individual" : "corporation";
      $EMAIL = $CLIENTE_INFO['email'];
      $TYPE_DOCUMENTS = $TYPE_CUSTOMER == "individual" ? "cpf" : "cnpj";
      $NUMBER = $CLIENTE_INFO['cpf_cnpj'];
      $BIRTHDAY = $CLIENTE_INFO['data_nasc'];
    }

    /*
    const BODY = {
      "external_order_id": 98302,
      "amount": 250.74,
      "card_number": "5236387041984690",
      "card_cvv": "319",
      "card_expiration_date": "0822",
      "card_holder_name": "Elisa Adriana Barbosa",
      "customer": {
        "external_id": "8796",
        "name": "Emanuelly Alice Alessandra de Paula",
        "type": "individual",
        "email": "emanuellyalice@ecompleto.com.br",
        "documents": [{
          "type": "cpf",
          "number": "96446953722"
        }],
        "birthday": "1988-01-18"
      }
    }
  */
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

  private static function get_pedido_info($pedido_id)
  {
    $SQL = Db::connect()->prepare("SELECT * FROM pedidos WHERE id=?");
    $SQL->execute(array($pedido_id));
    $PEDIDO_INFO = $SQL->fetch();
    return $PEDIDO_INFO;
  }

  private static function get_cliente_info($cliente_id)
  {
    $SQL = Db::connect()->prepare("SELECT * FROM clientes where id=?");
    $SQL->execute(array($cliente_id));
    $CLIENTE_INFO = $SQL->fetch();
    print_r($CLIENTE_INFO);
    return $CLIENTE_INFO;
  }
}
