{if $isAdmin || $isLecturer}<span class="editimg"
    ><a href="?act=edit,article,{$articleList[articlePos].id}&returntoparent=1"
    ><img src="images/famfamfam/page_edit.png"
          alt="[edit]" title="editovat tento článek" width="16" height="16"></a
	><a href="?act=delete,article,{$articleList[articlePos].id}&returntoparent=1"
    ><img src="images/famfamfam/page_delete.png"
          alt="[delete]" title="smazat tento článek" width="16" height="16"></a>{/if}