<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mann Travel — Register</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --navy-950:#0a1330;
    --navy-900:#0f1b3d;
    --navy-800:#152655;
    --blue-500:#4a63e7;
    --blue-400:#6b81ec;
    --blue-glow: rgba(74,99,231,0.35);
    --ink-900:#141c3a;
    --ink-600:#5a6padd;
    --slate-600:#5b6478;
    --slate-400:#9aa2b6;
    --line:#e7e9f2;
    --paper:#ffffff;
    --canvas:#f5f6fb;
    --good:#37c26a;
    --error:#dc2626;
  }
  *{box-sizing:border-box; margin:0; padding:0;}
  html,body{height:100%;}
  body{
    font-family:'Inter',sans-serif;
    background:var(--canvas);
    color:var(--ink-900);
    -webkit-font-smoothing:antialiased;
  }
  .shell{
    min-height:100vh;
    display:grid;
    grid-template-columns: minmax(320px, 42%) 1fr;
  }

  .brand{
    position:relative;
    background:
      radial-gradient(120% 90% at 12% 88%, rgba(74,99,231,0.30), transparent 60%),
      linear-gradient(165deg, var(--navy-900) 0%, var(--navy-950) 70%);
    color:#fff;
    padding:48px 48px 40px;
    display:flex;
    flex-direction:column;
    overflow:hidden;
  }
  .brand::before{
    content:"";
    position:absolute; inset:0;
    background-image: radial-gradient(rgba(255,255,255,0.055) 1px, transparent 1px);
    background-size: 22px 22px;
    mask-image: linear-gradient(180deg, black, transparent 78%);
    pointer-events:none;
  }

  .mark-row{
    display:flex; align-items:center; gap:12px;
    position:relative; z-index:2;
  }
  .mark{
    width:40px; height:40px; border-radius:10px;
    background:linear-gradient(145deg, var(--blue-400), var(--blue-500));
    display:flex; align-items:center; justify-content:center;
    font-weight:800; font-size:15px; letter-spacing:0.02em;
    box-shadow:0 8px 20px -6px var(--blue-glow);
  }
  .brandname{ font-weight:700; font-size:16.5px; letter-spacing:-0.01em; line-height:1.15;}
  .brandsub{ font-size:12.5px; color:#a9b1cf; margin-top:1px; }

  .route-stage{
    position:relative; z-index:2;
    flex:1;
    display:flex; flex-direction:column; justify-content:center;
    padding:24px 0 8px;
  }
  .eyebrow{
    font-family:'JetBrains Mono', monospace;
    font-size:11px; letter-spacing:0.14em; text-transform:uppercase;
    color:var(--blue-400); font-weight:500;
    display:flex; align-items:center; gap:8px; margin-bottom:22px;
  }
  .eyebrow .dot{ width:6px; height:6px; border-radius:50%; background:var(--good); box-shadow:0 0 0 3px rgba(55,194,106,0.25);}

  .headline{
    font-size:34px; font-weight:700; line-height:1.18; letter-spacing:-0.015em;
    max-width:420px;
  }
  .headline em{ color:var(--blue-400); font-style:normal; }
  .subline{
    margin-top:14px; font-size:14.5px; color:#aeb5cf; line-height:1.6; max-width:380px;
  }

  .route-card{
    margin-top:40px;
    background:rgba(255,255,255,0.05);
    border:1px solid rgba(255,255,255,0.09);
    border-radius:14px;
    padding:20px 22px;
    backdrop-filter: blur(6px);
    max-width:420px;
  }
  .route-line{
    display:flex; align-items:center; gap:10px;
  }
  .route-pt{ text-align:left; }
  .route-code{ font-family:'JetBrains Mono',monospace; font-weight:600; font-size:19px; letter-spacing:0.01em;}
  .route-city{ font-size:11px; color:#9aa2c2; margin-top:2px; }
  .route-track{
    flex:1; position:relative; height:14px;
  }
  .route-track::before{
    content:""; position:absolute; left:0; right:0; top:50%;
    border-top:1.5px dashed rgba(255,255,255,0.28);
  }
  .route-plane{
    position:absolute; top:50%; left:38%; transform:translate(-50%,-50%) rotate(90deg);
    font-size:13px; color:var(--blue-400);
  }
  .route-meta{
    display:flex; justify-content:space-between; margin-top:16px;
    font-family:'JetBrains Mono',monospace; font-size:10.5px; color:#8791b3; letter-spacing:0.03em;
  }
  .route-meta b{ color:#d7dbee; font-weight:500; }

  .brand-foot{
    position:relative; z-index:2;
    font-size:11.5px; color:#7c85a6;
    display:flex; justify-content:space-between; align-items:center;
    padding-top:28px;
  }

  .stage{
    display:flex; align-items:center; justify-content:center;
    padding:40px 32px;
    overflow-y:auto;
  }
  .panel{ width:100%; max-width:380px; }

  .panel-head{ margin-bottom:30px; }
  .panel-eyebrow{
    font-family:'JetBrains Mono',monospace; font-size:11px; letter-spacing:0.12em;
    text-transform:uppercase; color:var(--slate-400); font-weight:500; margin-bottom:10px;
  }
  .panel-title{ font-size:24px; font-weight:700; letter-spacing:-0.01em; color:var(--ink-900); }
  .panel-desc{ font-size:13.5px; color:var(--slate-600); margin-top:7px; line-height:1.55; }

  form{ margin-top:26px; display:flex; flex-direction:column; gap:16px; }
  .field{ display:flex; flex-direction:column; gap:7px; }
  .field label{
    font-size:12px; font-weight:600; color:var(--ink-900); letter-spacing:0.01em;
  }
  .input-wrap{ 
    position:relative;
  }
  .input-wrap svg{
    position:absolute; left:13px; top:50%; transform:translateY(-50%);
    width:16px; height:16px; color:var(--slate-400); pointer-events:none; z-index:1;
  }
  input{
    width:100%;
    font-family:'Inter',sans-serif;
    font-size:14px;
    padding:11.5px 44px 11.5px 38px;
    border:1.5px solid var(--line);
    border-radius:9px;
    background:var(--paper);
    color:var(--ink-900);
    transition: border-color .15s ease, box-shadow .15s ease;
  }
  input::placeholder{ color:#b7bccb; }
  input:focus{
    outline:none;
    border-color: var(--blue-500);
    box-shadow: 0 0 0 3.5px rgba(74,99,231,0.14);
  }
  input.error{
    border-color: var(--error);
  }
  input.error:focus{
    box-shadow: 0 0 0 3.5px rgba(220,38,38,0.14);
  }
  .error-msg{
    font-size:12px; color:var(--error); margin-top:-3px;
  }

  .submit{
    margin-top:6px;
    width:100%;
    padding:12.5px 16px;
    border:none; border-radius:9px;
    background:linear-gradient(145deg, var(--blue-400), var(--blue-500));
    color:#fff; font-size:14.5px; font-weight:600;
    cursor:pointer;
    box-shadow:0 10px 22px -8px var(--blue-glow);
    display:flex; align-items:center; justify-content:center; gap:8px;
    transition: transform .12s ease, box-shadow .12s ease, filter .12s ease;
  }
  .submit:hover{ filter:brightness(1.06); box-shadow:0 12px 26px -8px var(--blue-glow); }
  .submit:active{ transform:translateY(1px); }
  .submit svg{ width:15px; height:15px; }

  .agency-note{
    margin-top:18px;
    display:flex; gap:10px; align-items:flex-start;
    background:#f8f9fd; border:1px solid var(--line); border-radius:10px;
    padding:12px 13px;
  }
  .agency-note svg{ width:15px; height:15px; color:var(--blue-500); flex-shrink:0; margin-top:1px; }
  .agency-note p{ font-size:12px; color:var(--slate-600); line-height:1.55; }
  .agency-note b{ color:var(--ink-900); }

  .panel-foot{
    margin-top:26px; text-align:center; font-size:13px; color:var(--slate-600);
  }
  .panel-foot a{ color:var(--blue-500); font-weight:600; text-decoration:none; }
  .panel-foot a:hover{ text-decoration:underline; }

  @media (max-width: 860px){
    .shell{ grid-template-columns: 1fr; }
    .brand{ display:none; }
    .stage{ padding:56px 24px; }
  }

  @media (prefers-reduced-motion: reduce){
    *{ animation:none !important; transition:none !important; }
  }
</style>
</head>
<body>

<div class="shell">

  <!-- LEFT BRAND PANEL -->
  <aside class="brand">
    <div class="mark-row">
      <div class="mark">MT</div>
      <div>
        <div class="brandname">Mann Travel</div>
        <div class="brandsub">Ticketing Desk</div>
      </div>
    </div>

    <div class="route-stage">
      <div class="eyebrow"><span class="dot"></span>Fare desk — live</div>
      <div class="headline">Every fare, tracked <em>from published to sold.</em></div>
      <p class="subline">Create your agency account to start managing fares and commission automatically.</p>

      <div class="route-card">
        <div class="route-line">
          <div class="route-pt">
            <div class="route-code">NET</div>
            <div class="route-city">Published fare</div>
          </div>
          <div class="route-track">
            <svg class="route-plane" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M22 16.5v-2l-8.5-5V4a1.5 1.5 0 0 0-3 0v5.5L2 14.5v2l8.5-2.7V19l-2.5 1.8V22l3.5-1 3.5 1v-1.2L12.5 19v-5.2z"/></svg>
          </div>
          <div class="route-pt" style="text-align:right;">
            <div class="route-code">SELL</div>
            <div class="route-city">Agency fare</div>
          </div>
        </div>
        <div class="route-meta">
          <span>Commission <b>auto-applied</b></span>
          <span>Markup <b>configurable</b></span>
        </div>
      </div>
    </div>

    <div class="brand-foot">
      <span>© 2026 Mann Travel</span>
      <span>Ticketing Manager v2</span>
    </div>
  </aside>

  <!-- RIGHT FORM PANEL -->
  <main class="stage">
    <div class="panel">
      <div class="panel-head">
        <div class="panel-eyebrow">Ticketing desk access</div>
        <div class="panel-title">Create account</div>
        <p class="panel-desc">Register your agency to access the ticketing management system.</p>
      </div>

      <form method="POST" action="{{ route('register') }}">
        @csrf
        <div class="field">
          <label for="name">Agency name</label>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <input 
              id="name" 
              type="text" 
              name="name"
              placeholder="Your agency name" 
              autocomplete="name" 
              value="{{ old('name') }}"
              @error('name') class="error" @enderror
              required>
          </div>
          @error('name')
            <span class="error-msg">{{ $message }}</span>
          @enderror
        </div>

        <div class="field">
          <label for="email">Work email</label>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6.5A1.5 1.5 0 0 1 4.5 5h15A1.5 1.5 0 0 1 21 6.5v11a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 17.5v-11Z"/><path d="m4 6.5 8 6.5 8-6.5"/></svg>
            <input 
              id="email" 
              type="email" 
              name="email"
              placeholder="you@manntravel.com" 
              autocomplete="email" 
              value="{{ old('email') }}"
              @error('email') class="error" @enderror
              required>
          </div>
          @error('email')
            <span class="error-msg">{{ $message }}</span>
          @enderror
        </div>

        <div class="field">
          <label for="password">Password</label>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10.5" width="14" height="9.5" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
            <input 
              id="password" 
              type="password" 
              name="password"
              placeholder="••••••••••" 
              autocomplete="new-password"
              @error('password') class="error" @enderror
              required>
          </div>
          @error('password')
            <span class="error-msg">{{ $message }}</span>
          @enderror
        </div>

        <div class="field">
          <label for="password_confirmation">Confirm password</label>
          <div class="input-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="5" y="10.5" width="14" height="9.5" rx="2"/><path d="M8 10.5V7.5a4 4 0 0 1 8 0v3"/></svg>
            <input 
              id="password_confirmation" 
              type="password" 
              name="password_confirmation"
              placeholder="••••••••••" 
              autocomplete="new-password"
              @error('password_confirmation') class="error" @enderror
              required>
          </div>
          @error('password_confirmation')
            <span class="error-msg">{{ $message }}</span>
          @enderror
        </div>

        <button type="submit" class="submit">
          Create account
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
      </form>

      <div class="agency-note">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
        <p><b>Password requirements:</b> Minimum 8 characters. Choose a strong password with numbers and special characters.</p>
      </div>

      <p class="panel-foot">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
  </main>

</div>

</body>
</html>
