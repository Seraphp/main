<?php
/**
 * This file is a modified version of Pear/Net_Socket. All copyrights of the original file
 * goes to it's authors.
 *
 * @author Peter Nagy <antronin@gmail.com>
 * @version $Id$
 * @copyright Copyright (c) 2009, Peter Nagy
 * @package Comm
 * @filesource
 */
/**
 * Bundles all socket related function into an object
 *
 * @package Comm
 */
class Socket{

    const READ=1;
    const WRITE=2;
    const ERROR=4;

    /**
     * Socket file pointer.
     * @var resource $fp
     */
    private $fp = null;

    /**
     * Whether the socket is blocking. Defaults to true.
     * @var boolean $blocking
     */
    private $blocking = true;

    /**
     * Whether the socket is persistent. Defaults to false.
     * @var boolean $persistent
     */
    private $persistent = false;

    /**
     * The IP address to connect to.
     * @var string $addr
     */
    private $addr = '';

    /**
     * The port number to connect to.
     * @var integer $port
     */
    private $port = 0;

    /**
     * Number of miliseconds to wait on socket connections before assuming
     * there's no more data. Defaults to no timeout.
     * @var integer $timeout
     */
    private $timeout = 0;

    /**
     * Number of bytes to read at a time in readLine() and
     * readAll(). Defaults to 2048.
     * @var integer $lineLength
     */
    private $lineLength = 2048;

    /**
     * Options for the socket connection
     * @var array
     */
    private $options = array();

    /**
     * The type of socket to use, default is tcp
     * @var string
     */
    private $type = 'tcp';

    /**
     *
     * @param string $type         Type of the socket to open(@see: Socket::supportedTransports())
     * @param string  $addr        IP address or host name.
     * @param integer $port        port number.
     * @param boolean $persist     (optional, def: false) Whether the connection is
     *                             persistent (kept open between requests
     *                             by the web server).
     * @param integer $timeout     (optional, def:30s) How long to wait for data.
     * @param array   $options     See options for stream_context_create.
     * @return Socket
     */
    public function __construct($type, $addr, $port = 0, $persist = false, $timeout = 0, $options = null)
    {
        $this->setType($type);
        $this->setAddress($addr);
        $this->setPort($port);
        $this->setPersitent($persist);
        $this->setTimeout($timeout);
        $this->setOptions($options);
    }

    /**
     *
     * @param string $addr
     * @return boolean
     * @throws SocketException if address is empty
     */
    public function setAddress($addr)
    {
        if ( empty($addr) )
        {
        	throw new SocketException('Address cannot be empty!');
        }
        elseif (strspn($addr, ':.0123456789abcdefABCDEF') == strlen($addr) || strstr($addr, '/') !== false)
        {
            $this->addr = inet_ntop( inet_pton( $addr ) );
        }
        else
        {
            $this->addr = gethostbyname($addr);
        }
    }

    /**
     * @param integer $port  The port number (max. 65535)
     * @return void
     */
    public function setPort($port)
    {
        $this->port = $port % 65536;
    }

    /**
     * Sets context options for socket
     *
     * @param array $opt
     * @return void
     */
    public function setOptions($opt)
    {
        if( is_array($opt) )
        {
            $this->options = $opt;
        }
        else throw new SocketExcepion('Option must be an array');
    }

    /**
     * @param boolean $state
     * @return void
     */
    public function setPersistent($state = false)
    {
         $this->persistent = (boolean) $persistent;
    }

    public function setType($type)
    {
        if ( $this->isConnected() )
        {
            throw new SocketException('Cannot modify the type of an open socket');
        }
        if ( in_array( $type, self::supportedTransports() ) )
        {
            $this->type = $type;
        }
        else
        {
            throw new SocketException("Transport '$type' not supported!");
        }
    }

    public static function supportedTransports()
    {
        return stream_get_transports();
    }

    /**
     * Connect to the specified port. If called when the socket is
     * already connected, it disconnects and connects again.
     *
     * @return boolean  True on success
     * @throws SocketException
     */
    public function connect()
    {
        if ($this->isConnected())
        {
            $this->disconnect();
        }

        $errno = 0;
        $errstr = '';
        $old_track_errors = @ini_set('track_errors', 1);
        if ( $this->options !== array() )
        {
            $context = stream_context_create( $this->options );
        }
        else
        {
            $context = null;
        }
        $flags = $this->persistent ? STREAM_CLIENT_PERSISTENT : STREAM_CLIENT_CONNECT;
        $addr = $this->type.'://'.$this->addr . ':' . $this->port;
        $fp = stream_socket_client($addr, $errno, $errstr, $timeout/1000, $flags, $context);

        if (!$fp)
        {
            if ( $errno == 0 && isset( $php_errormsg ) )
            {
                $errstr = $php_errormsg;
            }
            ini_set('track_errors', $old_track_errors);
            throw new SocketException( $errstr );
        }
        ini_set('track_errors', $old_track_errors);
        $this->fp = $fp;
        return $this->setBlocking($this->blocking);
    }

