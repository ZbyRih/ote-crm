<div class="statistiky">
<h3>{$data.blockName}{if !empty($data.subName)}&nbsp;<small>{$data.subName}</small>{elseif !empty($data.title)}&nbsp;<small>{$data.title}</small>{/if}</h3>
{switch $data.type}
{case 'list'}{if isset($data.data.LIST)}{include file='./views/list.3.tpl' LIST=$data.data.LIST}{/if}{/case}
{case 'graph'}{include './graph.tpl' data=$data uID=$uID}{/case}
{/switch}
</div>