ALTER TABLE `competencias`
	CHANGE COLUMN `nome` `nome` VARCHAR(500) NOT NULL;

INSERT INTO alteracoes_base
(versao_primaria, versao_secundaria, versao_terciaria, nome_script, data_execucao)
values ('01', '00', '0004', '01.00.0004.sql', NOW());
