<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Models\Traits;

use Doctrine\DBAL\Schema\Column;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Trait Crudable
 * @package Technote\CrudHelper\Models\Traits
 * @mixin Model
 */
trait Crudable
{
    /**
     * @param  array  $rules
     * @param  string  $name
     * @param  Column  $column
     * @param  bool  $isUpdate
     * @param  int|null  $primaryId
     * @param  FormRequest  $request
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function filterCrudRules(/** @noinspection PhpUnusedParameterInspection */ array $rules, string $name, Column $column, bool $isUpdate, ?int $primaryId, FormRequest $request): array
    {
        return $rules;
    }

    /**
     * @param  string  $attr
     * @param  string  $name
     * @param  Column  $column
     * @param  bool  $isUpdate
     * @param  int|null  $primaryId
     * @param  FormRequest  $request
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function filterCrudAttribute(/** @noinspection PhpUnusedParameterInspection */ string $attr, string $name, Column $column, bool $isUpdate, ?int $primaryId, FormRequest $request): string
    {
        return $attr;
    }

    /**
     * @return array
     */
    public static function getCrudListRelations(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getCrudDetailRelations(): array
    {
        return [];
    }

    /**
     * @return array
     */
    public static function getCrudUpdateRelations(): array
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getPerPageName(): string
    {
        return 'per_page';
    }

    /**
     * @return string
     */
    public static function getCountName(): string
    {
        return 'count';
    }
}
