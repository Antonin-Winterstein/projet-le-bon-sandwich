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

    /**
     * 
     * public function sandwichs : liste tous les sandwichs
     * Possibilité de trier, changer l'ordre, paginer
     * 
     * @param Request $rq
     * @ param Response $rs
     * @return Response : la liste des sandwichs au format json
     * 
     */
    public function sandwichs(Request $rq, Response $rs, array $args) : Response {
        try{
            $db = MongoConnection::getCatalogue();

            //* Pagination, ordre par attributs et tri par type de pain
            $sortFilter = $rq->getQueryParam('sort', null);
            $type = $rq->getQueryParam('t', null);
            $page = $rq->getQueryParam('page', 1);
            $size = intval($rq->getQueryParam('size', 15));

            if($page <= 0) $page = 1;
            if($size <= 0) $size = 15;

            if(is_null($type)) $condition = [];
            else $condition = ['type_pain' => $type] ;

            $count = $db->sandwiches->count($condition);            
            $last = intdiv($count, $size)+1;
            if($page > $last) $page = $last;
            
            if(!is_null($sortFilter)){
                $sort = [$sortFilter => 1];
            }else $sort = ['ref' => 1];

            $sandwichs = $db->sandwiches->find($condition, [
                'skip' => ($page - 1) * $size,
                'limit' => $size,
                'sort' => $sort,
            ]);

            $tab_sandwichs = [];

            foreach ($sandwichs as $sandwich) {
                $tab_sandwichs[] = [
                    "sandwich" => [ 
                        "ref" => $sandwich->ref,
                        "nom" => $sandwich->nom,
                        "type_pain" => $sandwich->type_pain,
                        "prix" => $sandwich->prix,
                    ],
                    "links"=>[
                    "self"=> ['href' => $this->c->router->pathFor('sandwich', ['ref'=> $sandwich->ref])]
                ]];
            }

            $url_sandwichs = $this->c->router->pathFor('sandwichs', []);
            $next = (($page + 1 > $last) ? $last : $page + 1);
            $prev = (($page - 1 < 1) ? 1 : $page - 1);

            //* Mise en forme de la collection de commande
            $data = [
                'type' => 'collection',
                'count' => $count,
                'size' => $size,
                'date' => date('d-m-Y'),
                'sandwichs' => $tab_sandwichs,
                "links" => [
                    'next' => ['href' => $url_sandwichs . "?page=$next&size=$size"],
                    'prev' => ['href' => $url_sandwichs . "?page=$prev&size=$size"],
                    'first' => ['href' => $url_sandwichs . "?page=1&size=$size"],
                    'last' => ['href' => $url_sandwichs . "?page=$last&size=$size"],

                ]
            ];
    
            return Writer::json_output($rs, 200, $data);

        }catch(ModelNotFoundException $e){
            return Writer::json_error($rs, 404, "catalogue not found");
        }
    }

    /**
     * 
     * public function aSandwich : affiche le détail d'un sandwich
     * 
     * @param Request $rq
     * @param Response $rs
     * @return Response : le sandwich au format json
     * 
     */
    public function aSandwich(Request $rq, Response $rs, array $args) : Response {
        
        $ref = $args['ref'];

        try{
            $db = MongoConnection::getCatalogue();
            $sandwich = $db->sandwiches->findOne( ["ref" => $ref], ["projection" => ["_id" => 0]] );
            $categories = $db->categories->find( ["nom" => ['$in' => $sandwich->categories]], ["projection" => ["_id" => 0]] );
            
            $tab_categories = [];
            foreach ($categories as $c) {
                $tab_categories[] = [
                    'id' => $c->id,
                    'nom' => $c->nom,
                ];
            }

            //* Mise en forme de tous les attributs de la ressource
            $tab_sandwich = [
                "ref" => $sandwich->ref,
                "nom" => $sandwich->nom,
                "description" => $sandwich->description,
                "type_pain" => $sandwich->type_pain,
                "prix" => $sandwich->prix,
                "categories" => $tab_categories,
            ];
            

            //* Mise en forme de la ressource
            $data = [
                'type' => 'resource',
                'date' => date('d-m-Y'),
                "links"=>[
                    "self"=> ['href' => $this->c->router->pathFor('sandwich', ['ref'=> $sandwich->ref])],
                    "categories"=> ['href' => $this->c->router->pathFor('sandwichCategories', ['ref'=> $sandwich->ref])],
                ],
                'sandwich' => $tab_sandwich,
            ];

            return Writer::json_output($rs, 200, $data);


        }catch(ModelNotFoundException $e){
            return Writer::json_error($rs, 404, "sandwich $ref not found");
        }
    }

    /**
     * 
     * public function aSandwichCategories : affiche les catégories d'un sandwich
     * 
     * @param Request $rq
     * @param Response $rs
     * @return Response : les catégories du sandwich au format json
     * 
     */
    public function aSandwichCategories(Request $rq, Response $rs, array $args) : Response {

        $ref = $args['ref'];

        try{
            $db = MongoConnection::getCatalogue();
            $sandwich = $db->sandwiches->findOne( ["ref" => $ref], ["projection" => ["_id" => 0]] );
            $categories = $db->categories->find( ["nom" => ['$in' => $sandwich->categories]], ["projection" => ["_id" => 0]] );
            
            $tab_categories = [];
            foreach ($categories as $c) {
                $tab_categories[] = [
                    'id' => $c->id,
                    'nom' => $c->nom,
                    'description' => $c->description,
                    "links"=>[
                        "self"=> ['href' => $this->c->router->pathFor('category', ['id'=> $c->id])],
                    ],
                ];
            }          

            //* Mise en forme de la ressource
            $data = [
                'type' => 'collection',
                'count' => count($tab_categories),
                'date' => date('d-m-Y'),
                "links"=>[
                    "self"=> ['href' => $this->c->router->pathFor('sandwichCategories', ['ref'=> $sandwich->ref])],
                ],
                'categories' => $tab_categories,
            ];

            return Writer::json_output($rs, 200, $data);


        }catch(ModelNotFoundException $e){
            return Writer::json_error($rs, 404, "sandwich $ref not found");
        }
    }
}