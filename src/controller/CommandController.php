<?php

namespace controller;

use models\Commande;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class CommandController {

  // private $commands = [
  //   ["id" =>"45RF56TH", "mail_client" =>"g@g.fr", "date_commande" =>"1-12-2020", "montant" =>50.0],
  //   ["id" =>"46RF56TH", "mail_client" =>"a@aaa.fr", "date_commande" =>"2-12-2020", "montant" =>45.0],
  //   ["id" =>"57RF56TH", "mail_client" =>"l@ll.fr", "date_commande" =>"3-12-2020", "montant" =>27.5],
  //   ["id" =>"01RF56TH", "mail_client" =>"m@mmm.fr", "date_commande" =>"4-12-2020", "montant" =>30.0]
  // ];

  public function listCommands(Request $rq, Response $rs, array $args) : Response {
    $commands = Commande::select('id', 'mail', 'created_at', 'montant')->get();

    foreach ($commands as $command) {
      $command['date_commande'] = date('d M Y', strtotime($command['created_at']));
      $command['mail_client'] = $command['mail'];
      unset($command['created_at'], $command['mail']);
    }

    $data = ["type"=>"collection", "count"=> count($commands), "commandes" => $commands];

    $rs = $rs->withHeader('Content-Type', 'application/json');
    $rs->getBody()->write(json_encode($data));

    return $rs;
  }

  public function uneCommande(Request $rq, Response $rs, array $args) : Response {

    $id = $args['id'];

    $command = Commande::find($id);

    if ($command) {
      $data = ["type" => "ressource", "commande" => $command->first()];
    }else{
      $rs = $rs->withStatus(404);
      $data = ["type"=>"error", "code"=>404, "msg"=>"commande $id NOT FOUND"];
    }
        
    $rs = $rs->withHeader('Content-Type', 'application/json');
    $rs->getBody()->write(json_encode($data));

    return $rs;
  }
  
}