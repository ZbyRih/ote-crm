{config_load file="global.conf"}
{include './content/functions.tpl' scope=global inline}
{if $MODULE && $MODULE.elements->main}
{foreach from=$MODULE.elements->main item=e}{include './content/module.content.tpl' Module=$MODULE element=$e}{/foreach}
{else}
<div>
	<span>Pro pozadovany obsah nexistuje handler</span>
</div>
{/if}