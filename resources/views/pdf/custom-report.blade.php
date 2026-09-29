{{-- Custom report PDF. dompdf = CSS 2.1: layout is tables only (no flex/grid/JS).
     Colours are the light-theme tokens (dompdf resolves :root custom properties,
     see CLAUDE.md "Daily Report — PDF"). Charts are drawn as bar tables. --}}
@php
    use App\Services\Reports\ReportFormat as F;

    $report   = $doc->report;
    $kpis     = $doc->kpis();
    $blocks   = $doc->blocks();
    $findings = \App\Services\Reports\ReportDocument::findings($doc->results);
    $tone     = ['good' => 'var(--green)', 'warn' => 'var(--amber)', 'bad' => 'var(--red)', 'neutral' => 'var(--accent)'];
    // Inline background colours must be literal: dompdf resolves var() for text colour but not for backgrounds
    // (same values as the :root tokens below)
    $bg       = ['good' => '#0e9e86', 'warn' => '#d97706', 'bad' => '#e11d48', 'neutral' => '#3b6fd4', 'accent' => '#3b6fd4', 'red' => '#e11d48', 'track' => '#f0f2f8'];
    // Minus sign (U+2212) for negatives; values come from ReportFormat
    $fmt      = fn ($v, $type) => is_numeric($v) && $v < 0 && in_array($type, ['money', 'count', 'percent', 'ratio', 'days'], true)
                    ? '−' . F::value(abs($v), $type) : F::value($v, $type);
    $withUnit = fn ($v, $type) => $fmt($v, $type) . ($type === 'money' && $v !== null ? ' RWF' : '');
    $cmpText  = function (?array $c, string $type) use ($withUnit, $tone): ?array {
        if (! $c || $c['value'] === null) return null;
        $col = $c['good'] === true ? $tone['good'] : ($c['good'] === false ? $tone['bad'] : 'var(--text-dim)');
        $pct = $c['pct'] === null ? '' : ($c['pct'] >= 0 ? '+' : '−') . number_format(abs($c['pct']), 1) . '% · ';
        return [$pct . $c['period'] . ': ' . $withUnit($c['value'], $type), $col];
    };
    $kpiRows = array_chunk($kpis, 3, true);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>{{ $report->name }} — {{ $doc->periodLabel }}</title>
<style>
:root {
    --surface:#ffffff; --surface2:#f0f2f8; --border:#e2e6f3;
    --text:#1a1f36; --text-sub:#4a5372; --text-dim:#7a81a0;
    --accent:#3b6fd4; --green:#0e9e86; --amber:#d97706; --red:#e11d48;
}
@page { margin:14mm 14mm 18mm 14mm; }
body { font-family:'DejaVu Sans', sans-serif; font-size:7.5pt; color:var(--text); background:var(--surface); margin:0; }
table { width:100%; border-collapse:collapse; }
td, th { padding:1.5mm 2.4mm; vertical-align:middle; }
.r { text-align:right; } .nw { white-space:nowrap; } .dim { color:var(--text-dim); } .b { font-weight:bold; }

.head td { padding:0; vertical-align:top; }
.brand { font-size:8pt; font-weight:bold; color:var(--text-dim); text-transform:uppercase; letter-spacing:0.5pt; }
.title { font-size:15pt; font-weight:bold; color:var(--text); padding-top:1mm; }
.desc  { font-size:8pt; color:var(--text-sub); padding-top:1mm; }
.tag   { font-size:7pt; font-weight:bold; color:var(--accent); border:1px solid var(--accent); padding:0.8mm 2.6mm; }
.meta td { padding:1.6mm 3mm 1.6mm 0; font-size:7pt; color:var(--text-sub); }
.meta .l { color:var(--text-dim); text-transform:uppercase; font-size:6pt; font-weight:bold; }
.rule { border-top:2px solid var(--border); margin:3mm 0 0 0; }

