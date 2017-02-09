<fieldset>
<table id="educationhistorylist{$suffix}" class="tablerenderer resumefive resumecomposite">
    <thead>
        <tr>
            <th class="resumedate">{$startdate}</th>
            <th class="resumedate">{$enddate}</th>
            <th>{$qualification}</th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$rows item=row}
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-head">
            <td><a class="toggle" href="#">{$row->startdate}</a></td>
            <td>{$row->enddate}</td>
            <td>{$row->qualification()}</td>
        </tr>
        <tr class="{cycle values='r0,r0,r1,r1'} expandable-body">
            <td colspan="3">{$row->qualdesc|clean_html|safe}</td>
        </tr>
        {/foreach}
    </tbody>
</table>
</fieldset>
