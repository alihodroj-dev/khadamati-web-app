<?php

namespace App\Support;

use App\Models\Office;
use Carbon\Carbon;

class AppointmentAvailabilityBuilder
{
    public const DEFAULT_START = '09:00';

    public const DEFAULT_END = '15:00';

    public const SLOT_DURATION_MINUTES = 30;

    /**
     * @param  list<string>  $unavailableTimes  Times in H:i format
     * @return array{
     *     date: string,
     *     slot_duration_minutes: int,
     *     working_hours: array{start: string, end: string},
     *     available_times: list<string>,
     *     unavailable_times: list<string>
     * }
     */
    public function build(string $date, ?Office $office, array $unavailableTimes): array
    {
        $workingHours = $this->resolveWorkingHours($date, $office);
        $allSlots = $this->generateSlots(
            $date,
            $workingHours['start'],
            $workingHours['end'],
            self::SLOT_DURATION_MINUTES
        );

        $unavailableSet = array_flip($unavailableTimes);
        $unavailable = [];
        $available = [];

        foreach ($allSlots as $slot) {
            if (isset($unavailableSet[$slot])) {
                $unavailable[] = $slot;
            } else {
                $available[] = $slot;
            }
        }

        return [
            'date' => $date,
            'slot_duration_minutes' => self::SLOT_DURATION_MINUTES,
            'working_hours' => $workingHours,
            'available_times' => $available,
            'unavailable_times' => $unavailable,
        ];
    }

    /**
     * @return array{start: string, end: string}
     */
    public function resolveWorkingHours(string $date, ?Office $office): array
    {
        if ($office !== null) {
            $officeHours = $this->parseOfficeHoursForDate($date, $office->working_hours ?? []);

            if ($officeHours !== null) {
                return $officeHours;
            }
        }

        return [
            'start' => self::DEFAULT_START,
            'end' => self::DEFAULT_END,
        ];
    }

    /**
     * @param  array<string, mixed>  $workingHours
     * @return array{start: string, end: string}|null
     */
    public function parseOfficeHoursForDate(string $date, array $workingHours): ?array
    {
        if ($workingHours === []) {
            return null;
        }

        $dayKey = $this->dayKeyForDate($date);
        $value = $workingHours[$dayKey] ?? null;

        if ($value === null || $value === '' || $value === 'closed') {
            return null;
        }

        return $this->parseHoursRange($value);
    }

    /**
     * @return list<string>
     */
    public function generateSlots(string $date, string $start, string $end, int $durationMinutes): array
    {
        $slots = [];
        $cursor = Carbon::parse($date.' '.$start);
        $dayEnd = Carbon::parse($date.' '.$end);

        while ($cursor->copy()->addMinutes($durationMinutes)->lte($dayEnd)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes($durationMinutes);
        }

        return $slots;
    }

    /**
     * @return array{start: string, end: string}|null
     */
    private function parseHoursRange(mixed $value): ?array
    {
        if (is_array($value)) {
            $start = $value['start'] ?? null;
            $end = $value['end'] ?? null;

            if (is_string($start) && is_string($end) && $this->isValidTime($start) && $this->isValidTime($end)) {
                return ['start' => $start, 'end' => $end];
            }

            return null;
        }

        if (! is_string($value)) {
            return null;
        }

        if (preg_match('/^(\d{1,2}:\d{2})\s*-\s*(\d{1,2}:\d{2})$/', trim($value), $matches) !== 1) {
            return null;
        }

        $start = $this->normalizeTime($matches[1]);
        $end = $this->normalizeTime($matches[2]);

        if ($start === null || $end === null) {
            return null;
        }

        return ['start' => $start, 'end' => $end];
    }

    private function dayKeyForDate(string $date): string
    {
        return match (Carbon::parse($date)->dayOfWeek) {
            0 => 'sun',
            1 => 'mon',
            2 => 'tue',
            3 => 'wed',
            4 => 'thu',
            5 => 'fri',
            6 => 'sat',
        };
    }

    private function normalizeTime(string $time): ?string
    {
        if (! $this->isValidTime($time)) {
            return null;
        }

        return Carbon::createFromFormat('H:i', strlen($time) === 5 ? $time : '0'.$time)->format('H:i');
    }

    private function isValidTime(string $time): bool
    {
        return preg_match('/^\d{1,2}:\d{2}$/', $time) === 1;
    }
}
