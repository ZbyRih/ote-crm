<div class="cloud tag-list">
	<ol>
	{foreach from=$CLOUD.items item=c key=id}
		<li><span>{$c.name}&nbsp;({$c.count})</span><a href="?{#k_module#}={$Module.id}&amp;{#k_view#}={$Module.view}&amp;{#k_action#}={$CLOUD.del}&amp;{#k_record#}={$id}" title="Smazat" class="icony delete"><span class="ui_icos _d del"></span></a></li>
	{/foreach}
	</ol>
	<div class="clr"></div>
</div>
