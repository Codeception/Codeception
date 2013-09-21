# Codeception

**Moderno Framework de testes em PHP para qualquer um**


Codeception é um moderno e completo framework de testes para PHP.
Inspirando por BDD, ele provê uma forma totalmente nova de escrever testes de aceitação, funcionais e unitários. Powered by PHPUnit 3.7.


| release |  branch  |  status  |
| ------- | -------- | -------- |
| **Stable** | **1.6** | [![Build Status](https://secure.travis-ci.org/Codeception/Codeception.png?branch=1.6)](http://travis-ci.org/Codeception/Codeception) [![Latest Stable](https://poser.pugx.org/Codeception/Codeception/version.png)](https://packagist.org/packages/Codeception/Codeception)
| **Development** | **master** | [![Build Status](https://secure.travis-ci.org/Codeception/Codeception.png?branch=master)](http://travis-ci.org/Codeception/Codeception) [![Dependencies Status](https://d2xishtp1ojlk0.cloudfront.net/d/2880469)](http://depending.in/Codeception/Codeception)


#### Contribuições

**Bugfixes devem ser enviados para um branch corrente e estável, que é o mesmo número da versão major.**
Features quebradas e melhorias maiores devem ser enviadas para o branch `master`.
Quando você envia Pull Requests para o master, eles serão adicionados ao ciclo de release somente quando o próximo branch estável iniciar.

### Em uma olhada

Descreva o que você vai testar e como vai testá-lo. Use o próprio PHP para escrever descrições mais rápidamente.
Execute os teste e veja as açoes que foram realizadas e os resultados produzidos por elas.

#### Exemplo de teste de aceitação

``` php
<?php

$I = new TestGuy($scenario);
$I->wantTo('create wiki page');
$I->amOnPage('/');
$I->click('Pages');
$I->click('New');
$I->see('New Page');
$I->submitForm('form#new_page', array('title' => 'Tree of Life Movie Review','body' => "Next time don't let Hollywood create art-house!"));
$I->see('page created'); // notice generated
$I->see('Tree of Life Movie Review','h1'); // head of page of is our title
$I->seeInCurrentUrl('pages/tree-of-life-movie-review'); // slug is generated
$I->seeInDatabase('pages', array('title' => 'Tree of Life Movie Review')); // data is stored in database
?>
```

Para testes unitários você pode seguir os testes clássicos de PHPUnit, Codeception pode roda-los também.

## Documentação

[Documentação no Github](https://github.com/Codeception/Codeception/tree/master/docs)

A Documentação é correntemente incluida com o projeto. Olhe no diretório 'docs'.

## Instalação

### Phar

Download [codecept.phar](https://github.com/Codeception/Codeception/raw/master/package/codecept.phar)

Copie para o seu projeto.

Execute o utilitário CLI:

```
php codecept.phar
```

## Getting Started

Se você instalou com sucesso o Codeception, execute este comando:

```
codecept bootstrap
```

Ele vai criar uma estrutura padrão de diretórios e as suites de testes padrão.

```
codecept build
```

Este vai gerar as 'Guy-classes', em ordem para fazer o autocomplete funcionar.

Veja a Documentação para maiores informações.

### License
MIT

(c) Michael Bodnarchuk "Davert"
2011-2013
