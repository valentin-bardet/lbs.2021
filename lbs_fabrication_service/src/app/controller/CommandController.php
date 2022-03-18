<?php

namespace lbs\fab\app\controller;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \lbs\fab\app\models\Commande;
use \lbs\fab\app\models\Item;

use \Illuminate\Support\STR;


class CommandController
{
    public function listCommands(Request $req, Response $resp, array $args): Response
    {
        $s = $req->getQueryParam('s');
        $pageUri = $req->getQueryParam('page');
        $sizeUri = $req->getQueryParam('size');
        $page = 1;
        $size = 10;

        if (isset($sizeUri)) {
            $size = $sizeUri;
        }
        if (isset($pageUri)) {
            if ($pageUri <= 0) {
                $page = 1;
            } else {
                $page = $pageUri;
            }
        }

        $resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');
        if (isset($s)) {
            $commandes = Commande::where("status", "like", $s)->skip(($page - 1) * $size)->take($size)->get(["id", "nom", "created_at", "livraison", "status"]);
            if (!empty($commandes[0])) {
                $allCommandes = Commande::where("status", "like", $s)->get(["id", "nom", "created_at", "livraison", "status"]);
                $lastPage = (count($allCommandes) / $size);
                if ($page > ceil($lastPage)) {
                    $page = ceil($lastPage);
                }

                $res["type"] = "collection";
                $res["count"] = count($commandes);
                $res["size"] = $size;
                if ($page < ceil($lastPage)) {
                    $res["links"]["next"]["href"] = "/commandes/?s=$s&page=" . ($page + 1) . "&size=" . $size;
                }
                if ($page > 1) {
                    $res["links"]["prev"]["href"] = "/commandes/?s=$s&page=" . ($page - 1) . "&size=" . $size;
                }
                $res["links"]["last"]["href"] = "/commandes/?s=$s&page=" . ceil($lastPage) . "&size=" . $size;
                $res["links"]["first"]["href"] = "/commandes/?s=$s&page=1&size=" . $size;


                $i = 0;
                foreach ($commandes as $com) {
                    $res["commandes"][$i]['commande']['id'] = $com['id'];
                    $res["commandes"][$i]['commande']['nom'] = $com['nom'];
                    $res["commandes"][$i]['commande']['created_at'] = $com['created_at'];
                    $res["commandes"][$i]['commande']['livraison'] = $com['livraison'];
                    $res["commandes"][$i]['commande']['status'] = $com['status'];
                    $res["commandes"][$i]['links']['self']['href'] = "/commandes/" . $com['id'] . "/";
                    $i++;
                }
                $resp->getBody()->write(json_encode($res));
                return $resp;
            } else {
                $resp->getBody()->write(json_encode([
                    "type" => "error",
                    "error" => "404",
                    "message" => 'Ce status n\'existe pas',
                ]));
                return $resp;
            }
        } else {
            $allCommandes = Commande::All();
            $lastPage = (count($allCommandes) / $size);
            if ($page > ceil($lastPage)) {
                $page = ceil($lastPage);
            }
            $commandes = Commande::get(["id", "nom", "created_at", "livraison", "status"])->skip(($page - 1) * $size)->take($size);

            $res["type"] = "collection";
                $res["count"] = count($commandes);
                $res["size"] = $size;
                if ($page < ceil($lastPage)) {
                    $res["links"]["next"]["href"] = "/commandes/?page=" . ($page + 1) . "&size=" . $size;
                }
                if ($page > 1) {
                    $res["links"]["prev"]["href"] = "/commandes/?page=" . ($page - 1) . "&size=" . $size;
                }
                $res["links"]["last"]["href"] = "/commandes/?page=" . ceil($lastPage) . "&size=" . $size;
                $res["links"]["first"]["href"] = "/commandes/?page=1&size=" . $size;
            $i = 0;
            foreach ($commandes as $com) {
                $res["commandes"][$i]['commande']['id'] = $com['id'];
                $res["commandes"][$i]['commande']['nom'] = $com['nom'];
                $res["commandes"][$i]['commande']['created_at'] = $com['created_at'];
                $res["commandes"][$i]['commande']['livraison'] = $com['livraison'];
                $res["commandes"][$i]['commande']['status'] = $com['status'];
                $res["commandes"][$i]['links']['self']['href'] = "/commandes/" . $com['id'] . "/";
                $i++;
            }
            $resp->getBody()->write(json_encode($res));
            return $resp;
        }
    }
}
