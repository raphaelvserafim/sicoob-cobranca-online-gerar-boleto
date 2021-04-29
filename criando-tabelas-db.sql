 
CREATE TABLE `sicoob_access_token` (
  `conta` int(11) NOT NULL,
  `access_token` varchar(200) NOT NULL,
  `refresh_token` varchar(200) NOT NULL,
  `dataHoraGeradoAccess` datetime NOT NULL,
  `dataHoraExpiraAccess` datetime NOT NULL,
  `dataHoraExpiraRefresh` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `sicoob_access_token`
  ADD UNIQUE KEY `accessToken` (`access_token`),
  ADD UNIQUE KEY `refresh_token` (`refresh_token`),
  ADD UNIQUE KEY `conta` (`conta`),
  ADD KEY `conta_2` (`conta`);
COMMIT;


 
 
CREATE TABLE `sicoob_conta` (
  `idConta` int(11) NOT NULL,
  `numeroContrato` varchar(100) NOT NULL,
  `numeroContaCorrente` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 
ALTER TABLE `sicoob_conta`
  ADD PRIMARY KEY (`idConta`);

 
ALTER TABLE `sicoob_conta`
  MODIFY `idConta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;


 

CREATE TABLE `sicoob_credenciais` (
  `conta` int(11) NOT NULL,
  `client_id` varchar(200) NOT NULL,
  `Secret` varchar(255) NOT NULL,
  `Basic` varchar(255) NOT NULL,
  `redirect_uri` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 
 
ALTER TABLE `sicoob_credenciais`
  ADD UNIQUE KEY `client_id` (`client_id`);
COMMIT;


CREATE TABLE `sicoob_boleto` (
  `fatura` int(11) NOT NULL,
  `nossoNumero` varchar(200) NOT NULL,
  `codigoBarras` varchar(200) NOT NULL,
  `linhaDigitavel` varchar(200) NOT NULL,
  `pdfBoleto` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
ALTER TABLE `sicoob_boleto`
  ADD UNIQUE KEY `fatura` (`fatura`),
  ADD UNIQUE KEY `nossoNumero` (`nossoNumero`),
  ADD KEY `fatura_2` (`fatura`);
COMMIT;
 
 
 
CREATE TABLE `sicoob_code` (
  `code` varchar(200) NOT NULL,
  `dataExpira` date NOT NULL,
  `credencial` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
 
 
ALTER TABLE `sicoob_code`
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `credencial` (`credencial`);
COMMIT;
 
