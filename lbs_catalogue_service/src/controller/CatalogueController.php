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