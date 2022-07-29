<?php

namespace Bredala\Router;

/**
 * Router
 *
 * A simple RESTfull router
 */
class Router
{
    private array $wildcards;
    private array $routes = [];
    public array $route = [];

    // -------------------------------------------------------------------------

    public function __construct()
    {
        $this->wildcards = [
            'all' => '(.*)',
            'any' => '([^/]+)',
            'num' => '(-?[0-9]+)',
            'hex' => '([A-Fa-f0-9]+)',
            'uuid' => '([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12})',
        ];
    }


    // -------------------------------------------------------------------------

    /**
     * Add a wildcard
     *
     * @param string $shortcut The wildcard name
     * @param string $pattern Regex
     * @return Router
     */
    public function wildcard(string $shortcut, string $pattern): Router
    {
        $this->wildcards[$shortcut] = $pattern;
        return $this;
    }

    // -------------------------------------------------------------------------

    /**
     * @param string $method
     * @param string $uri
     * @param mixed $callback
     * @return Router
     */
    public function add(string $method, string $uri, $callback, array $params = []): Router
    {
        $this->routes[$method][$uri] = [
            'uri' => self::normalizeUri($uri),
            'callback' => $callback,
            'params' => $this->parseParams($params),
        ];

        return $this;
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @param array $params
     * @return Router
     */
    public function get(string $uri, $callback, array $params = []): Router
    {
        return $this->add('GET', $uri, $callback, $params);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @param array $params
     * @return Router
     */
    public function post(string $uri, $callback, array $params = []): Router
    {
        return $this->add('POST', $uri, $callback, $params);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @param array $params
     * @return Router
     */
    public function put(string $uri, $callback, array $params = []): Router
    {
        return $this->add('PUT', $uri, $callback, $params);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @param array $params
     * @return Router
     */
    public function patch(string $uri, $callback, array $params = []): Router
    {
        return $this->add('PATCH', $uri, $callback, $params);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @param array $params
     * @return Router
     */
    public function delete(string $uri, $callback, array $params = []): Router
    {
        return $this->add('DELETE', $uri, $callback, $params);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @param array $params
     * @return Router
     */
    public function options(string $uri, $callback, array $params = []): Router
    {
        return $this->add('OPTIONS', $uri, $callback, $params);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @param array $params
     * @return Router
     */
    public function head(string $uri, $callback, array $params = []): Router
    {
        return $this->add('HEAD', $uri, $callback, $params);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @param array $params
     * @return Router
     */
    public function cli(string $uri, $callback, array $params = []): Router
    {
        return $this->add('CLI', $uri, $callback, $params);
    }

    // -------------------------------------------------------------------------

    public function uris(): array
    {
        $uris = [];
        foreach ($this->routes as $method => $routes) {
            foreach ($routes as $uri => $item) {
                $uris[$method][] =  $uri;
            }
        }

        return $uris;
    }

    // -------------------------------------------------------------------------

    public function find(string $method, string $uri): Route
    {
        $uri = self::normalizeUri($uri);

        if (!isset($this->routes[$method])) {
            throw new RouterException('No matching routes');
        }

        if (($route = $this->routes[$method][$uri] ?? null)) {
            return new Route($route['uri'], $route['callback']);
        }

        foreach ($this->routes[$method] as $r) {

            $this->route = $r;

            if (($route = $this->match($uri))) {
                return $route;
            }
        }

        throw new RouterException('No matching routes');
    }

    // -------------------------------------------------------------------------

    protected static function normalizeUri(string $uri): string
    {
        return trim($uri, '/');
    }

    private function parseParams(array $params): array
    {
        foreach ($params as $name => $regex) {
            $regex = $this->wildcards[$regex] ?? $regex;
            $params[$name] = str_replace('(', '(?:', $regex);
        }

        return $params;
    }

    private function match(string $uri): ?Route
    {
        $matches = [];
        if (!preg_match($this->buildRegex(), $uri, $matches)) {
            return null;
        }

        $matches = self::buildMatches($matches);

        return new Route($this->route['uri'], $this->route['callback'], $matches);
    }

    private function buildRegex()
    {
        $regex = $this->route['uri'];
        $regex = str_replace('#', "\#", $regex);
        $regex = preg_replace_callback('#:([\w]+)#', [$this, 'paramMatch'], $regex);
        $regex = "#^{$regex}$#i";

        return $regex;
    }

    private function paramMatch(array $matches): string
    {
        return '(' . ($this->route['params'][$matches[1]] ?? '[^/]+') . ')';
    }

    /**
     * @param array $matches
     * @return array
     */
    private static function buildMatches(array $matches): array
    {
        array_shift($matches);

        $params = [];
        foreach ($matches as $match) {
            foreach (explode('/', trim($match, '/')) as $param) {
                $params[] = $param;
            }
        }

        return $params;
    }
}
