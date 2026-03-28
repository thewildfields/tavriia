<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Post;

/**
 * Abstract base providing typed meta value accessors.
 *
 * Concrete implementations supply the raw meta retrieval/update/delete
 * operations for their respective meta storage (post meta, term meta, etc.).
 */
abstract class AbstractMetaManager
{
    /**
     * Retrieve the raw scalar meta value for a given key.
     *
     * Returns null when no value is stored for that key.
     */
    abstract protected function getRawValue(string $key): mixed;

    /**
     * Retrieve a meta value as a string.
     *
     * Returns the default when no value is stored.
     */
    public function getString(string $key, string $default = ''): string
    {
        $value = $this->getRawValue($key);

        if ($value === null || $value === false || $value === '') {
            return $default;
        }

        return (string) $value;
    }

    /**
     * Retrieve a meta value as an integer.
     *
     * Returns the default when no value is stored.
     */
    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->getRawValue($key);

        if ($value === null || $value === false || $value === '') {
            return $default;
        }

        return (int) $value;
    }

    /**
     * Retrieve a meta value as a float.
     *
     * Returns the default when no value is stored.
     */
    public function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->getRawValue($key);

        if ($value === null || $value === false || $value === '') {
            return $default;
        }

        return (float) $value;
    }

    /**
     * Retrieve a meta value as a boolean.
     *
     * Treats '1', 'true', 'yes', and 'on' (case-insensitive) as true.
     * Returns the default when no value is stored.
     */
    public function getBool(string $key, bool $default = false): bool
    {
        $value = $this->getRawValue($key);

        if ($value === null || $value === false || $value === '') {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Retrieve a meta value as an array.
     *
     * When the stored value is a serialized array it will already have been
     * unserialized by WordPress. Returns the default when no value is stored
     * or when the value is not an array.
     *
     * @return array<mixed>
     */
    public function getArray(string $key, array $default = []): array
    {
        $value = $this->getRawValue($key);

        if ($value === null || $value === false || $value === '') {
            return $default;
        }

        if (!is_array($value)) {
            return $default;
        }

        return $value;
    }
}
