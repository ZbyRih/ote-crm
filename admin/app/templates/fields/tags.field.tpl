<select id="{$c.key}" name="{$c.key}" multiple data-role="_tagsinput" style="display: none;" placeholder="vepište a potvrdtě enterem"{if $d.allowCreate} create="true"{/if}{if $d.allowAjax} ajax="true"{/if}>
{foreach $d.list as $id => $name}
	<option id="{$id}" value="{$name}">{$name}</option>
{/foreach}
</select>