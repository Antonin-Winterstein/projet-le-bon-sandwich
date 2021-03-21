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

    /**
     * 
     * public function categories : liste les catégories
     * 
     * @param Request $rq
     * @param Response $rs
     * @return Response : liste les catégories au format json
     * 
     */
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
    
            return Writer::json_output($rs, 200, $data);
        } catch (ModelNotFoundException $e) {
            return Writer::json_error($rs, 404, "categories not found");
        }


    }

    /**
     * 
     * public function aCategory : affiche le détail d'une catégorie
     * 
     * @param Request $rq
     * @param Response $rs
     * @return Response : affiche la catégorie au format json
     * 
     */
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
                'date' => date('d-m-Y'),
                'category' => $tab_categ,
                "links"=>[
                    "sanchwichs"=> ['href' => $this->c->router->pathFor('categorySandwichs', ['id'=> $categ->id])],
                    "self"=> ['href' => $this->c->router->pathFor('category', ['id'=> $categ->id])],
                ],

            ];

            return Writer::json_output($rs, 200, $data);

        } catch (ModelNotFoundException $e) {
            return Writer::json_error($rs, 404, "category $id not found");
        }
    }

    /**
     * 
     * public function aCategorySandwichs : liste les sandwichs d'une catégorie
     * 
     * @param Request $rq
     * @param Response $rs
     * @return Response : lsite les sandwichs de la catégorie au format json
     * 
     */
    public function aCategorySandwichs(Request $rq, Response $rs, array $args) : Response {
        
        $id = intval($args['id']);

        try {

            $db = MongoConnection::getCatalogue();
            $categ = $db->categories->findOne( ["id" => $id], ["projection" => ["_id" => 0]] );

            $sandwichs = $db->sandwiches->find(['categories' => $categ->nom], []);

            foreach ($sandwichs as $sandwich) {
                $tab_sandwichs[] = [
                    "sandwich" => [ 
                        "ref" => $sandwich->ref,
                        "nom" => $sandwich->nom,
                        "description" => $sandwich->description,
                        "type_pain" => $sandwich->type_pain,
                        "prix" => $sandwich->prix,
                    ],
                    "links"=>[
                    "self"=> ['href' => $this->c->router->pathFor('sandwich', ['ref'=> $sandwich->ref])]
                ]];
            }

            $data = [
                'type' => 'collection',
                'count' => count($tab_sandwichs),
                'date' => date('d-m-Y'),
                'sandwichs' => $tab_sandwichs,
            ];

            return Writer::json_output($rs, 200, $data);
            
        } catch (ModelNotFoundException $e) {
            return Writer::json_error($rs, 404, "category $id not found");
        }
    }
}