<?php

namespace HFC\Database\Mysql;

use HFC\Database\DatabaseClient;
use HFC\Database\DatabaseConnectException;

/**
 * 用mysqli实现的mysql客户端
 * mysqli支持自动重连接
 *
 * @author Hoheart
 *        
 */
class MysqlClient extends DatabaseClient {
	
	/**
	 * 是否初始化
	 *
	 * @var boolean
	 */
	protected $mInited = false;
	
	/**
	 *
	 * @var \mysqli $mMysqli
	 */
	protected $mMysqli = null;

	public function init (array $conf) {
		$this->mMysqli = new \mysqli();
		
		$this->mMysqli->autocommit($this->mAutocommit);
		
		parent::init($conf);
	}

	protected function connect () {
		$ret = $this->mMysqli->real_connect($this->mConf['server'], $this->mConf['user'], $this->mConf['password'], 
				$this->mConf['name'], $this->mConf['port']);
		if (false === $ret) {
			throw new DatabaseConnectException('On Connection Error.' . $this->mMysqli->error);
		}
	}

	public function exec ($sql) {
		$ret = $this->mMysqli->query($sql);
		if (true === $ret) {
		}
	}

	public function select ($sql, $inputParams, $start = 0, $size = self::MAX_ROW_COUNT) {
	}

	public function query ($sql, $cursorType = self::CURSOR_FWDONLY) {
	}

	public function transLimitSelect ($sql, $start, $size) {
	}

	public function beginTransaction () {
	}

	public function rollBack () {
	}

	public function commit () {
	}

	public function inTransaction () {
	}

	protected function getClient () {
		$client = parent::getClient();
		if (! $this->mInited) {
			$this->mInited = true;
			
			$dt = new \DateTime();
			$tz = $dt->getTimezone();
			
			$intTz = (int) ($tz->getOffset($dt) / 3600);
			if ($intTz > 0) {
				$intTz = '+' . $intTz;
			}
			$client->exec("SET time_zone = '$intTz:00';COMMIT;");
		}
		
		return $client;
	}

	protected function getDSN () {
		$host = $this->mConf['server'];
		$port = $this->mConf['port'];
		$dbname = $this->mConf['name'];
		$charset = $this->mConf['charset'];
		$dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
		
		return $dsn;
	}

	public function change2SqlValue ($val, $type = 'string') {
		if (null === $val) {
			return 'null';
		}
		
		$type = strtolower($type);
		if ("\\" == $type[0]) {
			$type = substr($type, 1);
		}
		if (is_array($val)) {
			$ret = array();
			foreach ($val as $oneVal) {
				$ret[] = $this->change2SqlValue($oneVal, $type);
			}
			
			return $ret;
		}
		
		$v = null;
		if ('string' == substr($type, 0, 6)) {
			$v = $this->getClient()->quote($val);
		} else if ('int' == substr($type, 0, 3)) {
			$v = (int) $val;
		} else if ('float' == substr($type, 0, 5)) {
			$v = (float) $val;
		} else {
			switch ($type) {
				case 'date':
					$v = $val->format('Y-m-d');
					break;
				case 'time':
				case 'datetime':
					$v = $val->format('Y-m-d H:i:s');
					break;
				case 'boolean':
					$v = $val ? 1 : 0;
					break;
			}
			
			$v = "'$v'";
		}
		
		return $v;
	}
}