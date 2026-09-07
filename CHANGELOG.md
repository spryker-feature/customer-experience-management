# Changelog

All notable changes to this project will be documented in this file.

## 1.0.0

### Added

- Introduced the `customers` Backend API resource: `GET /customers`, `GET /customers/{customerReference}`,
  `POST /customers`, `PATCH /customers/{customerReference}` and `DELETE /customers/{customerReference}`.
- Collection pagination and filtering by search terms, email and customer reference.
- `DELETE` performs GDPR anonymization through `CustomerFacade::anonymizeCustomer()` instead of a hard delete.
