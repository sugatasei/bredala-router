<?php

namespace Bredala\Router;

/**
 * Router
 *
 * A simple RESTfull router
 */
class Router
{
    private array $wildcards = [
        '{all}' => '(.*)',
        '{any}' => '([^/]+)',
        '{int}' => '([+-]?[0-9]+)',
        '{float}' => '([+-]?[0-9]+(?:[.][0-9]+)?)',
        '{id}' => '([1-9][0-9]*)',
        '{hex}' => '([A-Fa-f0-9]+)',
        '{uuid}' => '([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12})',
    ];

    /**
     * @var Route[]
     */
    private array $routes = [];

    // -------------------------------------------------------------------------

    /**
     * Add a wildcard
     *
     * @param string $shortcut The wildcard name
     * @param string $pattern Regex
     * @return static
     */
    public function wildcard(string $shortcut, string $pattern): static
    {
        $shortcut = '{' . trim($shortcut, '{}') . '}';
        $this->wildcards[$shortcut] = $pattern;

        return $this;
    }

    // -------------------------------------------------------------------------

    /**
     * @param string $method
     * @param string $uri
     * @param mixed $callback
     * @return static
     */
    public function add(string $method, string $uri, mixed $callback): static
    {
        $uri = self::normalizeUri($uri);
        $this->routes[$method][$uri] = new Route($uri, $callback);

        return $this;
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @return static
     */
    public function get(string $uri, mixed $callback): static
    {
        return $this->add('GET', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @return static
     */
    public function post(string $uri, mixed $callback): static
    {
        return $this->add('POST', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @return static
     */
    public function put(string $uri, mixed $callback): static
    {
        return $this->add('PUT', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @return static
     */
    public function patch(string $uri, mixed $callback): static
    {
        return $this->add('PATCH', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @return static
     */
    public function delete(string $uri, mixed $callback): static
    {
        return $this->add('DELETE', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @return static
     */
    public function options(string $uri, mixed $callback): static
    {
        return $this->add('OPTIONS', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @return static
     */
    public function head(string $uri, mixed $callback): static
    {
        return $this->add('HEAD', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param mixed $callback
     * @return static
     */
    public function cli(string $uri, mixed $callback): static
    {
        return $this->add('CLI', $uri, $callback);
    }

    // -------------------------------------------------------------------------

    /**
     * Finds a route
     *
     * @param string $method
     * @param string $uri
     * @return Route
     */
    public function find(string $method, string $uri): Route
    {
        $uri = self::normalizeUri($uri);

        if (!isset($this->routes[$method])) {
            throw new RouterException('No matching routes');
        }

        if (($route = $this->routes[$method][$uri] ?? null)) {
            return $route;
        }

        foreach ($this->routes[$method] as $route) {
            if (($this->match($uri, $route))) {
                return $route;
            }
        }

        throw new RouterException('No matching routes');
    }

    // -------------------------------------------------------------------------

    protected static function normalizeUri(string $uri): string
    {
        return  '/' . trim($uri, '/');
    }

    private function match(string $uri, Route $route): bool
    {
        $matches = [];
        if (!preg_match($this->buildRegex($route), $uri, $matches)) {
            return false;
        }

        $route->setArguments(self::buildMatches($matches));

        return true;
    }

    private function buildRegex(Route $route): string
    {
        $regex = $route->uri();
        $regex = strtr($regex, $this->wildcards);
        $regex = "#^{$regex}$#i";

        return $regex;
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
