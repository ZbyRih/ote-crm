<?php
require __DIR__ . '/../vendor/autoload.php';

define('APP_DIR', __DIR__ . '/');
define('WWW_DIR', __DIR__ . '/..');

$loader = new Nette\Loaders\RobotLoader();
$loader->setTempDirectory(__DIR__ . '/../temp/cache/Nette.RobotLoader');
$loader->setAutoRefresh(true)
	->addDirectory(__DIR__)
	->register();

use App\Extensions\App\WebPackManifest;

$favico = WebPackManifest::file('/', [
	'/dist/css/favicon.ico'
]);

?>
<!DOCTYPE html>
<html lang="cs">
	<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta http-equiv="Content-Language" content="cs" />
    <meta http-equiv="imagetoolbar" content="no" />

    <meta name="abstract" content="" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />

    <meta name="copyright" content="{_'app.copyright'}{date('Y')}" />
    <meta name="author" content="{_'app.autor'}" />
    <meta name="robots" content="noindex,nofollow" />
    <meta name="rating" content="general" />

    <link rel="icon" type="image/x-icon" href="<?=$favico?>">

    <title>IPrint</title>
<?php
echo WebPackManifest::script('/', [
	'/dist/css/theme_def_css.css',
	'/dist/js/app_js.js'
]);
?>
	<!--[if IE]>
    <style type="text/css">
      select { padding-top:0; padding-bottom:0; height: 33px; }
    </style>
	<![endif]-->
	</head>
	<body class="login">
		<section class="section-account">
			<div class="img-backdrop" id="ote"></div>
			<div class="spacer"></div>
			<div class="card contain-sm style-transparent">
				<div class="card-body">
					<div class="row">
						<div class="col-sm-6">
							<br>
							<span class="text-xl text-bold text-primary">
<?php
echo WebPackManifest::image('/', [
	'/dist/css/background.png'
]);
?>OTE CRM</span>
							<br><br>
							<h1 class="text-center">Omlouváme se<br /> právě probíhá údržba systému.</h1>
						</div>
					</div>
				</div>
			</div>
		</section>
	</body>
</html>