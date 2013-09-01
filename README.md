LockManager
============

LockManager is a universal lock manager for inter-process synchronization in PHP.
Supports different types of back-ends: system-local or distributed (network shared).
Back-end logic implemented in drivers. Driver object mast be passed to LockManager
object in constructor or setDriver() method.

Two modes of lock acquiring supported: blocking and non-blocking. In blocking mode
code execution will be halted (forever of just until some timeout depending on driver).
Execution will be continued after the lock have been successful acquired.

In non-blocking mode result will be returned immediately. If lock would be acquired `true`
will be returned. In other case `false` will be returned.

Note, that no Exceptions driver-depended exceptions are thrown. For example, in case of
connection error to Memcached backend just false will be returned when can't connect
to memcached server. To add development mode when all exceptions will be thrown for debug
purposes is a TODO task.

Usage example
-------------

```PHP
// Use Redis back-end and blocking-mode (default)
$redis = new \Redis;
$redis->connect('127.0.0.1');
$backend = new \LockManager\Driver\Redis($redis);

$lockManager = new \LockManager\LockManager($backend);

if ($lockManager->lock('test-key')) {
    // do the job in safe
    $lockManager->release('test-key');
} else {
    die("Can't get lock!");
}

// Use Flock back-end and non-blocking mode
$backend = new \LockManager\Driver\Flock;
$lockManager->setDriver($backend);
$tries = 0;
while ($tries++ < 100) {
    if ($lockManager->lock('test-key', $needBlock = false)) {
        // do the job in safe
        break;
    }
    else
    {
        // do some other while-waiting job or just sleep()
    }
    $tries++;
}
```