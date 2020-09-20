{$e = $element}
{switch $e->type}
{case 'list'}{include '../views/list.3.tpl' LIST = $e->data.LIST}{/case}
{case 'glist'}{include '../views/grupped.list.2.tpl' LIST = $e->data.LIST}{/case}
{case 'tree'}{include '../views/tree.tpl' LIST = $e->data.LIST}{/case}
{case 'form'}{include '../views/form.tpl' FORM = $e->data.EFORM}{/case}
{case 'form_start'}{include '../views/form.begin.tpl' FORM = $e->data.EFORM}{/case}
{case 'form_end'}{include '../views/form.end.tpl' FORM = $e->data.EFORM}{/case}
{case 'cloud'}{include '../views/cloud.tpl' CLOUD = $e->data}{/case}
{case 'field'}{include $e->data.tpl c = $e->data d = $e->data.data class = "form-control"}{/case}
{case 'quick_nav'}{include '../views/quick.nav.tpl' QUICK_NAV = $e}{/case}
{case 'model_quick_nav'}{include '../views/model.quick.nav.tpl' QUICK_NAV = $e}{/case}
{case 'html'}{include '../views/html.tpl' data = $e->data}{/case}
{case 'file'}{include "`$e->data.file`.tpl" data = $e->data.data}{/case}
{case 'link'}
{$more  =  ($e->popup)? ' popup' : null}
{if $e->confirm}
	{call inkbtn icon = $e->ico title = $e->fraze text = $e->fraze more = "confirm btn-primary`$more`" link = "?`$e->link`"}
{else}
	{call inkbtn icon = $e->ico title = $e->fraze text = $e->fraze more = "btn-primary`$more`" link = "?`$e->link`"}
{/if}
{/case}
{case 'catchExt'}schvalne jestli tohle jeste nekde uvidim - odsouzeno k zaniku{/case}
{case 'stats'}
	<div>
		<h4>{$e->data.title}</h4>
		<ul>
		{if isset($e->data.statuss)}
		{foreach from = $e->data.statuss item = item}
			<li>{$item}</li>
		{/foreach}
		{/if}
		</ul>
	</div>
{/case}
{case 'jump'}<meta http-equiv = "refresh" content = "{#redirect_speed#}; URL = ?{$e->data.jumpto}" />{/case}
{case 'onSameLine'}{foreach from = $e.lineItems item = liElement}<div class = "in-line">{include './module.view.tpl' element = $liElement}</div>{/foreach}<div class = "clr"></div>{/case}
{case 'packet'}{include '../views/packet.tpl' element = $e}{/case}
{case 'statistics'}{include '../views/statistiky.tpl' data = $e->data uID = $e->uID}{/case}
{case 'tab'}{include '../views/tab.tpl' e = $e uID = $e->uID}{/case}
{case 'import'}{include '../views/import.tpl' data = $e->data}{/case}
{case 'export'}{include '../views/export.tpl' data = $e->data}{/case}
{case 'new_card'}{include '../views/new.card.tpl' element = $e}{/case}
{case 'message'}{include '../views/message.tpl' element = $e}{/case}
{case 'preformat'}{include '../views/preformat.tpl' element = $e}{/case}
{case 'ajax'}
	<a name = "#back_{$uid}" ></a>
	<a href = "#back_{$uid}" title = "Přidat {$e->data.fraze}" class = "ajax-select {$e->data.class}" sel = "{$e->data.selLink}" sav = "?{$e->data.saveLink}">Přidat {$e->data.fraze}&nbsp;{if $e->data.ico}{call icon icon = $e->data.ico}{/if}</a>
{/case}
{default}Element View <b>`{$e->type}`</b> nema sablonu!
{/switch}