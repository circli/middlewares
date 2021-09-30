<?php declare(strict_types=1);

namespace Circli\Middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JsonContentHandler implements MiddlewareInterface
{
	/**
	 * Process an incoming server request and return a response, optionally delegating
	 * response creation to a handler.
	 */
	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		if (!\in_array($request->getMethod(), ['GET', 'HEAD'])) {
			$parts = explode(';', $request->getHeaderLine('Content-Type'));
			$mime = strtolower(trim(array_shift($parts)));

			if (preg_match('~^application/([a-z.]+\+)?json($|;)~', $mime) && !$request->getParsedBody()) {
				$body = [];
				if ($request->getBody()->getSize()) {
					$body = json_decode((string)$request->getBody(), true);
					if (json_last_error()) {
						throw new \RuntimeException('Error parsing JSON: ' . json_last_error_msg());
					}
				}
				$request = $request->withParsedBody($body);
			}
		}

		return $handler->handle($request);
	}
}
