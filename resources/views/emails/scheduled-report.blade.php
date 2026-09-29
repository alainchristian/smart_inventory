{{-- Scheduled custom report email. Email clients ignore CSS variables and
     <style> blocks, so styles are inline with the light-theme token values. --}}
@php
    use App\Services\Reports\ReportFormat as F;
    $tone = ['good' => '#0e9e86', 'warn' => '#d97706', 'bad' => '#e11d48'];
    $kpis = array_slice($doc->kpis(), 0, 8, true);
@endphp
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $doc->report->name }}</title></head>
<body style="margin:0;padding:0;background:#f4f6fb;font-family:Arial,Helvetica,sans-serif;color:#1a1f36;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6fb;padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:12px;border:1px solid #e2e6f3;">
    <tr><td style="padding:24px 28px 8px;">
        <div style="font-size:11px;font-weight:bold;letter-spacing:.5px;text-transform:uppercase;color:#7a81a0;">{{ $tenant }}</div>
        <div style="font-size:20px;font-weight:bold;margin-top:6px;">{{ $doc->report->name }}</div>
        <div style="font-size:13px;color:#4a5372;margin-top:6px;">
            {{ $doc->periodLabel }} · {{ $doc->locationLabel }}@if ($doc->comparisonLabel) · compared with {{ $doc->comparisonLabel }}@endif
        </div>
    </td></tr>

    @if ($kpis)
        <tr><td style="padding:12px 28px 4px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
                @foreach ($kpis as $entry)
                    @php $r = $entry['result']; $h = $r['headline']; $c = $r['comparison']; @endphp
                    @continue (! $h || $r['error'])
                    <tr>
                        <td style="padding:9px 0;border-bottom:1px solid #e2e6f3;font-size:13px;color:#4a5372;">{{ $entry['block']['title'] ?? $entry['meta']['label'] }}</td>
                        <td style="padding:9px 0;border-bottom:1px solid #e2e6f3;font-size:14px;font-weight:bold;text-align:right;white-space:nowrap;">
                            {{ F::withUnit($h['value'], $h['type']) }}
                            @if ($c && $c['pct'] !== null)
                                <span style="font-size:11px;font-weight:bold;color:{{ $c['good'] === true ? '#0e9e86' : ($c['good'] === false ? '#e11d48' : '#7a81a0') }};">
                                    {{ $c['pct'] >= 0 ? '▲' : '▼' }} {{ number_format(abs($c['pct']), 1) }}%
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </td></tr>
    @endif

    @if ($findings)
        <tr><td style="padding:16px 28px 4px;">
            <div style="font-size:13px;font-weight:bold;margin-bottom:8px;">Key findings</div>
            @foreach ($findings as $f)
                <div style="border-left:3px solid {{ $tone[$f['tone']] ?? '#3b6fd4' }};padding:4px 0 4px 10px;margin-bottom:8px;font-size:13px;color:#4a5372;line-height:1.45;">{{ $f['text'] }}</div>
            @endforeach
        </td></tr>
    @endif

    <tr><td style="padding:16px 28px 26px;">
        <div style="font-size:13px;color:#4a5372;margin-bottom:16px;">The full report is attached as {{ $attachedAs }}.</div>
        <a href="{{ $url }}" style="display:inline-block;background:#3b6fd4;color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;padding:11px 20px;border-radius:8px;">Open the report</a>
    </td></tr>
</table>
<div style="max-width:600px;font-size:11px;color:#7a81a0;padding:14px 8px;line-height:1.5;">
    You get this because you're on the email list of "{{ $doc->report->name }}" ({{ $doc->report->emailSchedule()?->label() }}).
    The report's owner can change or stop the schedule in the report builder.
</div>
</td></tr>
</table>
</body>
</html>
