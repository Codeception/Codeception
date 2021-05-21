<?php

namespace Codeception\Lib\Interfaces;

interface DataMapper extends ORM, DoctrineProvider
{
    public function haveInRepository(string $entity, array $data);

    public function seeInRepository(string $entity, array $params = []): void;

    public function dontSeeInRepository(string $entity, array $params = []): void;

    public function grabFromRepository(string $entity, string $field, array $params = []);
}
