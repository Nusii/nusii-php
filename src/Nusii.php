<?php

declare(strict_types=1);

namespace Nusii;

use GuzzleHttp\Client as GuzzleClient;
use Nusii\Concerns\SendsRequests;
use Nusii\Resources\AccountResource;
use Nusii\Resources\ClientResource;
use Nusii\Resources\LineItemResource;
use Nusii\Resources\ProposalActivityResource;
use Nusii\Resources\ProposalResource;
use Nusii\Resources\SectionResource;
use Nusii\Resources\TemplateResource;
use Nusii\Resources\ThemeResource;
use Nusii\Resources\UserResource;
use Nusii\Resources\WebhookEndpointResource;

class Nusii
{
    use SendsRequests;

    public const string VERSION = '1.1.0';

    public ?int $rateLimitRemaining = null;
    public ?int $rateLimitRetryAfter = null;

    private GuzzleClient $httpClient;

    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl = 'https://app.nusii.com',
        ?GuzzleClient $client = null,
    ) {
        $this->httpClient = $client ?? new GuzzleClient();
    }

    public function accounts(): AccountResource
    {
        return new AccountResource($this);
    }

    public function clients(): ClientResource
    {
        return new ClientResource($this);
    }

    public function proposals(): ProposalResource
    {
        return new ProposalResource($this);
    }

    public function sections(): SectionResource
    {
        return new SectionResource($this);
    }

    public function lineItems(): LineItemResource
    {
        return new LineItemResource($this);
    }

    public function templates(): TemplateResource
    {
        return new TemplateResource($this);
    }

    public function proposalActivities(): ProposalActivityResource
    {
        return new ProposalActivityResource($this);
    }

    public function webhookEndpoints(): WebhookEndpointResource
    {
        return new WebhookEndpointResource($this);
    }

    public function users(): UserResource
    {
        return new UserResource($this);
    }

    public function themes(): ThemeResource
    {
        return new ThemeResource($this);
    }
}
