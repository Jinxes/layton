<?php
namespace Layton\Library\Http;

use InvalidArgumentException;
use \Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    /**
     * Uri scheme (without "://" suffix)
     *
     * @var string
     */
    protected $scheme = '';

    /**
     * Uri user
     *
     * @var string
     */
    protected $user = '';

    /**
     * Uri password
     *
     * @var string
     */
    protected $password = '';

    /**
     * Uri host
     *
     * @var string
     */
    protected $host = '';

    /**
     * Uri port number
     *
     * @var null|int
     */
    protected $port;

    /**
     * Uri base path
     *
     * @var string
     */
    protected $basePath = '';

    /**
     * Uri path
     *
     * @var string
     */
    protected $path = '';

    /**
     * Uri query string (without "?" prefix)
     *
     * @var string
     */
    protected $query = '';

    /**
     * Uri fragment string (without "#" prefix)
     *
     * @var string
     */
    protected $fragment = '';

    public static function createFromString($uri)
    {
        if (!is_string($uri) && !method_exists($uri, '__toString')) {
            throw new InvalidArgumentException('Uri must be a string');
        }

        $parts = parse_url($uri);
        $scheme = isset($parts['scheme']) ? $parts['scheme'] : '';
        $user = isset($parts['user']) ? $parts['user'] : '';
        $pass = isset($parts['pass']) ? $parts['pass'] : '';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $port = isset($parts['port']) ? $parts['port'] : null;
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? $parts['query'] : '';
        $fragment = isset($parts['fragment']) ? $parts['fragment'] : '';

        return new static($scheme, $host, $port, $path, $query, $fragment, $user, $pass);
    }

    /**
     * Create new Uri.
     *
     * @param string $scheme   Uri scheme.
     * @param string $host     Uri host.
     * @param int    $port     Uri port number.
     * @param string $path     Uri path.
     * @param string $query    Uri query string.
     * @param string $fragment Uri fragment.
     * @param string $user     Uri user.
     * @param string $password Uri password.
     */
    public function __construct(
        $scheme,
        $host,
        $port = null,
        $path = '/',
        $query = '',
        $fragment = '',
        $user = '',
        $password = ''
    ) {
        $this->scheme = $scheme;
        $this->host = $host;
        $this->port = $port;
        $this->path = empty($path) ? '/' : $path;
        $this->query = $query;
        $this->fragment = $fragment;
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Retrieve the scheme component of the URI.
     *
     * If no scheme is present, return an empty string.
     * @see https://tools.ietf.org/html/rfc3986#section-3.1
     * 
     * @return string The URI scheme.
     */
    public function getScheme()
    {

    }

    /**
     * Retrieve the authority component of the URI.
     *
     * If no authority information is present, return an empty string.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-3.2
     * @return string The URI authority, in "[user-info@]host[:port]" format.
     */
    public function getAuthority()
    {

    }

    /**
     * Retrieve the user information component of the URI.
     *
     * If no user information is present, return an empty
     * string.
     *
     * If a user is present in the URI, this will return that value;
     * additionally, if the password is also present, it will be appended to the
     * user value, with a colon (":") separating the values.
     * 
     * @return string The URI user information, in "username[:password]" format.
     */
    public function getUserInfo()
    {

    }

    /**
     * Retrieve the host component of the URI.
     *
     * If no host is present, return an empty string.
     * 
     * @see http://tools.ietf.org/html/rfc3986#section-3.2.2
     * @return string The URI host.
     */
    public function getHost()
    {

    }

    /**
     * Retrieve the port component of the URI.
     * 
     * @return null|int The URI port.
     */
    public function getPort()
    {

    }

    /**
     * Retrieve the path component of the URI.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.3
     * @return string The URI path.
     */
    public function getPath()
    {

    }

    /**
     * Retrieve the query string of the URI.
     *
     * If no query string is present, return an empty string.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.4
     * @return string The URI query string.
     */
    public function getQuery()
    {

    }

    /**
     * Retrieve the fragment component of the URI.
     *
     * If no fragment is present, return an empty string.
     * 
     * @see https://tools.ietf.org/html/rfc3986#section-2
     * @see https://tools.ietf.org/html/rfc3986#section-3.5
     * @return string The URI fragment.
     */
    public function getFragment()
    {

    }

    /**
     * Return an instance with the specified scheme.
     * An empty scheme is equivalent to removing the scheme.
     *
     * @param string $scheme The scheme to use with the new instance.
     * @return static A new instance with the specified scheme.
     * @throws \InvalidArgumentException for invalid or unsupported schemes.
     */
    public function withScheme($scheme)
    {

    }

    /**
     * Return an instance with the specified user information.
     *
     * @param string $user The user name to use for authority.
     * @param null|string $password The password associated with $user.
     * @return static A new instance with the specified user information.
     */
    public function withUserInfo($user, $password = null)
    {

    }

    /**
     * Return an instance with the specified host.
     * 
     * @param string $host The hostname to use with the new instance.
     * @return static A new instance with the specified host.
     * @throws \InvalidArgumentException for invalid hostnames.
     */
    public function withHost($host)
    {

    }

    /**
     * Return an instance with the specified port.
     * 
     * @param null|int $port The port to use with the new instance; a null value
     *     removes the port information.
     * @return static A new instance with the specified port.
     * @throws \InvalidArgumentException for invalid ports.
     */
    public function withPort($port)
    {

    }

    /**
     * Return an instance with the specified path.
     * 
     * @param string $path The path to use with the new instance.
     * @return static A new instance with the specified path.
     * @throws \InvalidArgumentException for invalid paths.
     */
    public function withPath($path)
    {

    }

    /**
     * Return an instance with the specified query string.
     * 
     * @param string $query The query string to use with the new instance.
     * @return static A new instance with the specified query string.
     * @throws \InvalidArgumentException for invalid query strings.
     */
    public function withQuery($query)
    {

    }

    /**
     * Return an instance with the specified URI fragment.
     * 
     * @param string $fragment The fragment to use with the new instance.
     * @return static A new instance with the specified fragment.
     */
    public function withFragment($fragment)
    {

    }

    /**
     * Return the string representation as a URI reference.
     * 
     * @return string
     */
    public function __toString()
    {

    }
}