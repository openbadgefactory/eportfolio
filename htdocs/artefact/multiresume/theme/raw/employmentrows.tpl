{if $rows}
<table id="employmentrows" class="tablerenderer resumefive resumecomposite multi{$id}">
    <thead>
        <tr>
            <th class="resumecontrols"></th>
            <th class="resumedate">{str tag='startdate' section='artefact.resume', lang=$lang}</th>
            <th class="resumedate">{str tag='enddate' section='artefact.resume', lang=$lang}</th>
            <th>{str tag='position' section='artefact.resume', lang=$lang}</th>
            <th class="resumecontrols"></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-head">
            <td class="buttonscell">
            <img src="{theme_url filename='images/btn_moveup.png'}" alt="" class="uparrow" />
            <img src="{theme_url filename='images/btn_movedown.png'}" alt="" class="downarrow" />
            </td>
            <td class="toggle">{$row->startdate}</td>
            <td>{$row->enddate}</td>
            <td>{$row->jobtitle}: {$row->employer}</td>
            <td class="buttonscell">
            <img src="{theme_url filename='images/btn_edit.png'}" alt="" class="edit_row" />
            <img src="{theme_url filename='images/btn_deleteremove.png'}" alt="" class="delete_row" />
            </td>
        </tr>
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-body">
            <td class="buttonscell"></td>
            <td colspan="3">{$row->jobdesc|clean_html|safe}</td>
            <td class="buttonscell"></td>
        </tr>
        {/foreach}
    </tbody>
</table>
{/if}
<div class="add-resumerow">
<a href="edit_row.php?cv={$cv}&amp;id={$id}" title="{str tag=add, lang=$lang}" class="btn">{str tag=addemprow, section=artefact.multiresume, lang=$lang}</a>
</div>
