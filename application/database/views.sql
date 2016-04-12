create or replace view v_rubricas_avaliacoes as
select grc.id id_mdl_gradingform_rubric_criteria,
       a.id_disciplina_turma id_disciplina_turma,
       acm.id_avaliacao id_avaliacao,
       grc.description rubrica,
       grc.sortorder ordem_rubrica
from lmsinfne_mdl.mdl_gradingform_rubric_criteria grc
	join lmsinfne_mdl.mdl_grading_definitions gd on gd.id = grc.definitionid
	join lmsinfne_mdl.mdl_grading_areas ga on ga.id = gd.areaid
	join lmsinfne_mdl.mdl_context c on c.id = ga.contextid
	join lmsinfne_mdl.mdl_course_modules cm on cm.id = c.instanceid
	join lmsinfne_resultados.avaliacoes_mdl_course_modules acm on acm.instance_mdl_course_modules = cm.instance
	join lmsinfne_resultados.avaliacoes a on a.id = acm.id_avaliacao;
