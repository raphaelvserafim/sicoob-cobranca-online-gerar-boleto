<?php

require  'config.php';


$sicoob = new Sicoob();

$sicoob->conta = 1; // conta escolhida 

$sicoob->consultaCredenciaisConta();

$accessToken = $sicoob->consultaAccessToken();

if ($accessToken["status"]) {

    // dados fatura 
    $sicoob->seuNumero           = 2; // ID da fatura 
    $sicoob->valorTitulo         = 5.00; // valor do documento
    $sicoob->dataEmissao         = date('Y-m-d');
    $sicoob->dataVencimento      = "2021-04-30"; // Define a data de vencimento;

    // dados cliente
    $sicoob->numeroCpfCnpj       = "00000000000";
    $sicoob->nome                = "Raphael";
    $sicoob->endereco            = "Rua";
    $sicoob->bairro              = "Cidade Nova";
    $sicoob->cidade              = "Guaranta Do Norte";
    $sicoob->cep                 = "78520000";
    $sicoob->uf                  = "UF";


    $verificacao  = $sicoob->verificaSeFaturaTemBoleto();
    
    if (empty($verificacao)) {

        $boletoJson   = $sicoob->gerarBoleto();
        $boleto       = json_decode($boletoJson, true);

        $boleto       = json_decode($boletoJson, true);

        if ($boleto["resultado"][0]["status"]["codigo"] == 200) {

            $sicoob->codigoBarras   = $boleto["resultado"][0]["boleto"]["codigoBarras"];
            $sicoob->linhaDigitavel = $boleto["resultado"][0]["boleto"]["linhaDigitavel"];
            $sicoob->nossoNumero    = $boleto["resultado"][0]["boleto"]["nossoNumero"];

            if (!empty($boleto["resultado"][0]["boleto"]["pdfBoleto"])) {

                $nomeDoPdf   = md5($boleto["resultado"][0]["boleto"]["nossoNumero"] . time()) . ".pdf";

                $pdf_decoded = base64_decode($boleto["resultado"][0]["boleto"]["pdfBoleto"]);

                $caminhoGravar = '../../../assets/pdf/boletos/sicoob/' . date('Y') . '/' . date('m');

                if (!is_dir($caminhoGravar)) {
                    mkdir($caminhoGravar, 0777, true);
                }
                $pdf = fopen($caminhoGravar . '/' . $nomeDoPdf, 'w');
                fwrite($pdf, $pdf_decoded);
                fclose($pdf);

                $caminhoSalveDb = 'assets/pdf/boletos/sicoob/' . date('Y') . '/' . date('m') . '/' . $nomeDoPdf;
                $sicoob->pdfBoleto = $caminhoSalveDb;
            }

            $salve = $sicoob->salvarBoletoDb();

            if ($salve == TRUE) {
                print  json_encode(array("status" => true, "mensagem" => "Boleto gerado e salvo com sucesso."));
            } else {
                print  json_encode(array("status" => false, "mensagem" =>  $salve));
            }
        } else {
            print  json_encode(array("status" => false, "mensagem" => $boleto["resultado"][0]["status"]["mensagem"]));
        }
    } else {
        print  json_encode(array("status" => false, "mensagem" => "Ja tem um boleto gerado para essa fatura"));
    }
} else {
    print  json_encode(array("status" => false, "mensagem" => $accessToken["mensagem"]));
}

