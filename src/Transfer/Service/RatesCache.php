<?php
declare(strict_types=1);

namespace App\Transfer\Service;

use App\Transfer\Entity\Enum\CurrencyEnum;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class RatesCache
{
    public const CACHE_KEY_PREFIX = 'exchange_rates_from_';
    public const CACHE_TTL = 3600; // 1 hour

    public function __construct(
        private CacheItemPoolInterface $cache,
    ) {
    }

    /**
     * @param CurrencyEnum $from
     * @return array<non-empty-string, float>|null
     * @throws InvalidArgumentException
     */
    public function getRates(CurrencyEnum $from = CurrencyEnum::USD): ?array
    {
        $cacheItem = $this->cache->getItem(self::CACHE_KEY_PREFIX . $from->name);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        return null;
    }

    /**
     * @param CurrencyEnum $currency
     * @param array<non-empty-string, float> $rates
     * @return void
     * @throws InvalidArgumentException
     */
    public function saveRatesForCurrency(CurrencyEnum $currency, array $rates): void
    {
        $cacheItem = $this->cache->getItem(self::CACHE_KEY_PREFIX . $currency->name);
        $cacheItem->set($rates);
        $cacheItem->expiresAfter(self::CACHE_TTL);
        $this->cache->save($cacheItem);
    }
}
