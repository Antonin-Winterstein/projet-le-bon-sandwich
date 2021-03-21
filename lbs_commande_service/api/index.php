<?php

require_once  __DIR__ . '/../src/vendor/autoload.php';

use lbs\commande\controller\CommandController;
use lbs\commande\middlewares\DataValidation;
use lbs\commande\middlewares\JwtToken;
use lbs\commande\middlewares\Token;
use Symfony\Component\Console\Command\Command;

$api_settings = require_once __DIR__ . '/../src/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/conf/api_errors.php';

$api_container = new \Slim\Container(array_merge($api_settings, $api_errors));

$app = new \Slim\App($api_container);


//*Config et Connexion à la BDD
$config = parse_ini_file($api_container->get('settings')['dbfile']);

$db = new Illuminate\Database\Capsule\Manager();

/* une instance de connexion */
$db->addConnection($config); /* configuration avec nos paramètres */
$db->setAsGlobal();          /* rendre la connexion visible dans tout le projet */
$db->bootEloquent();         /* établir la connexion */


//* Les objets de type requête

$app->get('/commandes[/]', CommandController::class . ':commands');

$app->get('/commandes/{id}[/]', CommandController::class . ':aCommand')
    ->add(Token::class . ':checkToken')
    ->setName('commande');

$app->post('commandes/{id}/paiement[/]', CommandController::class . ':payACommand')
    ->add(Token::class . ':checkToken')
    ->add(JwtToken::class . ':checkToken');

$validators = DataValidation::PostCommandValidators();
$app->post('/commandes[/]', CommandController::class . ':addCommand')
    ->add(new \DavidePastore\Slim\Validation\Validation($validators));

//* Déclenche le traitement par le framework de la requête courante et la compare dans l'ordre de chacune des routes
try {
    $app->run();
} catch (Throwable $e) {

}