<?php declare(strict_types=1);

namespace Circli\Middlewares\Tests;

use Circli\Middlewares\JsonContentHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class JsonContentHandlerTest extends TestCase
{
	public function testEmptyBody(): void
	{
		$mockStream = $this->createMock(StreamInterface::class);
		$mockStream->expects($this->once())->method('getSize')->willReturn(0);
		$mockStream->expects($this->never())->method('__toString');

		$handler = new JsonContentHandler();
		$mockRequest = $this->createMock(ServerRequestInterface::class);
		$mockRequest->expects($this->once())->method('getMethod')->willReturn('POST');
		$mockRequest->expects($this->once())->method('getHeaderLine')->willReturn('application/json');
		$mockRequest->expects($this->once())->method('getBody')->willReturn($mockStream);
		$mockRequest->expects($this->once())->method('withParsedBody')->with([])->willReturn($mockRequest);

		$mockHandler = $this->createMock(RequestHandlerInterface::class);
		$mockHandler->expects($this->once())->method('handle')->with($mockRequest);

		$handler->process($mockRequest, $mockHandler);
	}

	public function testInvalidJson(): void
	{
		$mockStream = $this->createMock(StreamInterface::class);
		$mockStream->expects($this->once())->method('getSize')->willReturn(1);
		$mockStream->expects($this->once())->method('__toString')->willReturn('{');

		$handler = new JsonContentHandler();
		$mockRequest = $this->createMock(ServerRequestInterface::class);
		$mockRequest->expects($this->once())->method('getMethod')->willReturn('POST');
		$mockRequest->expects($this->once())->method('getHeaderLine')->willReturn('application/json');
		$mockRequest->expects($this->atLeast(2))->method('getBody')->willReturn($mockStream);
		$mockRequest->expects($this->never())->method('withParsedBody');

		$mockHandler = $this->createMock(RequestHandlerInterface::class);
		$mockHandler->expects($this->never())->method('handle');

		$this->expectException(\RuntimeException::class);

		$handler->process($mockRequest, $mockHandler);
	}

	public function testNotProcessGetAndHead(): void
	{
		$handler = new JsonContentHandler();
		$mockRequest = $this->createMock(ServerRequestInterface::class);
		$mockRequest->expects($this->once())->method('getMethod')->willReturn('GET');
		$mockRequest->expects($this->never())->method('withParsedBody');

		$mockHandler = $this->createMock(RequestHandlerInterface::class);
		$mockHandler->expects($this->once())->method('handle')->with($mockRequest);

		$handler->process($mockRequest, $mockHandler);
	}

	public function testSizeNull(): void
	{
		$payload = ['test' => 1];
		$payloadStr = json_encode($payload);

		$mockStream = $this->createMock(StreamInterface::class);
		$mockStream->expects($this->once())->method('getSize')->willReturn(null);
		$mockStream->expects($this->once())->method('getContents')->willReturn($payloadStr);
		$mockStream->expects($this->once())->method('__toString')->willReturn($payloadStr);

		$handler = new JsonContentHandler();
		$mockRequest = $this->createMock(ServerRequestInterface::class);
		$mockRequest->expects($this->once())->method('getMethod')->willReturn('POST');
		$mockRequest->expects($this->once())->method('getHeaderLine')->willReturn('application/json');
		$mockRequest->expects($this->exactly(3))->method('getBody')->willReturn($mockStream);
		$mockRequest->expects($this->once())->method('withParsedBody')->with($payload)->willReturn($mockRequest);

		$mockHandler = $this->createMock(RequestHandlerInterface::class);
		$mockHandler->expects($this->once())->method('handle')->with($mockRequest);

		$handler->process($mockRequest, $mockHandler);
	}
}
