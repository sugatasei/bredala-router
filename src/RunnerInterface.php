<?php

namespace Bredala\Router;

interface RunnerInterface
{
    /**
     * @param Route $route
     * @throws RouterException
     * @return void
     */
    public static function run(Route $route);
}
