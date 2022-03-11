<?php

namespace lbs\command\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \lbs\command\app\models\Commande;
use \lbs\command\app\models\Item;

use \Illuminate\Support\STR;


class CommandController
{
	public function listCommands(Request $req, Response $resp, array $args): Response
	{
		$commandes = Commande::all();
		$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
		$resp->getBody()->write(json_encode($commandes));
		return $resp;
	}

	public function GetCommand(Request $req, Response $resp, array $args): Response
	{
		$token = $req->getQueryParam('token');
        $tokenHeader = $req->getHeader('X-lbs-token');
        $embed = $req->getQueryParam('embed');
        $res["type"] ="resource";
        $res["commandes"] = Commande::where("id", "like", $args["id"])->get(["id", "mail", "nom", "created_at", "livraison", "montant","token"]);
        if ($embed === 'items'){
            $items = Item::where("command_id", "like", $args["id"])->get(["id", "libelle", "tarif", "quantite"]);
            $res["commandes"][0]["items"] = $items;
        }
        
        $res["links"]["items"]["href"] = '/commandes/' . $args["id"] . '/items/';
        $res["links"]["self"]["href"] = '/commandes/' . $args["id"];
        if (isset($res["commandes"])) {
            if(isset($token) && $token!=null && $token == $res["commandes"][0]['token']){
                $resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
                $resp->getBody()->write(json_encode($res));
                return $resp;
            }
            elseif(isset($tokenHeader[0]) && $tokenHeader[0]!=null && $tokenHeader[0] == $res["commandes"][0]['token']){
                $resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
                $resp->getBody()->write(json_encode($res));
                return $resp;
            }else{
                $resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
                $resp->getBody()->write(json_encode([
                "type" => "error",
                "error" => "403",
                "message" => 'Token manquant ou non valide',
                "token" => $tokenHeader,
            ]));
            return $resp;
            }
            
        } else {
            $resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
            $resp->getBody()->write(json_encode([
                "type" => "error",
                "error" => "404",
                "message" => 'Ressource non disponible (id non existant) : ' . $args["id"],
            ]));
            return $resp;
        }
	}
	public function CommandsByIdWithItems(Request $req, Response $resp, array $args): Response
    {
        $items = Item::where("command_id", "like", $args["id"])->get(["id", "libelle", "tarif", "quantite"]);
        if (isset($items)) {
            $resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
            $resp->getBody()->write(json_encode([
				"type" => "collection",
				"count" => count($items),
				"items" => $items,
			]));
            return $resp;
        } else {
            $resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
            $resp->getBody()->write(json_encode([
                "type" => "error",
                "error" => "404",
                "message" => 'Ressource non disponible (id non existant) : ' . $args["id"],
            ]));
            return $resp;
        }
    }
	
	public function addCommand(Request $req, Response $resp, array $args): Response{
		$command_data = $req->getParsedBody();
		if(!isset($command_data['nom_client'])){
			$resp->getBody()->write(json_encode([
                "type" => "error",
                "error" => "400",
                "message" => 'Valeur manquante : nom_client',
            ]));
            return $resp;
		}
		if(!isset($command_data['mail_client'])){
			$resp->getBody()->write(json_encode([
                "type" => "error",
                "error" => "400",
                "message" => 'Valeur manquante : mail_client',
            ]));
            return $resp;
		}
		if(!isset($command_data['livraison']['date'])){
			$resp->getBody()->write(json_encode([
                "type" => "error",
                "error" => "400",
                "message" => 'Valeur manquante : livraison(date)',
            ]));
            return $resp;
		}
		if(!isset($command_data['livraison']['heure'])){
			$resp->getBody()->write(json_encode([
                "type" => "error",
                "error" => "400",
                "message" => 'Valeur manquante : livraison(heure)',
            ]));
            return $resp;
		}
		try {
			$c= new Commande();
			$c->id = STR::Uuid()->toString();
			$c->nom = filter_var($command_data['nom_client'],FILTER_SANITIZE_STRING);
			$c->mail = filter_var($command_data['mail_client'],FILTER_SANITIZE_EMAIL);
			$c->livraison= \DateTime::createFromFormat('d-m-Y H:i',$command_data['livraison']['date'].' '. $command_data['livraison']['heure']);
			$c->status= Commande::CREATED;
			$c->token = bin2hex(random_bytes(32));
			$c->montant=0;

			$c->save();

			$res['commande']['nom'] = $c->nom;
			$res['commande']['mail'] = $c->mail;
			$res['commande']['date_livraison'] = $c->livraison;
			$res['commande']['id'] = $c->id;
			$res['commande']['token'] = $c->token;
			$res['commande']['montant'] = $c->montant;

			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode($res));
            return $resp;

		}catch (\Exception $e){
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode([
                "type" => "error",
                "error" => "400",
                "message" => 'Rien ne marche',
            ]));
		}
	
	}
}