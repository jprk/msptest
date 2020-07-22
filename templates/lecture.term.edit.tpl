<form action="?act=save,lecture.term,{$lecture.id}" method="post">
    <table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
        <tr class="rowA">
            <td class="itemtitle" style="width: 32ex;">Přihlašování do skupin od</td>
            <td>
                <div class="input-group date" id="datetimepicker_f" data-target-input="nearest">
                    <input type="text" name="group_open_from"
                           class="form-control datetimepicker-input input-override-date" data-target="#datetimepicker_f"
                           value="{$termParam.group_open_from|date_format:"%d.%m.%Y %H:%M"}"/>
                    <div class="input-group-append" data-target="#datetimepicker_f" data-toggle="datetimepicker">
                        <div class="input-group-text icon-box-override"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
                {*</div>*}
                <script type="text/javascript">{literal}
                    $(function () {
                        $('#datetimepicker_f').datetimepicker({
                            locale: 'cs'
                        });
                    });
                    {/literal}
                </script>
            </td>
        </tr>
        <tr class="rowB">
            <td class="itemtitle" style="width: 32ex;">Přihlašování do skupin do</td>
            <td>
                <div class="input-group date" id="datetimepicker_t" data-target-input="nearest">
                    <input type="text" name="group_open_to"
                           class="form-control datetimepicker-input input-override-date" data-target="#datetimepicker_t"
                           value="{$termParam.group_open_to|date_format:"%d.%m.%Y %H:%M"}"/>
                    <div class="input-group-append" data-target="#datetimepicker_t" data-toggle="datetimepicker">
                        <div class="input-group-text icon-box-override"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
                <script type="text/javascript">{literal}
                    $(function () {
                        $('#datetimepicker_t').datetimepicker({
                            locale: 'cs'
                        });
                    });
                    {/literal}
                </script>
            </td>
        </tr>
        <tr class="rowA">
            <td>&nbsp;</td>
            <td>
                <input type="submit" value="Uložit">
                <input type="reset" value="Vymazat">
            </td>
        </tr>
    </table>
</form>
