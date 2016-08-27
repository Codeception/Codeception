<?php
namespace Codeception\Util;

/**
 * Class containing constants of HTTP Status Codes
 * and method to print HTTP code with its description.
 *
 * Usage:
 *
 * ```php
 * <?php
 * use \Codeception\Util\HttpCode;
 *
 * // using REST, PhpBrowser, or any Framework module
 * $I->seeResponseCodeIs(HttpCode::OK);
 * $I->dontSeeResponseCodeIs(HttpCode::NOT_FOUND);
 * ```
 *
 *
 */
class HttpCode
{
    const SWITCHING_PROTOCOLS = 101;
    const PROCESSING = 102;            // RFC2518
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;
    const MULTI_STATUS = 207;          // RFC4918
    const ALREADY_REPORTED = 208;      // RFC5842
    const IM_USED = 226;               // RFC3229
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const RESERVED = 306;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENTLY_REDIRECT = 308;  // RFC7238
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 409;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUEST_URI_TOO_LONG = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;
    const I_AM_A_TEAPOT = 418;                                               // RFC2324
    const UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const LOCKED = 423;                                                      // RFC4918
    const FAILED_DEPENDENCY = 424;                                           // RFC4918
    const RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const UPGRADE_REQUIRED = 426;                                            // RFC2817
    const PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    const VERSION_NOT_SUPPORTED = 505;
    const VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const LOOP_DETECTED = 508;                                               // RFC5842
    const NOT_EXTENDED = 510;                                                // RFC2774
    const NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    private static $codes = [
     100 => 'Continue',
     102 => 'Processing',
     200 => 'OK',
     201 => 'Created',
     202 => 'Accepted',
     203 => 'Non-Authoritative Information',
     204 => 'No Content',
     205 => 'Reset Content',
     206 => 'Partial Content',
     207 => 'Multi-Status',
     208 => 'Already Reported',
     226 => 'IM Used',
     300 => 'Multiple Choices',
     301 => 'Moved Permanently',
     302 => 'Found',
     303 => 'See Other',
     304 => 'Not Modified',
     305 => 'Use Proxy',
     307 => 'Temporary Redirect',
     308 => 'Permanent Redirect',
     400 => 'Bad Request',
     401 => 'Unauthorized',
     402 => 'Payment Required',
     403 => 'Forbidden',
     404 => 'Not Found',
     405 => 'Method Not Allowed',
     406 => 'Not Acceptable',
     407 => 'Proxy Authentication Required',
     408 => 'Request Timeout',
     409 => 'Conflict',
     410 => 'Gone',
     411 => 'Length Required',
     412 => 'Precondition Failed',
     413 => 'Request Entity Too Large',
     414 => 'Request-URI Too Long',
     415 => 'Unsupported Media Type',
     416 => 'Requested Range Not Satisfiable',
     417 => 'Expectation Failed',
     421 => 'Misdirected Request',
     422 => 'Unprocessable Entity',
     423 => 'Locked',
     424 => 'Failed Dependency',
     426 => 'Upgrade Required',
     428 => 'Precondition Required',
     429 => 'Too Many Requests',
     431 => 'Request Header Fields Too Large',
     500 => 'Internal Server Error',
     501 => 'Not Implemented',
     502 => 'Bad Gateway',
     503 => 'Service Unavailable',
     504 => 'Gateway Timeout',
     505 => 'HTTP Version Not Supported',
     506 => 'Variant Also Negotiates',
     507 => 'Insufficient Storage',
     508 => 'Loop Detected',
     510 => 'Not Extended',
     511 => 'Network Authentication Required'
    ];

    /**
     * Returns string with HTTP code and its description
     *
     * ```php
     * <?php
     * HttpCode::getDescription(200); // '200 (OK)'
     * HttpCode::getDescription(401); // '401 (Unauthorized)'
     * ```
     *
     * @param $code
     * @return mixed
     */
    public static function getDescription($code)
    {
        if (isset(self::$codes[$code])) {
            return sprintf('%d (%s)', $code, self::$codes[$code]);
        }
        return $code;
    }
}
