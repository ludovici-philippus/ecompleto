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
      $CARD_EXPIRATION_DATE = self::convert_card_data($value['vencimento']);
      $CARD_HOLDER_NAME = $value['nome_portador'];


      $EXTERNAL_ID = self::get_pedido_info($EXTERNAL_ORDER_ID)['id_cliente'];
      $CLIENTE_INFO = self::get_cliente_info($EXTERNAL_ID);

      $NAME = $CLIENTE_INFO['nome'];
      $TYPE_CUSTOMER = $CLIENTE_INFO['tipo_pessoa'] == 'F' ? "individual" : "corporation";
      $EMAIL = $CLIENTE_INFO['email'];
      $TYPE_DOCUMENTS = $TYPE_CUSTOMER == "individual" ? "cpf" : "cnpj";
      $NUMBER = $CLIENTE_INFO['cpf_cnpj'];
      $BIRTHDAY = $CLIENTE_INFO['data_nasc'];


      $API_PATH = 'https://api11.ecompleto.com.br/exams/processTransaction';
      $API_KEY = 'cb2eceb3338a2d3e845c4a14cb4f8887';

      echo "<script>";
      if ($key == 0) {
        echo "const HEADERS = new Headers({'Authorization': '$API_KEY'});";
      }
      echo "
      const BODY = {
        'external_order_id': $EXTERNAL_ORDER_ID,
        'amount': $AMOUNT,
        'card_number': '$CARD_NUMBER',
        'card_cvv': '$CARD_CVV',
        'card_expiration_date': '$CARD_EXPIRATION_DATE',
        'card_holder_name': '$CARD_HOLDER_NAME',
        'customer': {
          'external_id': $EXTERNAL_ID,
          'name': '$NAME',
          'type': '$TYPE_CUSTOMER',
          'email': '$EMAIL',
          'documents': [{
            'type': '$TYPE_DOCUMENTS',
            'number': $NUMBER
          }],
         'birthday': '$BIRTHDAY'
        }
      }

      console.log(JSON.stringify(BODY));

      fetch('$API_PATH', {
        method: 'POST',
        headers: HEADERS,
        body: JSON.stringify(BODY)
      }).then(response => console.log(response));
      </script>";
    }
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
    return $CLIENTE_INFO;
  }

  private static function convert_card_data($data)
  {
    $mes = explode("-", $data)[1];
    $ano = explode("-", $data)[0];
    $ano = substr($ano, 2);
    $data_final = $mes . $ano;
    return $data_final;
  }
}
