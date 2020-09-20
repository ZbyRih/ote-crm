{if $LIST.mode == 'check'}
	<input id="chkb_{$id}" name="chkb_{$id}" type="checkbox"{if isset($item.checked)} checked="checked"{/if} class="chbx" value="{$id}" />
{/if}

{if $total == 1 || $last}
	{list_tree_ico ico='tree-end'}
{else}
	{list_tree_ico ico='tree-mid'}
{/if}

{if not $LIST.mode}
	{if !$last && $total > 1 && $LIST.mode != 'select'}
		{list_move_btn smer='down' akce='mdown' title='Přesunout položku níž'}
	{else}
		{list_empty_ico}
	{/if}
	{if !$first && $total > 1 && $LIST.mode != 'select'}
		{list_move_btn smer='up' akce='mup' title='Přesunout položku víš'}
	{else}
		{list_empty_ico}
	{/if}
{/if}