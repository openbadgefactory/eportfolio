{include file="header.tpl"}
            <div class="rbuttons">
                <a class="btn" href="{$WWWROOT}artefact/multiresume/settings.php">{str section="artefact.multiresume" tag="newresume"}</a>
            </div>
            <div id="myresumes rel">
{if !$resumes}
           <div>{str tag=youhavenoresumes section=artefact.multiresume}</div>
{else}
        <div id="resumelist" class="fullwidth listing">
            {foreach from=$resumes item=resume}
                    <div class="{cycle values='r0,r1'} listrow">
                        <h3 class="title"><a href="{$WWWROOT}artefact/multiresume/edit.php?id={$resume->id}">{$resume->title}</a></h3>
                        <div class="fr nowrap">
                            <span class="btns2">
                                <a href="{$WWWROOT}artefact/multiresume/settings.php?id={$resume->id}" title="{str tag=settings}"><img src="{theme_url filename='images/btn_edit.png'}" alt="{str tag=settings}"></a>
                                <a href="{$WWWROOT}artefact/multiresume/delete.php?id={$resume->id}" title="{str tag=delete}"><img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}"></a>
                            </span>
                        </div>
                        <div id="resumedesc"><p>{$resume->description}</p></div>
                    <div class="cb"></div>
                    </div>
                {/foreach}
            
        </div>
          <!-- <table id="resumelist" class="tablerenderer">
             <thead>
               <tr><th></th><th></th><th></th></tr>
             </thead>
             <tbody>
                {foreach from=$resumes item=resume}
                    <tr class="{cycle values='r0,r1'}">
                    <td><strong><a href="{$WWWROOT}artefact/multiresume/edit.php?id={$resume->id}">{$resume->title}</a></strong></td>
                    <td>{$resume->description}</td>
                    <td class="right buttonscell btns2">
                    <a href="{$WWWROOT}artefact/multiresume/settings.php?id={$resume->id}" title="{str tag=settings}"><img src="{theme_url filename='images/btn_edit.png'}" alt="{str tag=settings}"></a>
                    <a href="{$WWWROOT}artefact/multiresume/delete.php?id={$resume->id}" title="{str tag=delete}"><img src="{theme_url filename='images/btn_deleteremove.png'}" alt="{str tag=delete}"></a>
                    </td>
                    </tr>
                {/foreach}
             </tbody>
           </table> -->
{/if}
                </div>
{include file="footer.tpl"}

