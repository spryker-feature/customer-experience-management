# Postman — Customers Backend API

A ready-to-run collection for the `customers` resource of the Backend API
(`glue-backend`, API Platform), mirroring
[`../api/backend/customers.resource.yml`](../api/backend/customers.resource.yml).

| File | Purpose |
|---|---|
| `customers.postman_collection.json` | The collection: auth, every read parameter, the full write lifecycle, the 401 paths |
| `spryker-backend-api.postman_environment.json` | Host and credentials for the local docker environment |

## Import and run

1. Postman → **Import** → drop both files in.
2. Select the **Spryker Backend API — local docker** environment (or edit the collection variables directly).
3. Send any request.

There is no "log in first" step. A collection-level pre-request script mints a bearer token from
`POST /token` whenever the stored one is missing or within 60 seconds of expiring, so every request
works standalone and in any order.

To run the whole thing as a smoke test, use the Collection Runner — the folders are ordered so a
top-to-bottom run makes sense, and every request carries assertions.

## What the folders do

**1. Auth** — the password grant, for when you want to inspect or force-refresh the token.

**2. Read** — read-only. Both pagination spellings (`page=<n>` and `page[limit]`/`page[offset]`),
free-text `q`, each `filter[customers.<property>]`, sorting, the `include=notes` relationship, and
the two documented error paths (`1203` unsupported sort → 400, `1201` unknown reference → 404).

**3. Write lifecycle** — create → read → update → anonymize → prove the record was retained and
scrubbed. It is self-contained: the create request generates a unique email and stores the resulting
`customerReference`, so every later request acts on the customer that run created. **It never
touches pre-existing data**, which is what makes the destructive step safe to leave enabled.

**4. Authorization failures** — no token and invalid token, both 401.

## Things worth knowing before you edit it

- **`DELETE` anonymizes, it does not delete.** The row is retained with `anonymizedAt` stamped and
  the personal data scrubbed. The last request in the write folder proves exactly that, and is the
  clearest executable statement of the difference.
- **Don't filter by `{{username}}`.** That variable is the Back Office *user* used for the password
  grant; it is not a customer, so `filter[customers.email]={{username}}` matches nothing and any
  per-row assertion over the empty result passes vacuously. The collection uses `{{customerEmail}}`,
  which the list request seeds from the live dataset.
- **`page[limit]` and `filter[...]` are bracketed keys.** Postman keeps them literal in the query
  editor; don't let an editor URL-encode the brackets into the key name.
- **The password is a local demo value.** Move it to an environment or a vault variable before
  sharing the collection anywhere real.
- **Sub-resources are not in here.** `customers` has two: `addresses` and `notes`
  (`/customers/{customerReference}/addresses`, `/customers/{customerReference}/notes`). Only the
  `?include=notes` relationship — which is part of the customers resource — is covered.

## Keeping it honest

The collection is generated against a running environment, not transcribed from the schema. When the
resource changes, re-verify rather than hand-patching: every request's expected status is asserted in
its own test script, so a Collection Runner pass is the check.
