<select name="{$c.key}" class="{$class}"{if isset($d.rFields)} fields="{implode pieces=$d.rFields glue=','}"{/if}>
{if isset($d.list)}
{foreach $d.list as $val => $sitem}
<option value="{$val}"{if $val==$d.value} selected="selected"{/if}{if $d.rItems}{foreach $d.rFields $key} {$key}="{$d.rItems[$key][$val]}"{/foreach}{/if}>{$sitem}</option>
{/foreach}
{/if}
</select>