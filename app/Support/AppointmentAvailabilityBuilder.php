<?php

namespace App\Support;

use App\Models\Office;
use App\Models\OfficeTimeSlot;
use App\Models\OfficeTimeSlotBlock;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AppointmentAvailabilityBuilder
{
    public const DEFAULT_START = '09:00';

    public const DEFAULT_END = '15:00';

    public const SLOT_DURATION_MINUTES = 60;

    /**
     * @param  list<string>  $unavailableTimes  Times in H:i format (booked)
     * @return array{
     *     date: string,
     *     slot_duration_minutes: int,
     *     working_hours: array{start: string, end: string},
     *     available_slots: list<string>,
     *     unavailable_slots: list<string>,
     *     source: string
     * }
     */
    public function build(
        string $date,
        ?Office $office,
        array $unavailableTimes,
        ?int $staffId = null,
    ): array {
        [$slotConfigs, $source] = $this->resolveScheduleConfigs($date, $office, $staffId);

        $slotDuration = $slotConfigs[0]['duration'] ?? self::SLOT_DURATION_MINUTES;
        $allSlots = $this->generateSlotsFromConfigs($date, $slotConfigs);

        $workingHours = $this->deriveWorkingHoursFromConfigs($slotConfigs);

        $blockedTimes = $office !== null
            ? $this->resolveBlockedTimes($date, $office, $staffId, $slotDuration)
            : [];

        $unavailableSet = array_flip(array_unique(array_merge(
            $unavailableTimes,
            $blockedTimes
        )));

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
            'slot_duration_minutes' => $slotDuration,
            'working_hours' => $workingHours,
            'available_slots' => $available,
            'unavailable_slots' => $unavailable,
            'source' => $source,
        ];
    }

    /**
     * @param  list<string>  $appointmentStartTimes  Times in H:i format
     * @return list<string>
     */
    public function occupiedSlotsForAppointments(string $date, array $appointmentStartTimes): array
    {
        $occupied = [];

        foreach ($appointmentStartTimes as $startTime) {
            $normalizedStart = Carbon::parse($startTime)->format('H:i');
            $end = Carbon::parse($date.' '.$normalizedStart)
                ->addMinutes(self::SLOT_DURATION_MINUTES)
                ->format('H:i');

            $occupied = array_merge(
                $occupied,
                $this->generateSlots($date, $normalizedStart, $end, self::SLOT_DURATION_MINUTES)
            );
        }

        return array_values(array_unique($occupied));
    }

    /**
     * @param  list<string>  $additionalUnavailable
     */
    public function isSlotAvailable(
        string $date,
        string $time,
        ?Office $office,
        ?int $staffId,
        array $additionalUnavailable = [],
    ): bool {
        $normalizedTime = Carbon::parse($time)->format('H:i');
        $payload = $this->build($date, $office, $additionalUnavailable, $staffId);

        return in_array($normalizedTime, $payload['available_slots'], true);
    }

    /**
     * @return array{start: string, end: string}
     */
    public function resolveWorkingHours(string $date, ?Office $office): array
    {
        [$configs] = $this->resolveScheduleConfigs($date, $office, null);

        return $this->deriveWorkingHoursFromConfigs($configs);
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
     * @return array{0: list<array{start: string, end: string, duration: int}>, 1: string}
     */
    private function resolveScheduleConfigs(string $date, ?Office $office, ?int $staffId): array
    {
        if ($office !== null) {
            $slotConfigs = $this->resolveOfficeTimeSlotConfigs($date, $office, $staffId);

            if ($slotConfigs !== []) {
                return [$slotConfigs, 'time_slots'];
            }

            $officeHours = $this->parseOfficeHoursForDate($date, $office->working_hours ?? []);

            if ($officeHours !== null) {
                return [[
                    [
                        'start' => $officeHours['start'],
                        'end' => $officeHours['end'],
                        'duration' => self::SLOT_DURATION_MINUTES,
                    ],
                ], 'working_hours'];
            }
        }

        return [[
            [
                'start' => self::DEFAULT_START,
                'end' => self::DEFAULT_END,
                'duration' => self::SLOT_DURATION_MINUTES,
            ],
        ], 'default'];
    }

    /**
     * @return list<array{start: string, end: string, duration: int}>
     */
    private function resolveOfficeTimeSlotConfigs(string $date, Office $office, ?int $staffId): array
    {
        if ($office->id === null) {
            return [];
        }

        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $query = OfficeTimeSlot::query()
            ->where('office_id', $office->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true);

        if ($staffId !== null) {
            $query->where(function ($builder) use ($staffId) {
                $builder
                    ->whereNull('staff_id')
                    ->orWhere('staff_id', $staffId);
            });
        } else {
            $query->whereNull('staff_id');
        }

        return $query
            ->orderBy('start_time')
            ->get()
            ->map(fn (OfficeTimeSlot $slot) => [
                'start' => Carbon::parse($slot->start_time)->format('H:i'),
                'end' => Carbon::parse($slot->end_time)->format('H:i'),
                'duration' => self::SLOT_DURATION_MINUTES,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  list<array{start: string, end: string, duration: int}>  $configs
     * @return list<string>
     */
    private function generateSlotsFromConfigs(string $date, array $configs): array
    {
        $slots = [];

        foreach ($configs as $config) {
            $slots = array_merge(
                $slots,
                $this->generateSlots($date, $config['start'], $config['end'], $config['duration'])
            );
        }

        $slots = array_values(array_unique($slots));
        sort($slots);

        return $slots;
    }

    /**
     * @param  list<array{start: string, end: string, duration: int}>  $configs
     * @return array{start: string, end: string}
     */
    private function deriveWorkingHoursFromConfigs(array $configs): array
    {
        if ($configs === []) {
            return [
                'start' => self::DEFAULT_START,
                'end' => self::DEFAULT_END,
            ];
        }

        $starts = array_column($configs, 'start');
        $ends = array_column($configs, 'end');

        return [
            'start' => min($starts),
            'end' => max($ends),
        ];
    }

    /**
     * @return list<string>
     */
    private function resolveBlockedTimes(
        string $date,
        Office $office,
        ?int $staffId,
        int $slotDurationMinutes,
    ): array {
        if ($office->id === null) {
            return [];
        }

        $query = OfficeTimeSlotBlock::query()
            ->where('office_id', $office->id)
            ->whereDate('date', $date);

        if ($staffId !== null) {
            $query->where(function ($builder) use ($staffId) {
                $builder
                    ->whereNull('staff_id')
                    ->orWhere('staff_id', $staffId);
            });
        } else {
            $query->whereNull('staff_id');
        }

        $blocked = [];

        /** @var Collection<int, OfficeTimeSlotBlock> $blocks */
        $blocks = $query->get();

        foreach ($blocks as $block) {
            $blocked = array_merge(
                $blocked,
                $this->generateSlots(
                    $date,
                    Carbon::parse($block->start_time)->format('H:i'),
                    Carbon::parse($block->end_time)->format('H:i'),
                    $slotDurationMinutes
                )
            );
        }

        return array_values(array_unique($blocked));
    }

    /**
     * @return array{start: string, end: string}|null
     */
    private function parseHoursRange(mixed $value): ?array
    {
        if (is_array($value)) {
            $start = $value['start'] ?? ($value[0] ?? null);
            $end = $value['end'] ?? ($value[1] ?? null);

            if (is_string($start) && is_string($end) && $this->isValidTime($start) && $this->isValidTime($end)) {
                return [
                    'start' => $this->normalizeTime($start),
                    'end' => $this->normalizeTime($end),
                ];
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
