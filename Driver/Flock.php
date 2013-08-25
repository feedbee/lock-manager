<?php

namespace LockManager\Driver;

/**
 * Local file LockManager back-end driver.
 * 
 * Algorithm original author: Valera Leontyev
 * 
 * (c) Valera Leontyev (feedbee), 2013
 * All LockManager code published under BSD 3-Clause License http://choosealicense.com/licenses/bsd-3-clause/
 */
class Flock implements DriverInterface
{
	private $lockFilesDir;

	private $lockHandlers = array();

	public function __construct()
	{
		$this->lockFilesDir = sys_get_temp_dir();
	}

	public function doLock($key, $blockOnBusy)
	{
		$key = urlencode($key);
		$this->lockHandlers[$key] = fopen("{$this->lockFilesDir}/lock-$key", "w+");
		if ($this->lockHandlers[$key]) {
			$flags = LOCK_EX | ($blockOnBusy ? LOCK_NB : 0);
			if (flock($this->lockHandlers[$key], $flags)) {
				return true;
			}
		}

		return false;
	}

	public function doRelease($key)
	{
		$name = "{$this->lockFilesDir}/lock-$key";

		$result = false;
		if (isset($this->lockHandlers[$key]) && $this->lockHandlers[$key]) {
			$result = flock($this->lockHandlers[$key], LOCK_UN);
			fclose($this->lockHandlers[$key]);
			unset($this->lockHandlers[$key]);
		}
		return $result;
	}
}