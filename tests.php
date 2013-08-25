<?php

/*
 * LockManager tests.
 *
 * Run this script in console in concurrent mode (many instances in parallel).
 * Tests completed successful if `Tests failed` value is 0 in all runs.
 * 
 * (c) Valera Leontyev (feedbee), 2013
 * All LockManager code published under BSD 3-Clause License http://choosealicense.com/licenses/bsd-3-clause/
 */

require_once('LockManager.php');
require_once('Driver/DriverInterface.php');
require_once('Driver/Flock.php');
require_once('Driver/Redis.php');
require_once('Driver/Memcached.php');

use LockManager\LockManager;
use LockManager\Driver\Memcached as MemcachedDriver;
use LockManager\Driver\Redis as RedisDriver;
use LockManager\Driver\Flock as FlockDriver;

$opt = getopt('mrfs');
if (isset($opt['m'])) {
	print "LockManager: Memcached back-end test" . PHP_EOL;
	$memcached = new \Memcached;
	$memcached->addServer('127.0.0.1', 11211);

	$backend = new \LockManager\Driver\Memcached($memcached);
}
else if (isset($opt['r'])) {
	print "LockManager: Redis back-end test" . PHP_EOL;
	$redis = new \Redis;
	$redis->connect('127.0.0.1');

	$backend = new RedisDriver($redis);
}
else if (isset($opt['f'])) {
	print "LockManager: Flock back-end test" . PHP_EOL;
	$backend = new FlockDriver;
}
else {
	die('Set back-end parameter: -m, -r, or -f');
}

$lockManager = new LockManager($backend);

if (isset($opt['s'])) {
	// Run simple test (for debug purpose)
	echo 'Before lock' . PHP_EOL;
	var_dump($lockManager->lock('test'));
	echo 'After lock' . PHP_EOL;
	sleep(5);
	echo 'Before release' . PHP_EOL;;
	var_dump($lockManager->release('test'));
	echo 'After release' . PHP_EOL;
	exit;
}

// Run standard test
print "Test is infinitive and must be interrupter with system termination signal (CTRL+C)" . PHP_EOL;
$dir = sys_get_temp_dir();
$fd = fopen("{$dir}/lock-test-file", "w+");

$all = $failed = 0;

do {

	print "Tests launched: {$all}\tTests failed: {$failed}\r";

	if ($lockManager->lock('test-key')) {
		if (!flock($fd, LOCK_EX)) {
			$failed++;
			continue;
		}

		usleep(120000);
		flock($fd, LOCK_UN);
		$lockManager->release('test-key');
	}
	usleep(rand(500, 50000));

	$all++;
}
while (1);

// Test is infinitive and must be interrupter with system termination signal (CTRL+C)