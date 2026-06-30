<?php

namespace App\Observers;

use App\Models\Promo;
use Illuminate\Support\Facades\Cache;

/**
 * @psalm-api
 */
class PromoObserver
{
    private function invalidatePromoCache(): void
    {
        Cache::flush();
    }

    public function created(Promo $_promo): void
    {
        $this->invalidatePromoCache();
    }

    public function updated(Promo $_promo): void
    {
        $this->invalidatePromoCache();
    }

    public function deleted(Promo $_promo): void
    {
        $this->invalidatePromoCache();
    }
}
