<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Models\Contracts;

use Doctrine\DBAL\Schema\Column;

/**
 * Interface Crudable
 * @package Technote\CrudHelper\Models\Contracts
 */
interface Crudable
{
    /**
     * @param  array  $rules
     * @param  string  $name
     * @param  Column  $column
     * @param  bool  $isUpdate
     * @param  int|null  $primaryId
     *
     * @return array
     */
    public static function filterCrudRules(array $rules, string $name, Column $column, bool $isUpdate, ?int $primaryId): array;

    /**
     * @param  string  $attr
     * @param  string  $name
     * @param  Column  $column
     * @param  bool  $isUpdate
     * @param  int|null  $primaryId
     *
     * @return string
     */
    public static function filterCrudAttribute(string $attr, string $name, Column $column, bool $isUpdate, ?int $primaryId): string;

    /**
     * @return array
     */
    public static function getCrudListRelations(): array;

    /**
     * @return array
     */
    public static function getCrudDetailRelations(): array;

    /**
     * @return array
     */
    public static function getCrudUpdateRelations(): array;

    /**
     * @return string
     */
    public static function getPerPageName(): string;

    /**
     * @return string
     */
    public static function getCountName(): string;
}
