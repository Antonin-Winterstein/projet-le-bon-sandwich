<?php

namespace lbs\fidelisation\middlewares;

use Exception;
use Firebase\JWT\JWT;
use lbs\fidelisation\utils\Writer;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;

class JwtToken{
    
    private $c;

    public function __construct(\Slim\Container $c){
        $this->c = $c;
    }
    
    private function decode(string $h){
        $tokenstring = sscanf($h, "Bearer %s")[0];
        $secret = $this->c->settings['secret'];
        $token = JWT::decode($tokenstring, $secret, ['HS512']);
        return $token;
    }


    public function checkToken( Request $rq, Response $rs, callable $next)
    {
        if (!$rq->hasHeader('Authorization')) {
            $rs = $rs->withStatus(401)->withHeader('WWW-authenticate', 'Basic realm="api de fidelisation"');
            return Writer::json_error($rs, 401, 'Pas de header Authorization');
        }
        try {
            
            $token = $this->decode($rq->getHeader('Authorization')[0]);
            
        } catch (Exception $e) {
            return Writer::json_error($rs, 401, "Token d'Authentification Invalide");
        }
        
        $route_id = $rq->getAttribute('route')->getArgument('id');
        $token_id = $token->cid;

        if ($route_id != $token_id) {
            return Writer::json_error($rs, 401, 'Authorization Invalide', 
            $this->c['router']->pathFor('login', ['id' => $route_id]));

        }

        $rq = $rq->withAttribute('validated_JwtToken', $token_id);

        $rs = $next($rq, $rs);

        return $rs;
    }

}