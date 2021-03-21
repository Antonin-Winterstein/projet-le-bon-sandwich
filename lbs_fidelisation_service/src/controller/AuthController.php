<?php

namespace lbs\fidelisation\controller;

use Firebase\JWT\JWT;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\fidelisation\middlewares\JwtToken;
use lbs\fidelisation\models\Carte;
use lbs\fidelisation\utils\Writer;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class AuthController {

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
  public function login(Request $rq, Response $rs, array $args) : Response {


    if (!$rq->hasHeader('Authorization')) {
        $rs = $rs->withStatus(401)->withHeader('WWW-authenticate', 'Basic realm="api de fidelisation"');
        return Writer::json_error($rs, 401, 'Pas de header Authorization');
    }

    $tab_auth = explode(':', base64_decode(explode(" ", $rq->getHeader('Authorization')[0])[1]));
    $usermail = $tab_auth[0];
    $password = $tab_auth[1];


    try {
        $carte = Carte::select('id', 'nom_client', 'mail_client', 'passwd')
            ->where('mail_client', '=', $usermail)
            ->where('id', '=', $args['id'])
            ->firstOrFail();

        if (!password_verify($password, $carte->passwd)) {
            throw new \Exception('wrong password');
        }

    } catch (ModelNotFoundException $e) {
        $rs = $rs->withStatus(401)->withHeader('WWW-authenticate', 'Basic realm="api de fidelisation"');
        return Writer::json_error($rs, 401, 'Erreur Authentification');
    } catch (\Exception $e) {
        $rs = $rs->withStatus(401)->withHeader('WWW-authenticate', 'Basic realm="api de fidelisation"');
        return Writer::json_error($rs, 401, 'Erreur Authentification');
    }


    // $usermail = $rq->getQueryParam('u', null);
    // $password = $rq->getQueryParam('p', null);


    $secret = $this->c->settings['secret'];
    
    $data[] = ["carte" => $secret];
    $token = JWT::encode( ['iss' => 'http://api.fidelisation.local:19280/login',
        'aud' => 'http://api.fidelisation.local:19280',
        'lat' => time(),
        'exp' => time()+(12*30*24*3600),
        'cid' => $carte->id ],
        $secret, 'HS512'); 



    $data = [
    'carte' => $carte,
    'jwt-token' => $token
    ];

    return Writer::json_output($rs, 200, $data);
    
  }
}