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
	public $exclude = array();

	/**
	 * @var boolean
	 */
	public $combinedPath = true;

	public function createUrl($manager,$route,$params,$ampersand)
	{
		if (in_array($route, array('content/index', 'content/view')) &&
			(isset($params['path']) || isset($params['channel_id']) || isset($params['channel_name']))) {
			if (isset($params['path'])) {
				$url = $params['path'];
				unset($params['path']);
			} elseif (isset($params['channel_id'])) {
				if (!$channel = Channel::get($params['channel_id'])) {
					return false;
				}
				if ($this->combinedPath) {
					$url = $channel->getPath();
				} else {
					$url = $channel->name;
				}
				unset($params['channel_id']);
			} elseif (isset($params['channel_name'])) {
				if ($this->combinedPath || !($channel = Channel::get($params['channel_name']))) {
					return false;
				} else {
					$url = $params['channel_name'];
				}
				unset($params['channel_name']);
			}

			if (!empty($params['id'])) {
				$url .= '/' . $params['id'];
				unset($params['id']);
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
		if (in_array($path, $this->exclude))
			return false;

		$parts = explode('/', $path);
		$i = 0;
		while($id = current($parts)) {
			if (in_array($id, $this->exclude))
				return false;

			if (!$channel = Channel::get($id)) {
				return false;
			} elseif ($this->combinedPath === false) {
				return true;
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