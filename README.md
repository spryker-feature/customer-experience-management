# CustomerExperienceManagement Module
[![Latest Stable Version](https://poser.pugx.org/spryker-feature/customer-experience-management/v/stable.svg)](https://packagist.org/packages/spryker-feature/customer-experience-management)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.3-8892BF.svg)](https://php.net/)

CustomerExperienceManagement exposes customer lifecycle management over the Spryker Backend API
(API Platform), covering the operations the Zed Back Office offers: listing customers with pagination
and filtering, reading a single customer, creating a customer through the Back Office registration
flow, partially updating a customer, and GDPR anonymization. It also exposes `company-users`, the
B2B side of the same back office, which links customers to companies, business units and roles.

The module is an API surface over `Spryker\Zed\Customer\Business\CustomerFacadeInterface`, which
keeps validation, email uniqueness and the anonymizer plugin stack behaving exactly as they do in
the Back Office. `company-users` sits the same way over
`Spryker\Zed\CompanyUser\Business\CompanyUserFacadeInterface` and
`Spryker\Zed\BusinessOnBehalf\Business\BusinessOnBehalfFacadeInterface`.

The resource carries no `password` property, matching the Back Office add-customer form. An
operator gets a customer to a working password with `sendPasswordToken`, which mails them a
password-restore link.

## Endpoints

### Customers

| Method | Path | Notes |
|---|---|---|
| `GET` | `/customers` | Paginated and filterable collection |
| `GET` | `/customers/{customerReference}` | Single customer |
| `POST` | `/customers` | Creates a customer via `registerCustomer()` |
| `PATCH` | `/customers/{customerReference}` | Partial update |
| `DELETE` | `/customers/{customerReference}` | GDPR anonymization — retains the record and scrubs the personal data |

`customerReference` is the sole customer identifier in every request and response payload.

### Customers: collection parameters

| Parameter | Effect |
|---|---|
| `page[limit]`, `page[offset]` | Pagination (defaults 10 / 0) |
| `q` | Free text, OR-matched against email, first name and last name |
| `filter[customers.email]` | Exact email match |
| `filter[customers.customerReference]` | Exact customer-reference match |
| `filter[customers.firstName]`, `filter[customers.lastName]` | Partial name match |
| `sort` | `customerReference`, `createdAt`, `email`, `firstName`, `lastName`, `registered`; prefix with `-` for descending |

### Customers: DELETE semantics

`DELETE` invokes `CustomerFacade::anonymizeCustomer()`. The customer row is retained with
`anonymized_at` set, personal data scrubbed and the email replaced by a generated value. The configured
`CustomerAnonymizerPluginInterface` stack runs inside the same transaction — on this project that means
newsletter unsubscribe, customer-group removal and availability-notification cleanup.

### Company users

A company user links one customer to one business unit of one company and carries the roles that
say what they may do there. A customer can have several — one per company and business unit
combination — and at most one of them is their default.

| Method | Path | Notes |
|---|---|---|
| `GET` | `/company-users` | Paginated and filterable collection, across all companies |
| `GET` | `/company-users/{uuid}` | Single company user |
| `POST` | `/company-users` | Creates a company user, optionally creating its customer too |
| `PATCH` | `/company-users/{uuid}` | Partial update; never touches `isActive` or `isDefault` |
| `DELETE` | `/company-users/{uuid}` | Deletes the company user and keeps the customer |
| `POST` | `/company-users/{uuid}/set-status` | Activates or deactivates it — body carries `isActive` |
| `POST` | `/company-users/{uuid}/set-default` | Sets or unsets it as its customer's default — body carries `isDefault` |

`uuid` addresses the company user in every operation. It is a top-level resource, not nested under
`/customers`; use `filter[company-users.customerReference]` to narrow it to one customer.

### Company users: collection parameters

| Parameter | Effect |
|---|---|
| `page[limit]`, `page[offset]` | Pagination (defaults 10 / 0) |
| `q` | Free text, OR-matched against the customer's first and last name |
| `filter[company-users.customerReference]` | Company users of one customer |
| `filter[company-users.companyUuid]` | Company users of one company |
| `filter[company-users.companyBusinessUnitUuid]` | Company users of one business unit |
| `filter[company-users.isActive]` | Only active, or only inactive, company users |
| `sort` | `companyName`, `customerEmail`, `customerFirstName`, `customerLastName`; prefix with `-` for descending |

Filters are combined with AND. An unknown `companyUuid` or `companyBusinessUnitUuid` matches no
company and returns an empty collection rather than a 404. Item reads return company users of
anonymized customers and of inactive or unapproved companies, so an operator can inspect one
before re-activating it; the collection does not.

### Company users: create

Two mutually exclusive paths, and `customerReference` wins — when it is present the `customer`
object is ignored entirely:

- **Existing customer** — send `customerReference` and nothing else about them. None of their data
  is read back or changed, and no mail is sent.
- **New customer** — omit `customerReference` and send a `customer` object; `email`, `firstName`,
  `lastName` and `salutation` are required on that path. The customer gets the usual registration
  mail, plus a password-restore mail if `sendPasswordToken` is set. As with `/customers`, no
  password is ever accepted in the request.

Sending neither is rejected with code `1218`.

A new company user is always created `isActive: true` and `isDefault: false` — including when it is
the customer's first, since no company user becomes default automatically.

### Company users: status and default

`isActive` and `isDefault` are read-only properties. `PATCH` never changes them; the two named
`POST` operations do, each taking a JSON:API document whose `attributes` carry that one boolean:

```json
{ "data": { "type": "company-users", "attributes": { "isActive": false } } }
```

A deactivated company user is kept in full but can no longer act on behalf of the company, and the
company drops out of that customer's switcher. Setting a default unsets the previous one in the
same request; unsetting leaves the customer with no default at all. Sending the state a company
user already has succeeds and changes nothing.

### Company users: cross-entity validation

`company-users.validation.yml` validates one request in isolation. The rules that span entities are
enforced by `Spryker\Zed\CompanyUser`'s `CompanyUserSavePreCheckPluginInterface` stack, wired in
`Pyz\Zed\CompanyUser\CompanyUserDependencyProvider::getCompanyUserSavePreCheckPlugins()`:

| Plugin | Rule |
|---|---|
| `CheckCompanyUserUniquenessCompanyUserSavePreCheckPlugin` | One company user per customer per business unit |
| `CompanyExistsCompanyUserSavePreCheckPlugin` | The company exists |
| `CompanyBusinessUnitBelongsToCompanyCompanyUserSavePreCheckPlugin` | The business unit belongs to that company |
| `CompanyRolesBelongToCompanyCompanyUserSavePreCheckPlugin` | Every role belongs to that company |

A failure rejects the whole request and writes nothing — including the new customer account, when
one was being created in the same call. `companyRoleUuids` is a full replacement on update: the list
you send becomes the complete new role set, so include every role the company user should keep.

### Company users: error codes

| Code | Status | Meaning |
|---|---|---|
| `1202` | 422 | A pre-check plugin or the facade rejected the request |
| `1203` | 400 | `sort` names a field outside the allow list above |
| `1213` | 404 | No company user matches the uuid |
| `1214` | 422 | The referenced company does not exist |
| `1215` | 422 | The referenced business unit does not exist |
| `1216` | 422 | A referenced role does not exist |
| `1217` | 400 | `/set-status` body is missing, unparseable, or `isActive` is absent or not a boolean |
| `1218` | 422 | Neither `customerReference` nor a usable `customer` object was sent |
| `1219` | 400 | `/set-default` body is missing, unparseable, or `isDefault` is absent or not a boolean |

### Company users: DELETE semantics

`DELETE` invokes `CompanyUserFacade::deleteCompanyUser()`. What the company user owns inside the
company — shopping lists, shared carts, quote requests, merchant relation requests — is cleaned up
first, then the company user itself is removed. The customer is kept: their account, their data and
their other company users are untouched, and they can be given a new company user afterwards. To
revoke access without deleting anything, deactivate it through `/set-status` instead.

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
