# Nusii PHP

A PHP client library for the [Nusii](https://nusii.com) proposal software API.

Requires **PHP 8.3+** and uses [Guzzle](https://docs.guzzlephp.org/) for HTTP requests.

## Installation

Install via Composer:

```bash
composer require nusii/nusii-php
```

## Quick Start

```php
use Nusii\Nusii;

$nusii = new Nusii('your-api-key');

// List your clients
$clients = $nusii->clients()->list();

foreach ($clients as $client) {
    echo $client['name'] . "\n";
}
```

### Custom Base URL

For self-hosted or development environments:

```php
$nusii = new Nusii('your-api-key', baseUrl: 'http://localhost:3000');
```

## Resources

### Account

```php
// Get current account
$account = $nusii->accounts()->me();

echo $account['name'];       // "Acme Corp"
echo $account['email'];      // "owner@acme.com"
echo $account['currency'];   // "USD"
echo $account['subdomain'];  // "acme"
```

### Clients

```php
// List clients (paginated)
$clients = $nusii->clients()->list(page: 1, perPage: 10);

echo $clients->totalCount;   // 42
echo $clients->currentPage;  // 1
echo $clients->totalPages;   // 5

foreach ($clients as $client) {
    echo $client['name'];
}

// Get a single client
$client = $nusii->clients()->get(id: 123);

// Create a client
$client = $nusii->clients()->create([
    'name' => 'John',
    'surname' => 'Doe',
    'email' => 'john@example.com',
    'business' => 'Acme Inc',
    'telephone' => '555-1234',
    'address' => '123 Main St',
    'city' => 'New York',
    'postcode' => '10001',
    'country' => 'US',
    'state' => 'NY',
    'web' => 'https://acme.com',
    'currency' => 'USD',
    'locale' => 'en',
]);

// Update a client
$client = $nusii->clients()->update(123, [
    'name' => 'Jane',
    'business' => 'Updated Corp',
]);

// Delete a client
$nusii->clients()->delete(123);
```

### Proposals

```php
// List proposals
$proposals = $nusii->proposals()->list();

// Filter by status: draft, pending, accepted, rejected
$drafts = $nusii->proposals()->list(status: 'draft');

// Filter archived proposals
$archived = $nusii->proposals()->list(archived: true);

// Get a single proposal
$proposal = $nusii->proposals()->get(456);

// Create a proposal
$proposal = $nusii->proposals()->create([
    'title' => 'Website Redesign',
    'client_id' => 123,
    'expires_at' => '2025-12-31',
    'currency' => 'EUR',
    'theme' => 'clean',
]);

// Update a proposal
$proposal = $nusii->proposals()->update(456, [
    'title' => 'Updated Proposal Title',
]);

// Send a proposal
$result = $nusii->proposals()->send(
    id: 456,
    email: 'client@example.com',
    subject: 'Your Proposal',            // optional
    message: 'Please review attached.',   // optional
    cc: 'manager@example.com',            // optional
    bcc: 'archive@example.com',           // optional
);

// Archive a proposal
$proposal = $nusii->proposals()->archive(456);

// Delete a proposal
$nusii->proposals()->delete(456);
```

### Sections

```php
// List all sections
$sections = $nusii->sections()->list();

// List sections for a specific proposal
$sections = $nusii->sections()->list(proposalId: 456);

// List sections for a specific template
$sections = $nusii->sections()->list(templateId: 789);

// Include line items in the response
$sections = $nusii->sections()->list(proposalId: 456, includeLineItems: true);

// Get a single section
$section = $nusii->sections()->get(10);

// Create a section
$section = $nusii->sections()->create([
    'proposal_id' => 456,
    'title' => 'Project Scope',
    'body' => '<p>Detailed project scope description.</p>',
    'position' => 1,
    'section_type' => 'cost',    // "cost" or "text"
    'optional' => false,
    'include_total' => true,
    'page_break' => false,
]);

// Update a section
$section = $nusii->sections()->update(10, [
    'title' => 'Updated Section Title',
    'body' => '<p>Updated content.</p>',
]);

// Delete a section
$nusii->sections()->delete(10);
```

### Line Items

Line items belong to sections. They must be created under a section.

```php
// List line items for a section
$items = $nusii->lineItems()->listBySection(sectionId: 10);

// Get a single line item
$item = $nusii->lineItems()->get(50);

// Create a line item for a section
$item = $nusii->lineItems()->createForSection(sectionId: 10, attributes: [
    'name' => 'Design Work',
    'quantity' => 10,
    'amount' => 7500,          // in cents
    'cost_type' => 'fixed',    // "fixed" or "hourly"
    'per_type' => 'hour',
    'position' => 1,
]);

// Update a line item
$item = $nusii->lineItems()->update(50, [
    'name' => 'Updated Design Work',
    'quantity' => 20,
]);

// Delete a line item
$nusii->lineItems()->delete(50);
```

### Templates

Templates are read-only.

```php
// List templates
$templates = $nusii->templates()->list();

// Get a single template
$template = $nusii->templates()->get(5);

echo $template['name'];  // "Web Project Template"
```

### Proposal Activities

Proposal activities are read-only and track events like views, accepts, and rejections.

```php
// List all activities
$activities = $nusii->proposalActivities()->list();

// Filter by proposal
$activities = $nusii->proposalActivities()->list(proposalId: 456);

// Filter by client
$activities = $nusii->proposalActivities()->list(clientId: 123);

// Get a single activity
$activity = $nusii->proposalActivities()->get(1);

echo $activity['activity_type'];    // "viewed", "accepted", "rejected"
echo $activity['proposal_title'];   // "Website Redesign"
echo $activity['client_email'];     // "john@example.com"
```

### Users

Users are read-only.

```php
// List users on the account
$users = $nusii->users()->list();

foreach ($users as $user) {
    echo $user['email'];
    echo $user['name'];
}
```

### Themes

Themes are read-only. Note: this endpoint returns a plain array, not a paginated response.

```php
// List available themes
$themes = $nusii->themes()->list();

foreach ($themes as $theme) {
    echo $theme['id'];
    echo $theme['name'];  // "Classic", "Modern", etc.
}
```

### Webhook Endpoints

```php
// List webhook endpoints
$webhooks = $nusii->webhookEndpoints()->list();

// Get a single webhook endpoint
$webhook = $nusii->webhookEndpoints()->get(1);

// Create a webhook endpoint
$webhook = $nusii->webhookEndpoints()->create([
    'target_url' => 'https://example.com/webhooks/nusii',
    'events' => [
        'proposal_created',
        'proposal_accepted',
        'proposal_rejected',
        'proposal_sent',
    ],
]);

// Delete a webhook endpoint
$nusii->webhookEndpoints()->delete(1);
```

**Available webhook events:**

| Event | Description |
|-------|-------------|
| `proposal_created` | A proposal was created |
| `proposal_updated` | A proposal was updated |
| `proposal_destroyed` | A proposal was deleted |
| `proposal_accepted` | A proposal was accepted by the client |
| `proposal_rejected` | A proposal was rejected by the client |
| `proposal_sent` | A proposal was sent to the client |
| `client_created` | A client was created |
| `client_updated` | A client was updated |
| `client_destroyed` | A client was deleted |
| `proposal_activity_client_viewed` | A client viewed something |
| `proposal_activity_client_viewed_proposal` | A client viewed a proposal |

## Pagination

All list endpoints return a `PaginatedResponse` object that is both countable and iterable:

```php
$response = $nusii->clients()->list(page: 1, perPage: 10);

// Pagination metadata
$response->currentPage;   // 1
$response->nextPage;      // 2 (or null)
$response->prevPage;      // null (or int)
$response->totalPages;    // 5
$response->totalCount;    // 42

// Navigation helpers
$response->hasNextPage(); // true
$response->hasPrevPage(); // false

// Iterate directly
foreach ($response as $item) {
    echo $item['name'];
}

// Count
echo count($response); // items on current page

// Access raw data array
$response->data; // array of items
```

### Paginating Through All Results

```php
$page = 1;

do {
    $response = $nusii->clients()->list(page: $page, perPage: 50);

    foreach ($response as $client) {
        // Process each client
    }

    $page++;
} while ($response->hasNextPage());
```

## Error Handling

The library throws specific exceptions for different HTTP error codes:

```php
use Nusii\Exceptions\AuthenticationException;
use Nusii\Exceptions\NotFoundException;
use Nusii\Exceptions\ValidationException;
use Nusii\Exceptions\RateLimitException;
use Nusii\Exceptions\ServerException;
use Nusii\Exceptions\NusiiException;

try {
    $client = $nusii->clients()->get(99999);
} catch (AuthenticationException $e) {
    // 401 - Invalid API key
    echo $e->getMessage();
} catch (NotFoundException $e) {
    // 404 - Resource not found
    echo $e->getMessage();
} catch (ValidationException $e) {
    // 400, 403, 422 - Validation or permission errors
    echo $e->getMessage();
    echo $e->getCode();          // HTTP status code
    print_r($e->responseBody);   // Raw response body
} catch (RateLimitException $e) {
    // 429 - Rate limit exceeded (100 requests per 30 seconds)
    echo $e->retryAfter;         // Seconds to wait before retrying
} catch (ServerException $e) {
    // 500, 503 - Server errors
    echo $e->getMessage();
} catch (NusiiException $e) {
    // Catch-all for any other API errors
    echo $e->getMessage();
}
```

All exceptions extend `NusiiException`, so you can catch that to handle any API error.

## Rate Limiting

The Nusii API allows 100 requests per 30 seconds. Rate limit information is tracked on the client instance:

```php
$nusii->clients()->list();

echo $nusii->rateLimitRemaining;   // 99
echo $nusii->rateLimitRetryAfter;  // null (or seconds to wait)
```

## Testing

Run the unit tests:

```bash
composer test
```

Or with Pest directly:

```bash
./vendor/bin/pest
```

### Integration Tests

Integration tests run against a live API server. Set the environment variables and run:

```bash
NUSII_API_KEY=your-key NUSII_API_URL=http://localhost:3000 ./vendor/bin/pest tests/IntegrationTest.php
```

Integration tests are automatically skipped when `NUSII_API_KEY` is not set.

## Authentication

The library uses token authentication. Include your API key when creating the client:

```php
$nusii = new Nusii('your-api-key');
```

All requests include the header:

```
Authorization: Token token=your-api-key
```

You can find your API key in your Nusii account settings under **Integrations**.

## Requirements

- PHP 8.3 or higher
- Guzzle 7.0 or higher

## License

MIT
