<div style="font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; color: #111827;">
    <h2 style="color:#0f172a;">Your report is ready: {{ $report->title }}</h2>
    <p style="color:#374151;">The report you requested has been generated and is ready for download.</p>

    @if($downloadUrl && $downloadUrl !== '#')
        <p><a href="{{ $downloadUrl }}" style="background:#059669;color:white;padding:8px 12px;border-radius:6px;text-decoration:none;">Download Report</a></p>
    @else
        <p style="color:#374151;">The report file will be available in your reports list shortly.</p>
    @endif

    <p style="color:#374151;">— The {{ config('app.name') }} team</p>
</div>
