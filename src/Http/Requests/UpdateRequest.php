<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Http\Requests;

use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Type;
use Eloquent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Technote\CrudHelper\Models\Contracts\Crudable;
use Technote\CrudHelper\Providers\Contracts\ModelInjectionable;

/**
 * Class CrudRequest
 * @package Technote\CrudHelper\Requests
 */
class UpdateRequest extends FormRequest implements ModelInjectionable
{
    /** @var string|Eloquent|Crudable $target */
    private $target;

    /** @var string[] $tables */
    private $tables;

    /**
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * @param  string  $target
     */
    public function setTarget(string $target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    protected function getSingularName()
    {
        return Str::singular($this->getTable($this->target));
    }

    /**
     * @return array
     */
    protected function getSubTargets(): array
    {
        return $this->target::getCrudUpdateRelations();
    }

    /**
     * @return bool
     */
    protected function isUpdate(): bool
    {
        return ! empty($this->route($this->getSingularName()));
    }

    /**
     * @return int|null
     */
    protected function getPrimaryId(): ?int
    {
        return $this->isUpdate() ? $this->route($this->getSingularName()) - 0 : null;
    }

    /**
     * @return array
     */
    protected function getTargets()
    {
        return array_merge([$this->target], array_values($this->getSubTargets()));
    }

    /**
     * @param  string  $target
     *
     * @return string
     */
    protected function getTable(string $target): string
    {
        if (! isset($this->tables[$target])) {
            $this->tables[$target] = $this->getInstance($target)->getTable();
        }

        return $this->tables[$target];
    }

    /**
     * @param  string  $target
     *
     * @return Model|Crudable
     * @SuppressWarnings(PHPMD.MissingImport)
     */
    private function getInstance(string $target)
    {
        return new $target;
    }

    /**
     * @return string
     */
    protected function getForeignKey()
    {
        return $this->getInstance($this->target)->getForeignKey();
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return collect($this->getTargets())->flatMap(function ($target) {
            return $this->getTableRules($target);
        })->toArray();
    }

    /**
     * @param  string  $target
     *
     * @return array
     */
    protected function getTableRules(string $target): array
    {
        $foreignKey = $this->getForeignKey();

        return collect(DB::connection()->getDoctrineSchemaManager()->listTableColumns($this->getTable($target)))->filter(function (Column $column) use ($foreignKey) {
            return ! in_array($column->getName(), [
                'id',
                'created_at',
                'updated_at',
                $foreignKey,
            ], true);
        })->mapWithKeys(function (Column $column) use ($target) {
            return ["{$this->getTable($target)}.{$column->getName()}" => $this->getRules($target, $column)];
        })->toArray();
    }

    /**
     * @param  string  $target
     * @param  Column  $column
     *
     * @return array
     */
    protected function getRules(string $target, Column $column): array
    {
        $rules = [];
        if ($column->getNotnull()) {
            if (is_null($column->getDefault())) {
                if ($this->isUpdate()) {
                    $rules['filled'] = 'filled';
                } else {
                    $rules['required'] = 'required';
                }
            } else {
                $rules['filled'] = 'filled';
            }
        } else {
            $rules['nullable'] = 'nullable';
        }

        // unsigned is invalid for sqlite
        // @codeCoverageIgnoreStart
        if ($column->getUnsigned()) {
            $rules['min'] = 'min:0';
        }
        if ($column->getLength()) {
            $rules['max'] = 'max:'.$column->getLength();
        }
        // @codeCoverageIgnoreEnd

        $rules = $this->getNameRules($rules, $column->getName());

        $rules = $this->getTypeRules($rules, $column->getType());

        return $this->filterRules($rules, "{$this->getTable($target)}.{$column->getName()}", $column);
    }

    /**
     * @param  array  $rules
     * @param  string  $name
     *
     * @return array
     */
    private function getNameRules(array $rules, string $name)
    {
        if (stristr($name, 'email') !== false) {
            $rules['email'] = 'email';
        }
        if (stristr($name, 'url') !== false) {
            $rules['url'] = 'url';
        }
        if (stristr($name, 'phone') !== false) {
            $rules['phone'] = 'phone';
        }
        if (preg_match('#\A(\w+)_id\z#', $name, $matches)) {
            $table           = Str::snake(Str::pluralStudly($matches[1]));
            $rules['exists'] = "exists:{$table},id";
        }
        if (stristr($name, 'kana') !== false) {
            $rules['katakana'] = 'katakana';
        }
        if (stristr($name, 'zip_code') !== false || stristr($name, 'postal_code') !== false) {
            $rules['zip_code'] = 'zip_code';
        }

        return $rules;
    }

    /**
     * @param  array  $rules
     * @param  string  $name
     * @param  Column  $column
     *
     * @return array
     */
    protected function filterRules(array $rules, string $name, Column $column): array
    {
        return $this->target::filterCrudRules($rules, $name, $column, $this->isUpdate(), $this->getPrimaryId(), $this);
    }

    /**
     * @param  array  $rules
     * @param  Type  $type
     *
     * @return array
     */
    protected function getTypeRules(array $rules, Type $type)
    {
        $normalized = null;
        if (in_array($type->getName(), [
            Type::BOOLEAN,
        ], true)) {
            $normalized = 'Boolean';
        } elseif (in_array($type->getName(), [
            Type::INTEGER,
            Type::BIGINT,
            Type::SMALLINT,
        ], true)) {
            $normalized = 'Int';
        } elseif (in_array($type->getName(), [
            Type::FLOAT,
        ], true)) {
            $normalized = 'Numeric';
        } elseif (in_array($type->getName(), [
            Type::DATETIME,
            Type::DATETIME_IMMUTABLE,
            Type::DATETIMETZ,
            Type::DATETIMETZ_IMMUTABLE,
            Type::DATE,
            Type::DATE_IMMUTABLE,
        ], true)) {
            $normalized = 'Date';
        } elseif (in_array($type->getName(), [
            Type::TIME,
            Type::TIME_IMMUTABLE,
        ], true)) {
            $normalized = 'Time';
        } elseif (in_array($type->getName(), [
            Type::STRING,
            Type::TEXT,
        ], true)) {
            $normalized = 'String';
        }

        if ($normalized) {
            $function = "get{$normalized}TypeRules";

            return $this->$function($rules);
        }

        // @codeCoverageIgnoreStart
        return $rules;
        // @codeCoverageIgnoreEnd
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @param  array  $rules
     *
     * @return array
     */
    private function getBooleanTypeRules(array $rules)
    {
        $rules['boolean']  = 'boolean';
        $rules['nullable'] = 'nullable';
        unset($rules['required']);
        unset($rules['filled']);
        unset($rules['max']);

        return $rules;
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @param  array  $rules
     *
     * @return array
     */
    private function getIntTypeRules(array $rules)
    {
        $rules['integer'] = 'integer';

        return $rules;
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @param  array  $rules
     *
     * @return array
     */
    private function getNumericTypeRules(array $rules)
    {
        $rules['numeric'] = 'numeric';

        return $rules;
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @param  array  $rules
     *
     * @return array
     */
    private function getDateTypeRules(array $rules)
    {
        $rules['date'] = 'date';

        return $rules;
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @param  array  $rules
     *
     * @return array
     */
    private function getTimeTypeRules(array $rules)
    {
        $rules['time'] = 'date_format:H:i';

        return $rules;
    }

    /**
     * @noinspection PhpUnusedPrivateMethodInspection
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @param  array  $rules
     *
     * @return array
     */
    private function getStringTypeRules(array $rules)
    {
        $rules['string'] = 'string';

        return $rules;
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return collect($this->getTargets())->flatMap(function ($target) {
            return $this->getTableAttributes($target);
        })->toArray();
    }

    /**
     * @param  string  $target
     *
     * @return array
     */
    protected function getTableAttributes(string $target): array
    {
        return collect($this->getInstance($target)->getConnection()->getDoctrineSchemaManager()->listTableColumns($this->getTable($target)))->filter(function (Column $column) {
            return ! in_array($column->getName(), [
                'id',
                'created_at',
                'updated_at',
            ], true);
        })->mapWithKeys(function (Column $column) use ($target) {
            $name = "{$this->getTable($target)}.{$column->getName()}";

            return [$name => $this->filterAttribute($this->translateColumn($name, $column), $name, $column)];
        })->toArray();
    }

    /**
     * @param  string  $name
     * @param  Column  $column
     *
     * @return string|null
     */
    protected function translateColumn(string $name, Column $column)
    {
        $key   = "database.{$name}";
        $value = __($key);
        if ($value === $key) {
            return $column->getComment() ?? $column->getName();
        }

        return $value;
    }

    /**
     * @param  string  $attr
     * @param  string  $name
     * @param  Column  $column
     *
     * @return string
     */
    protected function filterAttribute(string $attr, string $name, Column $column): string
    {
        return $this->target::filterCrudAttribute($attr, $name, $column, $this->isUpdate(), $this->getPrimaryId(), $this);
    }

    /**
     * @param  string  $target
     * @param  array  $merge
     *
     * @return array
     */
    protected function getSaveData(string $target, array $merge = []): array
    {
        return array_merge(Arr::get($this->validated(), $this->getTable($target), []), $merge);
    }

    /**
     * @return Collection
     */
    public function getData(): Collection
    {
        return collect([$this->getSaveData($this->target)])->concat(collect($this->getSubTargets())->map(function ($target, $relation) {
            return [
                'target'     => $target,
                'relation'   => $relation,
                'attributes' => $this->getSaveData($target),
            ];
        }));
    }
}
