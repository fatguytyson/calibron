<?php

namespace App\Model;

class Rect
{
    /** @var int */
    private $width;
    /** @var int */
    private $height;
    /** @var bool */
    private $rotated = false;
    /** @var int[]|null */
    private $placed = null;

    public function __construct(int $width, int $height) {
        $this->width = $width;
        $this->height = $height;
    }

    public function getWidth(): int
    {
        return $this->rotated ? $this->height : $this->width;
    }

    public function getHeight(): int
    {
        return $this->rotated ? $this->width : $this->height;
    }

    public function isRotated(): bool
    {
        return $this->rotated;
    }

    public function rotate(): Rect
    {
        $this->rotated = !$this->rotated;

        return $this;
    }

    public function isPlaced(): bool
    {
        return $this->placed !== null;
    }

    public function place(int $x, int $y): Rect
    {
        $this->placed = [$x, $y];

        return $this;
    }

    /**
     * @return int[]|null
     */
    public function getPlaced(): ?array
    {
        return $this->placed;
    }

    public function remove(): Rect
    {
        $this->placed = null;

        return $this;
    }
}