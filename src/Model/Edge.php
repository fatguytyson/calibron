<?php

namespace App\Model;

class Edge
{
    /** @var int */
    private $x;
    /** @var int */
    private $y;
    /** @var int */
    private $length;

    public function __construct(int $x, int $y, int $length)
    {
        $this->x = $x;
        $this->y = $y;
        $this->length = $length;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function getLength(): int
    {
        return $this->length;
    }
}