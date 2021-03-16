<?php

namespace lbs\fidelisation\controller;

use lbs\fidelisation\models\Carte;
use lbs\fidelisation\utils\Writer;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class FidelisationController {

  private $c;

  public function __construct(\Slim\Container $c){

    $this->c = $c;

  }

  
  /**
   * 
   * public function login : liste toutes les commandes
   * 
   * @return Response : la liste des commandes au format json
   * 
   */
  public function fidelisations(Request $rq, Response $rs, array $args) : Response {

    $cartes = Carte::select()->get();
    
    $data = ['cc' => 'oui'];
    
    foreach ($cartes as $i => $c) {
      $data[] = ["carte_$i" => $c];
    }


    return Writer::json_output($rs, 200, $data);
  }
  
  
  /**
   * 
   * public function login : liste toutes les commandes
   * 
   * @return Response : la liste des commandes au format json
   * 
   */
  public function aFidelisation(Request $rq, Response $rs, array $args) : Response {

    // $username = $args['u'];
    // $password = $args['p'];


    $cartes = Carte::select()->get();
    
    $data = ['cc' => 'oui'];
    
    foreach ($cartes as $i => $c) {
      $data[] = ["carte_$i" => $c];
    }


    $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
    $rs->getBody()->write(json_encode ($data));

    return $rs;
  }
  


}