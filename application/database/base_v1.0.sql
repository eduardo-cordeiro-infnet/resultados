-- --------------------------------------------------------
-- Host:                         54.233.108.90
-- Server version:               5.5.47-MariaDB-1ubuntu0.14.04.1 - (Ubuntu)
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             9.3.0.5046
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Dumping structure for table lmsinfne_resultados.alteracoes_base
CREATE TABLE IF NOT EXISTS `alteracoes_base` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `versao_primaria` varchar(2) NOT NULL,
  `versao_secundaria` varchar(2) NOT NULL,
  `versao_terciaria` varchar(4) NOT NULL,
  `nome_script` varchar(50) NOT NULL,
  `data_execucao` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.avaliacoes
CREATE TABLE IF NOT EXISTS `avaliacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativa` tinyint(4) NOT NULL DEFAULT '1',
  `id_disciplina_turma` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_avaliacoes_disciplinas_turmas` (`id_disciplina_turma`),
  CONSTRAINT `fk_avaliacoes_disciplinas_turmas` FOREIGN KEY (`id_disciplina_turma`) REFERENCES `disciplinas_turmas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.avaliacoes_mdl_course_modules
CREATE TABLE IF NOT EXISTS `avaliacoes_mdl_course_modules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_avaliacao` int(11) NOT NULL,
  `instance_mdl_course_modules` bigint(10) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_avaliacoes_mdl_modules_avaliacoes` (`id_avaliacao`),
  CONSTRAINT `fk_avaliacoes_mdl_modules_avaliacoes` FOREIGN KEY (`id_avaliacao`) REFERENCES `avaliacoes` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=653 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.blocos
CREATE TABLE IF NOT EXISTS `blocos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativo` tinyint(4) NOT NULL DEFAULT '1',
  `nome` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.competencias
CREATE TABLE IF NOT EXISTS `competencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativa` tinyint(1) DEFAULT '1',
  `id_disciplina_turma` int(11) NOT NULL,
  `nome` varchar(200) NOT NULL,
  `codigo` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_competencias_disciplinas_turmas` (`id_disciplina_turma`),
  CONSTRAINT `fk_competencias_disciplinas_turmas` FOREIGN KEY (`id_disciplina_turma`) REFERENCES `disciplinas_turmas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.disciplinas
CREATE TABLE IF NOT EXISTS `disciplinas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativa` tinyint(4) NOT NULL DEFAULT '1',
  `id_bloco` int(11) DEFAULT NULL,
  `nome` varchar(200) NOT NULL,
  `denominacao_bloco` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_disciplinas_blocos` (`id_bloco`),
  CONSTRAINT `fk_disciplinas_blocos` FOREIGN KEY (`id_bloco`) REFERENCES `blocos` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.disciplinas_turmas
