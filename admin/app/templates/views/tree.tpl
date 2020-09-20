{include './list.fces.tpl'}
<div class="tree-wrapper">
{if $LIST.errors}
	<div class="alert alert-danger" role="alert">{foreach from=$LIST.errors item=item}
		<p>{$item}</p>
	{/foreach}</div>
{/if}
	<div class="tree" uid="{$LIST.listUID}" model="{$LIST.scope->module}">
		{if isset($LIST.caption)}<div class="caption">{$LIST.caption}</div>{/if}
		<ul>
		{if isset($LIST.header)}
			<li class="head">{include './list.head.3.tpl' data=$LIST.header elm='span'}</li>
		{/if}
		{if isset($LIST.data) && !empty($LIST.data)}
			{include './tree.lines.tpl' rows=$LIST.data odsazeni='' parent=null}
		{else}
			<div>Seznam neobsahuje žádné položky</div>
		{/if}
		</ul>
	</div>
	{include './list.ajax.tpl'}
</div>