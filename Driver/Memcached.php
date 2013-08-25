<?php

namespace LockManager\Driver;

/**
 * Distributed Memcached LockManager back-end driver.
 * 
 * Algorithm original author: Valera Leontyev
 * Created with help of: http://programmers.stackexchange.com/questions/150230/atomic-memcache-operations-in-php
 * 
 * (c) Valera Leontyev (feedbee), 2013
 * All LockManager code published under BSD 3-Clause License http://choosealicense.com/licenses/bsd-3-clause/
 */
class Memcached implements DriverInterface
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
	 * @var \Memcached
	 */
	private $memcached;
	/**
	 * Stores the expire time of the currently held lock
	 * @var array
	 */
	private $expire = array();

	public function __construct(\Memcached $memcached = null)
	{
		$this->setMemcached($memcached);
	}

	public function setMemcached(\Memcached $memcached)
	{
		$this->memcached = $memcached;
	}
	public function getMemcached()
	{
		return $this->memcached;
	}

	public function doLock($key)
	{
		$storageKey = self::KEY . ":{$key}";

		try {
			$start = time();

			do {
				$this->expire[$key] = self::timeout();
				
				$acquired = false;
				$casToken = null;
				// if the key is not exists
				if (false === ($readValue = $this->memcached->get($storageKey, null, $casToken)))
				{
					// if the key added successful (not atomic)
					if (false !== $this->memcached->add($storageKey, $v = rand(1, 1000000000)))
					{
						// if nobody intercepted the key
						if ($v == $this->memcached->get($storageKey, null, $casToken))
						{
							// if nobody intercepted the key after last checkout, lock it (atomic)
							if (false !== $this->memcached->cas($casToken, $storageKey, $this->expire[$key]))
							{
								// OK
								$acquired = true;
								break;
							}
						}
					}
					// BUSY
				}
				// if the key exists
				else
				{
					// if the key is not valid
					if ($readValue <= time())
					{
						// if nobody intercepted the key after last checkout, lock it (atomic)
						if (false !== $this->memcached->cas($casToken, $storageKey, $this->expire[$key]))
						{
							// OK
							$acquired = true;
							break;
						}
					}
					// BUSY
				}

				usleep(self::SLEEP);
				
			} while (!is_numeric(self::LOCK_ACQUIRE_TIMEOUT) || time() < $start + self::LOCK_ACQUIRE_TIMEOUT);
	 
			return $acquired;

		} catch (\MemcachedException $e) {
			return false;
		}
	}

	public function doRelease($key)
	{
		$storageKey = self::KEY . ":{$key}";

		try {
			// Only release the lock if it hasn't expired
			if($this->expire[$key] > time()) {
				$this->memcached->delete($storageKey);
			}
		} catch (\MemcachedException $e) {
			return false;
		}

		return true;
	}
 
	/**
	 * Generates an expire time based on the current time
	 * @return int	timeout
	 */
	protected static function timeout() {
		return (int) (time() + self::LOCK_EXPIRE_TIMEOUT + 1);
	}
}