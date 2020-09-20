{foreach $FORM.elements as $i}
{if $i.key}
		<td>{if $i.tpl}{include $i.tpl c=$i d=$i.data class="form-control"}{/if}</td>
{else}
	<td></td>
{/if}
{/foreach}