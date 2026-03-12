<?php

declare(strict_types=1);

use Nusii\Exceptions\AuthenticationException;
use Nusii\Exceptions\BadRequestException;
use Nusii\Exceptions\ForbiddenException;
use Nusii\Exceptions\GoneException;
use Nusii\Exceptions\MethodNotAllowedException;
use Nusii\Exceptions\NotAcceptableException;
use Nusii\Exceptions\NotFoundException;
use Nusii\Exceptions\NusiiException;
use Nusii\Exceptions\PaymentRequiredException;
use Nusii\Exceptions\RateLimitException;
use Nusii\Exceptions\ServerException;
use Nusii\Exceptions\ServiceUnavailableException;
use Nusii\Exceptions\UnprocessableEntityException;
use Nusii\Exceptions\ValidationException;
use Nusii\Nusii;
use Tests\TestDoubles\MockClient;
use Tests\TestDoubles\TestResponse;

describe('Error Handling', function () {
    it('throws AuthenticationException on 401', function () {
        $mock = MockClient::create([
            TestResponse::error(401, 'Invalid API key'),
        ]);

        $nusii = new Nusii('bad-key', client: $mock->client());

        expect(fn () => $nusii->clients()->list())
            ->toThrow(AuthenticationException::class, 'Invalid API key');
    });

    it('throws NotFoundException on 404', function () {
        $mock = MockClient::create([
            TestResponse::error(404, 'Client not found'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->get(99999))
            ->toThrow(NotFoundException::class, 'Client not found');
    });

    it('throws BadRequestException on 400', function () {
        $mock = MockClient::create([
            TestResponse::error(400, 'Name is required'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->create([]))
            ->toThrow(BadRequestException::class, 'Name is required');
    });

    it('throws PaymentRequiredException on 402', function () {
        $mock = MockClient::create([
            TestResponse::error(402, 'Payment required'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->list())
            ->toThrow(PaymentRequiredException::class, 'Payment required');
    });

    it('throws ForbiddenException on 403', function () {
        $mock = MockClient::create([
            TestResponse::error(403, 'Forbidden'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->list())
            ->toThrow(ForbiddenException::class, 'Forbidden');
    });

    it('throws MethodNotAllowedException on 405', function () {
        $mock = MockClient::create([
            TestResponse::error(405, 'Method not allowed'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->list())
            ->toThrow(MethodNotAllowedException::class, 'Method not allowed');
    });

    it('throws NotAcceptableException on 406', function () {
        $mock = MockClient::create([
            TestResponse::error(406, 'Not acceptable'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->list())
            ->toThrow(NotAcceptableException::class, 'Not acceptable');
    });

    it('throws GoneException on 410', function () {
        $mock = MockClient::create([
            TestResponse::error(410, 'Gone'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->list())
            ->toThrow(GoneException::class, 'Gone');
    });

    it('throws UnprocessableEntityException on 422', function () {
        $mock = MockClient::create([
            TestResponse::error(422, 'Email is invalid'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->create(['email' => 'not-an-email']))
            ->toThrow(UnprocessableEntityException::class, 'Email is invalid');
    });

    it('throws RateLimitException on 429', function () {
        $mock = MockClient::create([
            TestResponse::json(429, ['error' => 'Rate limit exceeded'], [
                'x-ratelimit-retry-after' => '15',
            ]),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        try {
            $nusii->clients()->list();
            $this->fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            expect($e->getMessage())->toBe('Rate limit exceeded');
            expect($e->getCode())->toBe(429);
            expect($e->retryAfter)->toBe(15);
        }
    });

    it('throws ServerException on 500', function () {
        $mock = MockClient::create([
            TestResponse::error(500, 'Internal Server Error'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->list())
            ->toThrow(ServerException::class, 'Internal Server Error');
    });

    it('throws ServiceUnavailableException on 503', function () {
        $mock = MockClient::create([
            TestResponse::error(503, 'Service Unavailable'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        expect(fn () => $nusii->clients()->list())
            ->toThrow(ServiceUnavailableException::class, 'Service Unavailable');
    });

    it('includes response body in exception', function () {
        $mock = MockClient::create([
            TestResponse::error(422, 'Validation failed'),
        ]);

        $nusii = new Nusii('test-key', client: $mock->client());

        try {
            $nusii->clients()->create([]);
            $this->fail('Expected UnprocessableEntityException');
        } catch (UnprocessableEntityException $e) {
            expect($e->responseBody)->toBe(['error' => 'Validation failed']);
            expect($e->getCode())->toBe(422);
        }
    });

    it('all exceptions extend NusiiException', function () {
        expect(new AuthenticationException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new BadRequestException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new ForbiddenException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new GoneException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new MethodNotAllowedException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new NotAcceptableException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new NotFoundException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new PaymentRequiredException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new RateLimitException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new ServerException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new ServiceUnavailableException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new UnprocessableEntityException('test'))->toBeInstanceOf(NusiiException::class);
        expect(new ValidationException('test'))->toBeInstanceOf(NusiiException::class);
    });
});
