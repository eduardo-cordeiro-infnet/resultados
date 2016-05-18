<?php
defined('URL_BASE_LMS') OR define('URL_BASE_LMS', 'http://lms.infnet.edu.br/moodle');
defined('SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE') OR define('SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE', '*');
defined('NOMES_ASSESSMENT_FINAL') OR define('NOMES_ASSESSMENT_FINAL',
	serialize(array(
		'Assessment final',
		'Apresentação final',
		'AT',
		'Assessment',
		'AP',
		'Apresentação'
	))
);
defined('NOME_ASSESSMENT_FINAL') OR define('NOME_ASSESSMENT_FINAL', unserialize(NOMES_ASSESSMENT_FINAL)[0]);
defined('NOME_APRESENTACAO_PROJETO_FINAL') OR define('NOME_APRESENTACAO_PROJETO_FINAL', unserialize(NOMES_ASSESSMENT_FINAL)[1]);
