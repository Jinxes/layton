<?php
namespace Layton\Library\Http;

use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface
{
    const WRAPPER_PHP_INPUT = 'php://input';
    const WRAPPER_PHP_OUTPUT = 'php://output';
    const WRAPPER_PHP_TEMP = 'php://temp';
    const WRAPPER_PHP_MEMORY = 'php://memory';

    /**
     * Resource modes
     *
     * @var  array
     * @link http://php.net/manual/function.fopen.php
     */
    protected static $modes = [
        'readable' => ['r', 'r+', 'w+', 'a+', 'x+', 'c+'],
        'writable' => ['r+', 'w', 'w+', 'a', 'a+', 'x', 'x+', 'c', 'c+'],
    ];

    /**
     * The underlying stream resource
     *
     * @var resource
     */
    protected $stream;

    /**
     * Stream metadata
     *
     * @var array
     */
    protected $meta;

    /**
     * Is this stream readable?
     *
     * @var bool
     */
    protected $readable;

    /**
     * Is this stream writable?
     *
     * @var bool
     */
    protected $writable;

    /**
     * Is this stream seekable?
     *
     * @var bool
     */
    protected $seekable;

    /**
     * The size of the stream
     *
     * @var null|int
     */
    protected $size;

    /**
     * Is this stream a pipe?
     *
     * @var bool
     */
    protected $isPipe;

    /**
     * Create Stream from a Wrapper
     * 
     * @param string $wrapper
     * 
     * @return static
     */
    public static function createFromWrapper($wrapper)
    {
        $stream = new static();
        $stream->attach(fopen($wrapper, 'r+'));
        return $stream;
    }

    public function __toString()
    {
        if (!$this->isAttached()) {
            return '';
        }

        try {
            $this->rewind();
            return $this->getContents();
        } catch (\RuntimeException $e) {
            return '';
        }
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * Attach new resource to this object.
     *
     * @param resource $newStream PHP resource handle.
     *
     * @throws \InvalidArgumentException
     */
    public function attach($newStream)
    {
        if (! is_resource($newStream)) {
            throw new \InvalidArgumentException(__METHOD__ . ' argument must be a PHP resource');
        }

        if ($this->isAttached()) {
            $this->detach();
        }

        $this->stream = $newStream;
    }

    /**
     * Separates any resources from the stream.
     *
     * @return resource|null
     */
    public function detach()
    {
        $oldResource = $this->stream;
        $this->stream = null;
        $this->meta = null;
        $this->readable = null;
        $this->writable = null;
        $this->seekable = null;
        $this->size = null;
        $this->isPipe = null;

        return $oldResource;
    }

    /**
     * Is a resource attached to this stream?
     *
     * @return bool
     */
    protected function isAttached()
    {
        return is_resource($this->stream);
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close()
    {
        if ($this->isAttached()) {
            if ($this->isPipe()) {
                pclose($this->stream);
            } else {
                fclose($this->stream);
            }
        }

        $this->detach();
    }

    /**
     * Is the stream is a pipe?
     *
     * @return bool
     */
    public function isPipe()
    {
        if (\is_null($this->isPipe)) {
            $this->isPipe = false;
            if ($this->isAttached()) {
                $mode = fstat($this->stream)['mode'];
                $this->isPipe = ($mode & 0010000) !== 0;
            }
        }
        return $this->isPipe;
    }

    /**
     * Get stream resource
     * 
     * @return resource
     */
    public function getResource()
    {
        return $this->stream;
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null Returns the size in bytes if known, or null if unknown.
     */
    public function getSize()
    {
        if (!$this->size && $this->isAttached() === true) {
            $stats = fstat($this->stream);
            $this->size = isset($stats['size']) && !$this->isPipe() ? $stats['size'] : null;
        }

        return $this->size;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int Position of the file pointer
     *
     * @throws \RuntimeException on error.
     */
    public function tell()
    {
        if (!$this->isAttached() || ($position = ftell($this->stream)) === false || $this->isPipe()) {
            throw new \RuntimeException('Could not get the position of the pointer in stream');
        }

        return $position;
    }

    /**
     * Returns true if the stream is at the end of the stream.
     *
     * @return bool
     */
    public function eof()
    {
        return $this->isAttached() ? feof($this->stream) : true;
    }

    /**
     * Returns whether or not the stream is readable.
     *
     * @return bool
     */
    public function isReadable()
    {
        if (\is_null($this->readable)) {
            if ($this->isPipe()) {
                $this->readable = true;
            } else {
                $this->readable = false;
                if ($this->isAttached()) {
                    $meta = $this->getMetadata();
                    foreach (self::$modes['readable'] as $mode) {
                        if (strpos($meta['mode'], $mode) === 0) {
                            $this->readable = true;
                            break;
                        }
                    }
                }
            }
        }

        return $this->readable;
    }

    /**
     * Returns whether or not the stream is writable.
     *
     * @return bool
     */
    public function isWritable()
    {
        if ($this->writable === null) {
            $this->writable = false;
            if ($this->isAttached()) {
                $meta = $this->getMetadata();
                foreach (self::$modes['writable'] as $mode) {
                    if (strpos($meta['mode'], $mode) === 0) {
                        $this->writable = true;
                        break;
                    }
                }
            }
        }

        return $this->writable;
    }

    /**
     * Returns whether or not the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable()
    {
        if ($this->seekable === null) {
            $this->seekable = false;
            if ($this->isAttached()) {
                $meta = $this->getMetadata();
                $this->seekable = !$this->isPipe() && $meta['seekable'];
            }
        }

        return $this->seekable;
    }

    /**
     * Seek to a position in the stream.
     * 
     * @param int $offset Stream offset
     * @param int $whence
     * 
     * @throws \RuntimeException
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        // Note that fseek returns 0 on success!
        if (!$this->isSeekable() || fseek($this->stream, $offset, $whence) === -1) {
            throw new \RuntimeException('Could not seek in stream');
        }
    }

    /**
     * Seek to the beginning of the stream.
     *
     * @see seek()
     *
     * @throws \RuntimeException on failure.
     */
    public function rewind()
    {
        if (!$this->isSeekable() || rewind($this->stream) === false) {
            throw new \RuntimeException('Could not rewind stream');
        }
    }

    /**
     * Read data from the stream.
     *
     * @param int $length
     * 
     * @return string
     * 
     * @throws \RuntimeException
     */
    public function read($length)
    {
        if (!$this->isReadable() || ($data = fread($this->stream, $length)) === false) {
            throw new \RuntimeException('Could not read from stream');
        }

        return $data;
    }

    /**
     * Write data to the stream.
     *
     * @param string $string.
     *
     * @return int
     *
     * @throws \RuntimeException
     */
    public function write($string)
    {
        if (!$this->isWritable() || ($written = fwrite($this->stream, $string)) === false) {
            throw new \RuntimeException('Could not write to stream');
        }
        $this->size = null;

        return $written;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     *
     * @throws \RuntimeException if unable to read or an error occurs while
     *     reading.
     */
    public function getContents()
    {
        if (!$this->isReadable() || ($contents = stream_get_contents($this->stream)) === false) {
            throw new \RuntimeException('Could not get contents of stream');
        }

        return $contents;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     * 
     * @param string $key
     * 
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        $this->meta = stream_get_meta_data($this->stream);
        if (is_null($key) === true) {
            return $this->meta;
        }

        return isset($this->meta[$key]) ? $this->meta[$key] : null;
    }
}