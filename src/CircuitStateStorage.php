<?php declare(strict_types=1);

namespace Pyjac\Opinmona;


use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

class CircuitStateStorage
{
    /** @var string */
    private $circuitServiceName; 
    
    /** @var AdapterInterface */
    private $cache;

    /** @var int */
    private $ttl; 

    /** @var string */
    private $cachePrefix;

    public function __construct(string $circuitServiceName, AdapterInterface $cache, int $ttl = 3600, string $cachePrefix = '')
    {
        $this->circuitServiceName = $circuitServiceName;
        $this->ttl = $ttl;
        $this->cachePrefix = $cachePrefix;
        $this->cache = $cache;
    }

    public function saveStatus(string $attributeName, $value)
    {
        $item = $this->getItem($attributeName);
        $item->set($value);
        $item->expiresAfter($this->ttl);

        $this->cache->save($item);
    }

    private function getItem(string $attributeName): CacheItem
    {
        return $this->cache->getItem($this->cachePrefix . $this->circuitServiceName . $attributeName);
    }

    public function loadStatus(string $attributeName)
    {
        $item = $this->getItem($attributeName);

        if ($item->isHit() == false) {
            return false;
        }
        
        return $item->get();
    }

    public function setFailures($value)
    {
       $this->saveStatus('failures', $value);;
    }

    public function setLastTest($value)
    {
       $this->saveStatus('lastTest', $value);;
    }

    public function getFailures(): int
    {
        return (int) $this->loadStatus('failures');
    }

    public function getLastTest(): int
    {
        return (int) $this->loadStatus('lastTest');
    }

    public function increaseFailuresCount()
    {
        $this->setFailures($this->getFailures() + 1);
    }

    public function decreaseFailuresCount()
    {
        $this->setFailures($this->getFailures() - 1);
    }
}