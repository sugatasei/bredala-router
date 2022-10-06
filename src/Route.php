<?php

namespace Bredala\Router;

class Route
{
    private string $uri;
    private mixed $callback;
    private array $arguments = [];

    public function __construct(string $uri, mixed $callback)
    {
        $this->uri = $uri;
        $this->callback = $callback;
    }

    public function setArguments(array $arguments): static
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function callback(): mixed
    {
        return $this->callback;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }
}
