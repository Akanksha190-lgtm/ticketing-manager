<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mann Travel Ticketing</title>
    <style>
        :root {
            --navy-950: #0a1330;
            --navy-900: #0f1b3d;
            --blue-500: #4a63e7;
            --blue-400: #6b81ec;
            --ink-900: #141c3a;
            --slate-600: #5b6478;
            --slate-400: #9aa2b6;
            --line: #e7e9f2;
            --paper: #ffffff;
            --canvas: #f5f6fb;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--canvas);
            color: var(--ink-900);
        }

        .navbar {
            background: var(--paper);
            border-bottom: 1px solid var(--line);
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
            color: var(--ink-900);
        }

        .nav-brand .logo {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            background: linear-gradient(145deg, var(--blue-400), var(--blue-500));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 800;
            font-size: 12px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .logout-btn {
            padding: 8px 16px;
            background: var(--blue-500);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: filter .15s ease;
        }

        .logout-btn:hover {
            filter: brightness(1.06);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        .welcome {
            background: var(--paper);
            border-radius: 12px;
            padding: 32px;
            margin-bottom: 32px;
            border: 1px solid var(--line);
        }

        .welcome h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .welcome p {
            color: var(--slate-600);
            line-height: 1.6;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .card {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 24px;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        .card h3 {
            font-size: 16px;
            margin-bottom: 8px;
            color: var(--ink-900);
        }

        .card p {
            font-size: 13px;
            color: var(--slate-600);
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .card-link {
            display: inline-block;
            color: var(--blue-500);
            font-weight: 600;
            font-size: 13px;
            text-decoration: none;
        }

        .card-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="nav-brand">
        <div class="logo">MT</div>
        <span>Mann Travel Ticketing Desk</span>
    </div>
    <div class="nav-right">
        <div class="user-info">
            👤 {{ Auth::user()->name }}
        </div>
        <form method="POST" action="{{ route('logout') }}" style="display: inline;">
            @csrf
            <button type="submit" class="logout-btn">Sign out</button>
        </form>
    </div>
</nav>

<div class="container">
    <div class="welcome">
        <h1>Welcome, {{ Auth::user()->name }}! 👋</h1>
        <p>You're now authenticated and can access the ticketing management system. Use the menu to navigate to your fare desk and manage commissions.</p>
    </div>

    <div class="grid">
        <div class="card">
            <h3>📊 Ticketing Desk</h3>
            <p>Manage fares, view commissions, and track published vs. agency fares in real-time.</p>
            <a href="{{ route('ticketing.index') }}" class="card-link">Go to desk →</a>
        </div>

        <div class="card">
            <h3>✈️ Fare Management</h3>
            <p>Enter net fares and apply airline commissions. The system automatically calculates sell fares.</p>
            <a href="{{ route('ticketing.index') }}" class="card-link">Manage fares →</a>
        </div>

        <div class="card">
            <h3>💰 Commissions</h3>
            <p>Configure airline commissions and track commission entries across all your bookings.</p>
            <a href="{{ route('ticketing.index') }}" class="card-link">View commissions →</a>
        </div>
    </div>
</div>

</body>
</html>
