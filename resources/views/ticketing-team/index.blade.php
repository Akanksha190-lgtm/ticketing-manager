@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
@endpush
@section('content')
<div class="app-shell">

    <!-- ===================== TICKETING TEAM VIEW ===================== -->
    <div class="main-col">
        <div class="topbar">
            <div>
                <h1>Ticketing Team</h1>
                <p>
                    Everything needed to issue a ticket correctly.
                </p>
            </div>
            <div class="topbar-right">
                <span class="pill"><span class="live-dot"></span>Live</span>
                <span class="pill" id="topbar-date">—</span>
            </div>
        </div>
        
        <main>
            <section class="view active" id="view-ticketing">
                <div class="stat-row">
                    <div class="stat-tile good"><div class="label">Active Fares</div><div class="value" id="tt-stat-active">{{ $activeFares }}</div></div>
                    <div class="stat-tile warn"><div class="label">Expiring ≤ 7 Days</div><div class="value" id="tt-stat-expiring">{{ $expiringFares }}</div></div>
                    <div class="stat-tile"><div class="label">Avg. Commission/Discount</div><div class="value" id="tt-stat-avgcomm">{{ number_format($avgCommissionDiscount ?? 0, 2) }}%</div></div>
                    <div class="stat-tile gold"><div class="label">Total Margin (Active)</div><div class="value" id="tt-stat-margin">{{ number_format($totalMargin ?? 0, 2) }}</div></div>
                </div>
                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Fares &amp; commission detail</h2>
                            <p class="hint">Everything needed to issue a ticket correctly — net fare, applicable IATA/BSP commission or private discount, and sell price.</p>
                        </div>
                    </div>
                    
                    <form id="fare-search-form" method="GET" action="{{ route('ticketing.team') }}" class="filter-bar">
                        {{-- Search --}}
                        <input type="text" id="fare-search" name="search" value="{{ request('search') }}" placeholder="Search route or airline…">
                        {{-- Fare Source --}}
                        <select name="source" id="fare-source">
                            <option value="">All fare sources</option>
                            @foreach($fareSources as $fareSource)
                                <option value="{{ $fareSource->name }}"
                                    {{ request('source') == $fareSource->name ? 'selected' : '' }}>
                                    {{ $fareSource->name }}
                                </option>
                            @endforeach
                        </select>

                        {{-- Status --}}
                        <select name="status" id="fare-status">
                                <option value="">All statuses</option>
                                @foreach($statuses as $status)
                                <option value="{{ $status }}"{{ request('status') == $status ? 'selected' : '' }}>{{ ucfirst($status) }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Sort --}}
                        <select id="fare-sort" name="sort">
                            <option value="valid_until_asc"
                                {{ request('sort', 'valid_until_asc') == 'valid_until_asc' ? 'selected' : '' }}>
                                Sort: Expiring soonest
                            </option>

                            <option value="commission_desc"
                                {{ request('sort') == 'commission_desc' ? 'selected' : '' }}>
                                Sort: Commission % high → low
                            </option>

                            <option value="margin_desc"
                                {{ request('sort') == 'margin_desc' ? 'selected' : '' }}>
                                Sort: Margin high → low
                            </option>
                        </select>
                        <button type="submit" class="btn primary">Search</button>
                    </form>

                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th>Airline</th><th>Route</th><th>Cabin</th><th>Source</th><th>Tour Code / PCC</th><th>Published</th><th>Disc/Comm %</th><th>Net Fare</th><th>Gross (Sell)</th><th>Margin</th><th>Valid Until</th><th>Status</th>
                                    <!-- <th>Actions</th> -->
                                </tr>
                            </thead>
                            <tbody id="ticketing-table-body">
                                @foreach($farecomissentry as $fare_entry)
                                    <tr id="fare-entry-row-{{ $fare_entry->id }}">
                                        {{-- Airline --}}
                                        <td>
                                            <span class="airline-text">
                                                {{ $fare_entry->airline->airline ?? '-' }}
                                            </span>
                                        </td>
                                        {{-- Route --}}
                                        <td class="text-nowrap">
                                            @if($fare_entry->route)
                                                <strong>
                                                    {{ $fare_entry->route->origin }}
                                                    ({{ $fare_entry->route->origin_code }})
                                                    →
                                                    {{ $fare_entry->route->destination }}
                                                    ({{ $fare_entry->route->destination_code }})
                                                </strong>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        {{-- Cabin --}}
                                        <td>
                                            <span class="cabin-text">
                                                {{ $fare_entry->cabin->name ?? '-' }}
                                            </span>
                                        </td>
                                        {{-- Source --}}
                                        <td class="text-nowrap">
                                            @php
                                                $sourceName = $fare_entry->fareSource->name ?? '-';
                                                $sourceClass = str_contains(strtolower($sourceName), 'private') ? 'private' : 'bsp';
                                            @endphp
                                            <span class="source-text badge-source {{ $sourceClass }}">
                                                {{ $sourceName }}
                                            </span>
                                        </td>
                                        {{-- Tour Code --}}
                                        <td>
                                            <span class="tour-code-text">
                                                {{$fare_entry->tour_code ?: '-'}}
                                            </span>
                                        </td>
                                        {{-- Published --}}
                                        <td class="text-nowrap">
                                            <span class="published-text"><strong>
                                            {{ $fare_entry->currency->code ?? '-' }} {{ number_format($fare_entry->published, 2) }}
                                            </strong></span>
                                        </td>
                                        {{-- Discount / Commission --}}
                                        <td>
                                            <span class="disc-comm-text"><strong>
                                                {{ number_format($fare_entry->airline->au_commission ?? 0, 1) }}%
                                            </strong></span>
                                        </td>

                                        {{-- Net --}}
                                        <td class="text-nowrap">
                                            <span class="net-text"><strong>
                                                {{ $fare_entry->currency->code ?? '-' }} {{ number_format($fare_entry->net, 2) }}
                                            </strong></span>
                                        </td>
                                        </td>
                        
                                        {{-- Gross --}}
                                        <td class="text-nowrap">
                                            <span class="gross-text"><strong>
                                                {{ $fare_entry->currency->code ?? '-' }} {{ number_format($fare_entry->gross, 2) }}
                                            </strong></span>
                                        </td>
                                        {{-- Margin --}}
                                        <td class="text-nowrap">
                                            <span class="markup-text"><strong>
                                                {{ $fare_entry->currency->code ?? '-' }} {{ number_format($fare_entry->markup, 2) }}
                                            </strong></span>
                                        </td>

                                        {{-- Valid Until --}}
                                        <td class="text-nowrap">
                                            <span class="valid-until-text">
                                            {{ $fare_entry->valid_until->format('d M Y') }}
                                            </span>

                                            <span class="valid-until-value" style="display:none;">
                                                {{ $fare_entry->valid_until->format('Y-m-d') }}
                                            </span>
                                        </td>

                                        {{-- Status --}}
                                        <td>
                                            <span class="status-text status-badge {{ strtolower(str_replace(' ', '-', $fare_entry->status)) }}">
                                                <span class="dot"></span>{{ $fare_entry->status }}
                                            </span>
                                        </td>

                                        {{-- Actions --}}
                                        <!-- <td>
                                            <button type="button" class="btn edit-fare-entry" data-id="{{ $fare_entry->id }}">Edit</button>

                                            <button type="button" class="btn delete-fare-entry" data-id="{{ $fare_entry->id }}">Delete</button>
                                        </td> -->
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>IATA / BSP commission master list <span class="tag">Reference</span></h2>
                            <p class="hint">The commission level a ticket can be issued at for any carrier, independent of a specific fare deal above.</p>
                        </div>
                    </div>
                    <form id="master-search-form" method="GET" action="{{ route('ticketing.team') }}" class="filter-bar">
                        <input type="text" id="master-search" name="master_search" value="{{ request('master_search') }}" placeholder="Search airline or code…">
                        <button type="submit" class="btn primary">Search</button>
                    </form>
                    <div class="table-scroll">
                        <table>
                            <thead><tr><th>Airline</th><th>Code</th><th>Numeric</th><th>AU commission %</th><th>Ex-AU commission %</th></tr></thead>
                            <tbody id="master-table-body">
                                @foreach($airlineCommissions as $commission)
                                    <tr id="commission-row-{{ $commission->id }}">
                                        <td>
                                            <span class="airline-text">
                                                {{ $commission->airline }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="code-text">
                                                {{ $commission->code }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="numeric-text">
                                                {{ $commission->numeric }}
                                            </span>
                                        </td>

                                        <td>
                                            <span class="au-text">
                                                {{ number_format($commission->au_commission, 2) }}%
                                            </span>
                                        </td>

                                        <td>
                                            <span class="exau-text">
                                                {{ number_format($commission->ex_au_commission, 2) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

@endsection
@push('scripts')
<script src="{{ asset('js/ticketing.js') }}"></script>
@endpush