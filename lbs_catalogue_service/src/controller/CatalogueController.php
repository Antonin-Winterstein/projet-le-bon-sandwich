<?php

namespace lbs\catalogue\controller;

use lbs\catalogue\utils\Writer;
use lbs\catalogue\conf\MongoConnection;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CatalogueController {

    public function __construct(\Slim\Container $c){

        $this->c = $c;
    
    }

    
    public function catalogue(Request $rq, Response $rs, array $args) : Response {
        try{
            $db = MongoConnection::getCatalogue();
    
            $sandwichs = $db->sandwiches->find([ ], []);
    
            $tab_sandwichs = [];
            $count = 0;

            foreach ($sandwichs as $sandwich) {
                $tab_sandwichs[] = [
                    "sandwich" => [ 
                        "ref" => $sandwich->ref,
                        "nom" => $sandwich->nom,
                    ],
                    "links"=>[
                    "self"=> ["/sandwichs/" . $sandwich->_id]
                ]];
                $count++;
            }

            //* Mise en forme de la collection de commande
            $data = [
                'type' => 'collection',
                'count' => $count,
                'commandes' => $tab_sandwichs
            ];
    
            $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
            $rs->getBody()->write(json_encode($data));
            
            return $rs;

        }catch(ModelNotFoundException $e){
            return Writer::json_error($rs, 404, "catalogue not found");
        }
    }
}