{if $LIST.actions.bIcons && !isset($LIST.ajax)}
	<{$elm} class="actions">{if $LIST.actions.Tpl}{include "./`$LIST.actions.Tpl`" sc=$LIST.scope a=$LIST.actions id=$id spec=$row.spec first_row=$frs}{/if}</{$elm}>
{/if}
{if $LIST.actions.bHasMass}		<{$elm}><input type="checkbox" value="{$id}" class="ma"></{$elm}>
{/if}
