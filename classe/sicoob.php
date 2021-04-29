<?php


class Sicoob
{

    private $dataHoraAgora;

    public $conta;

    public $Basic;
    public $Key;
    public $Secret;
    public $code;
    public $client_id;
    public $redirect_uri;
    public $access_token;
    public $refresh_token;

    public $idCredencial;

    // Boleto 

    public $numeroContrato; //  Número que identifica o contrato do beneficiário no Sisbr.
    public $modalidade     = 1;   //  1 SIMPLES COM REGISTRO - 5 CARNÊ DE PAGAMENTOS - 6 INDEXADA - 14 CARTÃO DE CRÉDITO
    public $numeroContaCorrente; // Número da Conta Corrente onde será realizado o crédito da liquidação do boleto.
    public $especieDocumento = "FAT";  /*Espécie do documento - CH - Cheque - DM - Duplicata Mercantil - DMI - Duplicata Mercantil Indicação - DS - Duplicata de Serviço - DSI - Duplicata Serviço Indicação - DR - Duplicata Rural - LC - Letra de Câmbio - NCC - Nota de Crédito Comercial - NCE - Nota de Crédito Exportação - NCI - Nota de Crédito Industrial - NCR - Nota de Crédito Rural - NP - Nota Promissória - NPR - Nota Promissória Rural - TM - Triplicata Mercantil - TS - Triplicata de Serviço - NS - Nota de Seguro - RC - Recibo - FAT - Fatura - ND - Nota de Débito - AP - Apólice de Seguro - ME - Mensalidade Escolar - PC - Pagamento de Consórcio - NF - Nota Fiscal - DD - Documento de Dívida - CC - Cartão de Crédito - BDP - Boleto Proposta - OU - Outros */

    public $dataEmissao; //  
    public $nossoNumero = NULL; // OPCIONAL BANCO GERA

    public $seuNumero; // Nº da sua fatura melhor para indentificar `Tamanho máximo 18` 

    public $valorTitulo;     // Valor nominal do boleto. Decimal
    public $dataVencimento; //  Formato ANO-MES-DIA 

    public $dataLimitePagamento = NULL;   // OPCIONAL
    public $valorAbatimento     = NULL;  // OPCIONAL
    public $numeroParcela       = 1;


    // desconto 
    public $tipoDesconto          = 0;      //  - 0 Sem Desconto - 1 Valor Fixo Até a Data Informada - 2 Percentual até a data informada - 3 Valor por antecipação dia corrido - 4 Valor por antecipação dia útil - 5 Percentual por antecipação dia corrido - 6 Percentual por antecipação dia útil
    public $dataPrimeiroDesconto  = NULL;  // informar se for ter desconto ANO-MES-DIA
    public $valorPrimeiroDesconto = NULL; // informar se for ter desconto
    public $dataSegundoDesconto   = NULL;
    public $valorSegundoDesconto  = NULL;
    public $dataTerceiroDesconto  = NULL;
    public $valorTerceiroDesconto = NULL;



    public $gerarPdf = true; // true gera PDF ou false nao gera

    // Multa 
    public $tipoMulta  = 0;      // 0 Isento - 2 Percentual
    public $dataMulta  = NULL;  // informar se for cobrar multa ANO-MES-DIA
    public $valorMulta = NULL; // informar se for cobrar multa Decimal



    // juros
    public $tipoJurosMora  = 3;     //  2 Taxa Mensal - 3 Isento
    public $valorJurosMora = NULL; // informar se for cobrar juros
    public $dataJurosMora = NULL;
    // Negativacao
    public $codigoNegativacao     = 3;     // 2 Negativar Dias Úteis - 3 Não Negativar
    public $numeroDiasNegativacao = NULL; // informar nº dias de for negativar

    // protesto
    public $codigoProtesto     = 3;     // 1 Protestar Dias Corridos - 3 Não Protestar
    public $numeroDiasProtesto = NULL; // informar nº dias de for protestar

