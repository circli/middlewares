<?php declare(strict_types=1);

namespace Circli\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResponseSigningMiddleware implements MiddlewareInterface
{
    /** @var string */
    private $secret;

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = $handler->handle($request);
		$responseBody = $response->getBody()->__toString();
		$responseHash = hash_hmac('sha384', $responseBody, $this->secret);

		return $response->withHeader('X-Response-Hash', $responseHash);
	}
}
