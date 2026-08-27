@extends('layouts.app')
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
@endpush
@section('content')

<div class="app-shell">
  <div class="main-col">
    <div class="topbar">
      <div>
        <h1 id="topbar-title">Ticketing Manager</h1>
        <p id="topbar-desc">Enter the published/net fare, the IATA/BSP commission or private tour-code discount, and the agency markup — the sell fare for the Ticketing Team is calculated automatically.</p>
      </div>
      <div class="topbar-right">
        <span class="pill"><span class="live-dot"></span>Live</span>
        <span class="pill" id="topbar-date">—</span>
      </div>
    </div>

    <main>

      <!-- ===================== TICKETING MANAGER VIEW ===================== -->
      <section class="view active" id="view-manager">

        <div class="panel">
          <div class="panel-head">
            <div>
              <h2>Fare entry <span class="tag" id="form-mode-tag">New</span></h2>
              <p class="hint">Source this from airline trade circulars (private/tour-code fares) or the standing IATA/BSP commission schedule (published fares).</p>
            </div>
          </div>

          <form class="fare-form" id="fare-form" method="POST" action="{{ route('fare-commission-entries.store') }}">
            @csrf
            <input type="hidden" id="fare-id" name="fare_id">

            <div><label>Airline</label><input type="text" id="f-airline" name="airline" placeholder="e.g. Air India" required></div>
            <div><label>Airline code</label><input type="text" id="f-airline-code"  name="airline_code_id" placeholder="e.g. AI" maxlength="3" style="text-transform:uppercase"></div>
            <div><label>Origin</label><input type="text" id="f-origin" name="origin" placeholder="e.g. Melbourne" required></div>
            <div><label>Destination</label><input type="text" id="f-destination" name="destination" placeholder="e.g. Bengaluru" required></div>

            <div><label>Cabin</label>
                <select id="f-cabin" name="cabin_id">
                    <option value="">Select cabin</option>
                    @foreach($cabins as $cabin)
                        <option value="{{ $cabin->id }}">
                            {{ $cabin->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div><label>Fare source</label>
                <select id="f-source" name="source_id">
                    <option value="">Select fare source</option>
                    @foreach($fareSources as $source)
                        <option value="{{ $source->id }}">
                            {{ $source->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div><label>Tour code (if private)</label><input type="text" id="f-tourcode" name="tour_code" placeholder="e.g. AUSNT007"></div>
            <div><label>PCC / IATA ref</label><input type="text" id="f-pcc" name="pcc_iata_ref" placeholder="e.g. 8T01–8T04 / 0236266"></div>

            <div><label>Published fare</label><input type="number" step="0.01" id="f-published" name="published" placeholder="e.g. 1400" required></div>
            <div><label>Currency</label>
                <select id="f-currency" name="currency_id">
                    <option value="">Select currency</option>
                    @foreach($currencies as $currency)
                        <option value="{{ $currency->id }}">
                            {{ $currency->code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div><label>Discount / commission %</label><input type="number" step="0.1" id="f-commission-pct" name="disc_comm" placeholder="e.g. 10" readonly></div>
            <div><label>Agency markup ($ flat)</label><input type="number" step="0.01" id="f-markup" name="markup" placeholder="e.g. 90" required></div>

            <div><label>Travel from</label><input type="date" id="f-travel-from" name="travel_from" required></div>
            <div><label>Travel to</label><input type="date" id="f-travel-to" name="travel_to"></div>
            <div><label>Valid until (booking deadline)</label><input type="date" id="f-valid-until" name="valid_until" required></div>
            <div></div>

            <div class="calc-preview">
              <div>Net fare <strong id="preview-net">AUD 0.00</strong></div>
              <div>Gross / sell fare <strong id="preview-gross">AUD 0.00</strong></div>
              <div>Agency margin <strong id="preview-margin">AUD 0.00</strong></div>
            </div>

            <div class="full"><label>Internal notes — Ticketing Team only (RBDs, tour code conditions, ADM risk, etc.)</label>
              <textarea id="f-internal-notes" name="internal_notes" placeholder="e.g. RBDs UL-,LL-,GL-,WL-,VL- (Lean). Combinations within tour code not permitted."></textarea>
            </div>
            <div class="full"><label>Booking notes (baggage, blackout dates, conditions)</label>
              <textarea id="f-customer-notes" name="booking_notes" placeholder="e.g. Includes +5kg extra baggage to India (Economy Classic)."></textarea>
            </div>

            <div class="form-actions">
              <button type="submit" class="btn primary" >Add fare</button>
              <button type="button" class="btn" id="fare-form-cancel" style="display:none;">Cancel edit</button>
            </div>
          </form>
        </div>

        <div class="panel">
          <div class="panel-head">
            <div>
              <h2>All fare &amp; commission entries</h2>
              <p class="hint">Single source of truth — the Ticketing Team view reads from this list.</p>
            </div>
          </div>
          <div class="table-responsive">
          <table class="table">
            <thead><tr>
              <th>Airline</th><th>Route</th><th>Cabin</th><th>Source</th><th>Published</th><th>Disc/Comm %</th><th>Net</th><th>Markup</th><th>Gross</th><th>Valid Until</th><th>Status</th><th>Action</th><th>History</th>
            </tr></thead>
            <tbody id="manager-table-body">
                @foreach($farecomissentry as $fare_entry)
                    <tr id="fare-entry-row-{{ $fare_entry->id }}" data-origin="{{ $fare_entry->route->origin ?? '' }}" data-destination="{{ $fare_entry->route->destination ?? '' }}" data-currency="{{ $fare_entry->currency->code ?? '' }}" data-origin-code="{{ $fare_entry->route->origin_code ?? '' }}" data-destination-code="{{ $fare_entry->route->destination_code ?? '' }}" data-route-id="{{ $fare_entry->route->id ?? '' }}" data-route-exists="{{ $fare_entry->route ? '1' : '0' }}">
                        {{-- Airline --}}
                        <td class="text-nowrap airline-cell">
                            <span class="airline-text">
                                {{ $fare_entry->airline->airline ?? '-' }}
                            </span>
                        </td>
                        {{-- Route --}}
                        <td class="text-nowrap route-code-cell">
                            @if($fare_entry->route)
                                <strong class="route-text">
                                    {{ $fare_entry->route->origin }}
                                    ({{ $fare_entry->route->origin_code ?? '' }})
                                    →
                                    {{ $fare_entry->route->destination }}
                                    ({{ $fare_entry->route->destination_code ?? '' }})
                                </strong>

                                @if(empty($fare_entry->route->origin_code) || empty($fare_entry->route->destination_code))
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2 add-route-code-btn" data-bs-toggle="modal" data-bs-target="#routeCodeModal{{ $fare_entry->id }}">
                                        Add Code
                                    </button>
                                @endif
                            @else
                                -
                            @endif
                        </td>
                        {{-- Cabin --}}
                        <td class="text-nowrap">
                            <span class="cabin-text">
                                {{ $fare_entry->cabin->name ?? '-' }}
                            </span>
                        </td>
                        {{-- Source --}}
                        <td class="text-nowrap">
                            <span class="source-text">
                                {{ $fare_entry->fareSource->name ?? '-' }}
                            </span>
                        </td>
                        {{-- Published --}}
                        <td class="text-nowrap">
                            <span class="published-text"><strong>
                            {{ $fare_entry->currency->code ?? '-' }} {{ number_format($fare_entry->published, 2) }}
                            </span></strong>
                        </td>
                        {{-- Discount / Commission --}}
                        <td class="text-nowrap">
                            <span class="disc-comm-text"><strong>
                                {{ number_format($fare_entry->airline->au_commission ?? 0, 1) }}%
                            </span></strong>
                        </td>

                        {{-- Net --}}
                        <td class="text-nowrap">
                            <span class="net-text"><strong>
                                {{ $fare_entry->currency->code ?? '-' }} {{ number_format($fare_entry->net, 2) }}
                            </span></strong>
                        </td>
                        {{-- Markup --}}
                        <td class="text-nowrap">
                            <span class="markup-text"><strong>
                                {{ $fare_entry->currency->code ?? '-' }} {{ number_format($fare_entry->markup, 2) }}
                            </span></strong>
                        </td>

                        {{-- Gross --}}
                        <td class="text-nowrap">
                            <span class="gross-text"><strong>
                                {{ $fare_entry->currency->code ?? '-' }} {{ number_format($fare_entry->gross, 2) }}
                            </span></strong>
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
                        <td class="text-nowrap">
                            <span class="status-text">
                                {{ $fare_entry->status }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="text-nowrap">
                            <button type="button" class="btn edit-fare-entry" data-id="{{ $fare_entry->id }}" titlle="Edit"><i class="bi bi-pencil-square"></i></button>

                            <button type="button" class="btn delete-fare-entry" data-id="{{ $fare_entry->id }}" titlle="Delete"> <i class="bi bi-trash"></i></button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm view-history" data-id="{{ $fare_entry->id }}" title="View History">
                                <i class="bi bi-clock-history"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
          </table>
          </div>
        </div>

        <div class="panel">
          <div class="panel-head">
            <div>
              <h2>IATA / BSP commission master list <span class="tag">60 carriers · editable</span></h2>
              <p class="hint">Standing commission schedule per airline (AU-issued IATA PCC 8T03). Update when a carrier sends a revision circular — e.g. Air India's 1% change effective 1 Jun 2025, or Malaysia Airlines' 2% AU/NZ/SWP commission.</p>
            </div>
          </div>
          <form id="master-search-form" method="GET" action="{{ route('ticketing.index') }}" class="filter-bar">
            <input type="text" id="master-search" name="search" value="{{ request('search') }}" placeholder="Search airline or code…">
            <button type="submit" class="btn primary">Search</button>
          </form>
          <div class="table-scroll">
          <table>
            <thead><tr><th>Airline</th><th>Code</th><th>Numeric</th><th>AU commission %</th><th>Ex-AU commission %</th><th></th></tr></thead>
            <tbody id="master-table-body">
                @foreach($airlineCommissions as $commission)
                    <tr id="commission-row-{{ $commission->id }}">
                        <td>
                            <span class="airline-text white-space: nowrap;">
                                {{ $commission->airline }}
                            </span>
                        </td>

                        <td>
                            <span class="code-text white-space: nowrap;">
                                {{ $commission->code }}
                            </span>
                        </td>

                        <td>
                            <span class="numeric-text white-space: nowrap;">
                                {{ $commission->numeric }}
                            </span>
                        </td>

                        <td>
                            <span class="au-text white-space: nowrap;">
                                {{ number_format($commission->au_commission, 2) }}%
                            </span>
                        </td>

                        <td>
                            <span class="exau-text white-space: nowrap;">
                                {{ number_format($commission->ex_au_commission, 2) }}%
                            </span>
                        </td>

                        <td class="text-nowrap">
                            <button type="button" class="btn edit-commission" data-id="{{ $commission->id }}"><i class="bi bi-pencil-square"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
          </table>
            @foreach($farecomissentry as $fare_entry)

                @if($fare_entry->route)

                    <div class="modal fade" id="routeCodeModal{{ $fare_entry->id }}" tabindex="-1" aria-hidden="true">

                        <div class="modal-dialog modal-dialog-centered">

                            <div class="modal-content">

                                <form method="POST" action="{{ route('routes.codes.update', $fare_entry->route->id) }}" id="routeCodeForm{{ $fare_entry->id }}"
                                    data-row-id="fare-entry-row-{{ $fare_entry->id }}">

                                    @csrf
                                    @method('PUT')

                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            Add Route Codes
                                        </h5>

                                        <button type="button" class="btn-close" data-bs-dismiss="modal" ></button>
                                    </div>

                                    <div class="modal-body">
                                        {{-- Origin --}}
                                        <!-- <div class="mb-3">
                                            <label class="form-label">Origin</label>
                                            <input type="text" class="form-control" value="{{ $fare_entry->route->origin }}" readonly>
                                        </div> -->

                                        {{-- Origin Code --}}
                                        <div class="mb-3">

                                            <label class="form-label">Origin Code</label>

                                            <input type="text" name="origin_code" class="form-control" value="{{ $fare_entry->route->origin_code ?? '' }}"placeholder="e.g. SYD"
                                                maxlength="10">

                                        </div>

                                        {{-- Destination --}}
                                        <!-- <div class="mb-3">
                                            <label class="form-label">Destination</label>
                                            <input type="text" class="form-control" value="{{ $fare_entry->route->destination }}"readonly>
                                        </div> -->

                                        {{-- Destination Code --}}
                                        <div class="mb-3">

                                            <label class="form-label">
                                                Destination Code
                                            </label>

                                            <input type="text" name="destination_code" class="form-control" value="{{ $fare_entry->route->destination_code ?? '' }}" placeholder="e.g. LKO" maxlength="10">

                                        </div>

                                    </div>

                                    <div class="modal-footer">

                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                                        <button type="submit" class="btn btn-primary">Save Codes</button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </div>

                @endif

            @endforeach
          </div>
        </div>
      </section>

    </main>

    <footer class="app-footer">Mann Travel Ticketing Desk · fare &amp; commission data maintained by the Ticketing Team</footer>
  </div>
</div>

<div id="toast-stack"></div>

<!-- History Offcanvas -->
<div class="history-overlay" id="historyOverlay"></div>

<div class="history-panel" id="historyPanel">

    <div class="history-header">
        <div>
            <h5 class="mb-0">History</h5>
            <small class="text-muted" id="historyEntryTitle">
                Fare Commission
            </small>
        </div>

        <button type="button"
                class="btn-close"
                id="closeHistory">
        </button>
    </div>

    <div class="history-body" id="historyBody">

        <div class="text-center py-5">
            <div class="spinner-border spinner-border-sm"></div>
            <p class="mt-2 text-muted">Loading history...</p>
        </div>

    </div>

</div>
@endsection
@push('scripts')
<script src="{{ asset('js/ticketing.js') }}"></script>
@endpush


