<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('home.title') }}</title>
    <link rel="alternate" hreflang="de" href="{{ url('/?lang=de') }}">
    <link rel="alternate" hreflang="en" href="{{ url('/?lang=en') }}">
    <style>
        :root {
            --bg: #f4f1ea;
            --ink: #1c1915;
            --muted: #5c574e;
            --line: #d9d2c5;
            --card: #fffcf6;
            --accent: #1d4ed8;
            --ok: #0f7a4a;
            --bad: #b42318;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            color: var(--ink);
            background: var(--bg);
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            line-height: 1.5;
        }
        a { color: var(--accent); }
        header, main, footer { width: min(920px, calc(100% - 2rem)); margin-inline: auto; }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            padding: 1.25rem 0;
        }
        .brand { font-weight: 650; letter-spacing: -0.02em; }
        nav { display: flex; gap: 0.75rem; align-items: center; font-size: 0.9rem; }
        nav a { color: var(--muted); text-decoration: none; }
        nav a[aria-current="true"] { color: var(--ink); font-weight: 600; }
        .hero { padding: 0.5rem 0 1.5rem; }
        .eyebrow {
            color: var(--muted);
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }
        h1 { margin: 0.35rem 0 0.75rem; font-size: clamp(2rem, 5vw, 3rem); letter-spacing: -0.04em; }
        .tagline { max-width: 42rem; color: var(--muted); margin: 0; }
        .actions { display: flex; gap: 0.75rem; flex-wrap: wrap; margin-top: 1.25rem; }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.55rem 0.9rem;
            border-radius: 999px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
        }
        .btn-primary { background: var(--ink); color: var(--card); }
        .btn-ghost { border: 1px solid var(--line); color: var(--ink); }
        .grid { display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); }
        section {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 1rem;
            padding: 1.1rem 1.2rem;
        }
        h2 { margin: 0 0 0.6rem; font-size: 1rem; }
        p { margin: 0; color: var(--muted); }
        .checks { list-style: none; margin: 0.75rem 0 0; padding: 0; }
        .checks li {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            padding: 0.45rem 0;
            border-top: 1px solid var(--line);
            font-size: 0.95rem;
        }
        .ok { color: var(--ok); font-weight: 650; }
        .bad { color: var(--bad); font-weight: 650; }
        code, pre {
            font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
            font-size: 0.82rem;
        }
        pre {
            margin: 0.8rem 0 0;
            padding: 0.85rem;
            background: #1c1915;
            color: #f4f1ea;
            border-radius: 0.7rem;
            overflow-x: auto;
        }
        ul.decisions { margin: 0.5rem 0 0; padding-left: 1.1rem; color: var(--muted); }
        ul.decisions li { margin: 0.35rem 0; }
        footer { color: var(--muted); font-size: 0.85rem; padding: 2rem 0; }
        @media (max-width: 640px) {
            header { align-items: flex-start; flex-direction: column; }
        }
    </style>
</head>
<body>
    <header>
        <div class="brand">{{ __('home.title') }}</div>
        <nav>
            <a href="{{ url('/?lang=de') }}" @if ($locale === 'de') aria-current="true" @endif>DE</a>
            <a href="{{ url('/?lang=en') }}" @if ($locale === 'en') aria-current="true" @endif>EN</a>
            <a href="{{ url('/admin') }}">Admin</a>
        </nav>
    </header>
    <main>
        <div class="hero">
            <p class="eyebrow">{{ __('home.eyebrow') }}</p>
            <h1>{{ __('home.title') }}</h1>
            <p class="tagline">{{ __('home.tagline') }}</p>
            <div class="actions">
                <a class="btn btn-primary" href="{{ url('/admin') }}">{{ __('home.admin') }}</a>
                <a class="btn btn-ghost" href="{{ url('/ready') }}">GET /ready</a>
            </div>
        </div>
        <div class="grid">
            <section>
                <h2>{{ __('home.status_heading') }}</h2>
                <p>{{ __('home.liveness') }} <code>/up</code> — {{ __('home.liveness_hint') }}</p>
                <p style="margin-top:0.4rem">{{ __('home.readiness') }} <code>/ready</code> — {{ __('home.readiness_hint') }}</p>
                <ul class="checks">
                    @php($ready = $readiness['status'] === 'ready')
                    <li>
                        <span>/ready</span>
                        <span class="{{ $ready ? 'ok' : 'bad' }}">{{ $ready ? __('home.ready') : __('home.not_ready') }}</span>
                    </li>
                    @foreach ($readiness['checks'] as $name => $check)
                        <li>
                            <span>{{ $name === 'database' ? __('home.database') : __('home.redis') }}</span>
                            <span class="{{ $check['ok'] ? 'ok' : 'bad' }}">
                                {{ $check['ok'] ? __('home.ready') : __('home.not_ready') }}
                                · {{ number_format($check['latency_ms'], 1) }} ms
                            </span>
                        </li>
                    @endforeach
                    @unless (array_key_exists('redis', $readiness['checks']))
                        <li>
                            <span>{{ __('home.redis') }}</span>
                            <span>{{ __('home.skipped') }}</span>
                        </li>
                    @endunless
                </ul>
            </section>
            <section>
                <h2>{{ __('home.ingest_heading') }}</h2>
                <p>{{ __('home.ingest_body') }}</p>
                <pre>POST /api/price-updates
Authorization: Bearer …
{ "sku", "store_code", "amount_cents", "trace_id" }</pre>
            </section>
            <section>
                <h2>{{ __('home.admin_heading') }}</h2>
                <p>{{ __('home.admin_body') }}</p>
            </section>
            <section>
                <h2>{{ __('home.decisions_heading') }}</h2>
                <ul class="decisions">
                    <li>{{ __('home.decision_history') }}</li>
                    <li>{{ __('home.decision_queue') }}</li>
                    <li>{{ __('home.decision_probes') }}</li>
                </ul>
            </section>
        </div>
    </main>
    <footer>Price Broker · PHP 8.4 · Laravel · Filament · PostgreSQL · Redis · Docker</footer>
</body>
</html>
