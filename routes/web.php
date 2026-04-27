<?php


/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return 'servicios ;)';
});
//All issues
$router->get('/issues', 'IssuesController@index');

$router->get('/customFields', 'IssuesController@indexCustomFields');


//Create issue
$router->post('/issues', 'IssuesController@createIssue');
//Get specific issue
$router->get('/issues/{issue_id}', 'IssuesController@show');
//Get user issues
$router->get('/issues/user/{code_user}', 'IssuesController@getUserIssues');

$router->get('/issues/user/{code_user}/notClosed', 'IssuesController@getUserIssuesNotClosed');
//Add USER note to issue
$router->post('/issues/{issue_id}/notes', 'IssuesController@addUserNoteToIssue');

/*RUTAS PARA FORMULARIOS*/

$router->get('/conversions/{conversion_id}', 'FormController@generateResults');

// Enviar comentario al usuario
$router->post('/comments/issue/{issue_id}/', 'IssuesController@sendMessageToUserByEmail');
//$router->post('/comments/issue/{issue_id}/', 'IssuesController@testingSendMessageToUserEmail');
$router->get('/comments/issue/{issue_id}/new', 'IssuesController@sendMessageToUserForm');

//Previsualizar plantilla de correo
//$router->get('/preview-email', 'IssuesController@previewSendMessageToUserEmail');

/* RUTAS PQRS */
$router->get('/pqrs/form-data', 'PqrController@formData');
$router->get('/pqrs/lideres',   'PqrController@lideres');
$router->get('/pqrs/usuarios',  'PqrController@usuarios');
$router->post('/pqrs',              'PqrController@store');
$router->patch('/pqrs/{issue_id}',  'PqrController@update');
$router->get('/pqrs/{issue_id}',    'PqrController@show');

