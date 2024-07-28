<?php

namespace src\Decorator;

use DateTime;
use Exception;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use src\Integration\DataProviderInterface;

class CachedDataProvider implements DataProviderInterface
{
    private DataProviderInterface $dataProvider;
    private CacheItemPoolInterface $cache;
    private LoggerInterface $logger;
    private int $cacheDuration; // Время кеширования в секундах

    public function __construct(
        DataProviderInterface $dataProvider,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        int $cacheDuration = 86400 // По умолчанию 1 день (86400 секунд)
    ) {
        $this->dataProvider = $dataProvider;
        $this->cache = $cache;
        $this->logger = $logger;
        $this->cacheDuration = $cacheDuration;
    }

    /**
     * @throws Exception
     */
    public function get(array $request): array
    {
        try {
            $cacheKey = $this->getCacheKey($request);
            $cacheItem = $this->cache->getItem($cacheKey);
            if ($cacheItem->isHit()) {
                return $cacheItem->get();
            }

            $result = $this->dataProvider->get($request);

            if (!empty($result)) {
                $cacheItem->set($result)->expiresAfter($this->cacheDuration);
                $this->cache->save($cacheItem);
            }

            return $result;
        } catch (Exception $e) {
            $this->logger->critical('Error: ' . $e->getMessage(), ['exception' => $e]);
            throw $e; // Пробрасываем исключение выше
        }
    }

    private function getCacheKey(array $request): string
    {
        return hash('sha256', serialize($request));
    }
}
