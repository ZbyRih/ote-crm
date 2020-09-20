<?php

namespace App\Extensions\App;

use Nette;
use Nette\Application\Routers\RouteList;
use Nette\Application\Routers\Route;

class RouterFactory{

	/**
	 *
	 * @return Nette\Application\IRouter
	 */
	public static function createRouter(){
		$router = new RouteList();
		$router[] = new Route('admin/index.php', [
			'presenter' => 'Legacy',
			'action' => 'default',
			'do' => null
		]);

		$router[] = new Route('admin[/]', [
			'presenter' => 'Legacy',
			'action' => 'default',
			'do' => null
		]);

// 		$router[] = new Route('admin[/]',
// 			[
// 				'presenter' => 'Legacy',
// 				'action' => 'default',
// 				null => [
// 					Route::FILTER_IN => [
// 						'\App\Extensions\App\RouterFactory',
// 						'inFilterLegacy'
// 					]
// 				]
// 			], Route::ONE_WAY);

// 		$router[] = new Route('legacy/',
// 			[
// 				'presenter' => 'Legacy',
// 				'action' => 'default',
// 				null => [
// 					Route::FILTER_IN => [
// 						'\App\Extensions\App\RouterFactory',
// 						'inFilterLegacy'
// 					]
// 				]
// 			], Route::ONE_WAY);

		$router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');
		$router[] = new Route('<module>/<presenter>/<action>[/<id>]', ':Homepage:default');
		return $router;
	}
}