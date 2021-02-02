<?php

require_once  __DIR__ . '/../src/vendor/autoload.php';

use lbs\catalogue\conf\MongoConnection;
use lbs\catalogue\controller\CatalogueController;

$api_settings = require_once __DIR__ . '/../src/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/conf/api_errors.php';

$api_container = new \Slim\Container(array_merge($api_settings, $api_errors));

$app = new \Slim\App($api_container);


//*Connexion avec MongoDb
// $connection = new \MongoDB\Client("mongodb://dbcat/");

// $db = $connection->catalogue;

MongoConnection::createConnexion();

//* Les objets de type requête

$app->get('/sandwichs[/]', CatalogueController::class . ':sandwichs');

$app->get('/sandwichs/{ref}[/]', CatalogueController::class . ':aSandwich')
    ->setName('sandwich');

$app->run();

// //Recherche des sandwiches
// $sandwichs = $db->sandwiches->find([ ], []);
// var_dump($sandwichs);
// //Si il n'y à pas de résultat
// if(is_null($sandwichs)){
//     print"Pas de sndwiche dans notre catalgue";
//     die();
// }
// //Liste des sandwiches
// foreach ($sandwichs as $sdchs){
//     print $sdchs->nom.' '.$sdchs->type_pain.'</br>';
// }