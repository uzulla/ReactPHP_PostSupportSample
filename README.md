ReactPHP Post parameter support sample
======================================

[ReactPHP](http://reactphp.org/)'s HTTP server lib is not support post method parameter.

\\Uzulla\\React\\* is post parameter support sample.

> code base ReactPHP 0.4.0

`Transfer-Encoding: chunked` not supported.

usage
=====

see `/boot.php`

## setup

```
// build reactor
$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server($loop);
$http = new \Uzulla\React\Http\Server($socket);
$http->on('request', $app);
```

## get params.

```
$app = function (\Uzulla\React\Http\Request $req,\React\Http\Response $res) {
    $query = $req->getQuery(); // get params (already existent)
    $params = $req->getParams(); // post params (new add)
    //...
}
```

comment
=======

ReactPHP is interesting.

see also
========

[ReactPHP (github)](https://github.com/reactphp/react)
