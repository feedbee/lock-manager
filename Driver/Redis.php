<?php

namespace LockManager\Driver;

/**
 * Distributed Redis LockManager back-end driver.
 * 
 * Algorithm original author: nickyleach
 * Algorithm URL: https://gist.github.com/nickyleach/3694555
 * 
 * (c) Valera Leontyev (feedbee), 2013
 * All LockManager code published under BSD 3-Clause License http://choosealicense.com/licenses/bsd-3-clause/
 */
class Redis implements DriverInterface
{
	/**
	 * int seconds or null
	 */
	const LOCK_ACQUIRE_TIMEOUT = 10;
	/**
	 * int seconds
	 */
	const LOCK_EXPIRE_TIMEOUT = 20;
	/**
	 * int microseconds
	 */
	const SLEEP = 100000; //@TODO: randomize it
	/**
	 * string
	 */
	const KEY = 'LockManager';

	/**
	 * @var \Redis $redis
	 */
	private $redis;
	/**
	 * Stores the expire time of the currently held lock
	 * @var array
	 */
	private $expire = array();

	public function __construct(\Redis $redis = null)
	{
		$this->setRedis($redis);
	}

	public function setRedis(\Redis $redis)
	{
		$this->redis = $redis;
	}
	public function getRedis()
	{
		return $this->redis;
	}

	public function doLock($key, $blockOnBusy)
	{
		$storageKey = self::KEY . ":{$key}";
		$lockAcquireTimeout = $blockOnBusy ? self::LOCK_ACQUIRE_TIMEOUT : 0;

		try {
			$start = time();
 
			do {
				$this->expire[$key] = self::timeout();
				
				if ($acquired = ($this->redis->setnx($storageKey, $this->expire[$key]))) break;
				if ($acquired = ($this->recover($key))) break;
				if ($lockAcquireTimeout === 0) break;
	 
				usleep(self::SLEEP);
			} while (!is_numeric($lockAcquireTimeout) || time() < $start + $lockAcquireTimeout);
	 
			return $acquired;
		} catch (\RedisException $e) {
			return false;
		}
	}

	public function doRelease($key)
	{
		$storageKey = self::KEY . ":{$key}";

		try {
			// Only release the lock if it hasn't expired
			if($this->expire[$key] > time()) {
				$this->redis->del($storageKey);
			}
		} catch (\RedisException $e) {
			return false;
		}

		return true;
	}
 
	/**
	 * Generates an expire time based on the current time
	 * @return int timeout
	 */
	protected static function timeout() {
		return (int) (time() + self::LOCK_EXPIRE_TIMEOUT + 1);
	}
 
	/**
	 * Recover an abandoned lock
	 * @param  string $key Item to lock
	 * @return bool Was the lock acquired?
	 */
	protected function recover($key) {
		$storageKey = self::KEY . ":{$key}";

		if (($lockTimeout = $this->redis->get($storageKey)) > time()) {
			return false;
		}
 
		$timeout = self::timeout();
		$currentTimeout = $this->redis->getset($storageKey, $timeout);
 
		if ($currentTimeout != $lockTimeout) {
			return false;
		}
 
		$this->expire[$key] = $timeout;
		return true;
	}
}