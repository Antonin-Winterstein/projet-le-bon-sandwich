<?php

namespace lbs\fidelisation\controller;

use lbs\commande\models\Commande;
use lbs\fidelisation\models\Carte;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Console\Command\Command;

class FidelisationController {

  private $c;

  public function __construct(\Slim\Container $c){

    $this->c = $c;

  }


  
  /**
   * 
   * public function commands : liste toutes les commandes
   * 
   * @return Response : la liste des commandes au format json
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
  
}