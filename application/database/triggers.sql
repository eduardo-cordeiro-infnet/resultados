-- Dumping structure for trigger lmsinfne_resultados.disciplinas_turmas_after_delete
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
DROP TRIGGER IF EXISTS `disciplinas_turmas_after_delete`;
CREATE TRIGGER `disciplinas_turmas_after_delete` AFTER DELETE ON `disciplinas_turmas` FOR EACH ROW BEGIN
	update turmas set turmas.qtd_disciplinas_calc = (
		select COUNT(1) from disciplinas_turmas
		where id_turma = OLD.id_turma
	) where id = OLD.id_turma;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.disciplinas_turmas_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
DROP TRIGGER IF EXISTS `disciplinas_turmas_after_insert`;
CREATE TRIGGER `disciplinas_turmas_after_insert` AFTER INSERT ON `disciplinas_turmas` FOR EACH ROW BEGIN
	update turmas set turmas.qtd_disciplinas_calc = (
		select COUNT(1) from disciplinas_turmas
		where id_turma = NEW.id_turma
	) where id = NEW.id_turma;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.disciplinas_turmas_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
DROP TRIGGER IF EXISTS `disciplinas_turmas_after_update`;
CREATE TRIGGER `disciplinas_turmas_after_update` AFTER UPDATE ON `disciplinas_turmas` FOR EACH ROW BEGIN
	update turmas set turmas.qtd_disciplinas_calc = (
		select COUNT(1) from disciplinas_turmas
		where id_turma = NEW.id_turma
	) where id = NEW.id_turma;

	update turmas set turmas.qtd_disciplinas_calc = (
		select COUNT(1) from disciplinas_turmas
		where id_turma = OLD.id_turma
	) where id = OLD.id_turma;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.disciplinas_turmas_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
DROP TRIGGER IF EXISTS `disciplinas_turmas_before_insert`;
CREATE TRIGGER `disciplinas_turmas_before_insert` BEFORE INSERT ON `disciplinas_turmas` FOR EACH ROW BEGIN
	set NEW.id_bloco_red = (select id_bloco from disciplinas where id = NEW.id_disciplina);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.disciplinas_turmas_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
DROP TRIGGER IF EXISTS `disciplinas_turmas_before_update`;
CREATE TRIGGER `disciplinas_turmas_before_update` BEFORE UPDATE ON `disciplinas_turmas` FOR EACH ROW BEGIN
	set NEW.id_bloco_red = (select id_bloco from disciplinas where id = NEW.id_disciplina);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.subcompetencias_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
DROP TRIGGER IF EXISTS `subcompetencias_before_insert`;
CREATE TRIGGER `subcompetencias_before_insert` BEFORE INSERT ON `subcompetencias` FOR EACH ROW BEGIN
	set NEW.id_disciplina_turma_red = (select id_disciplina_turma from competencias where id = NEW.id_competencia);
	set NEW.codigo_completo_calc = (
		select CONCAT(cmp.codigo, '.', NEW.codigo, case when NEW.obrigatoria = 1 then '*' else '' end)
		from competencias cmp
		where cmp.id = NEW.id_competencia
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.subcompetencias_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
DROP TRIGGER IF EXISTS `subcompetencias_before_update`;
CREATE TRIGGER `subcompetencias_before_update` BEFORE UPDATE ON `subcompetencias` FOR EACH ROW BEGIN
	set NEW.id_disciplina_turma_red = (select id_disciplina_turma from competencias where id = NEW.id_competencia);
	set NEW.codigo_completo_calc = (
		select CONCAT(cmp.codigo, '.', NEW.codigo, case when NEW.obrigatoria = 1 then '*' else '' end)
		from competencias cmp
		where cmp.id = NEW.id_competencia
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.turmas_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
DROP TRIGGER IF EXISTS `turmas_before_insert`;
CREATE TRIGGER `turmas_before_insert` BEFORE INSERT ON `turmas` FOR EACH ROW BEGIN
	set NEW.id_escola_red = (
		select id_escola from programas where id = NEW.id_programa
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.turmas_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
DROP TRIGGER IF EXISTS `turmas_before_update`;
CREATE TRIGGER `turmas_before_update` BEFORE UPDATE ON `turmas` FOR EACH ROW BEGIN
	set NEW.id_escola_red = (
		select id_escola from programas where id = NEW.id_programa
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;
