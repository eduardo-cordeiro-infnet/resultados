ALTER TABLE `avaliacoes`
	DROP FOREIGN KEY `fk_avaliacoes_disciplinas_turmas`;
ALTER TABLE `competencias`
	DROP FOREIGN KEY `fk_competencias_disciplinas_turmas`;

RENAME TABLE turmas TO classes;
RENAME TABLE disciplinas_turmas TO turmas;

ALTER TABLE `turmas`
	DROP FOREIGN KEY `fk_disciplinas_turmas_blocos`,
	DROP FOREIGN KEY `fk_disciplinas_turmas_disciplinas`,
	DROP FOREIGN KEY `fk_disciplinas_turmas_turmas`;
ALTER TABLE `turmas`
	CHANGE COLUMN `id_turma` `id_classe` INT(11) NOT NULL AFTER `id_disciplina`,
	DROP INDEX `fk_disciplinas_turmas_blocos`,
	DROP INDEX `fk_disciplinas_turmas_turmas`,
	ADD INDEX `fk_turmas_blocos` (`id_bloco_red`),
	ADD INDEX `fk_turmas_disciplinas` (`id_disciplina`),
	ADD INDEX `fk_turmas_classes` (`id_classe`),
	ADD CONSTRAINT `fk_turmas_blocos` FOREIGN KEY (`id_bloco_red`) REFERENCES `blocos` (`id`),
	ADD CONSTRAINT `fk_turmas_disciplinas` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplinas` (`id`),
	ADD CONSTRAINT `fk_turmas_classes` FOREIGN KEY (`id_classe`) REFERENCES `classes` (`id`);

ALTER TABLE `classes`
	DROP FOREIGN KEY `fk_turmas_escolas`,
	DROP FOREIGN KEY `fk_turmas_modalidades`,
	DROP FOREIGN KEY `fk_turmas_programas`;
ALTER TABLE `classes`
	DROP INDEX `fk_turmas_modalidades`,
	DROP INDEX `fk_turmas_escolas`,
	DROP INDEX `fk_turmas_programas`,
	ADD INDEX `fk_classes_modalidades` (`id_modalidade`),
	ADD INDEX `fk_classes_escolas` (`id_escola_red`),
	ADD INDEX `fk_classes_programas` (`id_programa`),
	ADD CONSTRAINT `fk_classes_escolas` FOREIGN KEY (`id_escola_red`) REFERENCES `escolas` (`id`),
	ADD CONSTRAINT `fk_classes_modalidades` FOREIGN KEY (`id_modalidade`) REFERENCES `modalidades` (`id`),
	ADD CONSTRAINT `fk_classes_programas` FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id`);

ALTER TABLE `avaliacoes`
	CHANGE COLUMN `id_disciplina_turma` `id_turma` INT(11) NOT NULL AFTER `ativa`,
	DROP INDEX `fk_avaliacoes_disciplinas_turmas`,
	ADD INDEX `fk_avaliacoes_turmas` (`id_turma`),
	ADD CONSTRAINT `fk_avaliacoes_turmas` FOREIGN KEY (`id_turma`) REFERENCES `turmas` (`id`);

ALTER TABLE `competencias`
	CHANGE COLUMN `id_disciplina_turma` `id_turma` INT(11) NOT NULL AFTER `ativa`,
	DROP INDEX `fk_competencias_disciplinas_turmas`,
	ADD INDEX `fk_competencias_turmas` (`id_turma`),
	ADD CONSTRAINT `fk_competencias_turmas` FOREIGN KEY (`id_turma`) REFERENCES `turmas` (`id`);

ALTER TABLE `subcompetencias`
	CHANGE COLUMN `id_disciplina_turma_red` `id_turma_red` INT(11) NOT NULL AFTER `obrigatoria`,
	ADD INDEX `fk_subcompetencias_turmas` (`id_turma_red`),
	ADD CONSTRAINT `fk_subcompetencias_turmas` FOREIGN KEY (`id_turma_red`) REFERENCES `turmas` (`id`);

ALTER TABLE `subcompetencias_mdl_gradingform_rubric_criteria`
	ADD INDEX `fk_subcompetencias_mdl_grc_subcompetencias` (`id_subcompetencia`),
	ADD CONSTRAINT `fk_subcompetencias_mdl_grc_subcompetencias` FOREIGN KEY (`id_subcompetencia`) REFERENCES `subcompetencias` (`id`);

INSERT INTO alteracoes_base
(versao_primaria, versao_secundaria, versao_terciaria, nome_script, data_execucao)
values ('01', '00', '0005', '01.00.0005.sql', NOW());
