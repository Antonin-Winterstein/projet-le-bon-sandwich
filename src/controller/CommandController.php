<?php

namespace lbs\command\api\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class CommandController {

  private $commands = [
    ["id" =>"45RF56TH", "mail_client" =>"g@g.fr", "date_commande" =>"1-12-2020", "montant" =>50.0],
    ["id" =>"46RF56TH", "mail_client" =>"a@aaa.fr", "date_commande" =>"2-12-2020", "montant" =>45.0],
    ["id" =>"57RF56TH", "mail_client" =>"l@ll.fr", "date_commande" =>"3-12-2020", "montant" =>27.5],
    ["id" =>"01RF56TH", "mail_client" =>"m@mmm.fr", "date_commande" =>"4-12-2020", "montant" =>30.0]
  ];

  public function listCommands(Request $rq, Response $rs, array $args) : Response {
    $data = ["type"=>"collection", "count"=> count($this->commands), "commandes"=>$this->commands];

    $rs = $rs->withHeader('Content-Type', 'application/json');
    $rs->getBody()->write(json_encode($data));

    return $rs;
  }

  public function uneCommande(Request $rq, Response $rs, array $args) : Response {

    $id = $args['id'];

    $res = null;

    foreach ($this->commands as $commande) {
      if ($commande['id'] === $id) $res = $commande;
    }

    $rs = $rs->withHeader('Content-Type', 'application/json');

    if (is_null($res)) {
      $rs = $rs->withStatus(404);
      $data = ["type"=>"error", "code"=>404, "msg"=>"commande $id NOT FOUND"
      ];
    } else {
      $data = ["type" => "ressource", "commande" => $res];
    }

    $rs->getBody()->write(json_encode($data));

    return $rs;
  }
  
}