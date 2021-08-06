<?php

namespace Bredala\Router;

interface RunnerInterface
{
    /**
     * @param Route $route
     * @throws RouterException
     * @return void
     */
    public function run(Route $route);
}
