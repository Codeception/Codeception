<?php
namespace Codeception\Lib\Driver;

class RedisException extends \Exception
{
}

/**
 * Redis database connection class
 *
 * @author sash
 * @license LGPL
 * @version 1.2
 */
class Redis
{
    private $port;
    private $host;
    private $_sock;
    public $debug = false;

    function __construct($host = 'localhost', $port = 6379)
    {
        $this->host = $host;
        $this->port = $port;
    }

    private function connect()
    {
        if ($this->_sock) {
            return;
        }
        if ($sock = fsockopen($this->host, $this->port, $errno, $errmsg)) {
            $this->_sock = $sock;
            $this->debug('Connected');
            return;
        }
        $msg = "Cannot open socket to {$this->host}:{$this->port}";
        if (($errno !== 0) || ($errmsg !== null)) {
            $msg .= "," . ($errno ? " error $errno" : "") . ($errmsg ? " $errmsg" : "");
        }
        throw new RedisException ("$msg.");
    }

    private function debug($msg)
    {
        if ($this->debug) {
            codecept_debug("[Redis] %s\n", $msg);
        }
    }

    private function read()
    {
        if ($s = fgets($this->_sock)) {
            $this->debug('Read: ' . $s . ' (' . strlen($s) . ' bytes)');
            return $s;
        }
        $this->disconnect();
        throw new RedisException ("Cannot read from socket.");
    }

    private function cmdResponse()
    {
        // Read the response
        $s = trim($this->read());
        switch ($s[0]) {
            case '-' : // Error message
                throw new RedisException (substr($s, 1));
                break;
            case '+' : // Single line response
                return substr($s, 1);
            case ':' : //Integer number
                return substr($s, 1) + 0;
            case '$' : //Bulk data response
                $i = ( int )(substr($s, 1));
                if ($i == -1) {
                    return null;
                }
                $buffer = '';
                if ($i == 0) {
                    $this->read();
                }
                while ($i > 0) {
                    $s = $this->read();
                    $l = strlen($s);
                    $i -= $l;
                    if ($i < 0) {
                        $s = substr($s, 0, $i);
                    }
                    $buffer .= $s;
                }
                return $buffer;
                break;
            case '*' : // Multi-bulk data (a list of values)
                $i = ( int )(substr($s, 1));
                if ($i == -1) {
                    return null;
                }
                $res = [];
                for ($c = 0; $c < $i; $c++) {
                    $res [] = $this->cmdResponse();
                }
                return $res;
                break;
            default :
                throw new RedisException ('Unknown response line: ' . $s);
                break;
        }
    }

    private $pipeline = false;
    private $pipeline_commands = 0;

    function pipeline_begin()
    {
        $this->pipeline = true;
        $this->pipeline_commands = 0;
    }

    function pipeline_responses()
    {
        $response = [];
        for ($i = 0; $i < $this->pipeline_commands; $i++) {
            $response[] = $this->cmdResponse();
        }
        $this->pipeline = false;
        return $response;
    }

    private function cmd($command)
    {
        $this->debug('Command: ' . (is_array($command) ? join(', ', $command) : $command));
        $this->connect();

        if (is_array($command)) {
            // Use unified command format

            $s = '*' . count($command) . "\r\n";
            foreach ($command as $m) {
                $s .= '$' . strlen($m) . "\r\n";
                $s .= $m . "\r\n";
            }
        } else {
            $s = $command . "\r\n";
        }
        while ($s) {
            $i = fwrite($this->_sock, $s);
            if ($i == 0) {
                break;
            }
            $s = substr($s, $i);
        }
        if ($this->pipeline) {
            $this->pipeline_commands++;
            return null;
        } else {
            return $this->cmdResponse();
        }
    }

    function disconnect()
    {
        if ($this->_sock) {
            @fclose($this->_sock);
        }
        $this->_sock = null;
    }

    ////////////////////////////////
    ///// Connection handling
    ////////////////////////////////

    /**
     * close the connection
     *
     * Ask the server to silently close the connection.
     *
     * @return void The connection is closed as soon as the QUIT command is received.
     */
    function quit()
    {
        return $this->cmd('QUIT');
    }

    /**
     * simple password authentication if enabled
     *
     * Request for authentication in a password protected Redis server. A Redis server
     * can be instructed to require a password before to allow clients to issue commands.
     * This is done using the requirepass directive in the Redis configuration file.
     *
     * If the password given by the client is correct the server replies with an
     * OK status code reply and starts accepting commands from the client. Otherwise
     * an error is returned and the clients needs to try a new password. Note that for
     * the high performance nature of Redis it is possible to try a lot of passwords in
     * parallel in very short time, so make sure to generate a strong and very long password
     * so that this attack is infeasible.
     *
     * @param $password
     *
     * @return string Status code reply
     */
    function auth($password)
    {
        return $this->cmd(['AUTH', $password]);
    }

