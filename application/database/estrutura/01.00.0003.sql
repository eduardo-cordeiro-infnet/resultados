ALTER TABLE `avaliacoes`
	ADD COLUMN `avaliacao_final` TINYINT(1) NOT NULL DEFAULT '0' AFTER `nome`;
