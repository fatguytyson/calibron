<?php

namespace App\DependencyInjection;

use App\Model\Edge;
use App\Model\Rect;
use Symfony\Component\Console\Style\SymfonyStyle;

class Solver
{
    private static $solutions = 0;
    /** @var SymfonyStyle */
    private $io;
    /** @var int */
    private $width;
    /** @var int */
    private $height;
    /** @var Rect[] */
    private $pieces;
    /** @var Rect[] */
    private $placed = [];

    /**
     * @param SymfonyStyle $io
     */
    public function setIo(SymfonyStyle $io): void
    {
        $this->io = $io;

    }

    /**
     * @param int $width
     * @param int $height
     * @param Rect[] $pieces
     * @return bool
     */
    public function solve(int $width, int $height, array $pieces): bool
    {
        $this->width = $width;
        $this->height = $height;
        $this->pieces = $pieces;
        $this->placed = [];
        $edges[] = new Edge(0, 0, $this->width);

        return $this->tryPiece($edges);
    }

    /**
     * @param Edge[] $edges
     * @return void
     */
    protected function tryPiece(array $edges): bool
    {
        if (count($this->placed) === count($this->pieces)) {
            $this->io->success(sprintf('%d solutions found', ++self::$solutions));
            $this->printBoard();
            return true;
        }
        foreach ($this->pieces as $piece) {
            if ($piece->isPlaced()) {
                continue;
            }
            $attempt = $edges;
            if ($this->pieceFits($piece, $attempt)) {
                if ($this->tryPiece($attempt)) {
                    return true;
                }
                array_pop($this->placed);
                $piece->remove();
            }
            $piece->rotate();
            $attempt = $edges;
            if ($this->pieceFits($piece, $attempt)) {
                if ($this->tryPiece($attempt)) {
                    return true;
                }
                array_pop($this->placed);
                $piece->remove();
            }
        }
        return false;
    }

    protected function pieceFits(Rect $piece, array &$edges): bool
    {
        if ($piece->getWidth() > $edges[0]->getLength()) {
            return false;
        }
        if ($this->height < $edges[0]->getY() + $piece->getHeight()) {
            return false;
        }
        $origEdge = array_shift($edges);
        $this->placed[] = $piece->place($origEdge->getX(), $origEdge->getY());
        $new = new Edge($origEdge->getX(), $origEdge->getY() + $piece->getHeight(), $piece->getWidth());
        $edge = new Edge($origEdge->getX() + $piece->getWidth(), $origEdge->getY(), $origEdge->getLength() - $piece->getWidth());
        $this->insertEdge($new, $edges);
        if (0 < $edge->getLength()) {
            $this->insertEdge($edge, $edges);
        }
        return true;
    }

    /**
     * @param Edge $insert
     * @param Edge[] $edges
     * @return void
     */
    protected function insertEdge(Edge $insert, array &$edges): void
    {
        foreach ($edges as $index => $edge) {
            if ($insert->getY() > $edge->getY()) {
                continue;
            }
            if ($insert->getY() === $edge->getY()) {
                if ($insert->getX() === $edge->getX() + $edge->getLength()) {
                    if ($peek = $edges[$index + 1] ?? null) {
                        if ($insert->getY() === $peek->getY() && $insert->getX() + $insert->getLength() === $peek->getX()) {
                            array_splice(
                                $edges,
                                $index,
                                2,
                                [new Edge(
                                    $edge->getX(),
                                    $edge->getY(),
                                    $edge->getLength() + $insert->getLength() + $peek->getLength()
                                )]
                            );

                            return;
                        }
                    }
                    $edges[$index] = new Edge($edge->getX(), $edge->getY(), $edge->getLength() + $insert->getLength());

                    return;
                }
                if ($edge->getX() === $insert->getX() + $insert->getLength()) {
                    $edges[$index] = new Edge($insert->getX(), $edge->getY(), $edge->getLength() + $insert->getLength());

                    return;
                }
                array_splice($edges, $index + ($edge->getX() > $insert->getX() ? 1 : 0), 0, [$insert]);

                return;
            }
            array_splice($edges, $index, 0, [$insert]);

            return;
        }
        $edges[] = $insert;
    }

    protected function printBoard(): void
    {
        if (null === $this->io) {
            return;
        }
        $canvas = array_fill(0, $this->height+1, array_fill(0, $this->width+1, ' '));
        foreach ($this->placed as $rect) {
            [$x, $y] = $rect->getPlaced();
            $l = $x;
            $t = $y;
            $r = $l + $rect->getWidth();
            $b = $t + $rect->getHeight();
            $canvas[$y][$x] = "\u{250c}";
            while (++$x < $r) {
                $canvas[$y][$x] = "\u{2500}";
            }
            $canvas[$y][$x] = "\u{2510}";
            while (++$y < $b) {
                $canvas[$y][$x] = "\u{2502}";
            }
            $canvas[$y][$x] = "\u{2518}";
            while (--$x > $l) {
                $canvas[$y][$x] = "\u{2500}";
            }
            $canvas[$y][$x] = "\u{2514}";
            while (--$y > $t) {
                $canvas[$y][$x] = "\u{2502}";
            }
            $label = str_split(sprintf('%dx%d', $rect->getWidth(), $rect->getHeight()));
            array_splice($canvas[$y+1], $x+1, count($label), $label);
        }
        $this->io->writeln(implode(
            "\n",
            array_map(function ($line) {
                return implode('', $line);
            }, $canvas)
        ));
    }
}