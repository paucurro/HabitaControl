<?php

namespace App\Actions\Diario;

use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use stdClass;

class CalcularFechasAproximadas
{
    public function handle(string $legacySource): int
    {
        if (preg_match('/\A[a-zA-Z0-9_]+\z/', $legacySource) !== 1) {
            throw new InvalidArgumentException('La base de datos de origen no es válida.');
        }

        $calculated = 0;
        $today = CarbonImmutable::today();

        DB::table('diario_apuntes')
            ->where('legacy_source', $legacySource)
            ->whereNotNull('legacy_id')
            ->where(fn (Builder $query) => $this->invalidDate($query, $today))
            ->orderBy('id')
            ->chunkById(200, function ($entries) use ($legacySource, $today, &$calculated): void {
                foreach ($entries as $entry) {
                    $previous = $this->neighbor($entry, $legacySource, $today, previous: true);
                    $next = $this->neighbor($entry, $legacySource, $today, previous: false);

                    if ($previous === null || $next === null) {
                        continue;
                    }

                    $date = $this->interpolate($entry, $previous, $next);

                    DB::table('diario_apuntes')->where('id', $entry->id)->update([
                        'fecha' => $date->toDateString(),
                        'fecha_calculada' => true,
                        'updated_at' => now(),
                    ]);
                    $calculated++;
                }
            });

        return $calculated;
    }

    private function invalidDate(Builder $query, CarbonImmutable $today): void
    {
        $query->whereDate('fecha', '<=', '1970-01-01')
            ->orWhereDate('fecha', '>', $today->toDateString());
    }

    private function neighbor(
        stdClass $entry,
        string $legacySource,
        CarbonImmutable $today,
        bool $previous,
    ): ?stdClass {
        return DB::table('diario_apuntes')
            ->select(['legacy_id', 'fecha'])
            ->where('legacy_source', $legacySource)
            ->where('comunidad_id', $entry->comunidad_id)
            ->where('fecha_calculada', false)
            ->whereDate('fecha', '>', '1970-01-01')
            ->whereDate('fecha', '<=', $today->toDateString())
            ->where('legacy_id', $previous ? '<' : '>', $entry->legacy_id)
            ->orderBy('legacy_id', $previous ? 'desc' : 'asc')
            ->first();
    }

    private function interpolate(stdClass $entry, stdClass $previous, stdClass $next): CarbonImmutable
    {
        $previousDate = CarbonImmutable::parse($previous->fecha)->startOfDay();
        $nextDate = CarbonImmutable::parse($next->fecha)->startOfDay();
        $idDistance = (int) $next->legacy_id - (int) $previous->legacy_id;

        if ($idDistance <= 0 || $nextDate->lessThan($previousDate)) {
            return ((int) $entry->legacy_id - (int) $previous->legacy_id)
                <= ((int) $next->legacy_id - (int) $entry->legacy_id)
                    ? $previousDate
                    : $nextDate;
        }

        $ratio = ((int) $entry->legacy_id - (int) $previous->legacy_id) / $idDistance;
        $seconds = $nextDate->getTimestamp() - $previousDate->getTimestamp();

        return $previousDate->addSeconds((int) round($seconds * $ratio))->startOfDay();
    }
}
