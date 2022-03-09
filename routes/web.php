<?php


/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return 'servicios ;)';
});
//All issues
$router->get('/issues', 'IssuesController@index');
//Create issue
$router->post('/issues', 'IssuesController@createIssue');
//Get specific issue
$router->get('/issues/{issue_id}', 'IssuesController@show');
//Get user issues
$router->get('/issues/user/{code_user}', 'IssuesController@getUserIssues');
//Add USER note to issue
$router->post('/issues/{issue_id}/notes', 'IssuesController@addUserNoteToIssue');

/*RUTAS PARA FORMULARIOS*/

$router->get('/conversions/{conversion_id}', 'FormController@generateResults');

// Enviar comentario a el usuario
$router->post('/comments/issue/{issue_id}/', 'IssuesController@sendMessageToUserByEmail');
$router->get('/comments/issue/{issue_id}/new', 'IssuesController@sendMessageToUserForm');


