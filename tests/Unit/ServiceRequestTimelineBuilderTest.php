<?php

namespace Tests\Unit;

use App\Models\ServiceRequest;
use App\Support\ServiceRequestTimelineBuilder;
use Carbon\Carbon;
use Tests\TestCase;

class ServiceRequestTimelineBuilderTest extends TestCase
{
    public function test_pending_request_has_submitted_completed_and_later_steps_pending(): void
    {
        $serviceRequest = new ServiceRequest([
            'status' => 'pending',
            'submitted_at' => Carbon::parse('2026-05-10T09:00:00Z'),
        ]);

        $timeline = (new ServiceRequestTimelineBuilder)->build($serviceRequest);

        $this->assertSame('completed', $timeline[0]['status']);
        $this->assertSame('submitted', $timeline[0]['key']);
        $this->assertNotNull($timeline[0]['occurred_at']);
        $this->assertSame('pending', $timeline[1]['status']);
        $this->assertSame('pending', $timeline[2]['status']);
        $this->assertNull($timeline[2]['occurred_at']);
    }

    public function test_under_review_request_marks_review_step_completed(): void
    {
        $serviceRequest = new ServiceRequest([
            'status' => 'under_review',
            'submitted_at' => Carbon::parse('2026-05-10T09:00:00Z'),
            'reviewed_at' => Carbon::parse('2026-05-11T10:00:00Z'),
        ]);

        $timeline = (new ServiceRequestTimelineBuilder)->build($serviceRequest);

        $this->assertSame('completed', $timeline[1]['status']);
        $this->assertSame('2026-05-11T10:00:00.000000Z', $timeline[1]['occurred_at']);
        $this->assertSame('pending', $timeline[2]['status']);
    }

    public function test_completed_request_marks_all_steps_completed(): void
    {
        $serviceRequest = new ServiceRequest([
            'status' => 'completed',
            'submitted_at' => Carbon::parse('2026-05-10T09:00:00Z'),
            'reviewed_at' => Carbon::parse('2026-05-11T10:00:00Z'),
            'completed_at' => Carbon::parse('2026-05-12T14:00:00Z'),
        ]);

        $timeline = (new ServiceRequestTimelineBuilder)->build($serviceRequest);

        $this->assertSame('completed', $timeline[2]['status']);
        $this->assertSame('2026-05-12T14:00:00.000000Z', $timeline[2]['occurred_at']);
    }
}
