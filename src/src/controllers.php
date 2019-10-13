<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Proxy\Proxy;
use Proxy\Adapter\Guzzle\GuzzleAdapter;
use Proxy\Filter\RemoveEncodingFilter;
use Zend\Diactoros\ServerRequestFactory;

$app->post('/__api/mocks', function (Request $request) use ($app) {
    $data = $request->getContent();
    $data = $app['storage']->add(json_decode($data, true));
    $status = (key($data) == 'error') ? 401 : 200;
    return $app->json($data, $status);
});

$app->delete('/__api/mocks/{id}', function ($id) use ($app) {
    $data = $app['storage']->deleteMockById($id);
    return $app->json($data);
})->bind('removeMockById');

$getAll = (function($app, $request) {
    $data = $app['storage']->getAllMocks();
    $rules = $request->query->all();
    $data = $app['filter']->applyFilterRules($rules, $data);
    return $app->json($data);
});

$app->get('/__api/mocks/', function (Request $request) use ($app, $getAll) {
    return $getAll($app, $request);
})->bind('getMocks');

$app->get('/__api/mocks', function (Request $request) use ($app, $getAll) {
    return $getAll($app, $request);
})->bind('getMocksWithoutTailingSlash');

$app->get('/__api/mocks/{id}', function ($id) use ($app) {
    $data = $app['storage']->getMockById($id);
    if(!$data) {
        return $app->json(['error' => 'No hay mock bajo el id indicado']);
    }
    return $app->json($data);
})->bind('getMockById');

$app->put('/__api/mocks/{id}', function (Request $request, $id) use ($app) {
    $data = $request->getContent();
    $data = $app['storage']->update($id, $data);
    $status = (key($data) == 'error') ? 401 : 200;
    return $app->json($data, $status);
})->bind('updateMockById');

$app->get('/__admin/', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})->bind('admin');

$app->get('/__admin', function () use ($app) {
    return $app['twig']->render('index.html.twig', array());
})->bind('adminWithoutTailingSlash');

$app->match('/{url}', function (Request $request) use ($app) {
    $server = $request->server->all();
    $fileName = md5(strtolower($server['REQUEST_URI'].$server['REQUEST_METHOD']));
    $mock = $app['storage']->getMockByFileName($fileName);

    if(!$mock) {
        $allProxiesMocks = $app['storage']->getProxiesMocks();
        $ProxyMock = $app['helper']->getPossibleProxy($allProxiesMocks, $server);
        if($ProxyMock) {
            $mock = $ProxyMock;
        } else {
            return $app->json(['error' => 'Not found'], 404);
        }
    }

    if($mock['state'] === 'record' || $mock['state'] === 'proxy') {
        $_SERVER['REQUEST_URI'] = '/';
        $_SERVER['PATH_INFO'] = '/';
        $_SERVER['PHP_SELF'] = '/';
        $rq = ServerRequestFactory::fromGlobals();
        $guzzle = new GuzzleHttp\Client();
        $proxy = new Proxy(new GuzzleAdapter($guzzle));
        $proxy->filter(new RemoveEncodingFilter());
        $guzzleResponse = $proxy->forward($rq)->to($mock['proxy']);
        $response = (string) $guzzleResponse->getBody();
    }
    if ($mock['state'] === 'record') {
        $mock['payload'] = $response;
        $app['storage']->update($mock['id'], json_encode($mock));
    } else if ($mock['state'] === 'proxy') {
        $mock['payload'] = $response;
    }
    $response = new Response($mock['payload']);
    $response->headers->set('Content-Type', $mock['contentType']);
    $response->setStatusCode($mock['statusCode']);
    return $response;
})->method('GET|POST|PUT|OPTION|DELETE')->assert('url', '.+')
  ->value('url', '');
