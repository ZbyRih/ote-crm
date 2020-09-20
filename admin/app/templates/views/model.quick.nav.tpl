<div class="col-lg-12 card-head card-head-nav">
	{if $QUICK_NAV->prev}
		<a role="group" href="?{$QUICK_NAV->scope->getDynLink($QUICK_NAV->prev.recordId)}" title="Předchozí položka" class="{#btn_primary_ink#} col-lg-4">{call icon icon="md md-arrow-back"}<span class="text-sm text-default-light">[{$QUICK_NAV->prev.recordId}]</span>&nbsp;<span>{$QUICK_NAV->prev.name}</span></a>
	{else}
		<span class="col-lg-4"></span>		
	{/if}
	{if $QUICK_NAV->curr}
		<span class="col-lg-4"></span>		
{*		<span class="{#btn_primary#} disabled col-lg-4" style="opacity:1; background-color: inherit;"><span class="text-sm text-default-light">[{$QUICK_NAV->curr.recordId}]</span>&nbsp;<span class="text-primary-dark">{$QUICK_NAV->curr.name}</span></span>*}
	{/if}
	{if $QUICK_NAV->next}
		<a href="?{$QUICK_NAV->scope->getDynLink($QUICK_NAV->next.recordId)}" title="Další položka" class="{#btn_primary_ink#} col-lg-4"><span class="text-sm text-default-light">[{$QUICK_NAV->next.recordId}]</span>&nbsp;<span>{$QUICK_NAV->next.name}</span>{call icon icon="md md-arrow-forward"}</a>
	{else}
		<span class="col-lg-4"></span>		
	{/if}
</div>