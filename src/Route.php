<?php

namespace Bredala\Router;

class Route
{
    public string $uri;
    public $callback;
    public array $arguments = [];

    public function __construct(string $uri, $callback, array $arguments = [])
    {
        $this->uri = $uri;
        $this->callback = $callback;
        $this->arguments = $arguments;
    }
}
