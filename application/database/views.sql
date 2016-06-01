CREATE OR REPLACE view v_rubricas
AS
  SELECT grc.id          id_mdl_gradingform_rubric_criteria,
		 grc.description rubrica,
		 grc.sortorder   ordem_rubrica,
		 cm.instance     instance_mdl_course_module,
		 asg.name        name_mdl_assign,
		 cm.course       id_course
	FROM lmsinfne_mdl.mdl_gradingform_rubric_criteria grc
		 JOIN lmsinfne_mdl.mdl_grading_definitions gd
		   ON gd.id = grc.definitionid
		 JOIN lmsinfne_mdl.mdl_grading_areas ga
		   ON ga.id = gd.areaid
		 JOIN lmsinfne_mdl.mdl_context c
		   ON c.id = ga.contextid
		 JOIN lmsinfne_mdl.mdl_course_modules cm
		   ON cm.id = c.instanceid
		 JOIN lmsinfne_mdl.mdl_modules m
		   ON m.id = cm.module
			  AND m.name = 'assign'
		 JOIN lmsinfne_mdl.mdl_assign asg
		   ON asg.id = cm.instance;

CREATE OR REPLACE view v_rubricas_avaliacoes
AS
  SELECT vr.id_mdl_gradingform_rubric_criteria,
		 vr.instance_mdl_course_module,
		 a.id_turma,
		 acm.id_avaliacao,
		 vr.rubrica,
		 vr.ordem_rubrica
	FROM v_rubricas vr
		 JOIN lmsinfne_resultados.avaliacoes_mdl_course_modules acm
		   ON acm.instance_mdl_course_modules = vr.instance_mdl_course_module
		 JOIN lmsinfne_resultados.avaliacoes a
		   ON a.id = acm.id_avaliacao;
