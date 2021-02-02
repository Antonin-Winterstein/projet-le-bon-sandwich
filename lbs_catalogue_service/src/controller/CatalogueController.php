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

    
    public function sandwichs(Request $rq, Response $rs, array $args) : Response {
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
                        "type_pain" => $sandwich->type_pain,
                        "prix" => $sandwich->prix,
                    ],
                    "links"=>[
                    "self"=> [$this->c->router->pathFor('sandwich', ['ref'=> $sandwich->ref])]
                ]];
                $count++;
            }

            //* Mise en forme de la collection de commande
            $data = [
                'type' => 'collection',
                'count' => $count,
                'date' => date('d-m-Y'),
                'sandwichs' => $tab_sandwichs,
            ];
    
            $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
            $rs->getBody()->write(json_encode($data));
            
            return $rs;

        }catch(ModelNotFoundException $e){
            return Writer::json_error($rs, 404, "catalogue not found");
        }
    }

    public function aSandwich(Request $rq, Response $rs, array $args) : Response {
        
        $ref = $args['ref'];

        try{
            $db = MongoConnection::getCatalogue();
            $sandwich = $db->sandwiches->findOne( ["ref" => $ref], ["projection" => ["_id" => 0]] );


            //* Mise en forme de tous les attributs de la ressource
            $tab_sandwich = [
                "ref" => $sandwich->ref,
                "nom" => $sandwich->nom,
                "description" => $sandwich->description,
                "type_pain" => $sandwich->type_pain,
                "prix" => $sandwich->prix,
                "image" => $sandwich->image,
                "categories" => $sandwich->categories,
            ];

            //* Mise en forme de la ressource
            $data = [
                'type' => 'resource',
                'sandwich' => $tab_sandwich,
            ];

            $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
            $rs->getBody()->write(json_encode($data));
            
            return $rs;


        }catch(ModelNotFoundException $e){
            return Writer::json_error($rs, 404, "sandwich $ref not found");
        }
    }
}