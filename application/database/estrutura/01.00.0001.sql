ALTER TABLE `turmas`
	ADD COLUMN `trimestre` INT(11) NULL DEFAULT NULL AFTER `nome`,
	ADD COLUMN `ano` YEAR NULL DEFAULT NULL AFTER `trimestre`;

INSERT INTO alteracoes_base
(versao_primaria, versao_secundaria, versao_terciaria, nome_script, data_execucao)
values ('01', '00', '0001', '01.00.0001.sql', NOW());
