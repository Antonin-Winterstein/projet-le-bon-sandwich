<?php

require_once __DIR__ . '/../src/vendor/autoload.php';

use controller\CommandController;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;


$api_settings = require_once __DIR__ . '/../src/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/conf/api_errors.php';

$api_container = new \Slim\Container(array_merge($api_settings, $api_errors));

$app = new \Slim\App($api_container);

/*Config et Connexion à la BDD*/
$config = parse_ini_file($api_container->get('settings')['dbfile']);

$db = new Illuminate\Database\Capsule\Manager();

/* une instance de connexion  */
$db->addConnection($config); /* configuration avec nos paramètres */
$db->setAsGlobal();            /* rendre la connexion visible dans tout le projet */
$db->bootEloquent();           /* établir la connexion */

//* Les objets de type requête

$app->get('/commandes[/]', CommandController::class . ':listCommands');

$app->get('/commandes/{id}[/]', CommandController::class . ':uneCommande');

//! Sur Internet ou Postman écrire une URL de ce genre pour lister toutes les commandes :
//! http://localhost/TD1_ROUTES_JSON/app/commandes

//! Sur Internet ou Postman écrire une URL de ce genre pour afficher la commande avec l'ID passé en paramètre :
//! http://localhost/TD1_ROUTES_JSON/app/commandes/45RF56TH

//* Déclenche le traitement par le framework de la requête courante et la compare dans l'ordre de chacune des routes
$app->run();