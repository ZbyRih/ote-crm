

{*
<input type="hidden" name="{$c.key}" id="{$c.key}" value="{if isset($d.value)}{$d.value}{/if}" />
<a href="#" id="{$c.key}_link" title="Vybrat obsah">{if isset($d.cont)}{$d.cont}{else}Vybrat{/if}</a>
<script type="text/javascript">{#JS_CD_OPEN#}
	OBEAjax.bindAjaxSelectBox($('#{$c.key}_link'), {ldelim}
		  callForContent: '?{#k_ajax#}=select&{#k_module#}={$SELECT_MODULE}&{#k_formodule#}={$Module.id}&{#k_type#}=g'
		, confirm: AjaxContentHandler.ajaxSelect2FromMenuOrGroup
{if isset($bList)}		, bList: {$bList}
{/if}
		, field: '{$c.key}'
		, subSelName: 'menusetidt'
	{rdelim});
{#JS_CD_CLOSE#}</script>
*}