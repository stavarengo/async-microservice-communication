<?php

declare(strict_types=1);


namespace AMC\ConsumerServices\NameProvider;


use AMC\ConsumerServices\NameProvider\Exception\EmptyListOfHumanNames;

class HumanNameProvider implements NameProviderInterface
{
    public const LIST_OF_NAMES = 'list-of-names';

    /**
     * @var string[]
     */
    private array $listOfNames;

    public function __construct(string ...$listOfNames)
    {
        $this->listOfNames = $listOfNames;
    }

    public function getName(): string
    {
        if (!$this->listOfNames) {
            throw EmptyListOfHumanNames::create();
        }

        return $this->listOfNames[mt_rand(0, count($this->listOfNames) - 1)];
    }
}