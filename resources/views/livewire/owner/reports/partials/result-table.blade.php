{{--
    A MetricResult (as array) rendered as a table. Styles live in
    report-viewer.blade.php (rv- prefix).
    $r — MetricResult::toArray()
--}}
@php use App\Services\Reports\ReportFormat as F; @endphp
@if (empty($r['columns']))
    {{-- KPI-style result shown as a table: headline + stats --}}
    <div class="rv-scroll m-scroll">
        <table class="rv-table">
            <tbody>
                @if ($r['headline'])
                    <tr>
                        <td>{{ $r['headline']['label'] }}</td>
                        <td class="rv-num"><strong>{{ F::withUnit($r['headline']['value'], $r['headline']['type']) }}</strong></td>
                    </tr>
                @endif
                @foreach ($r['stats'] as $s)
                    <tr>
                        <td>{{ $s['label'] }}</td>
                        <td class="rv-num">{{ F::withUnit($s['value'], $s['type']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@elseif (empty($r['rows']))
    <div class="rv-empty">Nothing to show for this period.</div>
@else
    <div class="rv-scroll m-scroll">
        <table class="rv-table m-sticky-first">
            <thead>
                <tr>
                    @foreach ($r['columns'] as $c)
                        <th class="{{ F::align($c['type']) === 'right' ? 'rv-num' : '' }}">{{ F::header($c['label'], $c['type']) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($r['rows'] as $row)
                    <tr>
                        @foreach ($r['columns'] as $c)
                            @php $v = $row[$c['key']] ?? null; @endphp
                            @if (F::align($c['type']) === 'right')
                                <td class="rv-num {{ is_numeric($v) && $v < 0 ? 'rv-neg' : '' }}">{{ F::value($v, $c['type']) }}</td>
                            @else
                                <td @if(is_string($v) && mb_strlen($v) > 40) title="{{ $v }}" @endif class="{{ $loop->first ? 'rv-first' : '' }}">{{ F::value($v, $c['type']) }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            @if ($r['totals'] && count($r['rows']) > 1)
                <tfoot>
                    <tr>
                        @foreach ($r['columns'] as $c)
                            @php $t = $r['totals'][$c['key']] ?? null; @endphp
                            @if ($loop->first)
                                <td>Total</td>
                            @elseif ($t !== null)
                                <td class="rv-num">{{ F::value($t, $c['type']) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
@endif
