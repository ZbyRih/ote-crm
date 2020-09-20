<div class="ajax-multi-select" field="{$c.key}" url="{$d.link}">
	<a href="#" class="remove-all" title="Odstranit vše">{call name=icon icon="fa fa-eraser"}</a>
	{if $FORM.scope->isRecId()}	<a href="#" class="vybrat" title="{$c.title}" url="{$d.select}">{call name=icon icon="md md-blur-linear"}</a>{/if}
	<div class="ms_list">
	{if $d.list}
		{foreach $d.list as $id => $name}
		<span class="item {cycle values="odd,even" name="listCycle"}">[{$id}]&nbsp;{$name} <a href="#" data-id="{$id}" class="remove">{call name=icon icon="md md-delete"}</a></span><br />
		{/foreach}
	{/if}
	</div>
</div>
{*
<script type="text/javascript">{#JS_CD_OPEN#}
	var saveLink = '?{#k_ajax#}={ldelim}param{rdelim}&{#k_module#}={$Module.id}&{#k_record#}={$FORM.recordid}';
	OBEAjax.bindMultiItemRemove('list_of_{$c.key}', saveLink);
	OBEAjax.bindAjaxSelectBox($('#list_of_cl_{$c.key}'), {ldelim}
		  callForContent: '?{#k_ajax#}=select&{#k_module#}={$d.value.moduleid}'
		, field: 'list_of_{$c.key}'
		, buttons: true
		, confirm: AjaxContentHandler.multiListPickFieldConfirm
		, saveLink: saveLink
	{rdelim});
{#JS_CD_CLOSE#}</script>
*}