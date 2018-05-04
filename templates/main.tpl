<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!-- (c) 2004,2007,2008 jprk -->
<html>
<head>
<title> {$lecture.code} / {include file=$maincolumntitle}</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<!-- feed search engines with reasonable data -->
<meta name="keywords" lang="cs" content="systémy, procesy, matematické modelování">
<meta name="keywords" lang="en" content="systems, processes, mathematical modelling">
<meta name="description" lang="cs" content="Modelování systémů a procesů">
<meta name="description" lang="en" content="Systems and processes">
<!-- styles for the application -->
<link href="style.css" rel="stylesheet" title="formal" type="text/css">
<link href="ex/{$lecture.id}/metadata.css" rel="stylesheet" title="formal" type="text/css">
<link href="stylist.css" rel="stylesheet" title="formal" type="text/css">
<!-- overlay window -->
<link rel="stylesheet" href="css/lightwindow.css" type="text/css" media="screen">
<script type="text/javascript" language="JavaScript1.2">
<!--
/* Global onload list */
var ON_LOAD = [];
-->
</script>
{include file=$htmlareaheader}
{include file=$calendarheader}
</head>
<body>
<!-- body leftmargin="0" topmargin="0" rightmargin="0" bottommargin="0" marginwidth="0" marginheight="0" -->
<!-- map for the login image -->
<map id="loginmap" name="loginmap">
    <area shape="rect" coords="29,16,57,30"  href="http://{$HOST_NAME}{$SCRIPT_NAME}?act=show,login,42"    alt="přihlášení" title="Přihlášení uživatele">
    <area shape="rect" coords="70,16,102,30" href="http://{$HOST_NAME}{$SCRIPT_NAME}?act=delete,login,42"  alt="odhlášení"  title="Odhlášení uživatele">
</map>
<!-- page markup starts here -->
<table width="900" border="0" cellspacing="0" cellpadding="0">
  <tr id="pgrow_title">
    <td class="rightbordersolid">
    <a href="?act=show,home,{$lecture.id}">{*<img src="ex/{$lecture.id}/title.gif" alt="" width="410" height="40" border="0" align="left">*}<img src="ex/{$lecture.id}/title.gif" alt="{$lecture.code}" border="0" align="left"></a>
        {if $lecture.id > 0}<img src="ex/{$lecture.id}/login.gif" alt="" width="112" height="40" border="0" align="right" ismap="ismap" usemap="#loginmap" >{/if}
    </td>
  </tr>
  <tr>
    <td class="backcolor" height="25" align="right"><div style="padding-right:8px" class="user">uživatel: {if $isAdmin == 1 || $isLecturer == 1}<a href="?act=show,user,{$uid}">{$login}</a>{elseif $isStudent == 1}<a href="?act=show,student,{$uid}">{$login}</a>{else}{$login}{/if}</div></td>
  </tr>
  <tr id="pgrow_images">
    <td class="rightbordersolid" style="height: 72px; vertical-align: top;"
        ><div style="position: relative;"><div style="width: 100%; overflow: hidden; white-space: nowrap; position: absolute;"
        ><img src="ex/{$lecture.id}/i1.gif" width="180" height="72" alt="" border="0"
        ><img src="ex/{$lecture.id}/i2.gif" width="180" height="72" alt="" border="0"
        ><img src="ex/{$lecture.id}/i3.gif" width="180" height="72" alt="" border="0"
        ><img src="ex/{$lecture.id}/i4.gif" width="180" height="72" alt="" border="0"
        ><img src="ex/{$lecture.id}/i5.gif" width="180" height="72" alt="" border="0"
        ><img src="ex/{$lecture.id}/i1.gif" width="180" height="72" alt="" border="0"
        ><img src="ex/{$lecture.id}/i2.gif" width="180" height="72" alt="" border="0"
        ><img src="ex/{$lecture.id}/i3.gif" width="180" height="72" alt="" border="0"
        ><img src="ex/{$lecture.id}/i4.gif" width="180" height="72" alt="" border="0"
        ><img src="ex/{$lecture.id}/i5.gif" width="180" height="72" alt="" border="0"
        ></div></div></td>
  </tr>
{if $global_alert}
    <tr>
        <td style="margin: 0px; padding 0px;">{include file=$global_alert}</td>
    </tr>
{/if}
{if $lecture.alert}
    <tr>
        <td class="lecalert">{$lecture.alert}</td>
    </tr>
{/if}
  <tr>
    <td><!-- zacatek aktivni podtabulky s menu a obsahem -->
      <table border="0" cellspacing="0" cellpadding="0">
        <tr><!-- zacatek menu -->
          <td id="pgcol_menu" width="180" height="500" valign="top" bgcolor="#eeeeee" style="min-width: 180px;">
            <table border="0" cellspacing="0" cellpadding="0">
              <tr>
                <td><div style="padding-left:10px; padding-right:10px" class="menu">
{* ----- LEFT MENU ----- *}
{include file=$leftcolumn}
{* --------------------- *}
                  </div></td>
              </tr>
            </table>
          </td>
          <td width="720" valign="top" style="border-right: 1px solid #eeeeee;"><div style="padding: 6px;">
          <table border="0" cellspacing="0" cellpadding="0">
{if $newsList}
            <tr>
              <td valign="top">
                <br><img id="newstitle" src="hg.php?text=Novinky" alt="[Novinky]" border="0">
                <div id="news" valign="top" style="border-top: 1px solid #eeeeee;">
{* ----- NEWS ----- *}
{include file="news.tpl"}
{* ---------------- *}
                </div></td>
              <td align="right" valign="top" rowspan="2"><img src="ex/{$lecture.id}/news.png" width="240" height="158" alt="" border="0"></td>
              </tr>
{/if}
              <tr><!-- zacatek informaci -->
                <td width="718" colspan="2"><br><img id="maintitle" src="hg.php?text={include file=$maincolumntitle}" alt="[{include file=$maincolumntitle}]" border="0"><br></td>
              </tr>
              <tr>
                <td colspan="2"><div id="main" style="border-top: 1px solid #eeeeee;">
{* ----- MAIN TEXT ----- *}
{include file=$maincolumn}
{* --------------------- *}
                </div></td>
              </tr>  
            </table></div>
          </td>
        </tr>
      </table>
    </td><!-- konec aktivni podtabulky s menu a obsahem -->
  </tr>
  <tr>
        <td align="right">
          <div class="backcolor" style="padding: 4px 10px; color: white;">
          <p style="margin: 0px;"><small>
          Školní rok: {$schoolyear}.
{if $section.lastmodified}
          Poslední změna obsahu: {$section.lastmodified|date_format:"%d.%m.%Y %H:%M:%S"}.
{/if}
          Vzniklo díky podpoře grantu FRVŠ 1344/2007{$lecture.thanks}.</small></p>
          </div>
        </td>
  </tr>
</table>
<script type="text/javascript" language="JavaScript1.2">
{literal}
<!--
/* These are the last items before the end of `body`. Do not mess with them until you
   _well_ know what you are doing. */
function onLoadHandler ()
{
  for ( var i = 0 ; i < ON_LOAD.length ; i++ ) ON_LOAD[i]();
}
// Add always present onLoad handlers here
//ON_LOAD[ON_LOAD.length] = onLoadMoveMenu;
window.onload   = onLoadHandler;
{/literal}
-->
</script>
{include file=$calendarfooter}
</body>
</html>
