<?php

namespace Application\Cache;

use Doctrine\Common\Cache\Cache;
use Laminas\Cache\Storage\StorageInterface;

/**
 * Simple adapter that implements Doctrine\Common\Cache\Cache by delegating
 * to a Laminas Cache Storage adapter.
 */
class DoctrineCacheAdapter implements Cache
{
    /** @var StorageInterface */
    private $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    public function fetch($id)
    {
        try {
            $value = $this->storage->getItem($id);
            // Laminas returns null/default when missing; Doctrine expects false
            if ($this->storage->hasItem($id) === false) {
                return false;
            }
            return $value;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function contains($id)
    {
        try {
            return (bool) $this->storage->hasItem($id);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function save($id, $data, $lifeTime = 0)
    {
        try {
            // Laminas accepts TTL in seconds via setItem with metadata in some adapters,
            // but Memory adapter ignores TTL; we attempt setItem and return boolean.
            return (bool) $this->storage->setItem($id, $data);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function delete($id)
    {
        try {
            return (bool) $this->storage->removeItem($id);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getStats()
    {
        // Not implemented for Laminas Memory adapter; return null
        return null;
    }
}
