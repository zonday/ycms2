<?php
class YContentUrlRule extends CBaseUrlRule
{
	public function createUrl($manager,$route,$params,$ampersand)
	{
		//echo $route;
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
		}
		if (preg_match('#(?P<path>[a-z\-\/]+)#i', $pathInfo, $matches)) {
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
		while($id = current($parts)) {
			if (!$channel = Channel::get($id)) {
				return false;
			}

			if (isset($parent) && $parent->id != $channel->parent_id) {
				return false;
			}

			$parent = $channel;
			next($parts);
		}
		return true;
	}
}