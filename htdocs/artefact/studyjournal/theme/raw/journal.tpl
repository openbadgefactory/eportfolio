{if !$plain}
    {include file="header.tpl"}

    <h1>{$journal->get('title')}</h1>

    <div class="rbuttons">
        <a class="btn" href="{$WWWROOT}artefact/studyjournal/student/post.php?id={$journal->get('id')}">{str tag=createjournalentry section="artefact.studyjournal"}</a>
        {if $publishjournalform}
            {$publishjournalform|safe}
        {/if}
    </div>
{/if}

{if $journal->get('description')}
    <div id="journal-description">{$journal->get('description')|safe}</div>    
{/if}

<div id="journal-form">
{* Separate journal page *}
{if $dateform}
    {$dateform|safe}

    {* In a block/view, use Ajax. *}
{else}
    <div>
        <input type="hidden" id="viewid" value="{$view}" />
        <input type="hidden" id="blockid" value="{$block}" />

        <select class="select" id="dateselect-filter" name="filter">
            {foreach from=$dates key=value item=name}
                <option value="{$value}">{$name}</option>
            {/foreach}
        </select>

        <button id="dateselect-submit">{str tag=updatejournalpage section="artefact.studyjournal"}</button>
    </div>

    <script type="text/javascript">
        $j('#dateselect-submit').click(function() {
            var filter = $j('#dateselect-filter').val();
            var paginator = $j('#studyjournal-pagination-' + $j('#blockid').val());

            paginator[filter === 'alldates' ? 'show' : 'hide']();

            // If the user has selected to display entries paginated, the
            // paginator needs to be initialized. The only way to do this is
            // to emulate the click event on the paginator's "first page"-link.
            if (filter === 'alldates') {
                var firstlink = paginator.find('.first a');

                if (firstlink.size() > 0) {
                    actuateLink(firstlink[0]);
                }

                return;
            }

            // No pagination, just get the entries normally.
            var params = {
                filter: filter,
                id: $j('#viewid').val(),
                block: $j('#blockid').val(),
                offset: 0
            };

            sendjsonrequest(window.config.wwwroot + 'artefact/studyjournal/student/entries.json.php', params, 'GET', function(data) {
                $j('#journal-entries').html(data['data']['tablerows']);
            });
        });

        // http://blog.stchur.com/2010/01/15/programmatically-clicking-a-link-in-javascript/
        function actuateLink(link) {
            var allowDefaultAction = true;

            if (link.click) {
                link.click();
                return;
            }
            else if (document.createEvent) {
                var e = document.createEvent('MouseEvents');
                e.initEvent(
                        'click'     // event type
                        , true      // can bubble?
                        , true      // cancelable?
                        );
                allowDefaultAction = link.dispatchEvent(e);
            }

            if (allowDefaultAction) {
                var f = document.createElement('form');
                f.action = link.href;
                document.body.appendChild(f);
                f.submit();
            }
        }
    </script>
{/if}
</div>

{if !$entries}
    <p>{str tag=noentries section="artefact.studyjournal"}</p>
{else}
    <ol id="journal-entries" class="postlist">
        {foreach from=$entries item=entry}
            <li class="post">
                {include file="entry.tpl" entry=$entry view=$view}
            </li>
        {/foreach}
    </ol>

    {if $pagination}
        {$pagination.html|safe}
    {/if}

    {if $pagination.javascript}
        <script type="text/javascript">
            addLoadEvent(function() {literal}{{/literal}
            {$pagination.javascript|safe}
            {literal}}{/literal});
        </script>
    {/if}
{/if}

{if !$plain}
    {include file="footer.tpl"}
{/if}