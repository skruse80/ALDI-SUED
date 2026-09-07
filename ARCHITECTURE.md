# Architecture

Price Broker nimmt Preisupdates aus Filialen entgegen, prüft sie an der API-Kante und schreibt sie asynchron nach PostgreSQL. Das Filament-Admin liest dieselbe Datenbank.

## Aktueller Schnitt

```
Client  --HTTP-->  Nginx  --FastCGI-->  Laravel (php-fpm)
                                              |
                                              v
                                         Redis Queue
                                              |
                                              v
                                       Laravel Worker
                                              |
                                              v
                                         PostgreSQL
```

| Service    | Rolle                                      |
|------------|--------------------------------------------|
| `web`      | Nginx, öffentlicher HTTP-Einstieg          |
| `app`      | PHP-FPM: Validierung, Jobs dispatchen      |
| `worker`   | Queue konsumieren, persistieren            |
| `postgres` | System of record                           |
| `redis`    | Queue, Cache, Session                      |

## Bewusste Vereinfachungen

- Ein Runtime (PHP/Laravel), kein zweiter Ingest-Service.
- Redis statt Kafka/RabbitMQ: Laravel-idiomatisch, lokal mit einem Container.
- Kein DDD-Ordnerbaum, solange die Domäne drei Tabellen groß ist. Actions, DTO und Job reichen.

## Domäne

Integer-PKs intern, UUID öffentlich (`products.uuid`, `stores.uuid`). Preise liegen nur in `price_histories` (Cents, ISO-Währung). Jedes Update hängt eine Zeile an.

**Aktueller Preis** ist nicht denormalisiert. `App\Queries\CurrentStorePrices` wählt je Filiale/Produkt die Zeile mit dem höchsten `recorded_at` (bei Gleichstand `id`). So bleibt die History die einzige Wahrheit.

| Einstieg | Klasse |
|----------|--------|
| HTTP | `POST /api/price-updates` (Bearer `INGEST_API_TOKEN`) |
| Store-Client | `clients/ingest` — TypeScript, generiert `trace_id`, mapped 202/409/422 |
| Payload | `App\Data\PriceUpdateData` |
| Schreiben | `App\Actions\UpdateProductPriceAction` |
| Queue | `App\Jobs\ProcessPriceUpdateJob` |
| Lesen (aktuell) | `App\Queries\CurrentStorePrices` |
| Anzeige (aktuell) | `App\ViewModels\CurrentPriceRows` |
| Admin | Filament `/admin` — Produkt/Filiale als Cockpit, Verlauf read-only |

Validierung an der Kante: Schema, existierende SKU/Filiale, Token, Rate-Limit (`INGEST_RATE_PER_MINUTE`, Default 60/min). `202` dispatched den Job und gibt `trace_id` zurück. Derselbe `trace_id` mit gleichem Payload ist idempotent (`202 duplicate`). Derselbe `trace_id` mit anderem Payload ist `409`. Unbekannte SKU bleibt im Job abgesichert. `trace_id` ist unique; der Job ist `ShouldBeUnique`. Fehlgeschlagene Jobs liegen in `failed_jobs` und sind im Admin unter **Betrieb** sichtbar (Retry/Verwerfen).

**Autorisierung:** Panel-Zugang für authentifizierte User. Resource-Regeln in Policies: History darf nicht per Admin erzeugt, geändert oder gelöscht werden. Produkte/Filialen mit History sind nicht löschbar. Neue Preise nur über Ingest-API oder `RecordProductPriceAction` (dieselbe Domain-Action).

## Probes

| Endpoint | Bedeutung |
|----------|-----------|
| `GET /up` | Liveness. PHP-FPM/Laravel starten. |
| `GET /ready` | Readiness. `select 1` gegen die DB, Redis-PING nur wenn Cache/Queue/Session Redis nutzen. `200` oder `503`. |

Lokal prüft der Nginx-Healthcheck `/ready`. In Kubernetes wären das livenessProbe `/up` und readinessProbe `/ready` an denselben Prozessen (`app` + `worker`).
