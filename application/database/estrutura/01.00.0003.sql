ALTER TABLE `avaliacoes`
	ADD COLUMN `avaliacao_final` TINYINT(1) NOT NULL DEFAULT '0' AFTER `nome`;

INSERT INTO alteracoes_base
(versao_primaria, versao_secundaria, versao_terciaria, nome_script, data_execucao)
values ('01', '00', '0003', '01.00.0003.sql', NOW());