    ////////////////////////////////
    ///// Commands operating on string values
    ////////////////////////////////
    /**
     * set a key to a string value
     *
     * Time complexity: O(1)
     *
     * Set the string value as value of the key. The string can't be longer than 1073741824 bytes (1 GB).
     *
     * @param $key
     * @param $value
     * @param $preserve USE SETNX don't perform the operation if the target key already exists.
     *
     * @return string Status code reply
     */
    function set($key, $value, $preserve = false)
    {
        return $this->cmd([($preserve ? 'SETNX' : 'SET'), $key, $value]);
    }

    /**
     * return the string value of the key
     *
     * GET
     * Get the value of the specified key. If the key does not exist the special
     * value 'nil' is returned. If the value stored at key is not a string an
     * error is returned because GET can only handle string values.
     *
     * MGET - Time complexity: O(1) for every key
     * Get the values of all the specified keys. If one or more keys dont exist
     * or is not of type String, a 'nil' value is returned instead of the value
     * of the specified key, but the operation never fails.
     *
     * USAGES:
     *  $this->get('key1')
     *  $this->get(array('key1','key2'))
     *  $this->get('key1','key2')
     *
     * @param mixed $key
     *
     * @return mixed Bulk reply | Multi bulk reply
     */
    function get($key)
    {
        $args = func_get_args();
        if (count($args) > 1) {
            $key = $args;
        }
        if (is_array($key)) {
            array_unshift($key, "MGET");
            return $this->cmd($key);
        } else {
            return $this->cmd(["GET", $key]);
        }
    }

    function __get($key)
    {
        return $this->get($key);
    }

