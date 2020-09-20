<div class="select-content">
{if isset($d.value.desc)}{$d.value.desc}{/if}
{if isset($d.value.cont)}{if isset($d.value.desc)}<br />{/if}
	{if isset($bList) && $bList}
		{assign var=field value="`$c.key``$d.value.id`"}
	{else}
		{assign var=field value="`$c.key`"}
		<input type="hidden" name="{$c.key}_type" id="{$field}_type" value="{if isset($d.value.type)}{$d.value.type}{/if}" />
		<input type="hidden" name="{$c.key}" id="{$field}" value="{$d.value.id}" />
	{/if}
<a href="{if isset($locLink)}?{$locLink}{else}#{/if}" id="{$field}_link" title="{$d.value.cont.desc}" class="vybrat" data="{$d.request_link}">{$d.value.cont.desc}</a>
{if $d.value.id}<a href="#" id="{$field}_del" class="odpojit">Odebrat</a>{/if}
{/if}
</div>