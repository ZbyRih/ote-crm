		{if $LIST.numbered}<{$elm}>#</{$elm}>{/if}
		{foreach from=$data item=column key=k}
		<{$elm}{if $LIST.sort[$k] !== false} role="sort" class="sort-alpha{if $LIST.sort[$k] == '1'} sorting_asc{elseif $LIST.sort[$k] == '2'} sorting_desc{else} sorting{/if}" si="{$k}"{/if}{if isset($LIST.headInfo[$k])} data-toggle="tooltip" data-palcement="top" data-original-title="{$LIST.headInfo[$k]}"{/if}>
			{$column}
		</{$elm}>
		{/foreach}
		{if $LIST.actions.bIcons}<{$elm} class="nosort">Úkony</{$elm}>{/if}
		{if $LIST.actions.bHasMass}
		<{$elm} class="nosort mascts">
			<input type="checkbox" class="ma">
			<div class="btn-group">
				<button type="button" class="btn  btn-default-light ink-reaction dropdown-toggle" data-toggle="dropdown">
					<i class="fa fa-wrench"></i> <i class="fa fa-caret-down"></i>
				</button>
				<ul class="dropdown-menu" role="menu">
				{foreach $LIST.actions.actions as $action}
					{if !empty($action->icon) && $action->mass}
						{assign value="`$action->icon`" var="ico"}
					{if $action->indent && !$action@first && $action@total > 0}<li class="divider"></li>{/if}
					<li><a href="?{$LIST.scope->getDynLink(NULL, $action->mass)}"{if $action->right > 2} class="btn confirm"{/if} title="{$action->getMassTitle()}">{call name=icon icon=$action->icon} {$action->getMassTitle()}</a></li>
					{if $action->indent && !$action@last && $action@total > 0}<li class="divider"></li>{/if}
					{/if}
				{/foreach}
				</ul>
			</div>
		</{$elm}>
		{/if}