.sec { margin-top:5mm; }
.sec-head td { padding:0 0 1.2mm 0; border-bottom:1.5px solid var(--accent); }
.sec-title { font-size:9pt; font-weight:bold; color:var(--text); }
.sec-total { font-size:9pt; font-weight:bold; text-align:right; white-space:nowrap; }
.sec-total span { font-size:6.5pt; font-weight:normal; color:var(--text-dim); }
.note { font-size:6.5pt; color:var(--text-dim); padding:1.2mm 0 0 0; }
.insight td { font-size:7pt; color:var(--text-sub); padding:1.4mm 0 0 2.4mm; }

.find td { padding:1.8mm 2.4mm; border-bottom:1px solid var(--border); font-size:7.2pt; color:var(--text-sub); vertical-align:top; }
.find .t { font-size:6pt; font-weight:bold; text-transform:uppercase; color:var(--text-dim); }

.kf { table-layout:fixed; }
.kf td.card { border:1px solid var(--border); padding:2.6mm 3mm; width:33.33%; vertical-align:top; }
.kf td.gap { border:none; width:0; padding:0; }
.kf-l { font-size:6.3pt; text-transform:uppercase; color:var(--text-dim); font-weight:bold; }
.kf-v { font-size:12.5pt; font-weight:bold; padding-top:1mm; }
.kf-u { font-size:7pt; font-weight:normal; color:var(--text-dim); }
.kf-c { font-size:6.3pt; padding-top:0.8mm; }
.kf-s td { padding:0.7mm 0 0 0; font-size:6.5pt; color:var(--text-sub); border:none; }

.data th { font-size:6.3pt; text-transform:uppercase; color:var(--text-dim); text-align:left; border-bottom:1.5px solid var(--border); padding:1.4mm 2mm; }
.data th.r { text-align:right; }
.data td { border-bottom:1px solid var(--border); padding:1.3mm 2mm; font-size:7pt; }
.data tr.tot td { font-weight:bold; border-top:1.5px solid var(--border); border-bottom:none; }
.data td.first { font-weight:bold; color:var(--text); }
thead { display:table-header-group; }
tr { page-break-inside:avoid; }

.bars td { padding:0.9mm 2mm; font-size:6.8pt; border-bottom:1px solid var(--border); }
.bars { table-layout:fixed; }
.bars.dense td { padding:0.45mm 2mm; font-size:6.3pt; }
.bar-track { width:100%; border-collapse:collapse; table-layout:fixed; }
.bar-track td { padding:0; height:2.6mm; border:none; }
.text-block { font-size:8pt; line-height:1.55; color:var(--text-sub); padding-top:1.5mm; }
.err { font-size:7pt; color:var(--red); padding-top:1.5mm; }
</style>
</head>
<body>

{{-- Footer on every page; "Page X of Y" is drawn by the canvas, bottom-right --}}
<div style="position:fixed; left:0; right:0; bottom:-11mm; height:6mm;">
    <table style="border-top:1px solid var(--border);"><tr>
        <td style="padding:1.5mm 0 0 0; font-size:6.5pt; color:var(--text-dim);">{{ $tenant }} · {{ $report->name }} · {{ $doc->periodLabel }}</td>
        <td style="width:28%;"></td>
    </tr></table>
</div>

{{-- Header --}}
<table class="head">
    <tr>
        <td>
            <div class="brand">{{ $tenant }}</div>
            <div class="title">{{ $report->name }}</div>
            @if ($report->description) <div class="desc">{{ $report->description }}</div> @endif
        </td>
        <td style="width:30mm; text-align:right;"><span class="tag">Custom report</span></td>
    </tr>
</table>
<table class="meta" style="margin-top:3mm; width:auto;">
    <tr>
        <td><div class="l">Period</div>{{ $doc->periodLabel }}</td>
        <td><div class="l">Location</div>{{ $doc->locationLabel }}</td>
        @if ($doc->comparisonLabel) <td><div class="l">Compared with</div>{{ $doc->comparisonLabel }}</td> @endif
        <td><div class="l">Generated</div>{{ $doc->generatedAt }}@if ($doc->generatedBy) by {{ $doc->generatedBy->name }}@endif</td>
    </tr>
