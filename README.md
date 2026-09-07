# Price Broker

Laravel-Backend für asynchrone Preisupdates aus Filialen. Lokale Umgebung: PHP 8.4-FPM, Nginx, PostgreSQL, Redis, Queue-Worker.

Details zur Architektur: [ARCHITECTURE.md](ARCHITECTURE.md).

## Start

Voraussetzung: Docker Desktop.

```bash
cp .env.example .env
docker compose up --build
```

Der App-Container erzeugt den `APP_KEY`, wartet auf Postgres und führt die Migrationen aus.

- App: http://localhost:8000
- Health (liveness): http://localhost:8000/up
- Health (readiness): http://localhost:8000/ready
- Postgres: `localhost:55432` (User/Passwort: `price_broker` / `secret`)
- Redis: `localhost:6380`

Host-Ports sind bewusst nicht 5432/6379, damit lokale Postgres/Redis-Instanzen nicht kollidieren.

Die Startseite ist DE/EN umschaltbar und zeigt den Readiness-Status. `/up` ist Liveness (Prozess lebt), `/ready` prüft die konfigurierten Dependencies (Postgres, Redis in Docker).

Optional, für gestylte Frontend-Assets auf dem Host:

```bash
npm install
npm run dev
```

## Admin

Filament unter http://localhost:8000/admin

- E-Mail: `test@example.com`
- Passwort: `password`

Dashboard: Kennzahlen (Polling 15s) plus Tabelle der letzten Updates. Produkt- und Filialseiten zeigen **aktuelle Preise** und darunter den vollständigen Verlauf. History bleibt append-only.

Neue Preise: Ingest-API oder **Preis erfassen** am Produkt.

## Ingest API

`POST /api/price-updates` — Bearer-Token aus `INGEST_API_TOKEN`. Antwort `202` mit `trace_id`. Unbekannte SKU/Filiale und ungültige Payloads werden an der Kante mit `422` abgewiesen. Rate-Limit: `INGEST_RATE_PER_MINUTE` (Default 60).

Idempotenz über `trace_id`: denselben Request nochmal senden ergibt `202` mit `"message": "duplicate"` und keine zweite History-Zeile. Dieselbe ID mit anderem Preis ist `409`.

```bash
curl -sS -X POST http://localhost:8000/api/price-updates \
  -H "Authorization: Bearer local-ingest-token" \
  -H "Content-Type: application/json" \
  -d '{"sku":"MILCH-001","store_code":"K-101","amount_cents":129,"trace_id":"550e8400-e29b-41d4-a716-446655440000"}'
```

Typisierter Filial-Client (generiert `trace_id`, kennt 202/409/422/429):

```bash
cd clients/ingest && npm install && npm test
INGEST_API_TOKEN=local-ingest-token npx tsx src/cli.ts --sku MILCH-001 --store-code K-101 --amount-cents 149
```

Fehlgeschlagene Queue-Jobs erscheinen im Admin unter **Betrieb → Fehlgeschlagene Jobs**.

## Qualität

```bash
composer test
composer lint
composer analyse
```

CI führt Pint, PHPStan, Pest und den TypeScript-Ingest-Client aus.
