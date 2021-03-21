<?php

namespace lbs\commande\controller;

use Slim\Router;
use lbs\commande\utils\Writer;
use lbs\commande\models\Commande;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\Uuid;

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
   * @return string : le statut sous d'un tableau :
   * 
   * [
   * 'int' => numéro de statut,
   * 'str' => statut sous forme de string
   * ] 
   * 
   */
  private function commandStatus(int $status)
  {
    switch ($status) {
      case 1:
        return [
          'int' => 1,
          'str' => 'commande créée'
        ];
        break;

      case 2:
        return  [
          'int' => 2,
          'str' => 'commande payée'
        ];
        break;

      case 3:
        return  [
          'int' => 3,
          'str' => 'commande en cours de préparation'
        ];
        break;

      case 4:
        return  [
          'int' => 4,
          'str' => 'commande prête'
        ];
        break;

      case 5:
        return  [
          'int' => 5,
          'str' => '5 : commande terminée'
        ];
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
   * @param Request $rq
   * @param Response $rs
   * @return Response : la liste des commandes au format json
   * 
   */
  public function commands(Request $rq, Response $rs, array $args) : Response {
    $commandes = Commande::select('id', 'mail', 'livraison', 'montant', 'nom', 'status', 'token')->get();

    //* Mise en forme de toutes les commandes en tableau
    $tab_commandes = [];

    foreach ($commandes as $commande) {

      $tab_commandes[] = [
        "commande"=>[
          "token" => $commande->token,
          "id" => $commande->id,
          "nom" => $commande->nom,
          "date_livraison" => date('Y-m-d', strtotime($commande->livraison)),
          "statut" => $this->commandStatus($commande->status),
          "montant" => $commande->montant,
        ],
        "links"=>[
          "self" => ['href' => $this->c->router->pathFor('commande', ['id'=> $commande->id], ['token' => $commande->token])],
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
   * @param Request $rq
   * @param Response $rs
   * @return Response : le détail d'une commande au format json
   * 
   */
  public function aCommand(Request $rq, Response $rs, array $args) : Response {

    $id = $args['id'];
    $token = $rq->getQueryParam('token', null);


    try {
      $commande = Commande::select(['id', 'livraison', 'nom', 'mail', 'status', 'montant', 'token'])->with('items')->where('id', '=', $id)->where('token', '=', $token)->firstOrFail();
      
      //* Mise en forme de tous les sandwichs avec lien en tableau
      $tab_items = [];

      //* Mise en forme de tous les sandwiches
      foreach ($commande->items as $sandwich) {
        $tab_items[] = [
          "sandwich"=>[
            "nom" => $sandwich->libelle,
            "quantite" => $sandwich->quantite,
            "tarif" => $sandwich->tarif,
          ],
          "links"=>[
            "self"=> $sandwich->uri
          ]
        ];
      }
      
      //* Mise en forme de tous les attributs de la ressource
      $tab_commande[] = [
          "links"=>[
              "self" => ['href' => $this->c->router->pathFor('commande', ['id'=> $commande->id], ['token' => $commande->token])],
          ],
          "commande"=>[
            "token" => $commande->token,
            "id" => $commande->id,
            "nom" => $commande->nom,
            "mail" => $commande->mail,
            "statut" => $this->commandStatus($commande->status),
            "date_livraison" => date('Y-m-d', strtotime($commande->livraison)),
            "montant" => $commande->montant,
            "sandwichs" => $tab_items,
          ]];

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

      if($rq->getAttribute('has_errors')){
        return Writer::json_error($rs, 400, $rq->getAttribute('errors'));
      }

      $commande_data = $rq->getParsedBody();

      try {

        $c = new Commande();

        $c->id = Uuid::uuid4();
        // $c->nom = filter_var($commande_data['nom_client'], FILTER_SANITIZE_STRING);
        // $c->mail = filter_var($commande_data['mail_client'], FILTER_SANITIZE_EMAIL);
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
  
    /**
     * 
     * public function payACommand : paiement de la commande
     * 
     * @param Request $rq
     * @param Response $rs
     * @return Response
     * 
     */
    public function payACommand(Request $rq, Response $rs) : Response {
    
      $command_id = $rq->getQueryParams('id');

      $data = [
        'type' => 'action',
        'message' => "commande $command_id payée",
      ];
      if($rq->getAttribute('fidelisation_token')){

        // Appel vers l'API de fidélisation de la commande 
        // pour ajouter les données de la commande sur la carte de l'utilisateur
        // En cas de réussite :
        $data[] = [
          'fidelisee' => true
        ];
      };
      return Writer::json_output($rs, 200, $data);

    }
    
}