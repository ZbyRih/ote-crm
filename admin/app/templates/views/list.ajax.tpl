		{if $LIST.ajaxTemplItems}
		<div class="dataTables_ajax" uid="{$LIST.listUID}_ajax">
		{foreach from=$LIST.ajaxTemplItems item=c key=k}
			{if $c}
			<div class="ajax_edit_item" field="{$c.key}"{if $LIST.numTypes[$k] == 3} dt="currency"{/if}>
				{include $c.tpl c=$c d=$c.data class="form-control" attributes=""}
			</div>
			{/if}
		{/foreach}
		</div>
		{/if}