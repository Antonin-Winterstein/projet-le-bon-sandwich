<?php

require_once __DIR__ . '/../src/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$config = require_once __DIR__ . '/../src/conf/settings.php';
$c = new \Slim\Container($config);

$app = new \Slim\App($c);

//* Les objets de type requête

$app->get('/commandes[/]', \lbs\command\api\controller\CommandController::class . ':listCommands');

$app->get('/commandes/{id}[/]', \lbs\command\api\controller\CommandController::class . ':uneCommande');

//! Sur Internet ou Postman écrire une URL de ce genre pour lister toutes les commandes :
//! http://localhost/TD1_ROUTES_JSON/app/commandes

//! Sur Internet ou Postman écrire une URL de ce genre pour afficher la commande avec l'ID passé en paramètre :
//! http://localhost/TD1_ROUTES_JSON/app/commandes/45RF56TH



//* Déclenche le traitement par le framework de la requête courante et la compare dans l'ordre de chacune des routes
$app->run();