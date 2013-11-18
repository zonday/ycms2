<?php
/**
 * YContentUrlRule class file
 *
 * @author Yang <css3@qq.com>
 */

/**
 * YContentUrlRule
 *
 * @author Yang <css3@qq.com>
 * @package common.components
 */
class YContentUrlRule extends CBaseUrlRule
{
	public function createUrl($manager,$route,$params,$ampersand)
	{
		Yii::log(var_export($params, true));
		if (in_array($route, array('content/index', 'content/view')) && isset($params['path'])) {
			$url = $params['path'];
			$id = isset($params['id']) ? $params['id'] : null;
			unset($params['path'], $params['id']);

			if ($id) {
				$url .= '/' . $id;
			}

			$url .= $manager->urlSuffix;

			if ($params) {
				$url .= '?' . $manager->createPathInfo($params,'=',$ampersand);
			}
			return $url;
		}
		return false;
	}

	public function parseUrl($manager,$request,$pathInfo,$rawPathInfo)
	{
		if (preg_match('#(?P<path>[a-z\-\/]+)/(?P<id>\d+)#i', $pathInfo, $matches)) {
			if ($this->isValidChannel($matches['path'])) {
				$_GET[$manager->routeVar] = 'content/view';
				$_GET['path'] = $matches['path'];
				$_GET['id'] = $matches['id'];
				return true;
			}
		}elseif (preg_match('#(?P<path>[a-z\-\/]+)#i', $pathInfo, $matches)) {
			if ($this->isValidChannel($matches['path'])) {
				$_GET[$manager->routeVar] = 'content/index';
				$_GET['path'] = $matches['path'];
				return true;
			}
		}
		return false;
	}

	protected function isValidChannel($path)
	{
		$parts = explode('/', $path);
		$i = 0;
		while($id = current($parts)) {
			if (!$channel = Channel::get($id)) {
				return false;
			}

			if ($i == 0 && $channel->parent_id != 0) {
				return false;
			}

			if (isset($parent) && $parent->id != $channel->parent_id) {
				return false;
			}

			$parent = $channel;
			next($parts);
			$i++;
		}
		return true;
	}
}