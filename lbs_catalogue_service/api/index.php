<?php

require_once  __DIR__ . '/../src/vendor/autoload.php';

/*use \Psr\Http\Message\ServerRequestInterface as Request ;
use \Psr\Http\Message\ResponseInterface as Response ;

$app = new \Slim\App();

$app->get('/test[/]', function(Request $rq, Response $rs) : Response{
   $rs->getBody()->write("hello word : service catalague");
   return $rs;
});

$app->run();*/

//Connexion avec MongoDb
$connection = new \MongoDB\Client("mongodb://dbcat/");

$db = $connection->cataogue;
//Recherche des sandwiches
$sandwichs = $db->sandwiches->find([ ], []);
var_dump($sandwichs);
//Si il n'y à pas de résultat
if(is_null($sandwichs)){
    print"Pas de sndwiche dans notre catalgue";
    die();
}
//Liste des sanfwiches
foreach ($sandwichs as $sdchs){
    print $sdchs->nom.' '.$sdchs->type_pain.'</br>';
}