    /**
     * Disconnects from the peer, closes the socket.
     *
     * @return boolean true on success
     * @throws SocketException if not connected
     */
    function disconnect()
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected!');
        }
        fflush($this->fp);
        fclose($this->fp);
        $this->fp = null;
        return true;
    }

    /**
     * Find out if the socket is in blocking mode.
     *
     * @return boolean  The current blocking mode.
     */
    function isBlocking()
    {
        return $this->blocking;
    }

    /**
     * Sets whether the socket connection should be blocking or
     * not. A read call to a non-blocking socket will return immediately
     * if there is no data available, whereas it will block until there
     * is data for blocking sockets.
     *
     * @param boolean $mode  True for blocking sockets, false for nonblocking.
     * @return boolean true on success
     * @throws SocketException
     */
    function setBlocking($mode)
    {
        if (!$this->isConnected()) {
            throw new SocketException('Not connected!');
        }

        $this->blocking = $mode;
        stream_set_blocking($this->fp, $this->blocking);
        return true;
    }

    /**
     * Sets the timeout value on socket descriptor,
     * expressed in the sum of seconds and microseconds
     *
     * @param integer $seconds  Seconds.
     * @param integer $microseconds  Microseconds.
     * @return boolean  True on success
     */
    function setTimeout($seconds, $microseconds=0)
    {
        $this->timeout = $second*1000 + $microsecond;
        if ( $this->isConnected() )
        {
            return stream_set_timeout($this->fp, $seconds, $microseconds);
        }
        return true;
    }

    /**
     * Sets the file buffering size on the stream.
     * See php's stream_set_write_buffer for more information.
     *
     * @param integer $size  Write buffer size.
     * @return mixed on success
     */
    function setWriteBuffer($size)
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }
        $returned = stream_set_write_buffer( $this->fp, $size );
        if ($returned == 0)
        {
            return true;
        }
        throw new SocketException('Cannot set write buffer.');
    }

    /**
     * Returns information about an existing socket resource.
     * Currently returns four entries in the result array:
     *
     *
     * - timed_out (bool) - The socket timed out waiting for data
     * - blocked (bool) - The socket was blocked
     * - eof (bool) - Indicates EOF event
     * - unread_bytes (int) - Number of bytes left in the socket buffer
     * - stream_type (string) - describes the underlying stream implementation
     * - wrapper_type (string) - describes the protocol wrapper implementation layered over the stream
     * - wrapper_data (mixed) - wrapper specific data attached to this stream
     * - filters (array) - contains the names of any filters that have been stacked onto this stream
     * - mode (string) - the type of access required for this stream
     * - seekable (bool) - whether the current stream can be seeked
     * - uri (string) - the URI/filename associated with this stream
     * </p>
     *
     * @return array Array containing information about existing socket resource
     * @throws SocketException if not connected
     */
    function getStatus()
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }

        return stream_get_meta_data( $this->fp );
    }

    /**
     * Get a specified line of data
     *
     * @return $size bytes of data from the socket
     * @throws SocketException if not connected
     */
    function gets( $size )
    {
        if (!$this->isConnected())
        {
            throw new SocketException('Not connected');
        }
        return fgets($this->fp, $size);
    }

    /**
     * Read a specified amount of data. This is guaranteed to return,
     * and has the added benefit of getting everything in one fread()
     * chunk; if you know the size of the data you're getting
     * beforehand, this is definitely the way to go.
     *
     * @param integer $size  The number of bytes to read from the socket.
     * @return $size bytes of data from the socket
     * @throws SocketException if not connected
     */
    function read( $size )
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }
        return fread( $this->fp, $size );
    }

    /**
     * Write a specified amount of data.
     *
     * @param string  $data       Data to write.
     * @param integer $blocksize  Amount of data to write at once.
     *                            NULL means all at once.
     *
     * @return mixed If the write succeeds, returns the number of bytes written
     *               If the write fails, returns false.
     * @throws SocketException if not connected
     */
    function write( $data, $blocksize = 1024 )
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }
        if ( is_null($blocksize) && !OS_WINDOWS )
        {
            return fwrite( $this->fp, $data );
        }
        else
        {
            $pos = 0;
            $size = strlen($data);
            while ($pos < $size)
            {
                $written = @fwrite( $this->fp, substr( $data, $pos, $blocksize ) );
                if ($written === false)
                {
                    return false;
                }
                $pos += $written;
            }
            return $pos;
        }
    }

    /**
     * Write a line of data to the socket, followed by a trailing "\r\n".
     *
     * @return mixed fputs result
     * @throws SocketException if not connected
     */
    function writeLine($data)
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }
        return fwrite( $this->fp, $data . "\r\n" );
    }

    /**
     * Tests for end-of-file on a socket descriptor.
     *
     * Also returns true if the socket is disconnected.
     *
     * @return bool
     */
    function eof()
    {
        return ( !$this->isConnected() || feof( $this->fp ) );
    }

    /**
     * Reads a byte of data
     *
     * @return 1 byte of data from the socket
     * @throws SocketException if not connected
     */
    function readByte()
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }

        return ord( fread( $this->fp, 1 ) );
    }

    /**
     * Reads a word of data
     *
     * @return 1 word of data from the socket
     * @throws SocketException if not connected
     */
    function readWord()
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }

        $buf = fread( $this->fp, 2 );
        return ( ord( $buf[0] ) + ( ord( $buf[1] ) << 8 ) );
    }

    /**
     * Reads an int of data
     *
     * @return integer  1 int of data from the socket
     * @throws SocketException if not connected
     */
    function readInt()
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }

        $buf = fread( $this->fp, 4 );
        return ( ord( $buf[0] ) +
                ( ord( $buf[1] ) << 8 ) +
                ( ord( $buf[2] ) << 16 ) +
                ( ord( $buf[3] ) << 24 )
               );
    }

    /**
     * Reads a zero-terminated string of data
     *
     * @return string
     * @throws SocketException if not connected
     */
    function readString()
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }
        $string = '';
        while (( $char = fread( $this->fp, 1 ) ) != "\x00" )
        {
            $string .= $char;
        }
        return $string;
    }

    /**
     * Reads an IP Address and returns it in a dot formatted string
     *
     * @return Dot formatted string
     * @throws SocketException if not connected
     */
    function readIPAddress()
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }

        $buf = fread( $this->fp, 4 );
        return sprintf( '%d.%d.%d.%d',
                        ord( $buf[0] ),
                        ord( $buf[1] ),
                        ord( $buf[2] ),
                        ord( $buf[3] )
                      );
    }

    /**
     * Read until either the end of the socket or a newline, whichever
     * comes first. Strips the trailing newline from the returned data.
     *
     * @return All available data up to a newline, without that
     *         newline, or until the end of the socket
     * @throws SocketException if not connected
     */
    function readLine()
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }
        $line = '';
        $timeout = time() + $this->timeout;
        while ( !feof( $this->fp ) && ( !$this->timeout || time() < $timeout ) )
        {
            $line .= fgets( $this->fp, $this->lineLength );
            if ( substr( $line, -1 ) == "\n" )
            {
                return rtrim( $line, "\r\n" );
            }
        }
        return $line;
    }

    /**
     * Read until the socket closes, or until there is no more data in
     * the inner PHP buffer. If the inner buffer is empty, in blocking
     * mode we wait for at least 1 byte of data. Therefore, in
     * blocking mode, if there is no data at all to be read, this
     * function will never exit (unless the socket is closed on the
     * remote end).
     *
     * @return string  All data until the socket closes
     * @throws SocketException if not connected
     */
    function readAll()
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }
        $data = '';
        while ( !feof( $this->fp ) )
        {
            $data .= fread( $this->fp, $this->lineLength );
        }
        return $data;
    }

    /**
     * Runs the equivalent of the select() system call on the socket
     * with a timeout specified by tv_sec and tv_usec.
     *
     * @param integer $state    Which of read/write/error to check for.
     * @param integer $tv_sec   Number of seconds for timeout.
     * @param integer $tv_usec  Number of microseconds for timeout.
     *
     * @return False if select fails, integer describing which of read/write/error
     *         are ready
     * @throws SocketException if not connected
     */
    function select($state, $tv_sec, $tv_usec = 0)
    {
        if ( !$this->isConnected() )
        {
            throw new SocketException('Not connected');
        }
        $read = null;
        $write = null;
        $except = null;
        if ( $state & self::READ )
        {
            $read[] = $this->fp;
        }
        if ( $state & self::WRITE )
        {
            $write[] = $this->fp;
        }
        if ( $state & self::ERROR )
        {
            $except[] = $this->fp;
        }
        if ( false === ( $sr = stream_select($read, $write, $except, $tv_sec, $tv_usec ) ) )
        {
            return false;
        }

        $result = 0;
        if ( count( $read ) )
        {
            $result |= self::READ;
        }
        if ( count( $write ) )
        {
            $result |= self::WRITE;
        }
        if ( count( $except ) )
        {
            $result |= self::ERROR;
        }
        return $result;
    }

    /**
     * Turns encryption on/off on a connected socket.
     *
     * @param bool    $enabled  Set this parameter to true to enable encryption
     *                          and false to disable encryption.
     * @param integer $type     Type of encryption. See
     *                          http://se.php.net/manual/en/function.stream-socket-enable-crypto.php for values.
     *
     * @return false on error, true on success and 0 if there isn't enough data and the
     *         user should try again (non-blocking sockets only).
     * @throws SocketException if not connected
     * @throws Exception if PHP version below 5.1.0
     */
    function enableCrypto($enabled, $type)
    {
        if ( version_compare( phpversion(), "5.1.0", ">=" ) )
        {
            if ( !$this->isConnected() )
            {
                throw new SocketException('Not connected');
            }
            return stream_socket_enable_crypto( $this->fp, $enabled, $type );
        }
        else
        {
            throw new Exception('Phaser::Comm::Socket::enableCrypto() requires php version >= 5.1.0');
        }
    }
    /**
     * @return boolean
     */
    public function isConnected()
    {
        return is_resource( $this->fp );
    }
}

class SocketException extends Exception{}
?>