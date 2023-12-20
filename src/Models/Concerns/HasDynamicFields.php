<?php

namespace Jalno\AAA\Models\Concerns;

trait HasDynamicFields
{
    /**
     * @var string[]
     */
    protected static $hiddenFields = [];

    /**
     * @var string[]
     */
    protected static $fillableFields = [];

    /**
     * @var array<string,mixed>
     */
    protected static $castFields = [];

    public static function addHiddenField(string $name): void
    {
        self::$hiddenFields[] = $name;
    }

    public static function removeHiddenField(string $name): void
    {
        $i = array_search($name, self::$hiddenFields);
        if (false !== $i) {
            array_splice(self::$hiddenFields, $i, 1);
        }
    }

    /**
     * @return string[]
     */
    public static function getHiddenFields(): array
    {
        return self::$hiddenFields;
    }

    /**
     * @param string[] $fields
     */
    public static function setHiddenFields(array $fields): void
    {
        self::$hiddenFields = $fields;
    }

    public static function addFillableField(string $name): void
    {
        self::$fillableFields[] = $name;
    }

    public static function removeFillableField(string $name): void
    {
        $i = array_search($name, self::$fillableFields);
        if (false !== $i) {
            array_splice(self::$fillableFields, $i, 1);
        }
    }

    /**
     * @return string[]
     */
    public static function getFillableFields(): array
    {
        return self::$fillableFields;
    }

    /**
     * @param string[] $fields
     */
    public static function setFillableFields(array $fields): void
    {
        self::$fillableFields = $fields;
    }

    public static function addCastField(string $field, mixed $cast): void
    {
        self::$castFields[$field] = $cast;
    }

    public static function removeCastField(string $field): void
    {
        unset(self::$castFields[$field]);
    }

    /**
     * @param array<string,mixed>
     */
    public static function getCastFields(): array
    {
        return self::$castFields;
    }

    public static function bootHasDynamicFields(): void
    {
        $ref = new \ReflectionClass(static::class);
        if ($ref->hasProperty('casts')) {
            self::$castFields = $ref->getProperty('casts')->getDefaultValue();
        }
        if ($ref->hasProperty('fillable')) {
            self::$fillableFields = $ref->getProperty('fillable')->getDefaultValue();
        }
        if ($ref->hasProperty('hidden')) {
            self::$hiddenFields = $ref->getProperty('hidden')->getDefaultValue();
        }
    }

    public function initializeHasDynamicFields(): void
    {
        $this->casts = self::$castFields;
        $this->fillable = self::$fillableFields;
        $this->hidden = self::$hiddenFields;
    }
}
