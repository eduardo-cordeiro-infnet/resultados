DROP TRIGGER IF EXISTS `lmsinfne_resultados`.`turmas_after_insert`;
DROP TRIGGER IF EXISTS `lmsinfne_resultados`.`turmas_after_update`;
DROP TRIGGER IF EXISTS `lmsinfne_resultados`.`turmas_after_delete`;

ALTER TABLE classes
DROP COLUMN qtd_disciplinas_calc;

INSERT INTO alteracoes_base
(versao_primaria, versao_secundaria, versao_terciaria, nome_script, data_execucao)
values ('01', '00', '0007', '01.00.0007.sql', NOW());
