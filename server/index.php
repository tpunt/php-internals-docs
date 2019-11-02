<?php

require 'vendor/autoload.php';

use Amp\ByteStream\ResourceOutputStream;
use Amp\Http\Server\Request;
use Amp\Http\Server\RequestHandler\CallableRequestHandler;
use Amp\Http\Server\Response;
use Amp\Http\Server\Router;
use Amp\Http\Server\Server;
use Amp\Http\Server\Options;
use Amp\Http\Status;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Amp\Socket;
use Monolog\Logger;

use PHPInternalsDocs\Services\{
    MetaDataService,
    ArticlesService,
    CategoriesService,
    SymbolsService
};

if (!getenv('IP') || !getenv('PORT')) {
    echo 'The "IP" and "PORT" environment variables must be set first!', PHP_EOL;
    die;
}

$env = getenv('ENV') ? getenv('ENV') : 'dev';

if ($env == 'prod') {
    if (!getenv('CERT')) {
        echo 'The "CERT" environment variable must be set firts!', PHP_EOL;
        die;
    }
}

$data = require 'populate.php';
$response_headers = [
    'content-type' => 'application/json',
    'Access-Control-Allow-Origin' => '*',
    'Access-Control-Allow-Methods' => 'GET, OPTIONS, HEAD',
];

Amp\Loop::run(function () use ($data, $response_headers, $env) {
    $servers = [];
    $address = getenv('IP') . ':' . getenv('PORT');

    if ($env === 'prod') {
        $cert = new Socket\Certificate(getenv('CERT'));
        $context = (new Socket\BindContext)
            ->withTlsContext((new Socket\ServerTlsContext)
            ->withDefaultCertificate($cert));
        $servers[] = Socket\listen($address, $context);
    } else {
        $servers[] = Socket\listen($address);
    }

    $logHandler = new StreamHandler(new ResourceOutputStream(\STDOUT));
    $logHandler->setFormatter(new ConsoleFormatter);

    $logger = new Logger('server');
    $logger->pushHandler($logHandler);

    $router = new Router();

    $router->addRoute('OPTIONS', '/{path:.*}', new CallableRequestHandler(function (Request $request) use ($response_headers) {
        return new Response(204, $response_headers, '');
    }));

    $router->addRoute('GET', '/api/articles', new CallableRequestHandler(function (Request $request) use ($data, $response_headers) {
        parse_str($request->getUri()->getQuery(), $query);

        $response = ArticlesService::fetchArticles($data, $query);

        if ($response['code'] < 300) {
            $response['body'] = MetaDataService::performMetaActions($response['body'], $query);
            $response['body']['articles'] = $response['body']['default'];
            unset($response['body']['default']);
        }

        return new Response(
            $response['code'],
            $response_headers,
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $router->addRoute('GET', '/api/articles/{article_url}', new CallableRequestHandler(function (Request $request) use ($data, $response_headers) {
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
            $response_headers,
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $router->addRoute('GET', '/api/categories', new CallableRequestHandler(function (Request $request) use ($data, $response_headers) {
        parse_str($request->getUri()->getQuery(), $query);

        $response = CategoriesService::fetchCategories($data, $query);

        if ($response['code'] < 300) {
            $response['body'] = MetaDataService::performMetaActions($response['body'], $query);
            $response['body']['categories'] = $response['body']['default'];
            unset($response['body']['default']);
        }

        return new Response(
            $response['code'],
            $response_headers,
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $router->addRoute('GET', '/api/categories/{category_url}', new CallableRequestHandler(function (Request $request) use ($data, $response_headers) {
        $args = $request->getAttribute(Router::class);
        $response = CategoriesService::fetchCategory($data, $args['category_url']);

        return new Response(
            $response['code'],
            $response_headers,
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $router->addRoute('GET', '/api/symbols', new CallableRequestHandler(function (Request $request) use ($data, $response_headers) {
        parse_str($request->getUri()->getQuery(), $query);

        $response = SymbolsService::fetchSymbols($data, $query);

        if ($response['code'] < 300) {
            $response['body'] = MetaDataService::performMetaActions($response['body'], $query);
            $response['body']['symbols'] = $response['body']['default'];
            unset($response['body']['default']);
        }

        return new Response(
            $response['code'],
            $response_headers,
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $router->addRoute('GET', '/api/symbols/{symbol_id}', new CallableRequestHandler(function (Request $request) use ($data, $response_headers) {
        $args = $request->getAttribute(Router::class);
        $response = SymbolsService::fetchSymbol($data, $args['symbol_id']);

        return new Response(
            $response['code'],
            $response_headers,
            json_encode($response['body'], JSON_PRETTY_PRINT)
        );
    }));

    $server = new Server($servers, $router, $logger, (new Options)->withAllowedMethods(['GET', 'OPTIONS', 'HEAD']));

    yield $server->start();

    // unclean shutdown since PCNTL extension is not loaded (for SIGINT constant)
    // Stop the server when SIGINT is received (this is technically optional, but it is best to call Server::stop()).
    // Amp\Loop::onSignal(\SIGINT, function (string $watcherId) use ($server) {
    //     Amp\Loop::cancel($watcherId);
    //     yield $server->stop();
    // });
});
