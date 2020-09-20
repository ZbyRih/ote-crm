<p>
{if isset($d.list)}
{foreach $d.list as $val => $sitem}
<div class="checkbox checkbox-styled">
	<label>
		<input type="checkbox" {if is_array($d.value) && in_array($val, $d.value)}checked="checked"{/if} value="{$val}" class="{$class}" name="{$c.key}[]">
		<span>{$sitem}</span>
	</label>
</div>
{/foreach}
{/if}
</p>