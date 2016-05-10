-- Dumping structure for trigger lmsinfne_resultados.turmas_after_delete
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `turmas_after_delete` AFTER DELETE ON `turmas` FOR EACH ROW BEGIN
	update classes set classes.qtd_disciplinas_calc = (
		select COUNT(1) from turmas
		where id_classe = OLD.id_classe
	) where id = OLD.id_classe;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.turmas_after_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `turmas_after_insert` AFTER INSERT ON `turmas` FOR EACH ROW BEGIN
	update classes set classes.qtd_disciplinas_calc = (
		select COUNT(1) from turmas
		where id_classe = NEW.id_classe
	) where id = NEW.id_classe;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.turmas_after_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `turmas_after_update` AFTER UPDATE ON `turmas` FOR EACH ROW BEGIN
	update classes set qtd_disciplinas_calc = (
		select COUNT(1) from turmas
		where id_classe = NEW.id_classe
	) where id = NEW.id_classe;

	update classes set qtd_disciplinas_calc = (
		select COUNT(1) from turmas
		where id_classe = OLD.id_classe
	) where id = OLD.id_classe;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.turmas_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `turmas_before_insert` BEFORE INSERT ON `turmas` FOR EACH ROW BEGIN
	set NEW.id_bloco_red = (select id_bloco from disciplinas where id = NEW.id_disciplina);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.turmas_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `turmas_before_update` BEFORE UPDATE ON `turmas` FOR EACH ROW BEGIN
	set NEW.id_bloco_red = (select id_bloco from disciplinas where id = NEW.id_disciplina);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.subcompetencias_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `subcompetencias_before_insert` BEFORE INSERT ON `subcompetencias` FOR EACH ROW BEGIN
	set NEW.id_turma_red = (select id_turma from competencias where id = NEW.id_competencia);
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
CREATE TRIGGER `subcompetencias_before_update` BEFORE UPDATE ON `subcompetencias` FOR EACH ROW BEGIN
	set NEW.id_turma_red = (select id_turma from competencias where id = NEW.id_competencia);
	set NEW.codigo_completo_calc = (
		select CONCAT(cmp.codigo, '.', NEW.codigo, case when NEW.obrigatoria = 1 then '*' else '' end)
		from competencias cmp
		where cmp.id = NEW.id_competencia
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.classes_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `classes_before_insert` BEFORE INSERT ON `classes` FOR EACH ROW BEGIN
	set NEW.id_escola_red = (
		select id_escola from programas where id = NEW.id_programa
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.classes_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `classes_before_update` BEFORE UPDATE ON `classes` FOR EACH ROW BEGIN
	set NEW.id_escola_red = (
		select id_escola from programas where id = NEW.id_programa
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;
