# bredala-router

Fast request router for PHP.

````php
use Bredala\Router\Router;

$router = new Router();
$router->add('GET', '/', function() {
    echo 'home';
});

$router->add('GET', '/hello/([^/]+)', function(string $world) {
    echo "Hello {$world}!";
});
````
