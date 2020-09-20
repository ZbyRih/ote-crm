<?php
use Nette\Loaders\RobotLoader;
use Tests\Utils\TestsConfig;
use App\BootConfig;

ini_set('xdebug.var_display_max_depth', '5');

define('__ROOT__', __DIR__ . '/..');

require __ROOT__ . '/vendor/autoload.php';

\Tester\Environment::setup();

define('APP_DIR', __ROOT__ . '/app/');
define('WWW_DIR', __ROOT__ . '/');
define('DATA_DIR', __ROOT__ . '/tests/_data');

TestsConfig::$config = new BootConfig(
	[
		'log' => __DIR__ . '/_log',
		'temp' => __DIR__ . '/_temp',
		'debug' => 'yes',
		'dirs' => [
			__DIR__ . '/../app'
		],
		'config' => __DIR__ . '/../app/config',
		'mode' => 'test',
		'host' => 'local'
	]);

TestsConfig::$disableEvents = [
	'App\Models\Events\DBCommitEvent'
];

$loader = new RobotLoader();
$loader->addDirectory(TestsConfig::$config->dirs);
$loader->setCacheStorage(new Nette\Caching\Storages\FileStorage(TestsConfig::$config->temp));
$loader->register();