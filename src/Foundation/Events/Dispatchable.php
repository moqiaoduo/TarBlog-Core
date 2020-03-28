<?php

namespace TarBlog\Foundation\Events;

trait Dispatchable
{
    /**
     * Dispatch the event with the given arguments.
     *
     * @return array|null
     */
    public static function dispatch()
    {
        return event(new static(...func_get_args()));
    }
}
