<div class="select-content">
{if isset($bList) && $bList}{$field="`$c.key``$d.value`"}{else}{$field="`$c.key`"}{/if}
{if $d.itemLabel}<span id="{$field}_il">{$d.itemLabel}</span>{if $d.itemLabel && $d.selLinkUrl}<span class="col-md-offset-1"></span>{/if}{/if}
{if $d.selLinkUrl}
	{if !isset($bList) || !$bList}
		<input type="hidden" name="{$c.key}" id="{$field}" value="{$d.value}" />
	{/if}
	<a href="#" id="{$field}_link" title="{$d.selLinkTitle}" class="vybrat {#btn_primary#}" data="?{$d.selLinkUrl}" data-title="{$d.selLinkTitle}" fields2url="{$d.fields2url}" list2fields="{$d.list2fields}">{$d.selLinkTitle}</a>
	<a href="#" id="{$field}_del" class="odpojit {#btn_primary#}">{$d.remTitle}</a>
{/if}
</div>