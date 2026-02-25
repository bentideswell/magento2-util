<?php
/**
 * 
 */
namespace FishPig\Util\Console\Command;

class Pool
{
    public function __construct(
        private array $commands = []
    ) {}

    public function getAll(): array
    {
        return $this->commands;
    }
}
