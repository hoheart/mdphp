<?php

namespace HFC\Log;

use Framework\Facade\Module;
use Framework\Facade\Config;
use HFC\Log\Logger;
use Framework\IService;

class LoggerClient implements IService {
	
	/**
	 *
	 * @var \AMQPConnection
	 */
	protected $mConnection = null;
	
	/**
	 *
	 * @var \AMQPExchange
	 */
	protected $mExchange = null;

	public function __construct ($conf) {
		$this->mConnection = new \AMQPConnection($conf);
		$this->mConnection->connect();
		
		// 创建exchange名称和类型
		$channel = new \AMQPChannel($this->mConnection);
		$ex = new \AMQPExchange($channel);
		$ex->setName('EXCHANGE_LOG');
		$ex->setType(AMQP_EX_TYPE_DIRECT);
		$ex->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
		$ex->declareExchange();
		$this->mExchange = $ex;
		
		// 创建queue名称，使用exchange，绑定routingkey
		$q = new \AMQPQueue($channel);
		$q->setName('QUEUE_LOG');
		$q->setFlags(AMQP_DURABLE | AMQP_AUTODELETE);
		$q->declareQueue();
		$q->bind('EXCHANGE_LOG', '');
	}

	public function init (array $conf) {
	}

	public function start () {
	}

	public function stop () {
	}

	public function __destruct () {
		$this->mConnection->disconnect();
	}

	public function operationLog ($moduleName, $controllerName, $actionName, $operationName, $result, $desc) {
		$login = Module::getService('user', 'User\API\ILogin');
		$data = array(
			'type' => Logger::LOG_TYPE_OPERATION,
			'operatorId' => $login->getLoginedUserId(),
			'moduleName' => $moduleName,
			'controllerName' => $controllerName,
			'actionName' => $actionName,
			'operationName' => $operationName,
			'result' => $result,
			'sessionId' => session_id(),
			'desc' => $desc,
			'clientIp' => Config::get('app.localIP'),
			'platformId' => Config::get('log.platformId'),
			'createdTime' => date('Y-m-d H:i:s')
		);
		
		$this->mExchange->publish(json_encode($data), '');
	}

	public function log ($str, $type = Logger::LOG_TYPE_RUN, $modulePath = '', $level = Logger::LOG_LEVEL_FATAL) {
		$data = array(
			'type' => $type,
			'moduleName' => $modulePath,
			'desc' => $str,
			'level' => $level,
			'clientIp' => Config::get('app.localIP'),
			'platformId' => Config::get('log.platformId'),
			'createdTime' => date('Y-m-d H:i:s')
		);
		
		$this->mExchange->publish(json_encode($data), '');
	}
}



