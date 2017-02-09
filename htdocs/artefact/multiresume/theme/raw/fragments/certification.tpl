<fieldset>
<table id="certificationlist{$suffix}" class="tablerenderer resumefour resumecomposite">
    <thead>
        <tr>
            <th class="resumedate">{$date}</th>
            <th>{$title}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-head">
            <td><a href="#" class="toggle">{$row->date}</a></td>
            <td>{$row->title}</td>
        </tr>
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-body">
            <td colspan="2">{$row->description|clean_html|safe}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
</fieldset>
