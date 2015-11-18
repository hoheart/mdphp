<?php

namespace Framework;

/**
 * 之所以设计这个，是为了解决个服务之间的先后依赖关系，所以，先启动的服务，必须后停止。
 *
 * @author Hoheart
 *        
 */
interface IService {

	/**
	 * 对服务进行初始化
	 *
	 * @param array $conf        	
	 */
	public function init (array $conf);

	/**
	 * 启动服务
	 */
	public function start ();

	/**
	 * 停止服务，回收资源
	 */
	public function stop ();
}