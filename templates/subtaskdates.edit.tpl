<p>
V níže uvedeném formuláři můžete změnit datumy omezující dobu odevzdání
dílčí úlohy <em>{$subtask.title}</em>.
</p>
<form name="subtaskForm" action="?act=save,subtaskdates,{$subtask.id}" method="post">
<input type="hidden" name="subtask_id" value="{$subtaskdates.subtask_id}">
<input type="hidden" name="year" value="{$subtaskdates.year}">
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
<tr class="rowA">
<td class="itemtitle" style="width: 16ex;">Odevzdání od</td>
<td>
    {*<input type="text" name="datefrom" maxlength="10" size="10" value="{$subtaskdates.datefrom|date_format:"%d.%m.%Y"}">&nbsp;&nbsp;<img src="images/calendar.gif" alt="[kalendář]" onClick="openCalendar('subtaskForm','datefrom');">*}
    {*<div class="form-group">*}
        <div class="input-group date" id="datetimepicker1">
            <input type="text" name="datefrom" class="input-override form-control" value="{$subtaskdates.datefrom|date_format:"%d.%m.%Y %H:%M"}"/>
            <span class="input-group-addon glyphicon-override">
                <span class="glyphicon glyphicon-calendar"></span>
            </span>
        </div>
    {*</div>*}
    <script type="text/javascript">{literal}
        $(function () {
            $('#datetimepicker1').datetimepicker({
                locale: 'cs'
            });
        });
        {/literal}
    </script>
</td>
</tr>
<tr class="rowB">
<td class="itemtitle" style="width: 16ex;">Odevzdání do</td>
<td>
    {*<input type="text" name="dateto" maxlength="10" size="10" value="{$subtaskdates.dateto|date_format:"%d.%m.%Y"}">&nbsp;&nbsp;<img src="images/calendar.gif" alt="[kalendář]" title="otevři kalendář" onClick="openCalendar('subtaskForm','dateto');">*}
    {*<div class="form-group">*}
    <div class="input-group date" id="datetimepicker2">
        <input type="text" name="dateto" class="input-override form-control" value="{$subtaskdates.dateto|date_format:"%d.%m.%Y %H:%M"}"/>
            <span class="input-group-addon glyphicon-override">
                <span class="glyphicon glyphicon-calendar"></span>
            </span>
    </div>
    {*</div>*}
    <script type="text/javascript">{literal}
        $(function () {
            $('#datetimepicker2').datetimepicker({
                locale: 'cs'
            });
        });
        {/literal}
    </script>
</td>
</tr>
<tr class="submitrow">
<td>&nbsp;</td>
<td>
<input type="submit" value="Uložit">
<input type="reset" value="Vymazat">
</td>
</tr>
</table>
</form>
