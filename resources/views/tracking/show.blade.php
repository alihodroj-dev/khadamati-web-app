<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Track Request — {{ config('app.name') }}</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f3f4f6; color: #111827; margin: 0; padding: 2rem 1rem; }
        .card { max-width: 32rem; margin: 0 auto; background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 1.5rem; }
        h1 { font-size: 1.25rem; margin: 0 0 0.25rem; }
        .muted { color: #6b7280; font-size: 0.875rem; margin-bottom: 1.25rem; }
        dl { margin: 0; display: grid; gap: 0.75rem; }
        dt { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; }
        dd { margin: 0.15rem 0 0; font-size: 0.95rem; }
        .status { display: inline-block; padding: 0.2rem 0.55rem; border-radius: 9999px; background: #eff6ff; color: #1d4ed8; font-size: 0.8rem; font-weight: 600; text-transform: capitalize; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Service request status</h1>
        <p class="muted">{{ config('app.name') }}</p>

        <dl>
            <dt>Reference</dt>
            <dd>{{ $serviceRequest->reference_number }}</dd>

            <dt>Service</dt>
            <dd>{{ $serviceRequest->service?->name ?? '—' }}</dd>

            <dt>Status</dt>
            <dd><span class="status">{{ str_replace('_', ' ', $serviceRequest->status) }}</span></dd>

            <dt>Submitted</dt>
            <dd>{{ $serviceRequest->submitted_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') ?? '—' }}</dd>

            @if ($serviceRequest->reviewed_at)
                <dt>Reviewed</dt>
                <dd>{{ $serviceRequest->reviewed_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</dd>
            @endif

            @if ($serviceRequest->completed_at)
                <dt>Completed</dt>
                <dd>{{ $serviceRequest->completed_at->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</dd>
            @endif
        </dl>
    </div>
</body>
</html>
