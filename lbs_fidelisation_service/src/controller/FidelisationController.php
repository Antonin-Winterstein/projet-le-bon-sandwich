<?php

namespace lbs\fidelisation\controller;

use lbs\fidelisation\models\Carte;
use lbs\fidelisation\utils\Writer;

use lbs\fidelisation\models\Commande;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class FidelisationController {

  private $c;

  public function __construct(\Slim\Container $c){

    $this->c = $c;

  }


  /**
   *
   * public function Cartes : liste toutes les cartes
   *
   * @return Response : la liste des cartes au format json
   *
   */
  public function cartes(Request $rq, Response $rs, array $args) : Response {

    $cartes = Carte::select()->get();

    foreach ($cartes as $i => $c) {
      $data[] = ["carte_$i" => $c];
    }


    return Writer::json_output($rs, 200, $data);
  }


  /**
   *
   * public function aCarte : liste le détail de la carte en argument de l'URI
   *
   * @return Response : la liste des cartes au format json
   *
   */
  public function aCarte(Request $rq, Response $rs, array $args) : Response {

    

    $id = $args['id'];
    $carte = Carte::select()->where('id', '=', $id)->first();

    $data[] = ["carte" => $carte];

    $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
    $rs->getBody()->write(json_encode ($data));

    return $rs;
  }

    /**
     *
     * public function commmandesCarte : liste le détail de la cartes en argument de l'URI
     *
     * @return Response : la liste des commandes de la carte au format json
     *
     */
    public function commandesCarte(Request $rq, Response $rs, array $args) : Response {
        $id = $args['id'];
        $commandes = Commande::select()->where('carte_id', '=', $id)->get();

        foreach ($commandes as $commande) {
          $data[] = ["commandes" => $commande];
        }

        $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
        $rs->getBody()->write(json_encode ($data));

        return $rs;
    }
}