</table>
<div class="rule"></div>

{{-- Key findings --}}
@if ($findings)
    <div class="sec">
        <table class="sec-head"><tr><td class="sec-title">Key findings</td></tr></table>
        <table class="find">
            @foreach ($findings as $f)
                <tr>
                    <td style="width:1mm; padding:0; background-color:{{ $bg[$f['tone']] }};"></td>
                    <td><div class="t">{{ $f['title'] }}</div>{{ $f['text'] }}</td>
                </tr>
            @endforeach
        </table>
    </div>
@endif

{{-- Summary cards, 3 per row --}}
@if ($kpis)
    <div class="sec">
        <table class="sec-head"><tr><td class="sec-title">Summary</td></tr></table>
        <table class="kf" style="margin-top:2mm;">
            @foreach ($kpiRows as $row)
                <tr>
                    @foreach ($row as $entry)
                        @php $r = $entry['result']; $h = $r['headline']; $c = $h ? $cmpText($r['comparison'], $h['type']) : null; @endphp
                        <td class="card">
                            <div class="kf-l">{{ $entry['block']['title'] ?? $entry['meta']['label'] }}</div>
                            @if ($r['error'])
                                <div class="err">{{ $r['error'] }}</div>
                            @elseif ($h)
                                @php
                                    $status = ['warn' => 'var(--amber)', 'crit' => 'var(--red)'][$r['status'] ?? ''] ?? null;
                                    $color  = $status ?? (is_numeric($h['value']) && $h['value'] < 0 ? 'var(--red)' : 'var(--text)');
                                @endphp
                                <div class="kf-v" style="color:{{ $color }};">{{ $fmt($h['value'], $h['type']) }}@if ($h['type'] === 'money') <span class="kf-u">RWF</span>@endif</div>
                                @if ($c) <div class="kf-c" style="color:{{ $c[1] }};">{{ $c[0] }}</div>
                                @else <div class="kf-c dim">{{ $h['label'] }}</div> @endif
                                @if ($r['stats'])
                                    <table class="kf-s" style="margin-top:1.4mm;">
                                        @foreach (array_slice($r['stats'], 0, 3) as $s)
                                            <tr><td class="dim">{{ $s['label'] }}</td><td class="r nw">{{ $withUnit($s['value'], $s['type']) }}</td></tr>
                                        @endforeach
                                    </table>
                                @endif
                            @endif
                        </td>
                    @endforeach
                    @for ($i = count($row); $i < 3; $i++) <td class="card" style="border:none;"></td> @endfor
                </tr>
            @endforeach
        </table>
    </div>
@endif

