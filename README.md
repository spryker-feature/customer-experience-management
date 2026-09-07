# CustomerExperienceManagement Module
[![Latest Stable Version](https://poser.pugx.org/spryker-feature/customer-experience-management/v/stable.svg)](https://packagist.org/packages/spryker-feature/customer-experience-management)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

CustomerExperienceManagement exposes customer lifecycle management over the Spryker Backend API
(API Platform), covering the operations the Zed Back Office offers: listing customers with pagination
and filtering, reading a single customer, creating a customer through the Back Office registration
flow, partially updating a customer, and GDPR anonymization.

The module is an API surface over `Spryker\Zed\Customer\Business\CustomerFacadeInterface`, which
keeps validation, email uniqueness and the anonymizer plugin stack behaving exactly as they do in
the Back Office.

The resource carries no `password` property, matching the Back Office add-customer form. An
operator gets a customer to a working password with `sendPasswordToken`, which mails them a
password-restore link.

## Endpoints

| Method | Path | Notes |
|---|---|---|
| `GET` | `/customers` | Paginated and filterable collection |
| `GET` | `/customers/{customerReference}` | Single customer |
| `POST` | `/customers` | Creates a customer via `registerCustomer()` |
| `PATCH` | `/customers/{customerReference}` | Partial update |
| `DELETE` | `/customers/{customerReference}` | GDPR anonymization — retains the record and scrubs the personal data |

`customerReference` is the sole customer identifier in every request and response payload.

### Collection parameters

| Parameter | Effect |
|---|---|
| `page[limit]`, `page[offset]` | Pagination (defaults 10 / 0) |
| `q` | Free text, OR-matched against email, first name and last name |
| `filter[customers.email]` | Exact email match |
| `filter[customers.customerReference]` | Exact customer-reference match |
| `filter[customers.firstName]`, `filter[customers.lastName]` | Partial name match |
| `filter[customers.includeAnonymized]` | Set it to bring anonymized customers into the result; the default result is the active customers |
| `sort` | `customerReference`, `createdAt`, `email`, `firstName`, `lastName`, `registered`; prefix with `-` for descending |

### DELETE semantics

`DELETE` invokes `CustomerFacade::anonymizeCustomer()`. The customer row is retained with
`anonymized_at` set, personal data scrubbed and the email replaced by a generated value. The configured
`CustomerAnonymizerPluginInterface` stack runs inside the same transaction — on this project that means
newsletter unsubscribe, customer-group removal and availability-notification cleanup.

## Installation

```bash
composer require spryker-feature/customer-experience-management
```

The resource is discovered automatically from `resources/api/backend/`. After installing, regenerate the
Backend API resource classes:

```bash
GLUE_APPLICATION=GLUE_BACKEND vendor/bin/glue api:generate
```

## Documentation

[Spryker Documentation](https://docs.spryker.com)
