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
    public function validateKatakana(/** @noinspection PhpUnusedParameterInspection */ $attribute, $value, $parameters): bool
    {
        return (bool)preg_match('/^[ァ-ヾ 　〜ー−]+$/u', $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateZipCode(/** @noinspection PhpUnusedParameterInspection */ $attribute, $value, $parameters): bool
    {
        return strlen($value) < 9 && (bool)preg_match('/^\d{3}-\d{4}$|^\d{3}-\d{2}$|^\d{3}$|^\d{5}$|^\d{7}$/u', $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validatePhone(/** @noinspection PhpUnusedParameterInspection */ $attribute, $value, $parameters): bool
    {
        return strlen($value) < 15 && (bool)preg_match('/^\d{2,4}-?\d{2,4}-?\d{3,4}$/u', $value);
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateTime(/** @noinspection PhpUnusedParameterInspection */ $attribute, $value, $parameters): bool
    {
        $matches = null;
        if (strlen($value) > 8 || !preg_match('/^([0-2]?\d):([0-5]?\d)(:([0-5]?\d))?$/u', $value, $matches)) {
            return false;
        }

        if ((int)$matches[0] >= 24) {
            return false;
        }

        return true;
    }
}
