<?php

namespace Bredala\Router;

class Route
{
    public string $uri;
    public $callback;
    public array $matches = [];

    public function __construct(string $uri, $callback, array $matches = [])
    {
        $this->uri = $uri;
        $this->callback = $callback;
        $this->matches = $matches;
    }
}
