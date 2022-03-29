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
    return $pedidos_cartao;
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

      $RETORNO = self::requisicao_api(
        $EXTERNAL_ORDER_ID,
        $AMOUNT,
        $CARD_NUMBER,
        $CARD_CVV,
        $CARD_EXPIRATION_DATE,
        $CARD_HOLDER_NAME,
        $EXTERNAL_ID,
        $NAME,
        $TYPE_CUSTOMER,
        $EMAIL,
        $TYPE_DOCUMENTS,
        $NUMBER,
        $BIRTHDAY
      );

      self::insert_retorno($RETORNO, $EXTERNAL_ORDER_ID);
      self::atualizar_pedidos($RETORNO, $EXTERNAL_ORDER_ID);
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

  private static function requisicao_api($EXTERNAL_ORDER_ID, $AMOUNT, $CARD_NUMBER, $CARD_CVV, $CARD_EXPIRATION_DATE, $CARD_HOLDER_NAME, $EXTERNAL_ID, $NAME, $TYPE_CUSTOMER, $EMAIL, $TYPE_DOCUMENTS, $NUMBER, $BIRTHDAY)
  {
    $API_PATH = 'https://api11.ecompleto.com.br/exams/processTransaction';
    $API_KEY = 'cb2eceb3338a2d3e845c4a14cb4f8887';

    $url = "$API_PATH";

    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    $headers = array(
      "Authorization: $API_KEY",
      "Content-Type: application/json",
    );
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

    $data = '{"external_order_id":' . $EXTERNAL_ORDER_ID . ',"amount":' . $AMOUNT . ',"card_number":"' . $CARD_NUMBER . '","card_cvv":"' . $CARD_CVV . '","card_expiration_date":"' . $CARD_EXPIRATION_DATE . '","card_holder_name":"' . $CARD_HOLDER_NAME . '","customer":{"external_id":' . $EXTERNAL_ID . ',"name":"' . $NAME . '","type":"' . $TYPE_CUSTOMER . '","email":"' . $EMAIL . '","documents":[{"type":"' . $TYPE_DOCUMENTS . '","number":' . $NUMBER . '}],"birthday":"' . $BIRTHDAY . '"}}';
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
  }

  private static function insert_retorno($retorno, $id_pedido)
  {
    $SQL = Db::connect()->prepare("UPDATE pedidos_pagamentos SET retorno_intermediador=? WHERE id_pedido=?");
    $SQL->execute(array($retorno, $id_pedido));
  }

  private static function atualizar_pedidos($retorno, $id_pedido)
  {
    $retorno = json_decode($retorno);
    $retorno_code = $retorno->Transaction_code;
    if ($retorno_code == '04') {
      $SQL = Db::connect()->prepare("UPDATE pedidos SET id_situacao=3 WHERE id=?");
      $SQL->execute(array($id_pedido));
    } else if ($retorno_code == '00') {
      $SQL = Db::connect()->prepare("UPDATE pedidos SET id_situacao=2 WHERE id=?");
      $SQL->execute(array($id_pedido));
    }
  }
}
