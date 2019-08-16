<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Providers\Contracts;

/**
 * Interface ModelInjectionable
 * @package Technote\CrudHelper\Providers\Contracts
 */
interface ModelInjectionable
{
    /**
     * @param  string  $target
     */
    public function setTarget(string $target);
}
