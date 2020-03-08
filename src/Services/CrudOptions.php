<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Services;

class CrudOptions
{
    /**
     * @var array
     */
    protected $config;

    /**
     * CrudOptions constructor.
     *
     * @param  array  $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->config['namespace'];
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->config['prefix'];
    }

    /**
     * @return string[]
     */
    public function getMiddleware(): array
    {
        return $this->config['middleware'];
    }
}
