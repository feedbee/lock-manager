<?php

namespace LockManager;

use LockManager\Driver\DriverInterface;

/**
 * LockManager
 *
 * LockManager is a universal lock manager for inter-process synchronization in PHP.
 * Supports different types of back-ends: system-local or distributed (network shared).
 * Back-end logic implemented in drivers.
 * Driver object mast be passed to LockManager object in constructor or setDriver() method.
 * 
 * (c) Valera Leontyev (feedbee), 2013
 * All LockManager code published under BSD 3-Clause License http://choosealicense.com/licenses/bsd-3-clause/
 * Feedback: feedbee@gmail.com
 */
class LockManager
{
	/**
	 * @var \LockManager\Driver\DriverInterface $driver
	 */
	private $driver;

	/**
	 * @param DriverInterface $driver
	 */
	public function __construct(DriverInterface $driver)
	{
		$this->driver = $driver;
	}

	/**
	 * Set back-end driver object
	 *
	 * @param DriverInterface $driver
	 */
	public function setDriver(DriverInterface $driver)
	{
		$this->driver = $driver;
	}
	/**
	 * Get currently used back-end driver (or null if it isn't set)
	 *
	 * @return DriverInterface
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * Try to acquire lock
	 * Returns true on success or false on failure (can't acquire lock or any back-end internal error).
	 * $blockOnBusy parameter sets work-mode: would the function be blocked until lock is acquired?
	 *
	 * @param string $key
	 * @param bool $blockOnBusy True to wait while lock is acquired, false to return immediately
	 * @return bool
	 */
	public function lock($key, $blockOnBusy = true)
	{
		return $this->driver->doLock($key, $blockOnBusy);
	}

	/**
	 * Release lock
	 * Returns true on success or false on any back-end internal error.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function release($key)
	{
		return $this->driver->doRelease($key);
	}
}