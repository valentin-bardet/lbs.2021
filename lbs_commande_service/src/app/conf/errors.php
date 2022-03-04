<?php

/**  File:  errors.php */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

return[
    
    // "notFoundHandler" => function (\Slim\Container $c) {
    //     return function ($req, $resp, $methods) {
    //         $resp = $resp->withStatus(400)->withHeader('Content-Type', 'application/json');
    //         $resp->getBody()->write(json_encode(
    //             [
    //                 "type" => "error",
    //                 "error" => "400",
    //                 "message" => 'URI mal formée',
    //             ]
    //         ));
    //         return $resp;
    //     };
    // },
    // "notAllowedHandler" => function (\Slim\Container $c) {
    //     return function ($req, $resp, $methods) {
    //         $resp = $resp->withStatus(405);
    //         $resp->getBody()->write(json_encode(
    //             [
    //                 "type" => "error",
    //                 "error" => "405",
    //                 "message" => 'Methode autorisee : ' . implode(",", $methods),
    //             ]
    //         ));
    //         return $resp;
    //     };
    // },
    // "phpErrorHandler" => function (\Slim\Container $c) {
    //     return function ($req, $resp, \Throwable $error) {
    //         $resp = $resp->withStatus(500);
    //         $resp->getBody()->write(json_encode(
    //             [
    //                 "type" => "error",
    //                 "error" => "500",
    //                 "message" => "Erreur serveur : {$error->getMessage()}",
    //                 "trace" => $error->getTraceAsString(),
    //                 "file" => $error->getFile()."ligne: ".$error->getLine(),
    //             ]
    //         ));
    //         return $resp;
    //     };
    // },
];
