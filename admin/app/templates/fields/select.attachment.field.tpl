<div class="select-content" id="{$c.key}_FI">
{$field=$c.key}
	<input type="hidden" name="{$c.key}" id="{$c.key}" value="{if isset($d.value)}{$d.value}{/if}" />
	{if isset($d.img)}{include file='fields/variable.media.tpl' i=$d.img}{/if}
	</a>
	<div class="txt">
		<a href="#" id="{$field}_link" title="{$d.selLinkTitle}" class="vybrat {#btn_primary#}" data="?{$d.selLinkUrl}" fields2url="{$d.fields2url}" list2fields="{$d.list2fields}">{$d.selLinkTitle}</a>
		{if isset($d.img)}<a href="#" id="{$field}_del" class="odpojit {#btn_primary#}">{$d.remTitle}</a>{/if}
	</div>
	<div class="clr"></div>
</div>