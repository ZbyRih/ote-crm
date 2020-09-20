<input type="hidden" name="{$c.key}" value="{$d.value}" />
<select name="v_{$c.key}" disabled="disabled" class="{$class}" >
{foreach $d.list as $val => $sitem}
<option value="{$val}"{if $val==$d.value} selected="selected"{/if}>{$sitem}</option>
{/foreach}
</select>