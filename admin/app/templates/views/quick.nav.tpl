<div class="col-lg-12 card-head card-head-nav">
	{if $QUICK_NAV->prev}
		<a role="group" href="?{$QUICK_NAV->scope->getLinkExt($QUICK_NAV->key, $QUICK_NAV->prev)}" title="Předchozí položka" class="{#btn_primary_ink#} col-lg-4">{call icon icon="md md-arrow-back"}<span>{$QUICK_NAV->list[$QUICK_NAV->prev]}</span></a>
	{else}
		<span class="col-lg-4"></span>		
	{/if}
	{if $QUICK_NAV->curr}
{if $QUICK_NAV->cardTitle}	
		<span class="col-lg-4"></span>
{else}
		<span class="{#btn_primary#} disabled col-lg-4" style="opacity:1; background-color: inherit;"><span class="text-primary-dark">{$QUICK_NAV->list[$QUICK_NAV->curr]}</span></span>
{/if}
	{/if}
	{if $QUICK_NAV->next}
		<a href="?{$QUICK_NAV->scope->getLinkExt($QUICK_NAV->key, $QUICK_NAV->next)}" title="Další položka" class="{#btn_primary_ink#} col-lg-4"><span>{$QUICK_NAV->list[$QUICK_NAV->next]}</span>{call icon icon="md md-arrow-forward"}</a>
	{else}
		<span class="col-lg-4"></span>		
	{/if}
</div>