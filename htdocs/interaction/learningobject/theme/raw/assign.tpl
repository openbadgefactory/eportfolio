{include file="header.tpl"}

<div class="subpage" id="editaccess">

    <div id="editaccess_accesslist_container">

        <div id="editaccesswrap">

            <div id="potential-recipients" class="presets-container">

                <h2>{str tag=selectassignees section="interaction.learningobject"}</h2>

                <div id="institution-selector-container">
                    <button class="button" type="button" id="add-institution">{str tag=add}</button>
                    {$institutionselector|safe}
                </div>

                <div id="group-selector-container">
                    <button class="button" type="button" id="add-group">{str tag=add}</button>
                    <select id="group-selector">
                        <option value="-1">{str tag=selectgroup section="interaction.learningobject"}</option>
                        {if $groups}
                            {foreach from=$groups item=group}
                                <option value="{$group->id}">{$group->name}</option>
                            {/foreach}
                        {/if}
                    </select>
                </div>

                <div id="student-list">
                    <table id="students">
                        <tbody></tbody>
                    </table>
                </div>

                <h2>{str tag=sharewithmygroups section=view}</h2>
                {if $mygroups}
                    <ul id="mygroups">
                        {foreach from=$mygroups item=mygroup}
                            <li>
                                <button class="button" type="button" data-id="{$mygroup->id}">{str tag=add}</button>
                                <span>{$mygroup->name}</span>
                            </li>
                        {/foreach}
                    </ul>
                {/if}

                <h2>{str tag=addinstructor section="interaction.learningobject"}</h2>

                <div id="potential-instructors-container">
                    <p>{str tag=searchinstructorshelp section="interaction.learningobject"}</p>

                    <form id="instructor-search" method="get">
                        <input type="text" id="search-instructors" />
                        <button type="submit" class="btn-search" id="dosearch">{str tag=search}</button>
                    </form>

                    <div>
                        <table id="potential-instructors">
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div id="assignment-users">

                <div id="assignment-settings">
                    <h2>{str tag=assignmentsettings section="interaction.learningobject"}</h2>

                    <div class="form-row">
                        <label for="assignment-return-date">{str tag=assignmentreturndate section="interaction.learningobject"}</label>
                        <input type="text" id="assignment-return-date" value="{date_format $learningobject->get('return_date') $date_format}" />
                        <a href="#" id="assignment-return-date-btn" class="pieform-calendar-toggle">
                            <img src="{theme_url filename=images/btn_calendar.png}" alt="{str tag="element.calendar.opendatepicker" section="pieforms"}" />
                        </a>

                        <div class="form-item-description">{str tag=assignmentreturndatedescription section="interaction.learningobject"}</div>
                    </div>
                </div>

                <div id="assignment-recipients">
                    <h2>{str tag=assignmentrecipients section="interaction.learningobject"}</h2>
                    <table id="recipients">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>{str tag=assignee section="interaction.learningobject"}</th>
                                <th>{str tag=assignmentdate section="interaction.learningobject"}</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            {if $assignees}
                                {foreach from=$assignees item=assignee}
                                    <tr id="{$assignee->type}-{$assignee->id}" data-type="{$assignee->type}" data-value="{$assignee->id}">
                                        <td class="profileicon">
                                            {if $assignee->type == 'user'}
                                                <img src="{profile_icon_url user=$assignee->id maxwidth=25 maxheight=25}" />
                                            {else}
                                                <span>&nbsp;</span>
                                            {/if}
                                        </td>
                                        <td>{$assignee->name}</td>
                                        <td>
                                            <input type="text" id="date-{$assignee->type}-{$assignee->id}" class="assignedat" size="15" value="{$assignee->formatted_assignment_date}" />
                                            <a href="#" id="date-{$assignee->type}-{$assignee->id}-btn" class="pieform-calendar-toggle">
                                                <img src="{theme_url filename=images/btn_calendar.png}" alt="{str tag="element.calendar.opendatepicker" section="pieforms"}" />
                                            </a>
                                        </td>
                                        <td class="removebutton">
                                            <button class="button remove" type="button">{str tag=remove}</button>
                                        </td>
                                    </tr>
                                {/foreach}
                            {/if}
                        </tbody>
                        <tfoot{if count($assignees) > 0} style="display: none"{/if}>
                            <tr>
                                <td colspan="4">{str tag=notassignedyet section="interaction.learningobject"}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div id="assignment-instructors">
                    <h2>{str tag=assignmentinstructors section="interaction.learningobject"}</h2>
                    <table id="instructors">
                        <thead>
                            <tr>
                                <th>&nbsp;</th>
                                <th>{str tag=instructorname section="interaction.learningobject"}</th>
                                <th>&nbsp;</th>
                            </tr>
                        </thead>
                        <tbody>
                            {if $instructors}
                                {foreach from=$instructors item=instructor}
                                    <tr id="instructor-{$instructor->user}" data-value="{$instructor->user}">
                                        <td class="profileicon">
                                            <img src="{profile_icon_url user=$instructor->user maxwidth=25 maxheight=25}" />
                                        </td>
                                        <td>{$instructor->name}</td>
                                        <td class="removebutton">
                                            <button type="button" class="button remove">{str tag=remove}</button>
                                        </td>
                                    </tr>
                                {/foreach}
                            {/if}
                        </tbody>
                        <tfoot{if count($instructors) > 0} style="display: none"{/if}>
                            <tr>
                                <td colspan="4">{str tag=noinstructorsyet section="interaction.learningobject"}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

            </div>

            <div class="cb"></div>

        </div>

    </div>

    <button type="button" class="button" id="assign">{str tag=save}</button>
    <a class="btn" href="{$WWWROOT}interaction/learningobject/index.php">{str tag=cancel}</a>

</div>

{include file="footer.tpl"}