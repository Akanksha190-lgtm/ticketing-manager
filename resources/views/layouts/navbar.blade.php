<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="mark">MT</div>
        <div><strong>Mann Travel</strong><span>Ticketing Desk</span></div>
    </div>

    <nav class="sidebar-nav">
        {{-- Ticketing Manager --}}
        @if(auth()->user()->role === 'ticketing_manager')

            <a href="{{ route('ticketing.index') }}" class="nav-item {{ request()->routeIs('ticketing.index') ? 'active' : '' }}">
                <span class="icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                    </svg>
                </span>

                <span class="txt">
                    <strong>Ticketing Manager</strong>
                    <span>Add &amp; edit fares, commission</span>
                </span>
            </a>
        @endif
        {{-- Ticketing Team --}}
        @if(in_array(auth()->user()->role, ['ticketing_manager', 'ticketing_team']))

            <a href="{{ route('ticketing.team') }}" class="nav-item {{ request()->routeIs('ticketing.team') ? 'active' : '' }}">
                <span class="icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </span>

                <span class="txt">
                    <strong>Ticketing Team</strong>
                    <span>Fares &amp; issuance commission</span>
                </span>
            </a>
        @endif
    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-stat"><span>Active fares</span><strong id="sb-active">{{$activeFares}}</strong></div>
        <div class="sidebar-stat"><span>Expiring ≤ 7 days</span><strong id="sb-expiring">{{$expiringFares}}</strong></div>
        <div class="sidebar-stat"><span>Carriers on file</span><strong id="sb-carriers">{{$carriers}}</strong></div>
        <div class="sidebar-caption">Fare &amp; commission data sourced from airline trade circulars and the AU IATA/BSP commission schedule (PCC 8T03).</div>
    
        <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; flex-direction: column; gap: 8px; align-items: center;">
            <div style="font-size: 12px; color: #aeb5cf; text-align: center;">
            👤 {{ Auth::user()->name }}
            </div>
            <form method="POST" action="{{ route('logout') }}" style="width: 100%; margin: 0;">
            @csrf
            <button type="submit" style="width: 100%;padding: 8px 12px;background: linear-gradient(135deg, #2a78d6, #184f95);color: white;border: none;border-radius: 6px;
                font-size: 12px;font-weight: 600;cursor: pointer;transition: filter .15s ease;box-shadow:0 4px 12px -4px rgba(42, 120, 214, 0.55);" onmouseover="this.style.filter='brightness(1.06)'" onmouseout="this.style.filter='brightness(1)'">
                Sign out
            </button>
            </form>
        </div>
    </div>
</aside>