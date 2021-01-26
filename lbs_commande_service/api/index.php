<?php

require_once  __DIR__ . '/../src/vendor/autoload.php';

require_once __DIR__ . '/../src/vendor/autoload.php';

use lbs\commande\controller\CommandController;

$api_settings = require_once __DIR__ . '/../src/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/conf/api_errors.php';

$api_container = new \Slim\Container(array_merge($api_settings, $api_errors));

$app = new \Slim\App($api_container);


//*Config et Connexion à la BDD
$config = parse_ini_file($api_container->get('settings')['dbfile']);

$db = new Illuminate\Database\Capsule\Manager();

/* une instance de connexion  */
$db->addConnection($config); /* configuration avec nos paramètres */
$db->setAsGlobal();          /* rendre la connexion visible dans tout le projet */
$db->bootEloquent();         /* établir la connexion */


//* Les objets de type requête

$app->get('/commandes[/]', CommandController::class . ':commands');

$app->get('/commandes/{id}[/]', CommandController::class . ':aCommand')
    ->setName('commande');

$app->post('/commandes[/]', CommandController::class . ':addCommand');

//! Sur Internet ou Postman écrire une URL de ce genre pour lister toutes les commandes :
//! http://localhost/TD1_ROUTES_JSON/app/commandes

#use \Psr\Http\Message\ServerRequestInterface as Request ;
#use \Psr\Http\Message\ResponseInterface as Response ;


//* Déclenche le traitement par le framework de la requête courante et la compare dans l'ordre de chacune des routes
try {
    $app->run();
} catch (Throwable $e) {

}