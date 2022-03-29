<?php

?>

<script>
  var url = "https://api11.ecompleto.com.br/exams/processTransaction";

  var xhr = new XMLHttpRequest();
  xhr.open("POST", url);

  xhr.setRequestHeader("Authorization", "cb2eceb3338a2d3e845c4a14cb4f8887");
  xhr.setRequestHeader("Content-Type", "application/json");

  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      console.log(xhr.status);
      console.log(xhr.responseText);
    }
  };

  var data = '{"external_order_id":98308,"amount":87.9,"card_number":"5167913943407160","card_cvv":"441","card_expiration_date":"1022","card_holder_name":"Kevin Pedro","customer":{"external_id":9484,"name":"Kevin Yuri Pedro Lopes","type":"individual","email":"kevinpedro@ecompleto.com.br","documents":[{"type":"cpf","number":95829123088}],"birthday":"1996-06-03"}}';

  xhr.send(data);
</script>
