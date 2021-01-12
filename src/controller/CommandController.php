<?php

namespace controller;

use models\Commande;
use utils\Writer;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommandController {

  // private $commands = [
  //   ["id" =>"45RF56TH", "mail_client" =>"g@g.fr", "date_commande" =>"1-12-2020", "montant" =>50.0],
  //   ["id" =>"46RF56TH", "mail_client" =>"a@aaa.fr", "date_commande" =>"2-12-2020", "montant" =>45.0],
  //   ["id" =>"57RF56TH", "mail_client" =>"l@ll.fr", "date_commande" =>"3-12-2020", "montant" =>27.5],
  //   ["id" =>"01RF56TH", "mail_client" =>"m@mmm.fr", "date_commande" =>"4-12-2020", "montant" =>30.0]
  // ];

  public function listCommands(Request $rq, Response $rs, array $args) : Response {
    $commandes = Commande::select('id', 'mail', 'created_at', 'montant')->get();

    $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
    $rs->getBody()->write(json_encode ( [
      'type' => 'collection',
      'count' => count($commandes),
      'commandes' => $commandes->toArray()
    ]));

    return $rs;
  }

  public function uneCommande(Request $rq, Response $rs, array $args) : Response {

    $id = $args['id'];

    try {
      $commande = Commande::select(['id', 'livraison', 'nom', 'mail', 'status', 'montant'])->with('items')->where('id', '=', $id)->firstOrFail();

      $data = [
        'type' => 'resource',
        'commande' => $commande->toArray()
      ];

      return Writer::json_output($rs, 200, $data);

    } catch(ModelNotFoundException $e) {

      // ($this->c->get('logger.error'))->error("command $id not found", [404]);
      return Writer::json_error($rs, 404, "command $id not found");
    }
  }
  
}