    // dados pagador
    public $numeroCpfCnpj; //  CPF ou CNPJ do pagador do boleto de cobrança. `Tamanho máximo 14`
    public $nome;         // `Tamanho máximo 50`
    public $endereco;    // `Tamanho máximo 40`
    public $bairro;     // `Tamanho máximo 30`
    public $cidade;    // `Tamanho máximo 40`
    public $cep;      //`Tamanho máximo 8`
    public $uf;      // `Tamanho máximo 2`
    public $email = NULL;


    // Mensagem instrucao 
    public $mensagensInstrucao_1 = NULL;
    public $mensagensInstrucao_2 = NULL;
    public $mensagensInstrucao_3 = NULL;
    public $mensagensInstrucao_4 = NULL;
    public $mensagensInstrucao_5 = NULL;


    // Avalista 
    public $numeroCpfCnpjSacadorAvalista = NULL;  // OPCIONAL `Tamanho máximo 14` 
    public $nomeSacadorAvalista          = NULL; // OPCIONAL `Tamanho máximo 50`

    // Fim boleto // 

    public $codigoBarras;
    public $linhaDigitavel;
    public $pdfBoleto;

    public function __construct()
    {
        $this->db = new DB;
        $this->dataHoraAgora = date('Y-m-d H:i:s');
    }

    public function contas()
    {
        return   $this->db->select("SELECT * FROM sicoob_conta");
    }

