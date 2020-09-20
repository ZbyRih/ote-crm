<div class="graph">
	<div id="chart_{$uID}" class="chart loading">
	{if empty($data.data)}Data nejsou k dispozici{/if}
	</div>
	{if !empty($data.data)}
	<script type="text/javascript">{#JS_CD_OPEN#}
	{if $data.subType == 'line'}
		var data = [{foreach from=$data.data item=i name=f}['{$i.cdate}', {$i.stotal}]{if !$smarty.foreach.f.last}, {/if}{/foreach}];
		OBEAjax.bindGraphLine('chart_{$uID}', '{$data.title}', data);
	{else if $data.subType == 'bar'}
		var vals = [{foreach from=$data.data[0] item=i name=f}{$i|printf:'%.2f'}{if !$smarty.foreach.f.last},{/if}{/foreach}];
		var dates = [{foreach from=$data.data[1] item=i name=f}'{$i}'{if !$smarty.foreach.f.last},{/if}{/foreach}];
		OBEAjax.bindGraphBar('chart_{$uID}', '{$data.title}', vals, dates);
	{/if}
	{#JS_CD_CLOSE#}</script>
	{/if}
</div>