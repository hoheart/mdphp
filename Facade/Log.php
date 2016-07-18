<?php

namespace Framework\Facade;

use Framework\RequestContext;
use Framework\HFC\Log\Logger;

class Log {

	static public function r ($desc, $module, RequestContext $context = null) {
		Service::get('log')->log($desc, $module, Logger::LOG_TYPE_RUN, Logger::LOG_LEVEL_FATAL, $context);
	}

	static public function e ($desc, $module, $level, RequestContext $context = null) {
		Service::get('log')->log($desc, $module, Logger::LOG_TYPE_ERROR, $level, $context);
	}
}