    public function consultaCredenciaisConta()
    {

        $credenciais        =  $this->db->select("SELECT * FROM sicoob_credenciais 
        INNER JOIN sicoob_conta ON (sicoob_conta.idConta = sicoob_credenciais.conta ) WHERE conta='$this->conta'");
        $this->idCredencial        =  $credenciais[0]->conta;
        $this->client_id           =  $credenciais[0]->client_id;
        $this->Secret              =  $credenciais[0]->Secret;
        $this->Basic               =  $credenciais[0]->Basic;
        $this->redirect_uri        =  $credenciais[0]->redirect_uri;
        $this->numeroContrato      =  $credenciais[0]->numeroContrato;
        $this->numeroContaCorrente =  $credenciais[0]->numeroContaCorrente;
    }

    public function consultaAccessToken()
    {

        $tokens  = $this->db->select("SELECT * FROM sicoob_access_token 
         INNER JOIN sicoob_code ON (sicoob_code.credencial = $this->conta )
         INNER JOIN sicoob_credenciais ON (sicoob_credenciais.conta = sicoob_code.credencial)
         WHERE sicoob_access_token.conta='$this->conta'  ORDER BY sicoob_access_token.id DESC  LIMIT 1");

        if (!empty($tokens)) {
            if (strtotime($tokens[0]->dataHoraExpiraAccess) >  strtotime($this->dataHoraAgora)) {
                if (strtotime($tokens[0]->dataHoraExpiraRefresh) <  strtotime($this->dataHoraAgora)) {
                    $this->refresh_token =     $tokens[0]->refresh_token;
                    $this->access_token  =     $tokens[0]->access_token;
                    $this->code          =     $tokens[0]->code;
                    $this->redirect_uri  =     $tokens[0]->redirect_uri;
                    $this->Basic         =     $tokens[0]->Basic;
                    return $this->refreshToken();
                } else {

                    $this->access_token = $tokens[0]->access_token;
                    return json_encode(array("status" => true, "mensagem" =>  "Ta tudo ok"));
                }
            } else {
                return json_encode(array("status" => false, "mensagem" => "Precisa fazer Access Token novamente"));
            }
        } else {
            return  json_encode(array("status" => false, "mensagem" => "Access Token  e Refresh Token não foram gerados"));
        }
    }

    public function salvarAccessToken()
    {

        $dataHoraExpiraAccess  =   date('Y-m-d H:i:s', strtotime("+30 Days", strtotime($this->dataHoraAgora)));
        $dataHoraExpiraRefresh =   date('Y-m-d H:i:s', strtotime("+1 Hours", strtotime($this->dataHoraAgora)));
        return  $this->db->query("INSERT INTO  sicoob_access_token  SET 
            conta='$this->conta',  
            access_token='$this->access_token',	
            refresh_token='$this->refresh_token',
            dataHoraGeradoAccess='$this->dataHoraAgora',
            dataHoraExpiraAccess='$dataHoraExpiraAccess', 
            dataHoraExpiraRefresh='$dataHoraExpiraRefresh'
            ");
    }


    public function salvarCodeDB()
    {

        $dataHoraExpira  =   date('Y-m-d H:i:s', strtotime("+30 Days", strtotime($this->dataHoraAgora)));
        return  $this->db->query("INSERT INTO  sicoob_code  SET  code='$this->code', dataExpira='$dataHoraExpira', credencial='$this->conta' ");
    }

    public function atualizaRefreshToken()
    {
        $dataHoraExpiraRefresh =   date('Y-m-d H:i:s', strtotime("+1 Hours", strtotime($this->dataHoraAgora)));
        return  $this->db->query("UPDATE  sicoob_access_token  SET 
            access_token='$this->access_token', 
            refresh_token='$this->refresh_token',
            dataHoraExpiraRefresh='$dataHoraExpiraRefresh'
            WHERE conta='$this->conta' 
            ");
    }


    public function verificaSeFaturaTemBoleto()
    {
        return   $this->db->select("SELECT * FROM sicoob_boleto USE INDEX(fatura) WHERE fatura='$this->seuNumero' LIMIT 1");
    }


    // public function buscarCodeDB()
    // {

    //     return   $this->db->select("SELECT * FROM sicoob_code USE INDEX(credencial) WHERE credencial='$this->idCredencial' ORDER BY  dataExpira DESC LIMIT 1");
    // }


    public function salvarBoletoDB()
    {

        return  $this->db->query("INSERT INTO   sicoob_boleto  SET  
         fatura='$this->seuNumero', 
         nossoNumero='$this->nossoNumero',	
         codigoBarras='$this->codigoBarras',	
         linhaDigitavel='$this->linhaDigitavel',	
         pdfBoleto='$this->pdfBoleto'  
         ");
    }

     


    public function accessToken()
    {

        $dados = array(
            "grant_type" => "authorization_code",
            "code" => $this->code,
            "redirect_uri" =>  $this->redirect_uri
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sisbr.com.br/auth/token",
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($dados),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic " . $this->Basic
            ),
        ));
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        if ($err) {
            return json_encode(array("status" => false,  "mensagem: " => $err));
        } else {
            $res = json_decode($response, true);
            if (!empty($res["access_token"]) && !empty($res["refresh_token"])) {
                $this->access_token  = $res["access_token"];
                $this->refresh_token = $res["refresh_token"];
                $i = $this->salvarAccessToken();
                if ($i) {
                    return json_encode(array("status" => true, "mensagem" => "Salvo com sucesso"));
                } else {
                    return json_encode(array("status" => false,  "mensagem" => $i));
                }
            } else {
                return json_encode(array("status" => false,  "mensagem" => $res));
            }
        }
        curl_close($curl);
    }

    public function  refreshToken()
    {

        $dados = array(
            "grant_type" => "refresh_token",
            "refresh_token" => $this->refresh_token
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sisbr.com.br/auth/token",
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => http_build_query($dados),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/x-www-form-urlencoded",
                "Authorization: Basic " . $this->Basic
            ),
        ));

        $response = curl_exec($curl);
        $err      = curl_error($curl);
        if ($err) {
            return json_encode(array("status" => false,  "mensagem" => $err, "dados" => json_encode($dados)));
        } else {
            $res                 = json_decode($response, true);
            $this->access_token  = $res["access_token"];
            $this->refresh_token = $res["refresh_token"];
            $i = $this->atualizaRefreshToken();
            if ($i) {
                return json_encode(array("status" => false, "mensagem" => "Atualizado com sucesso"));
            } else {
                return json_encode(array("status" => false,  "mensagem" => $i));
            }
        }
        curl_close($curl);
    }


    public function gerarBoleto()
    {

        $data = array(
            0 =>
            array(
                'numeroContrato' => $this->numeroContrato,
                'modalidade' => $this->modalidade,
                'numeroContaCorrente' => $this->numeroContaCorrente,
                'especieDocumento' => $this->especieDocumento,
                'dataEmissao' =>  $this->dataEmissao,
                'nossoNumero' => $this->nossoNumero,
                'seuNumero' =>  $this->seuNumero,
                'identificacaoBoletoEmpresa' => NULL,
                'identificacaoEmissaoBoleto' => 1,
                'identificacaoDistribuicaoBoleto' => 1,
                'valor' => $this->valorTitulo,
                'dataVencimento' =>  $this->dataVencimento . 'T00:00:00-04:00',
                'dataLimitePagamento' => $this->dataLimitePagamento,
                'valorAbatimento' =>  $this->valorAbatimento,
                'tipoDesconto' => $this->tipoDesconto,
                'dataPrimeiroDesconto' =>  $this->dataPrimeiroDesconto,
                'valorPrimeiroDesconto' => $this->valorPrimeiroDesconto,
                'dataSegundoDesconto' =>   $this->dataSegundoDesconto,
                'valorSegundoDesconto' =>  $this->valorSegundoDesconto,
                'dataTerceiroDesconto' =>  $this->dataTerceiroDesconto,
                'valorTerceiroDesconto' => $this->valorTerceiroDesconto,
                'tipoMulta' =>  $this->tipoMulta,
                'dataMulta' =>  $this->dataMulta,
                'valorMulta' =>  $this->valorMulta,
                'tipoJurosMora' => $this->tipoJurosMora,
                'dataJurosMora' => $this->dataJurosMora,
                'valorJurosMora' => $this->valorJurosMora,
                'numeroParcela' => $this->numeroParcela,
                'aceite' => TRUE,
                'codigoNegativacao' =>  $this->codigoNegativacao,
                'numeroDiasNegativacao' => $this->numeroDiasNegativacao,
                'codigoProtesto' => $this->codigoProtesto,
                'numeroDiasProtesto' => $this->numeroDiasProtesto,
                'pagador' =>
                array(
                    'numeroCpfCnpj' => $this->numeroCpfCnpj,
                    'nome' =>  $this->nome,
                    'endereco' => $this->endereco,
                    'bairro' =>  $this->bairro,
                    'cidade' => $this->cidade,
                    'cep' => $this->cep,
                    'uf' => $this->uf,
                    'email' =>
                    array(
                        0 => $this->email,
                    ),
                ),
                'sacadorAvalista' =>
                array(
                    'numeroCpfCnpjSacadorAvalista' =>  $this->numeroCpfCnpjSacadorAvalista,
                    'nomeSacadorAvalista' =>  $this->nomeSacadorAvalista,
                ),
                'mensagensInstrucao' =>
                array(
                    'mensagens' =>
                    array(
                        0 => $this->mensagensInstrucao_1,
                        1 => $this->mensagensInstrucao_2,
                        2 => $this->mensagensInstrucao_3,
                        3 => $this->mensagensInstrucao_4,
                        4 => $this->mensagensInstrucao_5,
                    ),
                ),
                'gerarPdf' => $this->gerarPdf,
            ),
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL =>  "https://api.sisbr.com.br/cooperado/cobranca-bancaria/v1/boletos",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_HTTPHEADER  => array(
                "Content-type:application/json",
                "Authorization: Bearer " .  $this->access_token,
                "Client_id: " . $this->client_id
            ),
            CURLOPT_POSTFIELDS => json_encode($data),
        ));
        $response = curl_exec($curl);
        return $response;
        curl_close($curl);
    }


    public function baixarBoleto()
    {
        $dados = array(
            "numeroContrato" => $this->numeroContrato,
            "modalidade" => $this->modalidade,
            "nossoNumero" =>  $this->nossoNumero,
            "seuNumero" => $this->nossoNumero,
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.sisbr.com.br/cooperado/cobranca-bancaria/v1/boletos/baixa",
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "PATCH",
            CURLOPT_POSTFIELDS => http_build_query($dados),
            CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Authorization: Basic " . $this->accessToken,
                "Client_id:  " . $this->client_id
            ),
        ));
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        if ($err) {
            return  $err;
        } else {
            return  $response;
        }
        curl_close($curl);
    }
}
