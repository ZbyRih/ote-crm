	<head>
		<meta charset="utf-8"/>
		<meta http-equiv="Content-Language" content="cs" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{if isset($force_reload) && $force_reload}		<meta http-equiv="Cache-Control" content="must-revalidate, post-check=0, pre-check=0, no-cache" />
{/if}
		<meta http-equiv="imagetoolbar" content="no" />
		<meta name="MSSmartTagsPreventParsing" content="true" />
		<meta name="copyright" content="Všechna práva vyhrazena © Neutral Solution s.r.o." />
		<meta name="author" content="Zbyněk Říha (Neutral Solution s.r.o.)" />
{if $debug}		
		<meta name="debug" content="true" />
{/if}
		<title>{$MODULE.name} | {$BACKEND.NAME}</title>
		<link rel="shortcut icon" href="./favicon.ico" />
		<link href="{$THEME.stylePath}css/roboto.css" rel="stylesheet" type="text/css" rel="stylesheet" />
		<link href="{$THEME.stylePath}css/libs{if $THEME.color}_{$THEME.color}{/if}.css" rel="stylesheet" type="text/css" rel="stylesheet" />
		<link href="{$THEME.stylePath}css/main{if $THEME.color}_{$THEME.color}{/if}.css" rel="stylesheet" type="text/css" rel="stylesheet" />
		<link href="{$THEME.stylePath}css/font-awesome.min.css" type="text/css" rel="stylesheet" />
		<link href="{$THEME.stylePath}css/material-design-iconic-font.min.css?1421434286" type="text/css" rel="stylesheet" />		
		<link href="{$THEME.stylePath}css/mod{if $THEME.color}_{$THEME.color}{/if}.css" rel="stylesheet" type="text/css" rel="stylesheet" />
{if !empty($js_scripts)}
{foreach $js_scripts as $file}		<script type="text/javascript" src="{$httpBase}{#SCRIPTS_PATH#}{$file}"></script>
{/foreach}
{/if}
	</head>