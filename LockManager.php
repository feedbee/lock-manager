<?php

namespace LockManager;

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
	 * @var Driver\DriverInterface $driver
	 */
	private $driver;

	public function __construct(Driver\DriverInterface $driver)
	{
		$this->driver = $driver;
	}

	public function setDriver(Driver\DriverInterface $driver)
	{
		$this->driver = $driver;
	}
	public function getDriver()
	{
		return $this->driver;
	}

	public function lock($key)
	{
		return $this->driver->doLock($key);
	}

	public function release($key)
	{
		return $this->driver->doRelease($key);
	}
}