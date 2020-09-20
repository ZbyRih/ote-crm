<select name="{$c.key}" onchange="javascript:document.location = addParamToLink('{$c.key}', this.value, null{if isset($d.letOnly)}, '{$d.letOnly}'{/if});" class="{$class}" >
{foreach $c.list as $val => $sitem}
<option value="{$val}"{if $val==$d.value} selected="selected"{/if}>{$sitem}</option>
{/foreach}
</select>