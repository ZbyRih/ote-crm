{if !empty($LIST.filter)}
<div class="dataTables_filter" id="filter_{$LIST.listUID}" uid="{$LIST.listUID}">
	<i class="fa fa-search"></i>
	{foreach from=$LIST.filter item=filt}<label for="{$filt.key}">{$filt.title}</label>{include file=$filt.tpl c=$filt d=$filt.data class="form-control" attributes=""}{/foreach}
	<br />
	<button type="button" value="Filtrovat" name="filter" id="filter_{$LIST.listUID}_submit" class="{#btn_primary_ink#}">Filtrovat</button>
	<button type="button" value="Zrušit" name="rFilter" id="filter_{$LIST.listUID}_reset" class="{#btn_primary_ink#}">Zrušit</button>
</div>
{/if}