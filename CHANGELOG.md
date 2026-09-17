# Changelog

All notable changes to this project will be documented in this file.

## 0.2.0

### Changed

- **Breaking:** renamed the `customers` resource attribute `skipSendingRegistrationToken` to
  `sendRegistrationToken` and inverted its value, so that both mail flags read the same way as
  `sendPasswordToken`. Replace `skipSendingRegistrationToken: true` with `sendRegistrationToken: false`.
  Unknown attributes are ignored rather than rejected, so a request that still sends the old name is
  accepted and the registration-confirmation mail is sent. Search your integrations for
  `skipSendingRegistrationToken`.
- **Breaking:** `sendRegistrationToken` is accepted when creating a customer only. It is outside the
  update operation's serialization groups, so `PATCH /customers/{customerReference}` ignores it and
  the request succeeds without it taking effect — a registration token is issued only at
  registration. An earlier iteration of this release answered `422` with error code `1213` here;
  that code is withdrawn and no longer returned. Remove `sendRegistrationToken` from your update
  payloads and drop any handling of `1213`.
- **Breaking:** removed the `updatedAt` attribute from the `notes` resource, and withdrew it as a
  `sort` field. A note has no update path — not through the facade, the Back Office or this API —
  so Propel writes `updated_at` once, from the same value as `created_at`, and ordering by it was
  ordering by `createdAt` under another name. Read `createdAt` instead. The `sort` allow list is
  owned by `Spryker\Zed\CustomerNote\CustomerNoteConfig::getCustomerNoteCollectionSortableFieldMap()`
  in the released `spryker/customer-note`, so this package no longer advertises `updatedAt` but
  cannot reject it on its own: override that method in `Pyz\Zed\CustomerNote\CustomerNoteConfig` to
  have `?sort=updatedAt` answer `400` with error code `1203`.
- **Breaking:** `storeName` is now required when creating a customer. It is the context of every
  mail the registration sends, so without it the confirmation link carries no `_store` and a shop
  with more than one store has no safe default for where the link should land. `POST /customers`
  without it returns `422` and names the field, and no customer is created. It stays optional on
  `PATCH`, which sends no registration mail.

### Fixed

- The Swagger request bodies for every write operation can now be sent as shown. They were missing the
  JSON:API `data.type` envelope and answered `400 Post data is invalid.`; the envelope is restored by
  `spryker/api-platform`, which fixes both applications.
- This covers the `application/json` media type as well as `application/vnd.api+json`. Both are
  registered under the `jsonapi` format, so a request sent as `application/json` is read by the JSON:API
  denormalizer and needs the same enveloped body — but it was documented with the flat schema, on
  `POST /customers` as well as on `PATCH`. Sending either as documented answered `400`.
- The documented `PATCH` body now carries `data.id`, which JSON:API requires of the resource object of
  an update. It was inherited from the `POST` body shape, which omits the identifier because the
  database assigns it.
- The `customers` PATCH example no longer carries `sendRegistrationToken`, which is accepted when
  creating a customer only. The property is in the `customers:write:create` serialization group, and
  only the create operation's `denormalizationContext` lists that group, so the property stays in the
  POST body and is left out of the PATCH one.
- The `addresses` example no longer suggests `region: "DE-BE"`. A region is validated against the
  imported region data, of which a stock shop has none, so any value answered `422` (error code
  `1211`). The example is empty, which the validator skips.

## 1.0.0

### Added

- Introduced the `customers` Backend API resource: `GET /customers`, `GET /customers/{customerReference}`,
  `POST /customers`, `PATCH /customers/{customerReference}` and `DELETE /customers/{customerReference}`.
- Collection pagination and filtering by search terms, email and customer reference.
- `DELETE` performs GDPR anonymization through `CustomerFacade::anonymizeCustomer()` instead of a hard delete.
