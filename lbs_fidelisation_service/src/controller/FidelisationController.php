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

    $tab_cartes = [];
    foreach ($cartes as $carte) {
      $tab_cartes[] = [
        "carte" => [
          'id' => $carte->id,
          'nom' => $carte->nom_client,
          'mail' => $carte->mail_client,
        ],
        "links"=>[
          "self"=> ['href' => $this->c->router->pathFor('carte', ['id'=> $carte->id])],
          "commandes"=> ['href' => $this->c->router->pathFor('commandesCarte', ['id'=> $carte->id])],
      ],
      ];
    }

    $data = [
      'type' => 'collection',
      'count' => count($cartes),
      'commandes' => $tab_cartes,
    ];

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

    $tab_carte = [
      'id' => $carte->id,
      'nom' => $carte->nom_client,
      'mail' => $carte->mail_client,
      'cumul_achats' => $carte->cumul_achats,
      'cumul_commandes' => $carte->cumul_commandes
    ];

    $data = [
      'type' => 'resource',
      'carte' => $tab_carte,
      'links' => [
        "commandes" => ['href' => $this->c->router->pathFor('commandesCarte', ['id'=> $carte->id])]
      ]
    ]; 

    return Writer::json_output($rs, 200, $data);
  }

    /**
     *
     * public function commmandesCarte : liste les commandes de la carte en argument de l'URI
     *
     * @return Response : la liste des commandes de la carte au format json
     *
     */
    public function commandesCarte(Request $rq, Response $rs, array $args) : Response {
        $id = $args['id'];
        $commandes = Commande::select()->where('carte_id', '=', $id)->get();

        $tab_commandes = [];
        foreach ($commandes as $commande) {
          $tab_commandes[] = [
            "commande"=>[
              "id" => $commande->id,
              "date" => date('Y-m-d', strtotime($commande->created_at)),
              "montant" => $commande->montant,
            ],
          ];
        }
        
        $data = [
          'type' => 'collection',
          'count' => count($commandes),
          'links' => [
            'self' => ['href' => $this->c->router->pathFor('id', ['id'=> $id])],
          ],
          'commandes' => $tab_commandes,
          
        ];

        return Writer::json_output($rs, 200, $data);
    }


    /**
     *
     * public function fidelisation : fidelise une commande
     *
     * @return Response
     *
     */
    public function fidelisation(Request $rq, Response $rs, array $args) : Response {
        
      
      // $commande = $rq->getParsedBody()[0];
      //On est censé récupérer les données de la commande dans le body
      // Et mettre à jour les données sur la BDD fidélisation
      // Or problème de communication de l'API Commande à l'API fidélisation
      // (cf. CommandController - ligne 262)

        return Writer::json_output($rs, 201, ['test' => 'test']);

    }
}
