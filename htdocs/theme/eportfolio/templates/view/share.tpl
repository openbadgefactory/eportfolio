{include file="header.tpl"}

{if $institution}                {$institutionselector|safe}{/if}

{if !$accesslists.views && !$accesslists.collections}
<p>{str tag=youhaventcreatedanyviewsyet section=view}</p>
{else}

{if $accesslists.collections}
<table class="fullwidth accesslists">
  <thead>
    <tr>
      <th>{str tag=Collections section=collection}</th>
      <th>{str tag=accesslist section=view}</th>
      <th class="al-edit">{str tag=editaccess section=view}</th>
      {*<KYVYT*}
      {if $isgroup}
      <th class="al-edit">{str tag=editwriteaccess section=view}</th>
      {/if}
      {*KYVYT>*}
    </tr>
  </thead>
  {*<KYYVT*}
  <tbody>
  {*KYVYT>*}
{foreach from=$accesslists.collections item=collection}
    <tr class="{cycle values='r0,r1'}">
  {include file="view/accesslistrow.tpl" item=$collection}
    </tr>
{/foreach}
  </tbody>
</table>
{/if}

{if $accesslists.views}
<table class="fullwidth accesslists">
  <thead>
    <tr>
      <th>{str tag=Views section=view}</th>
    {if $accesslists.collections}
      <th></th>
      {*<KYVYT*}
      <th class="al-edit">&nbsp;</th>
      {if $isgroup}
      <th class="al-edit">&nbsp;</th>
      {/if}
      {*KYVYT>*}
    {else}
      <th>{str tag=accesslist section=view}</th>
      <th class="al-edit">{str tag=editaccess section=view}</th>
      {*<KYVYT*}
      {if $isgroup}
      <th class="al-edit">{str tag=editwriteaccess section=view}</th>
      {/if}
      {*KYVYT>*}
    {/if}
    </tr>
  </thead>
  <tbody>
{foreach from=$accesslists.views item=view}
    <tr class="{cycle values='r0,r1'}">
  {include file="view/accesslistrow.tpl" item=$view}
    </tr>
{/foreach}
  </tbody>
</table>
{/if}

{/if}
{include file="footer.tpl"}
