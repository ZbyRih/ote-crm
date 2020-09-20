{capture assign=lineLink}{$basicListLink}&amp;{if $LIST.subRecordKey}{$LIST.subRecordKey}{else}{#k_record#}{/if}={/capture}
{if $data}
{foreach from=$data item=menu name=loc}
	<tr class="{cycle values="odd,even"}">
		<td class="MenuItem{if $MODULE.selectedItem == $menu.id} Sel{/if}">
{if $LIST.mode == 'check'}
			<input id="chkb_{$menu.id}" name="chkb_{$menu.id}" type="checkbox"{if isset($menu.checked)} checked="checked"{/if} class="chbx" value="{$menu.id}" />
{/if}
{$odsazeni}{if $smarty.foreach.loc.total == 1 || $smarty.foreach.loc.last }
				{include file='ico.tpl' file_ico=#ico_t_end# class="licona"}

{else}				{include file='ico.tpl' file_ico=#ico_t_mid# class="licona"}

{/if}
{if not $LIST.mode}
{if !$smarty.foreach.loc.last && $smarty.foreach.loc.total > 1}
				<a href="?{$lineLink}{$menu.id}&amp;{#k_action#}=mdown" title="Přesunout položku níž" class="move">{include file='ico.tpl' file_ico=#ico_down#}</a>
{else}
				{include file='ico.tpl' file_ico=#ico_t_empty# class="licona move"}

{/if}
{if !$smarty.foreach.loc.first && $smarty.foreach.loc.total > 1}
				<a href="?{$lineLink}{$menu.id}&amp;{#k_action#}=mup" alt="Přesunout položku víš" class="move">{include file='ico.tpl' file_ico=#ico_up#}</a>
{else}
				{include file='ico.tpl' file_ico=#ico_t_empty# class="licona move"}

{/if}
				<a href="?{$lineLink}{$menu.id}&amp;{#k_action#}=select" title="Vybrat položku" class="txt">[{$menu.id}]&nbsp;{$menu.name}</a>
{elseif $LIST.mode == 'select'}
				<a rel="?{#k_record#}={$menu.id}" title="Vybrat položku" class="txt selm">[{$menu.id}]&nbsp;{$menu.name}</a>
{elseif $LIST.mode == 'check'}
				<span class="txt selm">[<span class="id">{$menu.id}</span>]&nbsp;<span class="name" id="name_{$menu.id}">{$menu.name}</span></span>
{/if}
			</td>
{if not $LIST.mode}

			<td class="Actions">
{assign var=actionLink value="`$lineLink``$menu.id`"}
{if isset($menu.visible)}
				{if $LIST.actions.actionsTpl}{include file="./views/`$LIST.actions.actionsTpl`" data=$LIST.actions basicLink=$actionLink spec=$menu.visible id=$menu.id}{/if}
{else}
				{if $LIST.actions.actionsTpl}{include file="./views/`$LIST.actions.actionsTpl`" data=$LIST.actions basicLink=$actionLink id=$menu.id}{/if}
{/if}
			</td>
{if $LIST.actions.bHasMassActions}		<td><input type="checkbox" value="{$menu.id}" class="mass-action-checkbox"></td>
{/if}
{/if}
		</tr>
{if isset($menu.sub)}
{capture name=tab assign=new_tab}{$odsazeni}				{if $smarty.foreach.loc.last}{include file='ico.tpl' file_ico=#ico_t_empty# class="licona"}{else}{include file='ico.tpl' file_ico=#ico_t_straight# class="licona"}{/if}
{/capture}
{include file='menu.tree.tpl' data=$menu.sub parent=$menu.id odsazeni=$new_tab}
{/if}
{if not $LIST.mode}
{if ( $MODULE.selectedItem == $menu.id )&&( $parent != $MODULE.selectedItem )&&( not isset($menu.sub) ) }
		<tr>
			<td colspan="2">
				{$odsazeni}{if $smarty.foreach.loc.last}{include file='ico.tpl' file_ico=#ico_t_empty# class="licona"}{else}{include file='ico.tpl' file_ico=#ico_t_straight# class="licona"}{/if}
				<img src="{$THEME.imgsPath}{#img_t_empty#}" width="{$smarty.config.icon_size*4}px" class="ln-mrn" />
				<a href="?{$lineLink}{$menu.id}&amp;{#k_action#}=create_def">{include file='ico.tpl' file_ico=#ico_addf#}Přidat pod-položku</a>
			</td>
		</tr>
{/if}
{/if}
{/foreach}
{/if}
{if not $LIST.mode}
<tr>
	<td colspan="{$LIST.headSize}">
		{$odsazeni}<img src="{$THEME.imgsPath}{#img_t_empty#}" width="{$smarty.config.icon_size*4}px" class="ln-mrn" /><a href="?{$lineLink}{if $parent}{$parent}{else}null{/if}&amp;{#k_action#}=create_def">{include file='ico.tpl' file_ico=#ico_addf#}Přidat další položku</a>
	</td>
</tr>
{/if}