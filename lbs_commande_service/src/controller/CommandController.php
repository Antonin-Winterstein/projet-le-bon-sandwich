<?php

namespace lbs\commande\controller;

use Slim\Router;
use lbs\commande\utils\Writer;
use lbs\commande\models\Commande;
use Ramsey\Uuid\Uuid;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CommandController {

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
  public function commands(Request $rq, Response $rs, array $args) : Response {
    $commandes = Commande::select('id', 'mail', 'created_at', 'montant', 'nom')->get();

    $tab_commandes = [];

    foreach ($commandes as $commande) {

      $tab_commandes[] = [
        "commande"=>[
          "id" => $commande->id,
          "nom" => $commande->nom,
          "date" => date('Y-m-d', strtotime($commande->created_at)),
          "montant" => $commande->montant,
        ],
        "links"=>[
          "self"=> ["/commandes/" . $commande->id]
        ]
        ];
    }

    $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
    $rs->getBody()->write(json_encode ( [
      'type' => 'collection',
      'count' => count($commandes),
      'commandes' => $tab_commandes
    ]));

    return $rs;
  }


  /**
   * 
   * public function aCommand : liste le détail de la commande en argument de l'URI
   * 
   * @return Response : le détail d'une commande au format json
   * 
   */
  public function aCommand(Request $rq, Response $rs, array $args) : Response {

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

    /**
     * 
     * public function addCommand : création simplifiée d'une nouvelle commande
     *        - Les données sont transmises au format json
     *        - Retourne un token
     * 
     * @param Request $rq
     * @param Response $rs
     * @return Response
     * 
     */
    public function addCommand(Request $rq, Response $rs) : Response {

      $commande_data = $rq->getParsedBody();

      if (!isset($commande_data['nom_client'])) {
        return Writer::json_error($rs, 400, " information manquante : nom_client");
      }

      if (!isset($commande_data['mail_client'])) {
        return Writer::json_error($rs, 400, " information manquante : mail_client");
      }

      if (!isset($commande_data['livraison']['date'])) {
        return Writer::json_error($rs, 400, " information manquante : livraison(date)");
      }

      if (!isset($commande_data['livraison']['heure'])) {
        return Writer::json_error($rs, 400, " information manquante : livraison(heure)");
      }

      try {

        $c = new Commande();

        $c->id = Uuid::uuid4();
        $c->nom = filter_var($commande_data['nom_client'], FILTER_SANITIZE_STRING);
        $c->mail = filter_var($commande_data['mail_client'], FILTER_SANITIZE_EMAIL);
        $c->livraison = \Datetime::createFromFormat('d-m-Y H:i',
          $commande_data['livraison']['date'] . ' ' .
          $commande_data['livraison']['heure']);
        $c->status = Commande::CREATED;

        $c->token = bin2hex(random_bytes(32));
        $c->montant = 0;

        $c->save();

        return Writer::json_output($rs, 201, ['commande'=> $c])
          ->withHeader('Location', $this->c->router->pathFor('commande', ['id'=> $c->id]));

      } catch (\Exception $e) {
        return Writer::json_error($rs, 500, $e->getMessage());
      }

    }
  
}