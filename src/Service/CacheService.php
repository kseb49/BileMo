<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class CacheService
{
    public function __construct(
       
        public CacheInterface $cache,
        public EntityManagerInterface $entity,
    )
    {
    }


    public function cache(int $offset, string $class, string $name)
    {
        $this->cache->get($name, function(ItemInterface $item) use($offset, $class)
        {
            echo ('mise en cache');
            $item->expiresAfter(20);
            $products = $this->entity->getRepository($class)->findWithPagination($offset);
            return $products;

        });
    }
}
