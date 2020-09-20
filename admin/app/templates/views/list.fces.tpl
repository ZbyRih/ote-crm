{function list_row_class}{if isset($row.color) && $row.color} {$row.color}{/if}{if $LIST.scope->info->selectedItem!== null && $LIST.scope->info->selectedItem == $id} selected{/if}{/function}
{function list_col_typ}{if $LIST.numTypes[$colid] == 3} dt="currency" class="text-right"{/if}{if $LIST.numTypes[$colid] == 2} dt="numeric" class="text-right"{/if}{/function}
{function list_empty_ico}<span class="ico"></span>{/function}
{function list_tree_ico}<span class="spacer"><span class="ico tree">{if isset($ico)}{icon icon=$ico}{/if}</span></span>{/function}
{function list_move_btn}{call name=btn kind='icon-toggle' icon="md md-keyboard-arrow-`$smer`" link="?`$LIST.scope->getDynLink($id, $akce)`" title=$title more='ink-reaction btn-xs'}{/function}
{function list_tree_add parent=null}<a href="?{$LIST.scope->getDynLink($parent, 'create_def')}" class="tree-add">{icon icon='btn ink-reaction btn-xs md md-add'}Přidat pod-položku</a>{/function}
{function list_tree_add_pad}{if $last}{list_empty_ico}{else}{list_tree_ico ico='tree-straight'}{/if}{/function}