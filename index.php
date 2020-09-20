<?php
if(file_exists('.maintenance')){
	require_once 'app/maintenance.php';
	exit(0);
}

require __DIR__ . '/vendor/autoload.php';

use Netpromotion\Profiler\Profiler;
use Tracy\Debugger;
use App\BootConfig;
use App\Booting;
use Nette\Environment;

Profiler::enable();
Profiler::start('container');

$config = new BootConfig(
	[
		'log' => __DIR__ . '/log',
		'temp' => __DIR__ . '/temp',
		'debug' => (getenv('APP_DEBUG') ?: [
			'est-debug@109.107.215.121',
			'est-debug@192.168.10.1',
			'est-debug@127.0.0.1'
		]),
		'dirs' => [
			__DIR__ . '/app'
		],
		'config' => __DIR__ . '/app/config',
		'mode' => (getenv('APP_MODE') ?: 'dev'),
		'host' => (getenv('APP_HOST') ?: 'prod')
	]);

$app = Booting::boot($config)->createContainer()->getByType(Nette\Application\Application::class);

Profiler::finish('container');

Profiler::start('app-run');

$ret = $app->run();

Profiler::finish('app-run');

exit($ret);
