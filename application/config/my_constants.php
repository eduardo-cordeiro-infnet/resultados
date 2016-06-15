<?php
defined('URL_BASE_LMS') OR define('URL_BASE_LMS', 'http://lms.infnet.edu.br/moodle');
defined('NOMES_PROJETO_BLOCO') OR define('NOMES_PROJETO_BLOCO',
	serialize(array(
		'Projeto de bloco',
		'Projeto do bloco'
	))
);
defined('NOME_PROJETO_BLOCO') OR define('NOME_PROJETO_BLOCO', unserialize(NOMES_PROJETO_BLOCO)[0]);
defined('SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE') OR define('SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE', '*');
defined('NOMES_TESTES_PERFORMANCE') OR define('NOMES_TESTES_PERFORMANCE',
	serialize(array(
		'Teste de Performance',
		'TP'
	))
);
defined('NOME_TESTE_PERFORMANCE') OR define('NOME_TESTE_PERFORMANCE', unserialize(NOMES_TESTES_PERFORMANCE)[0]);
defined('SIGLA_TESTE_PERFORMANCE') OR define('SIGLA_TESTE_PERFORMANCE', unserialize(NOMES_TESTES_PERFORMANCE)[1]);
defined('NOMES_ASSESSMENT_FINAL') OR define('NOMES_ASSESSMENT_FINAL',
	serialize(array(
		'Assessment final',
		'Apresentação final',
		'Assessment',
		'Apresentação',
		'Entrega do Projeto'
	))
);
defined('NOME_ASSESSMENT_FINAL') OR define('NOME_ASSESSMENT_FINAL', unserialize(NOMES_ASSESSMENT_FINAL)[0]);
defined('NOME_APRESENTACAO_PROJETO_FINAL') OR define('NOME_APRESENTACAO_PROJETO_FINAL', unserialize(NOMES_ASSESSMENT_FINAL)[1]);
