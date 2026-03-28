<?php

declare(strict_types=1);

namespace TheWildFields\Tavriia\Post;

/**
 * Typed wrapper around WordPress post meta functions.
 *
 * Bound to a specific post ID at construction time. Typed accessors
 * (getString, getInt, getBool, getArray) are inherited from AbstractMetaManager.
 * No raw meta values ever leave this class.
 */
final class MetaManager extends AbstractMetaManager
{
    public function __construct(private readonly int $postId)
    {
    }

    /**
     * Retrieve the raw scalar meta value for a given key.
     *
     * Uses the single-value form of get_post_meta() so WordPress
     * automatically unserializes stored data.
     */
    protected function getRawValue(string $key): mixed
    {
        $value = get_post_meta($this->postId, $key, true);

        // get_post_meta returns '' when the key does not exist (single=true)
        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Persist a meta value for the bound post.
     *
     * @param mixed $value Any scalar or serializable value.
     * @return bool        True on success, false on failure.
     */
    public function set(string $key, mixed $value): bool
    {
        return (bool) update_post_meta($this->postId, $key, $value);
    }

    /**
     * Delete a meta entry from the bound post.
     *
     * @param mixed $value When provided, only the matching value is deleted.
     * @return bool        True on success, false on failure.
     */
    public function delete(string $key, mixed $value = ''): bool
    {
        return (bool) delete_post_meta($this->postId, $key, $value);
    }

    /**
     * Check whether a meta key exists for the bound post.
     */
    public function has(string $key): bool
    {
        return metadata_exists('post', $this->postId, $key);
    }

    /**
     * Retrieve all meta for the bound post as a flat key → value map.
     *
     * Each meta key is mapped to its first (single) value, already unserialized.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        $raw = get_post_meta($this->postId);

        if (!is_array($raw)) {
            return [];
        }

        $result = [];
        foreach ($raw as $key => $values) {
            $result[$key] = maybe_unserialize($values[0] ?? '');
        }

        return $result;
    }
}
