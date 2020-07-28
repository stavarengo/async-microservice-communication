<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\NameProvider;


interface NameProviderInterface
{
    public function getName(): string;
}