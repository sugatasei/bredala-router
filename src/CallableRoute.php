<?php

namespace Bredala\Router;

class CallableRoute extends RouteAbstract
{
    public function execute(): mixed
    {
        if ($this->callback() && is_callable($this->callback())) {
            return call_user_func_array($this->callback(), $this->arguments());
        }

        return parent::execute();
    }
}
