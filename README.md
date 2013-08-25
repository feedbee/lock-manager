LockManager
============

LockManager is a universal lock manager for inter-process synchronization in PHP.
Supports different types of back-ends: system-local or distributed (network shared).
Back-end logic implemented in drivers. Driver object mast be passed to LockManager
object in constructor or setDriver() method.

Usage example
-------------

```PHP
// Use Redis back-end
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

// Use Flock back-end
$backend = new \LockManager\Driver\Flock;
$lockManager->setDriver($backend);
$tries = 0;
while ($tries < 100 && !$lockManager->lock('test-key')) {
    if ($lockManager->lock('test-key')) {
        // do the job in safe
        break;
    }
    $tries++;
}
```