    function __set($key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * set a key to a string returning the old value of the key
     *
     * GETSET is an atomic set this value and return the old value command. Set key
     * to the string value and return the old value stored at key. The string can't be
     * longer than 1073741824 bytes (1 GB).
     *
     * Design patterns
     * GETSET can be used together with INCR for counting with atomic reset when a
     * given condition arises. For example a process may call INCR against the key
     * mycounter every time some event occurred, but from time to time we need to get
     * the value of the counter and reset it to zero atomically using GETSET mycounter 0.
     *
     * @param $key
     * @param $value
     *
     * @return string Bulk reply
     */
    function getset($key, $value)
    {
        return $this->cmd(["GETSET", $key, $value]);
    }

    /**
     * increment the integer value of key
     *
     * Time complexity: O(1)
     *
     * Increment or decrement the number stored at key by one. If the key does not exist
     * or contains a value of a wrong type, set the key to the value of "0" before to
     * perform the increment or decrement operation.
     *
     * INCRBY and DECRBY work just like INCR and DECR but instead to increment/decrement
     * by 1 the increment/decrement is integer.
     *
     * @param $key
     * @param $amount
     *
     * @return int this commands will reply with the new value of key after the increment or decrement.
     */
    function incr($key, $amount = 1)
    {
        if ($amount == 1) {
            return $this->cmd(["INCR", $key]);
        } else {
            return $this->cmd(["INCRBY", $key, $amount]);
        }
    }

    /**
     * decrement the integer value of key
     *
     * Time complexity: O(1)
     * Increment or decrement the number stored at key by one. If the key does not exist
     * or contains a value of a wrong type, set the key to the value of "0" before to
     * perform the increment or decrement operation.
     *
     * INCRBY and DECRBY work just like INCR and DECR but instead to increment/decrement
     * by 1 the increment/decrement is integer.
     *
     * @param $key
     * @param $amount
     *
     * @return int this commands will reply with the new value of key after the increment or decrement.
     */
    function decr($key, $amount = 1)
    {
        if ($amount == 1) {
            return $this->cmd(["DECR", $key]);
        } else {
            return $this->cmd(["DECRBY", $key, $amount]);
        }
    }

    /**
     * test if a key exists
     *
     * Time complexity: O(1)
     * Test if the specified key exists. The command returns "0" if the key exists,
     * otherwise "1" is returned. Note that even keys set with an empty string as
     * value will return "1".
     *
     * @param $key
     *
     * @return int
     */
    function exists($key)
    {
        return $this->cmd(["EXISTS", $key]);
    }

    function __isset($key)
    {
        return $this->exists($key);
    }

    /**
     * delete a key
     *
     * Time complexity: O(1)
     * Remove the specified key. If the key does not exist no operation is performed.
     * The command always returns success.
     *
     * @param $key
     *
     * @return int
     */
    function delete($key)
    {
        return $this->cmd(["DEL", $key]);
    }

    function __unset($key)
    {
        return $this->delete($key);
    }

    /**
     * return the type of the value stored at key
     *
     * Time complexity: O(1)
     * Return the type of the value stored at key in form of a string. The type can
     * be one of "none", "string", "list", "set". "none" is returned if the key does not exist.
     *
     * @param $key
     *
     * @return string
     */
    function type($key)
    {
        return $this->cmd(["TYPE", $key]);
    }

    ////////////////////////////////
    ///// Commands operating on the key space
    ////////////////////////////////
    /**
     * return all the keys matching a given pattern
     *
     * @param $pattern
     *
     * @return string space separated list of keys
     */
    function keys($pattern)
    {
        return $this->cmd(["KEYS", $pattern]);
    }

    /**
     * return a random key from the key space
     *
     * @return unknown_type
     */
    function randomkey()
    {
        return $this->cmd("RANDOMKEY");
    }

    /**
     * rename the old key in the new one, destroying the newname key if it already exists if if $preserve - if the dst does not already exist
     *
     * Time complexity: O(1)
     * Atomically renames the key oldkey to newkey. If the source and destination name are the same an error is returned. If newkey already exists it is overwritten.
     *
     * @param $src
     * @param $dst
     * @param $preserve
     *
     * @return string Status code repy
     */
    function rename($src, $dst, $preserve = false)
    {
        if ($preserve) {
            return $this->cmd(["RENAMENX", $src, $dst]);
        }
        return $this->cmd(["RENAME", $src, $dst]);
    }

    /**
     * return the number of keys in the current db
     * @return int
     */
    function dbsize()
    {
        return $this->cmd("DBSIZE");
    }

    /**
     * set a time to live in seconds on a key
     *
     * @param string $key
     * @param int $ttl in seconds
     *
     * @return int 1: the timeout was set. | 0: the timeout was not set since the key already has an associated timeout, or the key does not exist.
     */
    function expire($key, $ttl)
    {
        return $this->cmd(["EXPIRE", $key, $ttl]);
    }

    /**
     * get the time to live in seconds of a key
     *
     * @param $key
     *
     * @return int
     */
    function ttl($key)
    {
        return $this->cmd(["TTL", $key]);
    }

    ////////////////////////////////
    ///// Commands operating on lists
    ////////////////////////////////

    /**
     * Append an element to the tail of the List value at key
     * if $tail == false - Append an element to the head of the List value at key
     *
     * @param $key
     * @param $value
     * @param $tail
     *
     * @return unknown_type
     */
    function push($key, $value, $tail = true)
    {
        // default is to append the element to the list
        return $this->cmd([$tail ? 'RPUSH' : 'LPUSH', $key, $value]);
    }

    /**
     * Return the length of the List value at key
     *
     * @param $key
     *
     * @return unknown_type
     */
    function llen($key)
    {
        return $this->cmd(["LLEN", $key]);
    }

    /**
     * Return a range of elements from the List at key
     *
     * @param $key
     * @param $start
     * @param $end
     *
     * @return unknown_type
     */
    function lrange($key, $start, $end)
    {
        return $this->cmd(["LRANGE", $key, $start, $end]);
    }

    /**
     * Trim the list at key to the specified range of elements
     *
     * @param $key
     * @param $start
     * @param $end
     *
     * @return unknown_type
     */
    function ltrim($key, $start, $end)
    {
        return $this->cmd(["LTRIM", $key, $start, $end]);
    }

    /**
     * Return the element at index position from the List at key
     *
     * @param $key
     * @param $index
     *
     * @return unknown_type
     */
    function lindex($key, $index)
    {
        return $this->cmd(["LINDEX", $key, $index]);
    }

    /**
     * Set a new value as the element at index position of the List at key
     *
     * @param $key
     * @param $value
     * @param $index
     *
     * @return unknown_type
     */
    function lset($key, $value, $index)
    {
        return $this->cmd(["LSET", $key, $index, $value]);
    }

    /**
     * Remove the first-N, last-N, or all the elements matching value from the List at key
     *
     * Time complexity: O(N) (with N being the length of the list)
     *
     * Remove the first count occurrences of the value element from the list.
     * If count is zero all the elements are removed. If count is negative elements
     * are removed from tail to head, instead to go from head to tail that is the
     * normal behaviour. So for example LREM with count -2 and hello as value to remove
     * against the list (a,b,c,hello,x,hello,hello) will lave the list (a,b,c,hello,x).
     * The number of removed elements is returned as an integer, see below for more
     * information about the returned value. Note that non existing keys are considered
     * like empty lists by LREM, so LREM against non existing keys will always return 0.
     *
     * @param $key
     * @param $value
     * @param $count
     *
     * @return int The number of removed elements if the operation succeeded
     */
    function lrem($key, $value, $count = 1)
    {
        return $this->cmd(["LREM", $key, $count, $value]);
    }

    /**
     * Return and remove (atomically) the last (first if not tail) element of the List at key
     *
     * @param $key
     * @param $tail
     *
     * @return string Bulk reply
     */
    function pop($key, $tail = true)
    {
        return $this->cmd([$tail ? 'RPOP' : 'LPOP', $key]);
    }


    ////////////////////////////////
    ///// Commands operating on sets
    ////////////////////////////////

    /**
     * Add the specified member to the Set value at name
     *
     * @param $key
     * @param $value
     *
     * @return unknown_type
     */
    function sadd($key, $value)
    {
        return $this->cmd(["SADD", $key, $value]);
    }

    /**
     * Remove the specified member from the Set value at name
     *
     * @param $key
     * @param $value
     *
     * @return unknown_type
     */
    function srem($key, $value)
    {
        return $this->cmd(["SREM", $key, $value]);
    }

    /**
     * Remove and return (pop) a random element from the Set value at key
     *
     * @return string
     */
    function spop($key)
    {
        return $this->cmd(["SPOP", $key]);
    }

    /**
     * Move the specified member from one Set to another atomically
     *
     * @param $srckey
     * @param $dstkey
     * @param $member
     *
     * @return int 1 if the element was moved | 0 if the element was not found on the first set and no operation was performed
     */
    function smove($srckey, $dstkey, $member)
    {
        $this->cmd(["SMOVE", $srckey, $dstkey, $member]);
    }

    /**
     * Return the number of elements (the cardinality) of the Set at key
     *
     * @param $key
     *
     * @return int
     */
    function scard($key)
    {
        return $this->cmd(["SCARD", $key]);
    }

    /**
     * Test if the specified value is a member of the Set at key
     *
     * @param $key
     * @param $value
     *
     * @return int
     */
    function sismember($key, $value)
    {
        return $this->cmd(["SISMEMBER", $key, $value]);
    }

    /**
     * Return the intersection between the Sets stored at key1, key2, ..., keyN
     *
     * @param $key1
     *
     * @return array
     */
    function sinter($key1)
    {
        if (is_array($key1)) {
            $sets = $key1;
        } else {
            $sets = func_get_args();
        }
        array_unshift($sets, 'SINTER');
        return $this->cmd($sets);
    }

    /**
     * Compute the intersection between the Sets stored at key1, key2, ..., keyN, and store the resulting
     *
     * @param $dstkey
     * @param $key1
     *
     * @return string Status code reply
     */
    function sinterstore($dstkey, $key1)
    {
        if (is_array($key1)) {
            $sets = $key1;
            array_unshift($sets, $dstkey);
        } else {
            $sets = func_get_args();
        }
        array_unshift($sets, 'SINTERSTORE');
        return $this->cmd($sets);
    }

    /**
     * Return the union between the Sets stored at key1, key2, ..., keyN
     *
     * @param $key1
     *
     * @return array
     */
    function sunion($key1)
    {
        if (is_array($key1)) {
            $sets = $key1;
        } else {
            $sets = func_get_args();
        }
        array_unshift($sets, 'SUNION');
        return $this->cmd($sets);
    }

    /**
     * Compute the union between the Sets stored at key1, key2, ..., keyN, and store the resulting Set at dstkey
     *
     * @param $dstkey
     * @param $key1
     *
     * @return string Status code reply
     */
    function sunionstore($dstkey, $key1)
    {
        if (is_array($key1)) {
            $sets = $key1;
            array_unshift($sets, $dstkey);
        } else {
            $sets = func_get_args();
        }
        array_unshift($sets, 'SUNIONSTORE');
        return $this->cmd($sets);
    }

    /**
     * Return the difference between the Set stored at key1 and all the Sets key2, ..., keyN
     *
     * @param $key1
     *
     * @return array
     */
    function sdiff($key1)
    {
        if (is_array($key1)) {
            $sets = $key1;
        } else {
            $sets = func_get_args();
        }
        array_unshift($sets, 'SDIFF');
        return $this->cmd($sets);
    }

    /**
     * Compute the difference between the Set key1 and all the Sets key2, ..., keyN, and store the resulting Set at dstkey
     *
     * @param $dstkey
     * @param $key1
     *
     * @return string Status code reply
     */
    function sdiffstore($dstkey, $key1)
    {
        if (is_array($key1)) {
            $sets = $key1;
            array_unshift($sets, $dstkey);
        } else {
            $sets = func_get_args();
        }
        array_unshift($sets, 'SDIFFSTORE');
        return $this->cmd($sets);
    }

    /**
     * Return all the members of the Set value at key
     *
     * @param $key
     *
     * @return array
     */
    function smembers($key)
    {
        return $this->cmd(["SMEMBERS", $key]);
    }


    ////////////////////////////////
    ///// Multiple databases handling commands
    ////////////////////////////////

    /**
     * Select the DB having the specified index
     *
     * @param $key
     *
     * @return string Status code reply
     */
    function select_db($key)
    {
        return $this->cmd(["SELECT", $key]);
    }

    /**
     * Move the key from the currently selected DB to the DB having as index dbindex
     *
     * @param $key
     * @param $db
     *
     * @return int 1 if the key was moved | 0 if the key was not moved because already present on the target DB or was not found in the current DB.
     */
    function move($key, $db)
    {
        return $this->cmd(["MOVE", $key, $db]);
    }

    /**
     * Remove all the keys of the currently selected DB
     * @return string Status code reply
     */
    function flushdb()
    {
        return $this->cmd("FLUSHDB");
    }

    /**
     * Remove all the keys from all the databases
     * @return string Status code reply
     */
    function flushall()
    {
        return $this->cmd("FLUSHALL");
    }


    ////////////////////////////////
    ///// Sorting
    ////////////////////////////////
    /**
     * Sort a Set or a List accordingly to the specified parameters
     *
     * @param $key
     * @param $query BY pattern LIMIT start end GET pattern ASC|DESC ALPHA
     *
     * @return array
     */
    function sort($key, $query = false)
    {
        if ($query === false) {
            return $this->cmd(["SORT", $key]);
        } else {
            return $this->cmd(["SORT", $key, $query]);
        }
    }


    ////////////////////////////////
    ///// Persistence control commands
    ////////////////////////////////

    /**
     * Synchronously save the DB on disk (if background = Asynchronously save the DB on disk)
     *
     * @param $background
     *
     * @return string Status code reply
     */
    function save($background = false)
    {
        return $this->cmd(($background ? "BGSAVE" : "SAVE"));
    }

    /**
     * Return the UNIX time stamp of the last successfully saving of the dataset on disk
     * @return int
     */
    function lastsave()
    {
        return $this->cmd("LASTSAVE");
    }

    /**
     * Synchronously save the DB on disk, then shutdown the server
     * @return string Status code reply on error. On success nothing is returned since the server quits and the connection is closed.
     */
    function shutdown()
    {
        return $this->cmd("SHUTDOWN");
    }

    ////////////////////////////////
    ///// Remote server control commands
    ////////////////////////////////
    /**
     * Provide information and statistics about the server
     *
     * @param $section
     *
     * @return unknown_type
     */
    function info($section = false)
    {
        if ($section === false) {
            return $this->cmd("INFO");
        } else {
            return $this->cmd(["INFO", $section]);
        }
    }

    /**
     * Change the replication settings
     *
     * The SLAVEOF command can change the replication settings of a slave on the fly.
     * If a Redis server is already acting as slave, the command SLAVEOF NO ONE will turn
     * off the replication turning the Redis server into a MASTER. In the proper form SLAVEOF
     * hostname port will make the server a slave of the specific server listening at the
     * specified hostname and port.
     *
     * If a server is already a slave of some master, SLAVEOF hostname port will stop
     * the replication against the old server and start the synchronization against the
     *  new one discarding the old dataset.
     *
     * The form SLAVEOF no one will stop replication turning the server into a MASTER
     * but will not discard the replication. So if the old master stop working it is
     *  possible to turn the slave into a master and set the application to use the
     *  new master in read/write. Later when the other Redis server will be fixed it
     *  can be configured in order to work as slave.
     *
     * @return string Status code reply
     */
    function slaveof($host = null, $port = 6379)
    {
        return $this->cmd(['SLAVEOF', $host ? "$host $port" : 'no one']);
    }

    ////////////////////////////////
    ///// MISC
    ////////////////////////////////
    function ping()
    {
        return $this->cmd("PING");
    }

    function do_echo($s)
    {
        return $this->cmd(["ECHO", $s]);
    }

    /**
     * Call any non-implemented function of redis using the new unified request protocol
     *
     * @param string $name
     * @param array $params
     */
    function __call($name, $params)
    {
        array_unshift($params, strtoupper($name));
        return $this->cmd($params);
    }
}
