{if $isAdmin || $isLecturer}<span class="editimg"
  ><a href="?act=edit,section,0&parent={$section.id}"
  ><img src="images/famfamfam/page_add.png"
        alt="[nová podsekce]" title="založit novou podsekci" width="16" height="16"></a
  ><a href="?act=edit,section,0&parent={$section.id}&returntoparent=1&amp;copy_parent=1"
  ><img src="images/famfamfam/page_copy.png"
        alt="[nová sekce jako kopie]" title="kopírovat sekci" width="16" height="16"></a
  ><a href="?act=edit,article,0&parent={$section.id}&type=4&returntoparent=1"
  ><img src="images/famfamfam/page_white_add.png"
        alt="[nový článek]" title="vložit nový článek" width="16" height="16"></a
  ><a href="?act=edit,file,0&objid={$section.id}&type=1&returntoparent=1"
  ><img src="images/famfamfam/attach.png"
    alt="[nový soubor]" title="připojit nový soubor" width="16" height="16"></a
  ><a href="?act=edit,section,{$section.id}"
  ><img src="images/famfamfam/page_edit.png"
    alt="[edit]" title="editovat text této stránky" width="16" height="16"></a
  ><a href="?act=delete,section,{$section.id}&returntoparent=1"
  ><img src="images/famfamfam/page_delete.png"
    alt="[delete]" title="smazat tuto stránku" width="16" height="16"></a
  ></span>{/if}
