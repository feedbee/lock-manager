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
	public function doLock($key);
	public function doRelease($key);
}