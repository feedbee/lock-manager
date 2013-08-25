<?php

namespace LockManager\Driver;

/**
 * LockManager back-end driver interface
 * 
 * (c) Valera Leontyev (feedbee), 2013
 * All LockManager code published under BSD 3-Clause License http://choosealicense.com/licenses/bsd-3-clause/
 */
interface DriverInterface
{
	/**
	 * Lock acquire back-end implementation. Returns true on success or false on failure (can't acquire lock
	 * or any internal error).
	 *
	 * @param string $key
	 * @param bool $blockOnBusy Would the function be blocked until lock is acquired?
	 * @return bool
	 */
	public function doLock($key, $blockOnBusy);
	/**
	 * Lock release back-end implementation. Returns true on success or false on any internal error.
	 *
	 * @param string $key
	 * @return bool
	 */
	public function doRelease($key);
}