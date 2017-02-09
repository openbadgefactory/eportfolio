{if !$items}
    <p class="message">{str tag=nomessages section=blocktype.announcement}</p>
{else}
<table id="announcementblock" class="itemlist fullwidth fixwidth">
{foreach from=$items item=i name=announcement}
<tr class="{cycle values='r0,r1'}{if $dwoo.foreach.announcement.iteration > 5} extra hidden{/if}">
<td class="icon-container">
  {if $i->read}
      <img src="{theme_url filename=cat('images/' $i->type '.png')}" alt="{$i->strtype}" />
  {else}
      <img src="{theme_url filename=cat('images/' $i->type '.png')}" class="unreadmessage" alt="{$i->strtype}" />
  {/if}
    </td>
    <td>
  {if $i->message}
      <h3 class="title">
        <a href="{if $i->url}{$WWWROOT}{$i->url}{else}{$WWWROOT}account/activity/index.php{/if}" class="inbox-showmessage{if !$i->read} unread{/if}">
        {if !$i->read}<span class="accessible-hidden">{str tag=unread section=activity}: </span>{/if}{$i->subject}
        </a>
      </h3>
      <div class="inbox-message hidden messagebody-{$i->type}" id="inbox-message-{$i->id}">{$i->message|safe}
      {if $i->url}<br><a href="{$WWWROOT}{$i->url}">{if $i->urltext}{$i->urltext} &raquo;{else}{str tag="more..."}{/if}</a>{/if}
      </div>
  {elseif $i->url}
      <a href="{$WWWROOT}{$i->url}">{$i->subject}</a>
  {else}
      {$i->subject}
  {/if}
  <div class="groupuserdate">
        <a href="{profile_url($i->from)}">{$i->author} </a>
        <span class="postedon">- {$i->relativedate}</span>
  </div>
    </td>
</tr>
{/foreach}
</table>

{if count($items) > 5}
    <div class="morelinkwrap">
        <a class="morelink" href="#">{str tag=More section=blocktype.inbox}</a>
    </div>
{/if}

{*{if $desiredtypes}
<div class="morelinkwrap"><a class="morelink" href="{$WWWROOT}account/activity/index.php?type={$desiredtypes}">{str tag=More section=blocktype.inbox} &raquo;</a></div>
<div class="cb"></div>
{/if}*}
<script>
{literal}
addLoadEvent(function() {
    forEach(
{/literal}
        getElementsByTagAndClassName('a', 'inbox-showmessage', '{$blockid}'),
{literal}
        function(element) {
        connect(element, 'onclick', function(e) {
            e.stop();
            var message = getFirstElementByTagAndClassName('div', 'inbox-message', element.parentNode.parentNode);
            var unreadText = getFirstElementByTagAndClassName(null, 'accessible-hidden', element);
            toggleElementClass('hidden', message);
            if (hasElementClass(element, 'unread')) {
                var id = getNodeAttribute(message, 'id').replace(/inbox-message-(\d+)$/, '$1');
                var pd = {'readone':id};
                sendjsonrequest(config.wwwroot + 'account/activity/index.json.php', pd, 'GET', function(data) {
                    removeElementClass(element, 'unread');
                    removeElement(unreadText);
                    updateUnreadCount(data);
                });
            }
        });
    });
});
{/literal}
</script>
{/if}
