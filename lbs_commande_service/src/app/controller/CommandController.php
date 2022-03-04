<?php

namespace lbs\command\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \lbs\command\app\models\Commande;
use \lbs\command\app\models\Item;


class CommandController
{
	public function listCommands(Request $req, Response $resp, array $args): Response
	{
		$commandes = Commande::all();
		$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
		$resp->getBody()->write(json_encode($commandes));
		return $resp;
	}

	public function listCommandsById(Request $req, Response $resp, array $args): Response
	{
		$res["commandes"] = Commande::where("id", "like", $args["id"])->get(["id", "mail", "nom", "created_at", "livraison", "montant"]);
		$res["links"]["items"]["href"] = '/commandes/' . $args["id"] . '/items/';
		$res["links"]["self"]["href"] = '/commandes/' . $args["id"];
		if (isset($res["commandes"])) {
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode($res));
			return $resp;
		} else {
			/* 
				Il faut générer une erreur ici car c'est une erreur que le contrôleur trouve.
				Ce n'est pas une erreur handlable dans le container de dépendances de Slim.
				*/
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
	public function CommandsByIdAll(Request $req, Response $resp, array $args): Response
	{
		$items = Item::where("command_id", "like", $args["id"])->get(["id", "libelle", "tarif", "quantite"]);
		$res["commandes"] = Commande::where("id", "like", $args["id"])->get(["id", "mail", "nom", "created_at", "livraison", "montant"]);
		$res["commandes"]["items"] = $items;
		$res["links"]["items"]["href"] = '/commandes/' . $args["id"] . '/items/';
		$res["links"]["self"]["href"] = '/commandes/' . $args["id"];
		if (isset($res["commandes"])) {
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode($res));
			return $resp;
		} else {
			/* 
				Il faut générer une erreur ici car c'est une erreur que le contrôleur trouve.
				Ce n'est pas une erreur handlable dans le container de dépendances de Slim.
				*/
			$resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
			$resp->getBody()->write(json_encode([
				"type" => "error",
				"error" => "404",
				"message" => 'Ressource non disponible (id non existant) : ' . $args["id"],
			]));
			return $resp;
		}
	}
}