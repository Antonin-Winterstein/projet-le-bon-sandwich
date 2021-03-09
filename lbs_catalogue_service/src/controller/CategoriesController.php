<?php

namespace lbs\catalogue\controller;

use lbs\catalogue\utils\Writer;
use lbs\catalogue\conf\MongoConnection;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CategoriesController {

    public function __construct(\Slim\Container $c){

        $this->c = $c;
    
    }

    
    public function categories(Request $rq, Response $rs, array $args) : Response {

        try {
            $db = MongoConnection::getCatalogue();

            $categs = $db->categories->find([], []);
        
            $tab_categ = [];
            $count=0;
            foreach($categs as $categ){
                $count++;
                $tab_categ[] = [
                    "category" => [
                        "id" => $categ->id,
                        "nom" => $categ->nom,
                        "links"=>[
                            "self"=> ['href' => $this->c->router->pathFor('category', ['id'=> $categ->id])]
                        ],
                    ],
                ];
            }
    
            $data = [
                'type' => 'collection',
                'count' => $count,
                'categories' => $tab_categ,
            ];
    
            $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
            $rs->getBody()->write(json_encode($data));
            
            return $rs;
        } catch (ModelNotFoundException $e) {
            return Writer::json_error($rs, 404, "categories not found");
        }


    }

    public function aCategory(Request $rq, Response $rs, array $args) : Response {
        
        $id = intval($args['id']);

        try {

            
            $db = MongoConnection::getCatalogue();
            $categ = $db->categories->findOne( ["id" => $id], ["projection" => ["_id" => 0]] );

            $tab_categ = [
                "id" => $categ->id,
                "nom" => $categ->nom,
                "description" => $categ->description,
            ];
    
            $data = [
                'type' => 'resource',
                'category' => $tab_categ,
            ];

            $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
            $rs->getBody()->write(json_encode($data));
            
            return $rs;
        } catch (ModelNotFoundException $e) {
            return Writer::json_error($rs, 404, "category $id not found");
        }
    }

}