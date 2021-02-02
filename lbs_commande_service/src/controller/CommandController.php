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
   * private function commandStatus : transforme le statut de la commande d'entier à chaine de caractère
   * 
   * @param int $status : le statut de la commande
   * @return string : le statut sous forme de chaine de caractère
   * 
   */
  private function commandStatus(int $status)
  {
    switch ($status) {
      case 1:
        return '1 : en cours de paiement';
        break;

      case 2:
        return '2 : en cours de traitement';
        break;

      case 3:
        return '3 : en cours de livraison';
        break;

      case 4:
        return '4 : livrée';
        break;
      
      default:
        return 'Erreur : statut inconnu';
        break;
    }
  }


  /**
   * 
   * public function commands : liste toutes les commandes
   * 
   * @return Response : la liste des commandes au format json
   * 
   */
  public function commands(Request $rq, Response $rs, array $args) : Response {
    $commandes = Commande::select('id', 'mail', 'livraison', 'montant', 'nom', 'status')->get();

    //* Mise en forme de toutes les commandes en tableau
    $tab_commandes = [];

    foreach ($commandes as $commande) {

      $tab_commandes[] = [
        "commande"=>[
          "id" => $commande->id,
          "nom" => $commande->nom,
          "date_livraison" => date('Y-m-d', strtotime($commande->livraison)),
          "statut" => $this->commandStatus($commande->status),
          "montant" => $commande->montant,
        ],
        "links"=>[
          "self"=> ["/commandes/" . $commande->id]
      ]];
    }

    //* Mise en forme de la collection de commande
    $data = [
      'type' => 'collection',
      'count' => count($commandes),
      'commandes' => $tab_commandes
    ];


    $rs = $rs->withStatus(200)->withHeader('Content-Type', 'application/json;charset=utf-8');
    $rs->getBody()->write(json_encode ($data));

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
      
      //* Mise en forme de tous les sandwichs avec lien en tableau
      $tab_items = [];

      foreach ($commande->items as $sandwich) {
        $tab_items[] = [
          "sandwich"=>[
            "nom" => $sandwich->libelle,
            "quantite" => $sandwich->quantite,
            "tarif" => $sandwich->tarif,
          ],
          "links"=>[
            "self"=> [$sandwich->uri]
          ]
          ];
      }
      
      //* Mise en forme de tous les sandwichs avec lien en tableau
      $tab_commande = [
        "id" => $commande->id,
        "nom" => $commande->nom,
        "mail" => $commande->mail,
        "statut" => $this->commandStatus($commande->status),
        "date_livraison" => date('Y-m-d', strtotime($commande->livraison)),
        "montant" => $commande->montant,
        "sandwichs" => $tab_items,
      ];

      //* Mise en forme de la ressource
      $data = [
        'type' => 'resource',
        'commande' => $tab_commande,
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