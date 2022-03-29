<?php

?>

<script>
  const HEADERS = new Headers({
    "Authorization": "cb2eceb3338a2d3e845c4a14cb4f8887",
    "Access-Control-Allow-Origin": "*",
    "Access-Control-Allow-Methods": "DELETE, POST, GET, OPTIONS",
    "Access-Control-Allow-Headers": "Content-Type, Authorization, X-Requested-With"
  });

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

  fetch("https://api11.ecompleto.com.br/exams/processTransaction", {
    method: "POST",
    headers: HEADERS,
    body: JSON.stringify(BODY)
  }).then(response => console.log(response));
</script>