CREATE TABLE IF NOT EXISTS `disciplinas_turmas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_disciplina` int(11) NOT NULL,
  `id_turma` int(11) NOT NULL,
  `trimestre_inicio` int(11) DEFAULT NULL,
  `ano_inicio` year(4) DEFAULT NULL,
  `trimestre_fim` int(11) DEFAULT NULL,
  `ano_fim` year(4) DEFAULT NULL,
  `id_mdl_course` bigint(10) DEFAULT NULL,
  `id_bloco_red` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `id_disciplina_id_turma` (`id_disciplina`,`id_turma`),
  KEY `fk_disciplinas_turmas_turmas` (`id_turma`),
  KEY `fk_disciplinas_turmas_blocos` (`id_bloco_red`),
  CONSTRAINT `fk_disciplinas_turmas_blocos` FOREIGN KEY (`id_bloco_red`) REFERENCES `blocos` (`id`),
  CONSTRAINT `fk_disciplinas_turmas_disciplinas` FOREIGN KEY (`id_disciplina`) REFERENCES `disciplinas` (`id`),
  CONSTRAINT `fk_disciplinas_turmas_turmas` FOREIGN KEY (`id_turma`) REFERENCES `turmas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.escolas
CREATE TABLE IF NOT EXISTS `escolas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativa` tinyint(1) NOT NULL DEFAULT '1',
  `nome` varchar(100) NOT NULL,
  `sigla` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sigla` (`sigla`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.formacoes
CREATE TABLE IF NOT EXISTS `formacoes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativa` tinyint(1) NOT NULL DEFAULT '1',
  `id_escola` int(11) NOT NULL,
  `nome` varchar(500) NOT NULL,
  `sigla` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sigla` (`sigla`),
  KEY `fk_formacoes_escolas` (`id_escola`),
  CONSTRAINT `fk_formacoes_escolas` FOREIGN KEY (`id_escola`) REFERENCES `escolas` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.modalidades
CREATE TABLE IF NOT EXISTS `modalidades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativa` tinyint(4) NOT NULL DEFAULT '1',
  `nome` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.subcompetencias
CREATE TABLE IF NOT EXISTS `subcompetencias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativa` tinyint(1) NOT NULL DEFAULT '1',
  `id_competencia` int(11) NOT NULL,
  `nome` varchar(500) NOT NULL,
  `codigo` int(11) NOT NULL,
  `obrigatoria` tinyint(1) NOT NULL DEFAULT '0',
  `id_disciplina_turma_red` int(11) NOT NULL,
  `codigo_completo_calc` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_subcompetencias_competencias` (`id_competencia`),
  CONSTRAINT `fk_subcompetencias_competencias` FOREIGN KEY (`id_competencia`) REFERENCES `competencias` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=latin1;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.subcompetencias_mdl_gradingform_rubric_criteria
CREATE TABLE IF NOT EXISTS `subcompetencias_mdl_gradingform_rubric_criteria` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_subcompetencia` int(11) NOT NULL DEFAULT '0',
  `id_mdl_gradingform_rubric_criteria` bigint(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=108 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for table lmsinfne_resultados.turmas
CREATE TABLE IF NOT EXISTS `turmas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ativa` tinyint(4) NOT NULL DEFAULT '1',
  `id_formacao` int(11) NOT NULL,
  `id_modalidade` int(11) NOT NULL,
  `id_mdl_course_category` bigint(10) DEFAULT NULL,
  `nome` varchar(100) NOT NULL,
  `id_escola_red` int(11) DEFAULT NULL,
  `qtd_disciplinas_calc` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_turmas_modalidades` (`id_modalidade`),
  KEY `fk_turmas_formacoes` (`id_formacao`),
  KEY `fk_turmas_escolas` (`id_escola_red`),
  CONSTRAINT `fk_turmas_escolas` FOREIGN KEY (`id_escola_red`) REFERENCES `escolas` (`id`),
  CONSTRAINT `fk_turmas_formacoes` FOREIGN KEY (`id_formacao`) REFERENCES `formacoes` (`id`),
  CONSTRAINT `fk_turmas_modalidades` FOREIGN KEY (`id_modalidade`) REFERENCES `modalidades` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- Data exporting was unselected.
-- Dumping structure for view lmsinfne_resultados.v_rubricas_avaliacoes
-- Creating temporary table to overcome VIEW dependency errors
CREATE TABLE `v_rubricas_avaliacoes` (
	`id_mdl_gradingform_rubric_criteria` BIGINT(10) NOT NULL,
	`id_disciplina_turma` INT(11) NOT NULL,
	`id_avaliacao` INT(11) NOT NULL,
	`rubrica` LONGTEXT NULL COLLATE 'utf8_general_ci'
) ENGINE=MyISAM;

-- Dumping structure for procedure lmsinfne_resultados.db_ajustar
DELIMITER //
CREATE DEFINER=`eduardo.cordeiro`@`187.16.96.234` PROCEDURE `db_ajustar`()
BEGIN
	update turmas t 
		left join (
			select dt.id_turma,
				COUNT(1) cnt                
            from disciplinas_turmas dt
        	group by dt.id_turma
		) dt on dt.id_turma = t.id
	set t.qtd_disciplinas_calc = COALESCE(dt.cnt, 0)
	where t.qtd_disciplinas_calc <> COALESCE(dt.cnt, 0);
	
	update subcompetencias scmp
		join competencias cmp on cmp.id = scmp.id_competencia
	set scmp.id_disciplina_turma_red = cmp.id_disciplina_turma
	where scmp.id_disciplina_turma_red <> cmp.id_disciplina_turma;

	update subcompetencias scmp
		join competencias cmp on cmp.id = scmp.id_competencia
	set scmp.codigo_completo_calc = CONCAT(cmp.codigo, '.', scmp.codigo, case when scmp.obrigatoria = 1 then '*' else '' end)
	where scmp.codigo_completo_calc <> CONCAT(cmp.codigo, '.', scmp.codigo, case when scmp.obrigatoria = 1 then '*' else '' end);
END//
DELIMITER ;

-- Dumping structure for procedure lmsinfne_resultados.db_verificar
DELIMITER //
CREATE DEFINER=`eduardo.cordeiro`@`187.16.96.234` PROCEDURE `db_verificar`()
begin
	declare secao varchar(200);

	set secao = 'Consistência com Moodle';

		select secao, 'disciplinas_turmas.id_mdl_course = cursos inexistentes no Moodle';
		select *
		from disciplinas_turmas dt
		where dt.id_mdl_course is not null
			and not exists (
				select 1 from lmsinfne_mdl.mdl_course crs
				where crs.id = dt.id_mdl_course
			);

		select secao, 'turmas.id_mdl_course_category = categorias inexistentes no Moodle';
		select *
		from turmas t
		where t.id_mdl_course_category is not null
			and not exists (
				select 1 from lmsinfne_mdl.mdl_course_categories c
				where c.id = t.id_mdl_course_category
			);

		select secao, 'avaliacoes_mdl_course_modules.instance_mdl_course_modules = módulos inexistentes no Moodle';
		select *
		from avaliacoes_mdl_course_modules acm
		where not exists (
			select 1 from lmsinfne_mdl.mdl_course_modules cm
			where cm.instance = acm.instance_mdl_course_modules
		);

		select secao, 'subcompetencias_mdl_gradingform_rubric_criteria.id_mdl_gradingform_rubric_criteria = rubricas inexistentes no Moodle';
		select *
		from subcompetencias_mdl_gradingform_rubric_criteria scmpgrc
		where not exists (
			select 1 from lmsinfne_mdl.mdl_gradingform_rubric_criteria grc
			where grc.id = scmpgrc.id_mdl_gradingform_rubric_criteria
		);

	set secao = 'Consistência de campos atualizados por trigger';

		select secao, 'turmas.qtd_disciplinas_calc';
		select t.*
		from turmas t
			left join (
				select id_turma,
					COUNT(1) cnt
	            from disciplinas_turmas
	        	group by id_turma
			) dt on dt.id_turma = t.id
		where t.qtd_disciplinas_calc <> COALESCE(dt.cnt, 0);

		select secao, 'subcompetencias.codigo_completo_calc';
		select *
		from subcompetencias scmp
			join competencias cmp on cmp.id = scmp.id_competencia
		where scmp.codigo_completo_calc <> CONCAT(cmp.codigo, '.', scmp.codigo, case when scmp.obrigatoria = 1 then '*' else '' end);

		select secao, 'disciplinas_turmas.id_bloco_red';
		select *
		from disciplinas_turmas dt
			join disciplinas d on d.id = dt.id_disciplina
			left join blocos b on b.id = d.id_bloco
		where b.id <> dt.id_bloco_red;

		select secao, 'turmas.id_escola_red';
		select *
		from turmas t
			join formacoes f on f.id = t.id_formacao
			join escolas e on e.id = f.id_escola
		where e.id <> t.id_escola_red;

		select secao, 'subcompetencias.id_disciplina_turma_red';
		select *
		from subcompetencias scmp
			join competencias cmp on cmp.id = scmp.id_competencia
		where scmp.id_disciplina_turma_red <> cmp.id_disciplina_turma;
end//
DELIMITER ;

-- Dumping structure for trigger lmsinfne_resultados.disciplinas_turmas_after_delete
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
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
CREATE TRIGGER `disciplinas_turmas_before_insert` BEFORE INSERT ON `disciplinas_turmas` FOR EACH ROW BEGIN
	set NEW.id_bloco_red = (select id_bloco from disciplinas where id = NEW.id_disciplina);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.disciplinas_turmas_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `disciplinas_turmas_before_update` BEFORE UPDATE ON `disciplinas_turmas` FOR EACH ROW BEGIN
	set NEW.id_bloco_red = (select id_bloco from disciplinas where id = NEW.id_disciplina);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.subcompetencias_before_insert
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
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
CREATE TRIGGER `turmas_before_insert` BEFORE INSERT ON `turmas` FOR EACH ROW BEGIN
	set NEW.id_escola_red = (
		select id_escola from formacoes where id = NEW.id_formacao
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for trigger lmsinfne_resultados.turmas_before_update
SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='';
DELIMITER //
CREATE TRIGGER `turmas_before_update` BEFORE UPDATE ON `turmas` FOR EACH ROW BEGIN
	set NEW.id_escola_red = (
		select id_escola from formacoes where id = NEW.id_formacao
	);
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

-- Dumping structure for view lmsinfne_resultados.v_rubricas_avaliacoes
-- Removing temporary table and create final VIEW structure
DROP TABLE IF EXISTS `v_rubricas_avaliacoes`;
CREATE ALGORITHM=UNDEFINED DEFINER=`eduardo.cordeiro`@`187.16.96.234` SQL SECURITY DEFINER VIEW `v_rubricas_avaliacoes` AS select `grc`.`id` AS `id_mdl_gradingform_rubric_criteria`,`a`.`id_disciplina_turma` AS `id_disciplina_turma`,`acm`.`id_avaliacao` AS `id_avaliacao`,`grc`.`description` AS `rubrica` from ((((((`lmsinfne_mdl`.`mdl_gradingform_rubric_criteria` `grc` join `lmsinfne_mdl`.`mdl_grading_definitions` `gd` on((`gd`.`id` = `grc`.`definitionid`))) join `lmsinfne_mdl`.`mdl_grading_areas` `ga` on((`ga`.`id` = `gd`.`areaid`))) join `lmsinfne_mdl`.`mdl_context` `c` on((`c`.`id` = `ga`.`contextid`))) join `lmsinfne_mdl`.`mdl_course_modules` `cm` on((`cm`.`id` = `c`.`instanceid`))) join `lmsinfne_resultados`.`avaliacoes_mdl_course_modules` `acm` on((`acm`.`instance_mdl_course_modules` = `cm`.`instance`))) join `lmsinfne_resultados`.`avaliacoes` `a` on((`a`.`id` = `acm`.`id_avaliacao`)));

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
