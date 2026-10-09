# Changelog

## 1.3.0

- Add a `publicTemplates` argument to `templates()->list()` that lists Nusii's public templates instead of the account's own.

## 1.2.0

- Document the `range` cost type and the `maximum_amount` attribute for price range line items. Attributes pass through, so no code change is required; the new `maximum_amount_in_cents` / `maximum_amount_formatted` (line items) and `maximum_total_in_cents` / `maximum_total_formatted` (sections) response fields are already available.

## 1.1.0

- Add `BadRequestException` for HTTP 400 responses
- Add `PaymentRequiredException` for HTTP 402 responses
- Add `ForbiddenException` for HTTP 403 responses
- Add `MethodNotAllowedException` for HTTP 405 responses
- Add `NotAcceptableException` for HTTP 406 responses
- Add `GoneException` for HTTP 410 responses
- Add `UnprocessableEntityException` for HTTP 422 responses
- Add `ServiceUnavailableException` for HTTP 503 responses

## 1.0.0

- Initial release
