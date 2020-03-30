<?php

namespace TarBlog\Support;

use Illuminate\Contracts\Support\Arrayable;

/**
 * 数据队列支持
 */
trait Queue
{
    /**
     * 队列
     *
     * @var array
     */
    protected $queue = [];

    /**
     * 当前出队的数据
     *
     * @var mixed
     */
    protected $row;

    /**
     * @return array
     */
    public function getQueue(): array
    {
        return $this->queue;
    }

    /**
     * @param array $queue
     */
    public function setQueue(array $queue)
    {
        $this->queue = $queue instanceof Arrayable ? $queue->toArray() : $queue;
    }

    /**
     * 队列是否不为空
     *
     * @return boolean
     */
    public function have()
    {
        return !empty($this->queue);
    }

    /**
     * 出队
     *
     * @return mixed|null
     */
    public function next()
    {
        if ($this->have()) {
            $row = array_shift($this->queue);
        } else {
            return null;
        }

        $this->row = $row;

        return $row;
    }
}