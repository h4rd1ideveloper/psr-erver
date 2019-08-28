<?php

declare(strict_types=1);

namespace App\http;

use App\assets\lib\Helpers;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

/**
 * PSR-7 response implementation.
 */
class Response implements ResponseInterface
{


    /** Map of standard HTTP status code/reason phrases */
    private const PHRASES = array(
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-status',
        208 => 'Already Reported',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        306 => 'Switch Proxy',
        307 => 'Temporary Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Time-out',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Large',
        415 => 'Unsupported Media Type',
        416 => 'Requested range not satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Unordered Collection',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Time-out',
        505 => 'HTTP Version not supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        511 => 'Network Authentication Required',
    );

    /** @var string */
    private $reasonPhrase = '';

    /** @var int */
    private $statusCode = 200;
    /** @var array Map of all registered headers, as original name => array of values */
    private $headers = array();
    /** @var array Map of lowercase header name => original name at registration */
    private $headerNames = array();
    /** @var string */
    private $protocol = '1.1';
    /** @var StreamInterface */
    private $stream;

    /**
     * @param int $status Status code
     * @param array $headers Response headers
     * @param string|null|resource|StreamInterface $body Response body
     * @param string $version Protocol version
     * @param string|null $reason Reason phrase (when empty a default will be used based on the status code)
     */
    public function __construct(
        int $status = 200,
        array $headers = array(),
        $body = null,
        string $version = '1.1',
        string $reason = null
    )
    {
        $this->assertStatusCodeRange($status);

        $this->statusCode = $status;

        if ($body !== '' && $body !== null) {
            $this->stream = stream_for($body);
        }

        $this->setHeaders($headers);
        if ($reason == '' && isset(self::PHRASES[$this->statusCode])) {
            $this->reasonPhrase = self::PHRASES[$this->statusCode];
        } else {
            $this->reasonPhrase = (string)$reason;
        }

        $this->protocol = $version;
    }

    private function assertStatusCodeRange(int $statusCode)
    {
        if ($statusCode < 100 || $statusCode >= 600) {
            throw new InvalidArgumentException('Status code must be an integer value between 1xx and 5xx.');
        }
    }

    private function setHeaders(array $headers): void
    {
        $this->headerNames = array();
        $this->headers = array();
        foreach ($headers as $header => $value) {
            if (is_int($header)) {
                // Numeric array keys are converted to int by PHP but having a header name '123' is not forbidden by the spec
                // and also allowed in withHeader(). So we need to cast it to string again for the following assertion to pass.
                $header = (string)$header;
            }
            $this->assertHeader($header);
            $value = $this->normalizeHeaderValue($value);
            $normalized = strtolower($header);
            if (isset($this->headerNames[$normalized])) {
                $header = $this->headerNames[$normalized];
                $this->headers[$header] = array_merge($this->headers[$header], $value);
            } else {
                $this->headerNames[$normalized] = $header;
                $this->headers[$header] = $value;
            }
        }
        $this->applyHeaders();
    }

    private function assertHeader($header)
    {
        if (!is_string($header)) {
            throw new InvalidArgumentException(sprintf(
                'Header name must be a string but %s provided.',
                is_object($header) ? get_class($header) : gettype($header)
            ));
        }

        if ($header === '') {
            throw new InvalidArgumentException('Header name can not be empty.');
        }
    }

    private function normalizeHeaderValue($value): array
    {
        if (!is_array($value)) {
            return $this->trimHeaderValues([$value]);
        }

        if (count($value) === 0) {
            throw new InvalidArgumentException('Header value can not be an empty array.');
        }

        return $this->trimHeaderValues($value);
    }

    /**
     * Trims whitespace from the header values.
     *
     * Spaces and tabs ought to be excluded by parsers when extracting the field value from a header field.
     *
     * header-field = field-name ":" OWS field-value OWS
     * OWS          = *( SP / HTAB )
     *
     * @param string[] $values Header values
     *
     * @return string[] Trimmed header values
     *
     * @see https://tools.ietf.org/html/rfc7230#section-3.2.4
     */
    private function trimHeaderValues(array $values): array
    {
        return array_map(function ($value) {
            if (!is_string($value) && !is_numeric($value)) {
                throw new InvalidArgumentException(sprintf(
                    'Header value must be a string or numeric but %s provided.',
                    is_object($value) ? get_class($value) : gettype($value)
                ));
            }

            return trim((string)$value, " \t");
        }, $values);
    }

    private function applyHeaders()
    {
        if (count($this->headers)) {
            foreach ($this->headers as $key => $value) {
                Helpers::setHeader(sprintf("%s:%s",$key, $this->getHeaderLine($key)));
            }
        }
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getReasonPhrase()
    {
        return $this->reasonPhrase;
    }

    public function withStatus($code, $reasonPhrase = '')
    {
        $this->assertStatusCodeIsInteger($code);
        $code = (int)$code;
        $this->assertStatusCodeRange($code);

        $new = clone $this;
        $new->statusCode = $code;
        if ($reasonPhrase == '' && isset(self::PHRASES[$new->statusCode])) {
            $reasonPhrase = self::PHRASES[$new->statusCode];
        }
        $new->reasonPhrase = $reasonPhrase;
        return $new;
    }

    private function assertStatusCodeIsInteger($statusCode)
    {
        if (filter_var($statusCode, FILTER_VALIDATE_INT) === false) {
            throw new InvalidArgumentException('Status code must be an integer value.');
        }
    }

    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    public function withProtocolVersion($version)
    {
        if ($this->protocol === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasHeader($header)
    {
        return isset($this->headerNames[strtolower($header)]);
    }

    public function getHeaderLine($header)
    {
        return implode(', ', $this->getHeader($header));
    }

    public function getHeader($header)
    {
        $header = strtolower($header);
        if (!isset($this->headerNames[$header])) {
            return array();
        }

        $header = $this->headerNames[$header];

        return $this->headers[$header];
    }

    public function withHeader($header, $value)
    {
        $this->assertHeader($header);
        $value = $this->normalizeHeaderValue($value);
        $normalized = strtolower($header);
        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            unset($new->headers[$new->headerNames[$normalized]]);
        }
        $new->headerNames[$normalized] = $header;
        $new->headers[$header] = $value;
        $new->applyHeaders();
        return $new;
    }

    public function withAddedHeader($header, $value)
    {
        $this->assertHeader($header);
        $value = $this->normalizeHeaderValue($value);
        $normalized = strtolower($header);

        $new = clone $this;
        if (isset($new->headerNames[$normalized])) {
            $header = $this->headerNames[$normalized];
            $new->headers[$header] = array_merge($this->headers[$header], $value);
        } else {
            $new->headerNames[$normalized] = $header;
            $new->headers[$header] = $value;
        }
        $new->applyHeaders();
        return $new;
    }

    public function withoutHeader($header)
    {
        $normalized = strtolower($header);

        if (!isset($this->headerNames[$normalized])) {
            return $this;
        }

        $header = $this->headerNames[$normalized];

        $new = clone $this;
        unset($new->headers[$header], $new->headerNames[$normalized]);
        $new->applyHeaders();
        return $new;
    }

    public function getBody()
    {
        if (!$this->stream) {
            $this->stream = stream_for('');
        }
        return $this->stream;
    }

    public function withBody(StreamInterface $body)
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;
        return $new;
    }
}
