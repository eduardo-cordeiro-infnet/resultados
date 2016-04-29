drop procedure if exists `db_ajustar`;
delimiter //
create procedure `db_ajustar`()
	language sql
	not deterministic
	contains sql
	sql security definer
	comment ''
begin
	update classes c
		left join (
			select t.id_classe,
				count(1) cnt
            from turmas t
        	group by t.id_classe
		) t on t.id_classe = c.id
	set c.qtd_disciplinas_calc = coalesce(t.cnt, 0)
	where c.qtd_disciplinas_calc <> coalesce(t.cnt, 0);

	update subcompetencias scmp
		join competencias cmp on cmp.id = scmp.id_competencia
	set scmp.id_turma_red = cmp.id_turma
	where scmp.id_turma_red <> cmp.id_turma;

	update subcompetencias scmp
		join competencias cmp on cmp.id = scmp.id_competencia
	set scmp.codigo_completo_calc = concat(cmp.codigo, '.', scmp.codigo, case when scmp.obrigatoria = 1 then '*' else '' end)
	where scmp.codigo_completo_calc <> concat(cmp.codigo, '.', scmp.codigo, case when scmp.obrigatoria = 1 then '*' else '' end);
end//
delimiter ;
