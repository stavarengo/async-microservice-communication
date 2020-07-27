<?php


namespace AMC\Broker\Persistence;


interface IDGeneratorInterface
{
    public function generate(): string;
}