<?php


require  'config.php';


$sicoob  = new Sicoob();

$sicoob->conta = 1; // id da conta

$sicoob->consultaCredenciaisConta();


if (!empty($_GET["code"])) {

     $sicoob->code = $_GET["code"];
     print  $sicoob->accessToken();
}

