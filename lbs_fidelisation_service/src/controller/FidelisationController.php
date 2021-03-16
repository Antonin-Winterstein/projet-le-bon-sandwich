<?php

namespace lbs\fidelisation\controller;

use lbs\fidelisation\models\Carte;
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
   * public function login : ...
   *
   * @return Response : login
   *
   */
  public function login(Request $rq, Response $rs, array $args) : Response {

    $username = $args['u'];
    // $password = $args['p'];


    $cartes = Carte::where('mail_client', '=', $username)->get();

    foreach ($cartes as $i => $c) {
      $data[] = ["carte_$i" => $c];
    }


    $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
    $rs->getBody()->write(json_encode ($data));

    return $rs;
  }

  /**
   *
   * public function Carte : liste toutes les cartes
   *
   * @return Response : la liste des cartes au format json
   *
   */
  public function cartes(Request $rq, Response $rs, array $args) : Response {

    $cartes = Carte::select()->get();

    foreach ($cartes as $i => $c) {
      $data[] = ["carte_$i" => $c];
    }


    $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
    $rs->getBody()->write(json_encode ($data));

    return $rs;
  }


  /**
   *
   * public function aCarte : liste le détail de la carte en argument de l'URI
   *
   * @return Response : la liste des cartes au format json
   *
   */
  public function aCarte(Request $rq, Response $rs, array $args) : Response {

    // $username = $args['u'];
    // $password = $args['p'];
    $id = $args['id'];
    $cartes = Carte::select()->where('id', '=', $id)->get();

    foreach ($cartes as $i => $c) {
      $data[] = ["carte_$i" => $c];
    }


    $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
    $rs->getBody()->write(json_encode ($data));

    return $rs;
  }

    /**
     *
     * public function comCarte : liste le détail de la cartes en argument de l'URI
     *
     * @return Response : la liste des commandes de la carte au format json
     *
     */
    public function comCarte(Request $rq, Response $rs, array $args) : Response {

        // $username = $args['u'];
        // $password = $args['p'];
        $id = $args['id'];
        $comCartes = Commande::select()->where('carte_id', '=', $id)->get();

        foreach ($comCartes as $i => $c) {
            $data[] = ["carte_$i" => $c];
        }

        $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
        $rs->getBody()->write(json_encode ($data));

        return $rs;
    }
}
