<select name="{$c.key}" class="{$class} {if $d['form-send']}form-send{else}self-send{/if}" >
{foreach $d.list as $val => $sitem}
<option value="{$val}"{if $val==$d.value} selected="selected"{/if}>{$sitem}</option>
{/foreach}
</select>