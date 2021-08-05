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
        'all' => '(.*)',
        'any' => '([^/]+)',
        'num' => '(-?[0-9]+)',
        'hex' => '([A-Fa-f0-9]+)',
        'uuid' => '([a-fA-F0-9]{8}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{4}-[a-fA-F0-9]{12})'
    ];

    /**
     * @var array
     */
    private array $routes = [];

    /**
     * @var string
     */
    private string $group = '';

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

    /**
     * @param string $httpMethod
     * @param string $uri
     * @param callable $callback
     * @return Router
     */
    public function add(string $httpMethod, string $uri, callable $callback): Router
    {
        $this->routes[$httpMethod][$this->group . $uri] = $callback;
        return $this;
    }

    /**
     * Current group
     *
     * @param string $group Uri prefix
     */
    public function group(string $group = ''): Router
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return Router
     */
    public function get(string $uri, callable $callback): Router
    {
        return $this->add('GET', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return Router
     */
    public function post(string $uri, callable $callback): Router
    {
        return $this->add('POST', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return Router
     */
    public function put(string $uri, callable $callback): Router
    {
        return $this->add('PUT', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return Router
     */
    public function patch(string $uri, callable $callback): Router
    {
        return $this->add('PATCH', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return Router
     */
    public function delete(string $uri, callable $callback): Router
    {
        return $this->add('DELETE', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return Router
     */
    public function options(string $uri, callable $callback): Router
    {
        return $this->add('OPTIONS', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return Router
     */
    public function head(string $uri, callable $callback): Router
    {
        return $this->add('HEAD', $uri, $callback);
    }

    /**
     * @param string $uri
     * @param callable $callback
     * @return Router
     */
    public function cli(string $uri, callable $callback): Router
    {
        return $this->add('CLI', $uri, $callback);
    }

    // -------------------------------------------------------------------------

    /**
     * @param string $httpMethod
     * @param string $uriPath
     */
    public function run(string $httpMethod, string $uriPath)
    {
        // Method not found
        if (!isset($this->routes[$httpMethod])) {
            throw new RouterException("Route not found: {$httpMethod} {$uriPath}", 404);
        }

        // Exact match
        if (isset($this->routes[$httpMethod][$uriPath]) && mb_strpos($uriPath, '{') === false) {
            return call_user_func($this->routes[$httpMethod][$uriPath]);
        }

        // Regex match
        $search = [];
        $replace = [];
        foreach ($this->wildcards as $k => $v) {
            $search[] = '\{' . $k . '\}';
            $replace[] = $v;
        }

        foreach ($this->routes[$httpMethod] as $u => $callback) {
            if (mb_strpos($u, '{') !== false) {
                $matches = [];
                $pattern = '#^' . str_replace($search, $replace, preg_quote($u, '#')) . '$#';

                if (preg_match($pattern, $uriPath, $matches)) {
                    return call_user_func_array($callback, self::getParams($matches));
                }
            }
        }

        // Not found
        throw new RouterException("Route not found: {$httpMethod} {$uriPath}", 404);
    }

    /**
     * @param array $matches
     * @return array
     */
    private static function getParams(array $matches): array
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

    // -------------------------------------------------------------------------
}
