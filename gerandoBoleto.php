<?php

require  'config.php';


$sicoob = new Sicoob();

$sicoob->conta = 1; // conta escolhida 

$sicoob->consultaCredenciaisConta();

$accessToken = $sicoob->consultaAccessToken();

if ($accessToken["status"]) {


    $sicoob->seuNumero           = 1;
    $sicoob->valorTitulo         = 5.00;
    
    $sicoob->dataEmissao         = date('Y-m-d');
    $sicoob->dataVencimento      = "2021-04-30";
   
  

    // dados cliente
    $sicoob->numeroCpfCnpj       = "000000000";
    $sicoob->nome                = "Raphael";
    $sicoob->endereco            = "Rua ";
    $sicoob->bairro              = "Bairro";
    $sicoob->cidade              = "Guaranta Do Norte";
    $sicoob->cep                 = "78520000";
    $sicoob->uf                  = "MT";


    $boleto  = $sicoob->gerarBoleto();

    var_dump($boleto);
    
    
} else {
    print  json_encode(array("status" => false, "mensagem" => $accessToken["mensagem"]));
    exit;
}
