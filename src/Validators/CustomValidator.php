<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Validators;

/**
 * Class ExtensionValidator
 * @package Technote\CrudHelper\Validators
 */
class CustomValidator
{
    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateKatakana(/** @noinspection PhpUnusedParameterInspection */ $attribute, $value, $parameters)
    {
        return (bool) preg_match('/^[ァ-ヾ 　〜ー−]+$/u', $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateZipCode(/** @noinspection PhpUnusedParameterInspection */ $attribute, $value, $parameters)
    {
        return (bool) preg_match('/^\d{3}-\d{4}$|^\d{3}-\d{2}$|^\d{3}$|^\d{5}$|^\d{7}$/u', $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validatePhone(/** @noinspection PhpUnusedParameterInspection */ $attribute, $value, $parameters)
    {
        return (bool) preg_match('/^\d{2,4}-?\d{2,4}-?\d{3,4}$/u', $value);
    }
}
