<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Providers\Contracts;

/**
 * Interface ModelInjectable
 * @package Technote\CrudHelper\Providers\Contracts
 */
interface ModelInjectable
{
    /**
     * @param string $target
     *
     * @return void
     */
    public function setTarget(string $target): void;
}
