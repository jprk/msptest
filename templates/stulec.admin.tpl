<p>
    V následující tabulce si zvolte studenta, jehož chcete přiřadit ke studentům předmětu <em>{$lecture.title}
    ({$lecture.code})</em>.
</p>
<p class="center">
    {firstletter obj="stulec" act="admin" id=$lecture.id name="first"}
</p>
<table class="admintable table-override" border="0" cellpadding="2" cellspacing="1">
    <tr class="newobject">
        <td colspan="5">Přidat dalšího studenta</td>
        <td width="40" class="smaller" valign="middle"
                ><a href="?act=edit,student,0"><img src="images/famfamfam/report_add.png" title="přidat studenta"
                                                    alt="[nový student]" width="16" height="16"></a></td>
    </tr>
{section name=sId loop=$studentList}
    {if $smarty.section.sId.iteration is even}
    <tr class="rowA">
    {else}
    <tr class="rowB">
    {/if}
        <td>{$studentList[sId].surname}</td>
        <td>{$studentList[sId].firstname}</td>
        <td>{$studentList[sId].yearno}</td>
        <td>{$studentList[sId].groupno}</td>
        <td>{$studentList[sId].id}</td>
        <td width="16" class="smaller" valign="middle"
                ><a href="?act=save,stulec,{$lecture.id}&student_id={$studentList[sId].id}"
                ><img src="images/famfamfam/user_go.png" alt="[přidat]" title="přidat" width="16" height="16"></a></td>
    </tr>
{/section}
</table>
