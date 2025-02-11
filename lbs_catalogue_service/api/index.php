<?php

require_once  __DIR__ . '/../src/vendor/autoload.php';

use lbs\catalogue\conf\MongoConnection;
use lbs\catalogue\controller\CatalogueController;
use lbs\catalogue\controller\CategoriesController;

$api_settings = require_once __DIR__ . '/../src/conf/api_settings.php';
$api_errors = require_once __DIR__ . '/../src/conf/api_errors.php';

$api_container = new \Slim\Container(array_merge($api_settings, $api_errors));

$app = new \Slim\App($api_container);


//*Connexion avec MongoDb
// $connection = new \MongoDB\Client("mongodb://dbcat/");

// $db = $connection->catalogue;

MongoConnection::createConnexion();

//* Les objets de type requête

$app->get('/sandwichs[/]', CatalogueController::class . ':sandwichs')
    ->setName('sandwichs');

$app->get('/sandwichs/{ref}[/]', CatalogueController::class . ':aSandwich')
    ->setName('sandwich');

$app->get('/categories[/]', CategoriesController::class . ':categories')
    ->setName('categories');

$app->get('/categories/{id}[/]', CategoriesController::class . ':aCategory')
    ->setName('category');
    
$app->get('/sandwichs/{ref}/categories[/]', CatalogueController::class . ':aSandwichCategories')
    ->setName('sandwichCategories');
    
$app->get('/categories/{id}/sandwichs[/]', CategoriesController::class . ':aCategorySandwichs')
    ->setName('categorySandwichs');


$app->run();