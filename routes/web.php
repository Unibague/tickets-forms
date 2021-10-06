<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return 'hey';
});
//$router->get('/issues', 'IssuesController@index');

$router->get('/issues', 'FormController@index');
$router->get('/issues/prueba', 'IssuesController@createIssue');
$router->get('/issues/{issue_id}', 'IssuesController@show');
$router->get('/issues/user/{code_user}', 'IssuesController@getUserIssues');

/*RUTAS PARA FORMULARIOS*/

//$router->get('/conversions/{conversion_id}', 'FormController@generateResults');
$router->get('/conversions/{conversion_id}', 'FormController@generateResults');


