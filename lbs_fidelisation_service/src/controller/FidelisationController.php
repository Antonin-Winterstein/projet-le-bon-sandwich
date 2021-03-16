<?php

namespace lbs\fidelisation\controller;

use lbs\fidelisation\models\Carte;
use \Psr\Http\Message\ResponseInterface as Response;

class FidelisationController {

  private $c;

  public function __construct(\Slim\Container $c){

    $this->c = $c;

  }
  
  /**
   * 
   * public function fidelisation : ...
   * 
   * @return Response : login
   * 
   */
  public function login(Request $rq, Response $rs, array $args) : Response {

    // $username = $args['u'];
    // $password = $args['p'];


    $cartes = Carte::select('nom_client')->get();
    
    $data = ['cc' => 'oui'];
    
    foreach ($cartes as $i => $c) {
      $data[] = ["carte_$i" => $c];
    }


    $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
    $rs->getBody()->write(json_encode ($data));

    return $rs;
  }

    /**
     *
     * public function fidelisations : liste toutes les fidelisations
     *
     * @return Response : la liste des fidelisations au format json
     *
     */
    public function cartes(Request $rq, Response $rs, array $args) : Response {
        $cartes = Carte::select('id', 'mail', 'livraison', 'montant', 'nom', 'status', 'token')->get();

        //* Mise en forme de toutes les cartes en tableau
        $tab_commandes = [];

        foreach ($cartes as $carte) {

            $tab_commandes[] = [
                "commande"=>[
                    "token" => $carte->token,
                    "id" => $carte->id,
                    "nom" => $carte->nom,
                    "date_livraison" => date('Y-m-d', strtotime($carte->livraison)),
                    "statut" => $this->commandStatus($carte->status),
                    "montant" => $carte->montant,
                ],
                "links"=>[
                    "self"=> "/commandes/" . $carte->id . "?token=" . $carte->token . "/"
                ]];
        }

        //* Mise en forme de la collection de commande
        $data = [
            'type' => 'collection',
            'count' => count($cartes),
            'commandes' => $tab_commandes
        ];


        $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
        $rs->getBody()->write(json_encode ($data));

        return $rs;
    }
  
}