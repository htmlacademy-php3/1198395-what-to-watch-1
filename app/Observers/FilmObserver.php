<?php

namespace App\Observers;

use App\Models\Film;
use Illuminate\Support\Facades\Cache;

/**
 * @psalm-api
 */
class FilmObserver
{
    private function invalidateFilmsCache(): void
    {
        Cache::flush();
    }

    public function created(Film $_film): void
    {
        $this->invalidateFilmsCache();
    }

    public function updated(Film $_film): void
    {
        $this->invalidateFilmsCache();
    }

    public function deleted(Film $_film): void
    {
        $this->invalidateFilmsCache();
    }
}
