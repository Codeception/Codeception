<?php
namespace Codeception\TestCase;

interface ScenarioDriven {

    public function getFeature();

    public function getScenario();

    public function getScenarioText();
}