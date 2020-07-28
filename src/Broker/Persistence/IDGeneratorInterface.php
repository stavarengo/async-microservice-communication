<?php

declare(strict_types=1);


namespace AMC\Broker\Persistence;


interface IDGeneratorInterface
{
    public function generate(): string;
}