<div class="actions">
{foreach from=$a.actions item=action}
	{if !empty($action->icon)}
		{if $action->indent && !$action@first && $action@total > 1}<i class="fa"></i>{/if}
		{assign value=false var=block}
		{switch $action->action}
{case 'none'}{assign value=true var=block}{/case}
{case 'hide'}{if !$spec}{assign value=true var=block}{/if}{/case}
{case 'show'}{if $spec}{assign value=true var=block}{/if}{/case}
{case 'mup'}{if $first_row == "none" || $first_row == "first"}{call name=icon icon=none}{assign value=true var=block}{/if}{/case}
{case 'mdown'}{if $first_row == "none" || $first_row == "last"}{call name=icon icon=none}{assign value=true var=block}{/if}{/case}
		{/switch}
		{if !$block}{if $action->right > 2}{call name=btn kind="icon-toggle" icon=$action->icon link="?`$sc->getDynLink($id, $action->action)`" title=$action->title more='ink-reaction confirm'}{else}{call name=btn kind="icon-toggle" icon=$action->icon link="?`$sc->getDynLink($id, $action->action)`" title=$action->title more='ink-reaction'}{/if}{/if}
	{/if}
	{if $action->indent && !$action@last && $action@total > 1}<i class="fa"></i>{/if}
{/foreach}
</div>