<?php
require_once 'vendor/autoload.php';

$loader = new Twig_Loader_Filesystem(__DIR__.'/templates');
$twig = new Twig_Environment($loader, [
    'cache' => __DIR__.'/twig_cache',
]);
$access_log = new \Monolog\Logger('access');
$access_log->pushHandler(new \Monolog\Handler\StreamHandler(__DIR__.'/access.log', \Monolog\Logger::INFO));

// app
$app = function (\Uzulla\React\Http\Request $req,\React\Http\Response $res) use ($twig, $access_log) {
    $path = $req->getPath();
    $method = $req->getMethod();

    $access_log->addInfo("{$method}\t{$path}");

    $params = [
        'params'=>$req->getParams(),
        'query'=>$req->getQuery()
    ];

    if($method === 'GET' && preg_match('|\A/\z|u', $path)){
        $res->writeHead(200, ['Content-Type' => 'text/html']);
        $res->end($twig->render('index.twig',$params));
    }elseif($method === 'POST' && preg_match('|\A/\z|u', $path)){
        $res->writeHead(200, ['Content-Type' => 'text/html']);
        $res->end($twig->render('index.twig',$params));
    }else{
        $res->writeHead(404, ['Content-Type' => 'text/html']);
        $res->end($twig->render('notfound.twig'));
    }
};

// build reactor
$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new \Uzulla\React\Http\Server($socket);
$http->on('request', $app);
$socket->listen(8080);
echo "running...\n";
$loop->run();
