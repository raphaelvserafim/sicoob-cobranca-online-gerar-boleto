# API GERAR BOLETO SICOOB - PHP 

### Classe em PHP cobrança online Sicoob 
 
# CONTATO 
 
<p>
<a href="https://wa.me/5566996852025" target="_blank"> 
 <img src="https://img.shields.io/badge/WhatsApp-25D366?style=for-the-badge&logo=whatsapp&logoColor=white" title="+55 66 99685-2025"/> 
</a>

 <a href="https://t.me/raphaelserafim" target="_blank">
  <img src="https://img.shields.io/badge/Telegram-2CA5E0?style=for-the-badge&logo=telegram&logoColor=white" target="_blank">
 </a>  

<a href="https://instagram.com/raphaelvserafim" target="_blank">
 <img src="https://img.shields.io/badge/-Instagram-%23E4405F?style=for-the-badge&logo=instagram&logoColor=white" target="_blank">
</a>
 
<a href="https://www.linkedin.com/in/raphaelvserafim" target="_blank">
 <img src="https://img.shields.io/badge/-LinkedIn-%230077B5?style=for-the-badge&logo=linkedin&logoColor=white" target="_blank">
</a>  
</p>


#### Exemplo de Uso Gerando Boleto


## Instalando via composer
```
composer require cachesistemas/sicoob
```

```php

 use Cachesistemas\Sicoob\Sicoob;

 require  'config.php';

    $sicoob           = new Sicoob();

    $sicoob->conta    = 1; // ID da sua conta

    $sicoob->consultaCredenciaisConta();

    $accessTokenJson  = $sicoob->consultaAccessToken();

    $accessToken      = json_decode($accessTokenJson, true);

   
    if ($accessToken["status"]) {

        // dados fatura 
        $sicoob->seuNumero           = $fatura[0]->id_fatura;
        $sicoob->valorTitulo         = $fatura[0]->valor;
        $sicoob->dataEmissao         = date('Y-m-d');
        $sicoob->dataVencimento      = $fatura[0]->data_vencimento;

        // dados cliente
        $sicoob->numeroCpfCnpj       = $fatura[0]->cpf_aluno;
        $sicoob->nome                = $fatura[0]->nome_aluno;
        $sicoob->endereco            = $fatura[0]->logradouro_aluno;
        $sicoob->bairro              = $fatura[0]->end_bairro_aluno;
        $sicoob->cidade              = $fatura[0]->nome_cidade;
        $sicoob->cep                 = preg_replace('/[^0-9]/', '', $fatura[0]->end_cep); // apenas numeros
        $sicoob->uf                  = $fatura[0]->uf_estado;


        if ($fatura[0]->valor_desconto > 0) {
            $sicoob->tipoDesconto          = 1;
            $sicoob->dataPrimeiroDesconto  = $fatura[0]->data_desconto;
            $sicoob->valorPrimeiroDesconto = $fatura[0]->valor_desconto;
        }


        if ($fatura[0]->multa > 0) {
            $dataMulta  =   date('Y-m-d', strtotime("+2 Days", strtotime($fatura[0]->data_vencimento)));
            $sicoob->tipoMulta  = 2;
            $sicoob->dataMulta  =  $dataMulta;
            $sicoob->valorMulta = $fatura[0]->multa;
        }

        if ($fatura[0]->juros > 0) {
            $dataJuros  =   date('Y-m-d', strtotime("+2 Days", strtotime($fatura[0]->data_vencimento)));
            $sicoob->tipoJurosMora  = 2;
            $sicoob->valorJurosMora = $fatura[0]->juros;
            $sicoob->dataJurosMora  = $dataJuros;
        }

        if (!empty($fatura[0]->observacao)) {
            $sicoob->mensagensInstrucao_1 = $fatura[0]->observacao;
        }


        $verificacao  = $sicoob->verificaSeFaturaTemBoleto();

        if (sizeof($verificacao["consulta"]) == 0) {

            $boletoJson   = $sicoob->gerarBoleto();
            $boleto       = json_decode($boletoJson, true);

            if ($boleto["resultado"][0]["status"]["codigo"] == 200) {

                $sicoob->codigoBarras   = $boleto["resultado"][0]["boleto"]["codigoBarras"];
                $sicoob->linhaDigitavel = $boleto["resultado"][0]["boleto"]["linhaDigitavel"];
                $sicoob->nossoNumero    = $boleto["resultado"][0]["boleto"]["nossoNumero"];

                if (!empty($boleto["resultado"][0]["boleto"]["pdfBoleto"])) {

                    $nomeDoPdf   = md5($boleto["resultado"][0]["boleto"]["nossoNumero"] . time()) . ".pdf";

                    $pdf_decoded = base64_decode($boleto["resultado"][0]["boleto"]["pdfBoleto"]);

                    $caminhoGravar =   'assets/pdf/boletos/sicoob/' . date('Y') . '/' . date('m');

                    if (!is_dir($caminhoGravar)) {
                        mkdir($caminhoGravar, 0777, true);
                    }
                    $pdf = fopen($caminhoGravar . '/' . $nomeDoPdf, 'w');
                    fwrite($pdf, $pdf_decoded);
                    fclose($pdf);
                    $caminhoSalveDb = 'assets/pdf/boletos/sicoob/' . date('Y') . '/' . date('m') . '/' . $nomeDoPdf;
                    $sicoob->pdfBoleto = $caminhoSalveDb;
                }

                $salve = $sicoob->salvarBoletoDB();

                if ($salve["status"]) {
                    print  json_encode(array("status" => true, "mensagem" => "Boleto gerado e salvo com sucesso."));
                } else {
                    print  json_encode(array("status" => false, "mensagem" =>  $salve));
                }
            } else {
                print  json_encode(array("status" => false, "mensagem" => "Erro ao Gerar Boleto: " . json_encode($boleto)));
            }
        } else {
            print  json_encode(array("status" => false, "mensagem" => "Ja tem um boleto gerado para essa fatura"));
        }
    } else {
        print  json_encode(array("status" => false, "mensagem" => $accessToken["mensagem"]));
    }


```

