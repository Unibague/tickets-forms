<?php


/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|---------------------------------------------------------------------getUserIssues-----
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    \Illuminate\Support\Facades\Mail::to('juan.ospina@unibague.edu.co')->send(new \App\Mail\userMessageNotification(150, 'Solucionado, claro que si'));
});
//$router->get('/issues', 'IssuesController@index');

$router->get('/issues', 'IssuesController@index');
$router->post('/issues', 'IssuesController@createIssue');
$router->get('/issues/{issue_id}', 'IssuesController@show');
$router->get('/issues/user/{code_user}', 'IssuesController@getUserIssues');
$router->post('/issues/{issue_id}/notes', 'IssuesController@addUserNoteToIssue');

/*RUTAS PARA FORMULARIOS*/

$router->get('/conversions/{conversion_id}', 'FormController@generateResults');

// Enviar comentario a el usuario
$router->post('/comments/issue/{issue_id}/', 'IssuesController@sendMessageToUserByEmail');
$router->get('/comments/issue/{issue_id}/new', 'IssuesController@sendMessageToUserForm');


