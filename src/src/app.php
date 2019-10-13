<?php

include 'services/storage.php';
include 'services/filter.php';
include 'services/helper.php';
include 'repositories/hd.php';
include 'repositories/mongo.php';

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Services\Storage;
use Services\Filter;
use Services\Helper;
use Repositories\Hd;
use Repositories\Mongo;
use Lalbert\Silex\Provider\MongoDBServiceProvider;

$app = new Application();

// Registers

$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app->register(new Rpodwika\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/settings.yml'));
$app->register(new MongoDBServiceProvider(), [
    'mongodb.config' => [
        'server' => $app['config']['database']['protocol'] . '://' . $app['config']['database']['host'] . ':' . $app['config']['database']['port'],
        'options' => [],
        'driverOptions' => [],
    ]
]);

// Services

$app['repositories.mongo'] = function ($app) {
    return new Mongo($app['mongodb'], $app['config']['database']['dbname']);
};

$app['repositories.hd'] = function ($app) {
    return new Hd(__DIR__ . '/../' . $app['config']['filesystem']['data']);
};

$app['storage'] = function ($app) {
    return new Storage($app['config']['storage']['type'], $app['repositories.mongo'], $app['repositories.hd']);
};
$app['filter'] = function () {
    return new Filter();
};
$app['helper'] = function () {
    return new Helper();
};

// Twigs

$app['twig'] = $app->extend('twig', function ($twig, $app) {
    return $twig;
});

return $app;
