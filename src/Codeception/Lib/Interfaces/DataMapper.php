<?php
namespace Codeception\Lib\Interfaces;

interface DataMapper extends ORM, DoctrineProvider
{
    public function haveInRepository($entity, array $data);

    public function seeInRepository($entity, $params = []);

    public function dontSeeInRepository($entity, $params = []);

    public function grabFromRepository($entity, $field, $params = []);
}
