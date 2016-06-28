<?php

namespace Framework;

use Framework\Facade\Service;
use Framework\HFC\Log\Logger;

class ErrorHandler {

	public function register2System () {
		// 关闭所有错误输出
		ini_set('display_errors', 'on');
		error_reporting(- 1);
		
		set_error_handler(array(
			$this,
			'processError'
		), E_ALL | E_STRICT);
		set_exception_handler(array(
			$this,
			'handleException'
		));
		register_shutdown_function(array(
			$this,
			'handleShutdown'
		));
	}

	public function handleShutdown () {
		$errinfo = error_get_last();
		if (null != $errinfo) {
			$this->handle($errinfo['type'], $errinfo['message'], $errinfo['file'], $errinfo['line']);
		}
	}

	public function handleException (\Exception $e, RequestContext $context) {
		$this->handle(0, '', '', - 1, $e, array(), $context);
	}

	public function processError ($errno, $errstr, $errfile, $errline, array $errcontext) {
		$this->handle($errno, $errstr, $errfile, $errline, null, $errcontext);
	}

	public function handle ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array(), RequestContext $context = null) {
		if (null != $e) {
			$errno = $e->getCode();
		}
		// 永远不处理这两个错误，因为这才是php好用的地方。
		if (E_STRICT === $errno || E_NOTICE === $errno) {
			return;
		}
		
		$jsonDetail = self::GetErrorAsJsonDetail($errno, $errstr, $errfile, $errline, $e, $errcontext);
		
		// 记录日志
		$log = Service::get('log');
		$log->log($jsonDetail, Logger::LOG_TYPE_ERROR, '', Logger::LOG_LEVEL_FATAL);
		
		// 调用用户配置的错误处理
		$errConf = Config::Instance()->get('app.error_processor');
		if (! empty($errConf)) {
			$p = new $errConf();
			$p->handle($errno, $errstr, $errfile, $errline, $e, $errcontext, $context);
			
			return;
		}
		
		if (Config::Instance()->get('app.debug')) {
			$json = $jsonDetail;
			
			// 输出到控制台，以方便调试
			echo ("Error:$errno:$errstr.");
			echo ('<br>');
			echo ("In file:$errfile:$errline.");
			echo ('<br>');
			echo ('<br>');
			echo ('<pre>');
			
			if (null === $e) {
				// 当调用栈太大时，会导致内存达到配置的最大内存限制
				debug_print_backtrace(~ DEBUG_BACKTRACE_IGNORE_ARGS);
			} else {
				print_r($e);
			}
			
			echo ('</pre>');
		} else {
			$json = self::GetErrorAsJsonByDebug($errno, $errstr, $errfile, $errline, $e, $errcontext);
		}
		
		App::Respond($context, $json);
	}

	static public function GetErrorAsJsonDetail ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array()) {
		$node = array(
			'errcode' => $errno,
			'errstr' => $errstr,
			'errDetail' => array(
				'errfile' => $errfile,
				'errline' => $errline,
				'errcontext' => $errcontext
			)
		);
		if (null != $e) {
			$node['errcode'] = $e->getCode();
			$node['errstr'] = $e->getMessage();
			$node['errDetail'] = $e->__toString();
		}
		
		return json_encode($node);
	}

	/**
	 * 根据是否是debug输出error
	 *
	 * @param unknown $errno        	
	 * @param unknown $errstr        	
	 * @param unknown $errfile        	
	 * @param unknown $errline        	
	 * @param unknown $e        	
	 * @param array $errcontext        	
	 */
	static public function GetErrorAsJsonByDebug ($errno, $errstr, $errfile, $errline, $e = null, $errcontext = array()) {
		if (Config::Instance()->get('app.debug')) {
			return self::GetErrorAsJsonDetail($errno, $errstr, $errfile, $errline, $e, $errcontext);
		} else {
			$node = array(
				'errcode' => $errno,
				'errstr' => $errstr,
				'data' => null
			);
			if (null != $e) {
				$node['errcode'] = $e->getCode();
				$node['errstr'] = $e->getMessage();
			}
			if ($node['errcode'] < 400000 || $node['errcode'] >= 500000) {
				$node['errcode'] = 500000;
				$node['errstr'] = 'system error.';
				$node['data'] = null;
			}
			
			return json_encode($node);
		}
	}
}