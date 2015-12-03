<?php
namespace Codeception\Lib\Interfaces;

/**
 * Interface SupportsDomainRouting
 * @package Codeception\Lib\Interfaces
 *
 * Used to distinguish framework modules that support domains from those that don't
 */
interface SupportsDomainRouting
{
    /**
     * @return array a list of recognized domain names
     */
    public function getInternalDomains();
}
