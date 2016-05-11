<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;

$route['cadastros/classe/turmas(.*)'] = 'cadastros/classe/turmas$1';
$route['cadastros/classe/avaliacoes(.*)'] = 'cadastros/classe/avaliacoes$1';
$route['cadastros/classe/rubricas(.*)'] = 'cadastros/classe/rubricas$1';
$route['cadastros/classe/preparar_estrutura(.*)'] = 'cadastros/classe/preparar_estrutura$1';
$route['cadastros/classe/atualizar_estrutura(.*)'] = 'cadastros/classe/atualizar_estrutura$1';
$route['cadastros/competencia/subcompetencias(.*)'] = 'cadastros/competencia/subcompetencias$1';

$route['cadastros/(:any)/(:any)/(:any)(.*)'] = 'cadastros/$1/cadastro/$2/$3$4';
$route['cadastros/(:any)/(:any)(.*)'] = 'cadastros/$1/cadastro/$2$3';
$route['cadastros/(:any)(.*)'] = 'cadastros/$1/cadastro$2';

$route['relatorios/resultados_turma/(:num)'] = 'relatorios/resultados_turma/relatorio/$1';
$route['relatorios/resultados_turma'] = 'relatorios/resultados_turma/selecionar_turma';

$route['default_controller'] = 'inicio';
