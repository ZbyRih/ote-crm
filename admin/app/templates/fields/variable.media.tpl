{if $i}
{if $i->type == 'image'}
{if $image_prev == 'list'}
{image image=$i title=$i->name path='../' resize='100x100'}
{else}
{image image=$i title=$i->name path='../' resize='200x200'}
{/if}{elseif $i->type == 'flash'}
<span>{$i->file}</span>{elseif $i->type == 'video'}
<span>{$i->file}</span>{elseif $i->type == 'other'}
<img src="{if $i->ico}{$THEME.imgsPath}{$i->ico}{else}{$THEME.imgsPath}{#ico_adr#}{#ico_EXT_unknow#}{/if}" width="{#icon_size#}" height="{#icon_size#}" class="icony" /> [{$i->fsize} B]
<span>{$i->file}</span>{elseif !empty($i->file)}
<span>{$i->file}</span>{/if}
{else}<img src="{$THEME.imgsPath}{#select_picture#}" height="{#sel_pic_h#}" width="{#sel_pic_w#}" />
{/if}