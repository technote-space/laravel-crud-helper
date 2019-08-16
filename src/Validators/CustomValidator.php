<?php
declare(strict_types=1);

namespace Technote\CrudHelper\Validators;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Validation\Validator;

/**
 * Class ExtensionValidator
 * @package Technote\CrudHelper\Validators
 */
class CustomValidator extends Validator
{
    /**
     * CustomValidator constructor.
     *
     * @param  Translator  $translator
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     */
    public function __construct(Translator $translator, array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        parent::__construct($translator, $data, $rules, array_merge($messages, static::getMessages()), $customAttributes);
    }

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

    /**
     * @return array
     */
    public static function getMessages()
    {
        return [
            'katakana' => trans('technote::validation.katakana'),
            'zip_code' => trans('technote::validation.zip_code'),
            'phone'    => trans('technote::validation.phone'),
        ];
    }
}
