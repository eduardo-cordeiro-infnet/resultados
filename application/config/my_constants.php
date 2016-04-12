<?php
defined('URL_BASE_LMS') OR define('URL_BASE_LMS', 'http://lms.infnet.edu.br/moodle');
defined('SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE') OR define('SUBCOMPETENCIA_SIMBOLO_OBRIGATORIEDADE', '*');
defined('NOMES_ASSESSMENT_FINAL') OR define('NOMES_ASSESSMENT_FINAL',
	serialize(array(
		'Assessment final',
		'AT',
		'Assessment',
		'AP',
		'Apresentação',
		'Apresentação final'
	))
);
defined('NOME_ASSESSMENT_FINAL') OR define('NOME_ASSESSMENT_FINAL', unserialize(NOMES_ASSESSMENT_FINAL)[0]);
