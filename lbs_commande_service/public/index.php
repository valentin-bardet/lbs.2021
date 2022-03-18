<?php

/**
 * File:  index.php
 *
 */

require_once  __DIR__ . '/../src/vendor/autoload.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \lbs\command\app\controller\CommandController;

// $errors = require_once __DIR__ .'/../src/app/conf/errors.php';

$configuration = [
    'settings' => [
        'displayErrorDetails' => true, // Mettre à false pour déployer l'api en mode production
    ],
    'dbconf' => function ($c) {
        return parse_ini_file(__DIR__ . '/../src/app/conf/commande.db.conf.ini');
    },
    "phpErrorHandler" => function (\Slim\Container $c) {
        return function ($req, $resp, \Throwable $error) {
            $resp = $resp->withStatus(500)->withHeader('Content-Type', 'application/json');
            $resp->getBody()->write(json_encode(
                [
                    "type" => "error",
                    "error" => "500",
                    "message" => "Erreur serveur : {$error->getMessage()}",
                    "trace" => $error->getTraceAsString(),
                    "file" => $error->getFile()."ligne: ".$error->getLine(),
                ]
            ));
            return $resp;
        };
    },

    "notAllowedHandler" => function ($c) {
        return function ($req, $resp, $methods) {
            $resp = $resp->withStatus(405)->withHeader('Content-Type', 'application/json');
            $resp->getBody()->write(json_encode(
                [
                    "type" => "error",
                    "error" => "405",
                    "message" => 'Methode autorisee : ' . implode(",", $methods),
                ]
            ));
            return $resp;
        };
    },
    "notFoundHandler" => function (\Slim\Container $c) {
        return function ($req, $resp) {
            $resp = $resp->withStatus(400)->withHeader('Content-Type', 'application/json');
            $resp->getBody()->write(json_encode(
                [
                    "type" => "error",
                    "error" => "400",
                    "message" => "URI mal formee",
                ]
            ));
            return $resp;
        };
    },
];

$c = new \Slim\Container($configuration);
$app = new \Slim\App($c);

$db = new Illuminate\Database\Capsule\Manager();

$db->addConnection($c->dbconf); /* configuration avec nos paramètres */
$db->setAsGlobal(); /* rendre la connexion visible dans tout le projet */
$db->bootEloquent(); /* établir la connexion */

$app->get(
    '/commandes/',
    function (Request $req, Response $resp, $args): Response {
        $ctrl = new CommandController($this);
        return $ctrl->listCommands($req, $resp, $args);
    }
);

$app->get(
    '/commandes/{id}[/]',
    function (Request $req, Response $resp, $args): Response {
        $ctrl = new CommandController($this);
        return $ctrl->GetCommand($req, $resp, $args);
    }
);
$app->get(
    '/commandes/{id}/items[/]',
    function (Request $req, Response $resp, $args): Response {
        $ctrl = new CommandController($this);
        return $ctrl->CommandsByIdWithItems($req, $resp, $args);
    }
);

$app->post(
    '/commande[/]',
    function (Request $req, Response $resp, $args): Response {
        $ctrl = new CommandController($this);
        return $ctrl->addCommand($req, $resp, $args);
    }
);


$app->run();