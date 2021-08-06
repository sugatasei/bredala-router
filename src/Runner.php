<?php

namespace Bredala\Router;

class Runner implements RunnerInterface
{
    /**
     * @param Route $route
     */
    public function run(Route $route)
    {
        if ($route->callback && is_callable($route->callback)) {
            return call_user_func_array($route->callback, $route->matches);
        }

        throw new RouterException($route->uri . ' is not callable');
    }
}
