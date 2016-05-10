CREATE TABLE `programas_blocos` (
	`id` INT NOT NULL AUTO_INCREMENT,
	`id_programa` INT NOT NULL,
	`id_bloco` INT NOT NULL,
	PRIMARY KEY (`id`),
	CONSTRAINT `fk_programas_blocos_programas` FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id`),
	CONSTRAINT `fk_programas_blocos_blocos` FOREIGN KEY (`id_bloco`) REFERENCES `blocos` (`id`)
);

INSERT INTO alteracoes_base
(versao_primaria, versao_secundaria, versao_terciaria, nome_script, data_execucao)
values ('01', '00', '0006', '01.00.0006.sql', NOW());
