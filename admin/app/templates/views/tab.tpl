{if !empty($e->items)}
	<ul class="nav nav-tabs">
{foreach $e->items as $k => $tab}
		{if $e->value == $k}<li class="active"><a href="javascript:void(0)'">{$tab}{if isset($e->badges[$k])}<sup class="badge style-{$e->badges[$k]['t']}">{$e->badges[$k]['v']}</sup>{/if}</a></li>
		{else}
		<li><a href="?{$Module.scope->getLink()}&amp;{$e->inputKey}={$k}">{$tab}{if isset($e->badges[$k])}<sup class="badge style-{$e->badges[$k]['t']}">{$e->badges[$k]['v']}</sup>{/if}</a></li>
		{/if}
{/foreach}	
	</ul>
{/if}