<?php

require 'vendor/autoload.php';

use Amp\ByteStream\ResourceOutputStream;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\Server;
use Amp\Http\Status;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Socket;
use Monolog\Logger;

use PHPInternalsDocs\Services\{MetaDataService, ArticlesService, CategoriesService};

$data = require 'populate.php';

Amp\Loop::run(function () use ($data) {
    $servers = [
        Socket\listen("0.0.0.0:4000"),
    ];

    $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
    $logHandler->setFormatter(new ConsoleFormatter);

    $logger = new Logger('server');
    $logger->pushHandler($logHandler);

    $router = new Router();

    $router->addRoute('GET', '/articles', new CallableRequestHandler(function (Request $request) use ($data) {
        parse_str($request->getUri()->getQuery(), $query);

        $response = ArticlesService::fetchArticles($data, $query);

        if ($response['code'] < 300) {
            $response['body'] = MetaDataService::performMetaActions($response['body'], $query);
            $response['body']['articles'] = $response['body']['default'];
            unset($response['body']['default']);
        }

        return new Response(
            $response['code'],
            ['content-type' => 'application/json'],
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $router->addRoute('GET', '/articles/{article_url}', new CallableRequestHandler(function (Request $request) use ($data) {
        parse_str($request->getUri()->getQuery(), $query);

        $args = $request->getAttribute(Router::class);
        $response = ArticlesService::fetchArticle($data, $args['article_url']);

        if ($response['code'] < 300) {
            if (is_array($response['body'])) { // an article_url can be the name of a series
                $response['body'] = MetaDataService::performMetaActions($response['body'], $query);
                $response['body']['articles'] = $response['body']['default'];
                unset($response['body']['default']);
            }
        }

        return new Response(
            $response['code'],
            ['content-type' => 'application/json'],
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $router->addRoute('GET', '/categories', new CallableRequestHandler(function (Request $request) use ($data) {
        parse_str($request->getUri()->getQuery(), $query);

        $response = CategoriesService::fetchCategories($data, $query);

        if ($response['code'] < 300) {
            $response['body'] = MetaDataService::performMetaActions($response['body'], $query);
            $response['body']['categories'] = $response['body']['default'];
            unset($response['body']['default']);
        }

        return new Response(
            $response['code'],
            ['content-type' => 'application/json'],
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $router->addRoute('GET', '/categories/{category_url}', new CallableRequestHandler(function (Request $request) use ($data) {
        $args = $request->getAttribute(Router::class);
        $response = CategoriesService::fetchCategory($data, $args['category_url']);

        return new Response(
            $response['code'],
            ['content-type' => 'application/json'],
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $server = new Server($servers, $router, $logger);

    yield $server->start();

    // unclean shutdown since PCNTL extension is not loaded (for SIGINT constant)
    // Stop the server when SIGINT is received (this is technically optional, but it is best to call Server::stop()).
    // Amp\Loop::onSignal(\SIGINT, function (string $watcherId) use ($server) {
    //     Amp\Loop::cancel($watcherId);
    //     yield $server->stop();
    // });
});
