{*
<!DOCTYPE html>
<html>
*}
{config_load "global.conf"}
{include './content/functions.tpl' scope=global inline}

{*

{include './content/page.head.tpl'}

<head>
	<meta charset="utf-8"/>
	<meta http-equiv="Content-Language" content="cs" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	{$scripts}
</head>
<body class="menubar-hoverable header-fixed{if $debug} debug{/if}{if isset($smarty.cookies['traxell-admin-menu']) && $smarty.cookies['traxell-admin-menu'] == 2} menubar-pin menubar-visible{/if}">
	{if isset($userLogged) && $userLogged}
	<header id="header">
		<div class="headerbar">
			<div class="headerbar-left">
				<ul class="header-nav">
					<li class="header-nav-brand">
						<div class="brand-holder">
							<span class="text-lg text-bold {if $demo}text-danger{else}text-primary{/if}"><a href="/">{$SETTINGS.ADMIN_NAME}</a></span>{if $USER.superuser}<br/><span class="host">{$smarty.server.HTTP_HOST}</span>{/if}
						</div>
					</li>
					<li>{btn icon='fa fa-bars' kind='icon-toggle' more='" data-toggle="menubar'}</li>
					{if $demo}<li><span class="text-xxl text-danger">sandbox</span></li>{/if}
					{if $last_user_conlision}<li><span class="alert alert-danger">{$last_user_conlision}</span></li>{/if}
				</ul>
			</div>					
			<div class="headerbar-right">
				<ul class="header-nav header-nav-options">
					<li class="dropdown hidden-xs">
						<a href="javascript:void(0);" class="btn btn-icon-toggle btn-default" data-toggle="dropdown" aria-expanded="true">
							<i class="fa fa-bell"></i>{if $user_messages_now}<sup class="badge style-danger">{$user_messages_now}</sup>{/if}
						</a>
						<ul class="dropdown-menu animation-expand">
							<li><a href="?{#k_module#}=info">Zobrazit vše <span class="pull-right"><i class="fa fa-arrow-right"></i></span></a></li>
							{if $user_messages_now}
							<li><a href="?{#k_module#}=info&{#k_ajax#}=markAsReaded" class="ajax link">Přečteno <span class="pull-right"><i class="fa fa-arrow-right"></i></span></a></li>
							{/if}
							<li class="dropdown-header">Nepřečtené</li>
							{foreach $user_messages as $m}
							<li>
								<a class="alert alert-callout {if $m['viewed'] != null}alert-success{elseif $m['type'] == 'ote'}alert-warning{else}alert-info{/if}" href="javascript:void(0);">
									<small>{$m['type']}:</small><strong>{$m['message']|replace:'|':'<br/>'}</strong><br>
									<small>{$m['created']|date_format:'d.m. Y H:i:s'}</small>
								</a>
							</li>
							{/foreach}
						</ul><!--end .dropdown-menu -->
					</li>					
				</ul>
				<ul class="header-nav">
					{if $MODULE.help}<li class="module-help">{btn icon='md md-live-help' kind='icon-toggle' link="?`$smarty.config.k_module`=`$MODULE.scope->module`&amp;`$smarty.config.k_ajax`=help" title='Nápověda'}</li>{/if}
					<li class="header-nav-user"><span class="user">{$USER.jmeno}<small>{$USER.role}</small></span></li>
					<li>{btn icon='fa fa-power-off' kind='icon-toggle' link='?logout=true' title='Odhlásit'}</li>
				</ul>
			</div>
		</div>
	</header>
	<div id="base">
		<div class="offcanvas"></div>
		<div id="content">
*}
			{if $ContentTemplate}{include "content/$ContentTemplate" Module=$MODULE}{/if}
{*		
		</div>
		<div id="menubar" class="menubar-inverse">
			<div class="menubar-scroll-panel">
				<ul id="main-menu" class="gui-controls">
				{function name=menu_item i=null}{if $i.visible}<li{if $i.selected} class="active"{/if}>				
				<a href="?{#k_module#}={$i.file}">
					<div class="gui-icon"><i class="{$i.icon}"></i></div>
					<span class="title">{$i.name}</span>
				</a>{/if}{/function}
				{foreach $MENU.items as $item}
					{if isset($item.items)}
					<li class="{if isset($item.items)}gui-folder{if $item.selected} expanded{/if}{else}{if $item.selected} active{/if}{/if}">
						<a href="javascript: void(0);">
							<div class="gui-icon"><i class="{$item.icon}"></i></div>
							<span class="title">{$item.name}</span>
						</a>
						{if isset($item.items)}
						<ul>
						{foreach from=$item.items item=modul}
							{menu_item i=$modul}
						{/foreach}
						</ul>
						{/if}
					</li>
					{else}
						{menu_item i=$item}
					{/if}
				{/foreach}
				</ul>
				<div class="menubar-foot-panel">
					<small class="no-linebreak hidden-folded">
						<span class="opacity-75">{$SETTINGS.COPYRIGHT}</span><br /><strong>{$SETTINGS.AUTOR}</strong>
					</small>
				</div>				
			</div>
		</div>

	</div>
	{else}

	<section class="section-account">
		<div class="img-backdrop" style="background-image: url('./themes/standart/css/img/traxik.jpg')"></div>
		<div class="spacer"></div>
		<div class="card contain-sm style-transparent">
			<div class="card-body">
				<div class="row">
					<div class="col-sm-6">
						<br>
						<span class="text-xxxl text-bold text-primary"><img src="./themes/standart/css/img/traxell.png" width="60px" height="63px" />{$SETTINGS.ADMIN_NAME}</span>
						<br><br>
						<form class="form floating-label" action="index.php" accept-charset="utf-8" method="post">
							<div class="form-group">
								<input type="text" class="form-control" id="username" name="data_login">
								<label for="data_login">Uživatelské jméno</label>
							</div>
							<div class="form-group">
								<input type="password" class="form-control" id="password" name="data_passwd">
								<label for="data_passwd">Heslo</label>
							</div>
							<br>
							<div class="row">
								<div class="col-xs-6 text-left">
								</div>
								<div class="col-xs-6 text-right">
									<button class="btn btn-primary btn-raised" type="submit">Přihlásit</button>
								</div>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>
	{/if}
</body>
</html>
*}