{{-- Tables, charts, text --}}
@foreach ($blocks as $entry)
    @php
        $block = $entry['block'];
        $r     = $entry['result'];
        $isText = ($block['metric_id'] ?? '') === 'text_block';
        $title = $block['title'] ?? ($entry['meta']['label'] ?? '');
    @endphp

    @if ($isText)
        @continue (trim($block['content'] ?? '') === '')
        <div class="sec" style="page-break-inside:avoid;">
            @if ($title !== '' && $title !== 'Text / Narrative')
                <table class="sec-head"><tr><td class="sec-title">{{ $title }}</td></tr></table>
            @endif
            <div class="text-block">{!! nl2br(e($block['content'])) !!}</div>
        </div>
        @continue
    @endif

    <div class="sec">
        <table class="sec-head"><tr>
            <td class="sec-title">{{ $title }}</td>
            @if ($r && ! $r['error'] && $r['headline'])
                @php $c = $cmpText($r['comparison'], $r['headline']['type']); @endphp
                <td class="sec-total">
                    {{ $withUnit($r['headline']['value'], $r['headline']['type']) }}
                    <span>{{ $r['headline']['label'] }}@if ($c) · <span style="color:{{ $c[1] }};">{{ $c[0] }}</span>@endif</span>
                </td>
            @endif
        </tr></table>

        @if (! $r || $r['error'])
            <div class="err">{{ $r['error'] ?? "Couldn't load this block." }}</div>
        @elseif (in_array($block['viz'] ?? '', ['bar_chart', 'line_chart'], true) && ! empty($r['series']['labels']))
            @php
                $ds     = $r['series']['datasets'][0];
                $isDate = ($r['columns'][0]['type'] ?? '') === 'date';
                $max    = max(array_map('abs', $ds['data']) ?: [0]) ?: 1;
            @endphp
            <table class="bars {{ count($r['series']['labels']) > 15 ? 'dense' : '' }}" style="margin-top:1mm;">
                @foreach ($r['series']['labels'] as $i => $label)
                    @php $v = $ds['data'][$i] ?? 0; $w = $v == 0 ? 0 : max(0.5, round(abs($v) / $max * 100, 1)); @endphp
                    <tr>
                        <td style="width:26%;" class="nw">{{ $isDate ? F::value($label, 'date') : \Illuminate\Support\Str::limit($label, 38) }}</td>
                        <td style="width:52%;">
                            <table class="bar-track"><tr>
                                @if ($w > 0)<td style="width:{{ $w }}%; background-color:{{ $v < 0 ? $bg['red'] : $bg['accent'] }};"></td>@endif
                                <td style="width:{{ 100 - $w }}%; background-color:{{ $bg['track'] }};"></td>
                            </tr></table>
                        </td>
                        <td class="r nw" style="width:22%;">{{ $withUnit($v, $ds['type']) }}</td>
                    </tr>
                @endforeach
            </table>
        @elseif (empty($r['columns']))
            <table class="data" style="margin-top:1mm;">
                @foreach ($r['stats'] as $s)
                    <tr><td>{{ $s['label'] }}</td><td class="r nw">{{ $withUnit($s['value'], $s['type']) }}</td></tr>
                @endforeach
            </table>
        @elseif (empty($r['rows']))
            <div class="note">Nothing to show for this period.</div>
        @else
            <table class="data" style="margin-top:1mm;">
                <thead><tr>
                    @foreach ($r['columns'] as $col)
                        <th class="{{ F::align($col['type']) === 'right' ? 'r' : '' }}">{{ F::header($col['label'], $col['type']) }}</th>
                    @endforeach
                </tr></thead>
                <tbody>
                    @foreach ($r['rows'] as $row)
                        <tr>
                            @foreach ($r['columns'] as $col)
                                @php $v = $row[$col['key']] ?? null; $right = F::align($col['type']) === 'right'; @endphp
                                <td class="{{ $right ? 'r nw' : ($loop->first ? 'first' : '') }}" @if ($right && is_numeric($v) && $v < 0) style="color:var(--red);" @endif>
                                    {{ $right ? $fmt($v, $col['type']) : \Illuminate\Support\Str::limit(F::value($v, $col['type']), 60) }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                    @if ($r['totals'] && count($r['rows']) > 1)
                        <tr class="tot">
                            @foreach ($r['columns'] as $col)
                                @php $t = $r['totals'][$col['key']] ?? null; @endphp
                                <td class="{{ $loop->first ? '' : 'r nw' }}">{{ $loop->first ? 'Total' : ($t === null ? '' : $fmt($t, $col['type'])) }}</td>
                            @endforeach
                        </tr>
                    @endif
                </tbody>
            </table>
        @endif

        @if ($r && ! $r['error'])
            @if ($r['insight'])
                <table class="insight"><tr>
                    <td style="width:1mm; padding:0; background-color:{{ $bg[$r['insight']['tone']] ?? $bg['neutral'] }};"></td>
                    <td>{{ $r['insight']['text'] }}</td>
                </tr></table>
            @endif
            @foreach ($r['notes'] as $note) <div class="note">{{ $note }}</div> @endforeach
        @endif
    </div>
@endforeach

</body>
</html>
