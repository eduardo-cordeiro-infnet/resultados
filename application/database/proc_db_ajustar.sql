drop procedure if exists `db_ajustar`;
delimiter //
create procedure `db_ajustar`()
	language sql
	not deterministic
	contains sql
	sql security definer
	comment ''
begin
	update turmas t
		left join (
			select dt.id_turma,
				count(1) cnt
            from disciplinas_turmas dt
        	group by dt.id_turma
		) dt on dt.id_turma = t.id
	set t.qtd_disciplinas_calc = coalesce(dt.cnt, 0)
	where t.qtd_disciplinas_calc <> coalesce(dt.cnt, 0);

	update subcompetencias scmp
		join competencias cmp on cmp.id = scmp.id_competencia
	set scmp.id_disciplina_turma_red = cmp.id_disciplina_turma
	where scmp.id_disciplina_turma_red <> cmp.id_disciplina_turma;

	update subcompetencias scmp
		join competencias cmp on cmp.id = scmp.id_competencia
	set scmp.codigo_completo_calc = concat(cmp.codigo, '.', scmp.codigo, case when scmp.obrigatoria = 1 then '*' else '' end)
	where scmp.codigo_completo_calc <> concat(cmp.codigo, '.', scmp.codigo, case when scmp.obrigatoria = 1 then '*' else '' end);
end//
delimiter ;
