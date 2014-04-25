<?php
  // ==========================================================================
  // Altered for the K611 web
  // ==========================================================================
  
  /* We expect two parameters to be supplied to this script via GET method:
     (1) `objid` determining the identifier of the object we are editing
         (this will be some section id or article id),
     (2) `ftype` determining the file type (mostly FT_S_IMAGE or FT_A_IMAGE). */
  $objid = $_GET['objid'];
  $ftype = $_GET['ftype'];

  /* Global configuration */
  require ( '../../../config.php' );

  /* First include classes implementing parts of the interface to our
     web application. */
  require ( REQUIRE_DIR . 'CPPSmarty.class.php');
  require ( REQUIRE_DIR . 'DBWrap.class.php' );
  require ( REQUIRE_DIR . 'BaseBean.class.php');
  require ( REQUIRE_DIR . 'DatabaseBean.class.php');
  require ( REQUIRE_DIR . 'FileBean.class.php');
  require ( REQUIRE_DIR . 'SessionDataBean.class.php');
    
  /* Make sure we will use UTF-8 output */
	header ("Content-Type: text/html; charset=utf-8");

  /* Fetch / initialize session */
  session_start ();
  
  /* Construct a Smarty instance. Configuration has been specified in
     config.php. */
  $smarty = new CPPSmarty ( $config, true );

  /* Initialise database connection. */
  $smlink = $smarty->dbOpen ();

  /* Create an instance of file processing bean. */
  $fileBean = new FileBean ( 0, $smarty, "", "" );
  
  /* Check the file upload. */
  if ( array_key_exists ( 'userfile', $_FILES ))
  {
  	if ( $_FILES['userfile'] )
  	{
      /* This would be normally called by `actionHandler()` method in the
         contoller, but we do not use MVC here. We will call the action directly
         and react on possible errors after examining the assigned Smarty
         variables. */
      $fileBean->doSave();
      /* In this case $objid and $ftype would not be set properly as they are
         not sent via GET request. Set them now. */
      $objid = $fileBean->objid;
      $ftype = $fileBean->type;
    }
  }
  
  /* Query the list of files. */
  $imageList = $fileBean->dbQueryObjectFiles ( $objid, $ftype );
?>
<html>

<head>
  <title>Insert Image</title>
	
<link rel="stylesheet" type="text/css" href="../../popups/popup.css" />
<script type="text/javascript" src="../../popups/popup.js"></script>

<script type="text/javascript">

var HTMLArea = window.opener.HTMLArea;

/* Create a list of alternative file descriptions. */
var IMG_ALT = new Array();
<?php
  if ( ! empty ( $imageList ))
  {
    foreach ( $imageList as $val )
    {
      echo 'IMG_ALT[' . $val['id']. '] = "' . $val['description'] .'";';
    }
  }
?>


/* Remember the base URL of the application. All image URLs are just HTTP GET
   request extensions of this URL. */
<?php
  echo 'var BASE_URL = "' . BASE_DIR . '/' . CONTROLLER_NAME . '";'; 
?>


function i18n(str) {
  return (HTMLArea._lc(str, 'HTMLArea'));
}

function Init() {
  __dlg_translate("InsertPicture");
  __dlg_init();
  window.resizeTo(500, 500);
  // Make sure the translated string appears in the drop down. (for gecko)
  document.getElementById("f_align").selectedIndex = 1;
  document.getElementById("f_align").selectedIndex = 5;
  var param = window.dialogArguments;
  if (param)
  {
      /* Initialise the values in the dialog with parameters of the current
         image passed from the editor. */ 
      document.getElementById("f_url").value    = param["f_url"];
      document.getElementById("f_alt").value    = param["f_alt"];
      document.getElementById("f_border").value = param["f_border"];
      document.getElementById("f_align").value  = param["f_align"];
      document.getElementById("f_vert").value   = param["f_vert"];
      document.getElementById("f_horiz").value  = param["f_horiz"];
      document.getElementById("f_height").value = param["f_height"];
      document.getElementById("f_width").value  = param["f_width"];
      /* Display the preview. */			
      window.ipreview.location.replace ( BASE_URL + param.f_url );
  }
  /* Do not focus the URL input as the input is locked. */
  // document.getElementById("f_url").focus();
}

function onOK() {
  var required = {
    "f_url": i18n("You must enter the URL")
  };
  for (var i in required) {
    var el = document.getElementById(i);
    if (!el.value) {
      alert(required[i]);
      el.focus();
      return false;
    }
  }
  // pass data back to the calling window
  var fields = ["f_url", "f_alt", "f_align", "f_border",
                "f_horiz", "f_vert"];
  var param = new Object();
  for (var i in fields) {
    var id = fields[i];
    var el = document.getElementById(id);
    param[id] = el.value;
  }
  __dlg_close(param);
  return false;
}

function onUpload() {
  var required = {
    "file": i18n("Please select a file to upload."),
    "description": i18n("Please fill in the file description.")
  };
  for (var i in required) {
    var el = document.getElementById(i);
    if (!el.value) {
      alert(required[i]);
      el.focus();
      return false;
    }
  }
  submit();
  return false;
}

function onCancel() {
  __dlg_close(null);
  return false;
}

function onPreview() {
  var f_url = document.getElementById("f_url");
  var url = f_url.value;
  // Check that the URL is not empty string
  if ( ! url )
  {
    alert ( i18n ("You must enter the URL"));
    f_url.focus();
    return false;
  }
  // Modify the URL to contain also the base URL
  url = BASE_URL + url;
  // And display the object that is refferred to by this URL
  if ( document.all )
  {
    window.ipreview.location.replace('viewpicture.html?'+url);
  }
  else
  {
    window.ipreview.location.replace(url);
  }
  return false;
}

var img = new Image();
function imgWait() {
  waiting = window.setInterval("imgIsLoaded()", 1000)
}
function imgIsLoaded() {
  if(img.width > 0) {
    window.clearInterval(waiting)
    document.getElementById("f_width").value = img.width;
    document.getElementById("f_height").value = img.height;
  }
}

function CopyToURL(imgId)
{
  var imgURL = "?act=show,file," + imgId;
  document.getElementById("f_url").value = imgURL;
  document.getElementById("f_alt").value = IMG_ALT[imgId];
  onPreview();
  img.src = BASE_URL + imgURL;
  img.onLoad = imgWait()
}

function openFile() {
  window.open(document.getElementById("f_url").value,'','');
}
</script>

</head>

<body class="dialog" onload="Init()">

<div class="title">Vložit obrázek</div>
<table border="0" width="100%" style="padding: 0px; margin: 0px;">
  <tbody>
  <tr>
    <td>Obrázky uložené na serveru:<br>
    <select value="" style="width:200" size="10" onClick="CopyToURL(this[this.selectedIndex].value);">
<?php
  if ( ! empty ( $imageList ))
  {
    foreach ( $imageList as $val )
    {
      echo '    <option value="' . $val['id']. '">' . 
           $val['origfname'] . ' (' . $val['description'] .')</option>' . "\n";
    }
  }
?>
    </select>
<?php
    echo '      <form method="post" action="'.$_SERVER['PHP_SELF'].'" enctype="multipart/form-data">' . "\n";
    /* See file.edit.tpl for a full example of file input. */
    echo '      <input type="hidden" name="type"  value="' . $ftype . '"/>' . "\n";
    echo '      <input type="hidden" name="objid" value="' . $objid . '"/>' . "\n";    
?>
      <input type="hidden" name="position" value="0"/>
      <input type="hidden" name="id" value="0"/>
      <table>
      <tr><td>Nový soubor:</td><td><input type="file" name="userfile" id="file" size="20"/></td></tr>
      <tr><td>Popis:</td><td><input type="text" name="description" id="description" maxlength="255" size="40"/></td><tr>
      </table>
      <button type="submit" name="ok" onclick="return onUpload();">Nahrát soubor</button><br>
      <span><?php echo $message ?></span>
    </form>

    </td>
    <td valign="center" width="200" height="230">
    <span>Náhled obrázku:</span>
    <a href="#" onClick="javascript:openFile();"title=" Open file in new window"><img src="img/btn_open.gif"  width="18" height="18" border="0" title="Open file in new window" /></a><br />
    <iframe name="ipreview" id="ipreview" frameborder="0" style="border : 1px solid gray;" height="200" width="200" src=""></iframe>
    </td>
  </tr>
  </tbody>
</table>

<form action="" method="get">
<table border="0" width="100%" style="padding: 0px; margin: 0px">
  <tbody>
  <tr>
    <td style="width: 7em; text-align: right">URL obrázku:</td>
    <td
      ><input type="text" name="url" id="f_url" style="width:75%"
              readonly="readonly" title="Odkaz na obrázek" />
      <button name="preview" onclick="return onPreview();" 
              title="Preview the image in a new window">Náhled</button></td>
  </tr>
  <tr>
    <td style="width: 7em; text-align: right">Alternativní text:</td>
    <td
      ><input type="text" name="alt" id="f_alt" style="width:100%"
              title="For browsers that don't support images" /></td>
  </tr>
  </tbody>
</table>

<p />

<fieldset style="float: left; margin-left: 5px;">
<legend>Layout</legend>

<div class="space"></div>

<div class="fl" style="width: 6em;">Alignment:</div>
<select size="1" name="align" id="f_align"
  title="Positioning of this image">
  <option value=""                             >Not set</option>
  <option value="left"                         >Left</option>
  <option value="right"                        >Right</option>
  <option value="texttop"                      >Texttop</option>
  <option value="absmiddle"                    >Absmiddle</option>
  <option value="baseline" selected="1"        >Baseline</option>
  <option value="absbottom"                    >Absbottom</option>
  <option value="bottom"                       >Bottom</option>
  <option value="middle"                       >Middle</option>
  <option value="top"                          >Top</option>
</select>

<p />

<div class="fl" style="width: 6em;">Border thickness:</div>
<input type="text" name="border" id="f_border" size="5"
title="Leave empty for no border" />

<div class="space"></div>

</fieldset>

<fieldset style="float: left; margin-left: 5px;">
<legend>Size</legend>

<div class="space"></div>

<div class="fl" style="width: 5em;">Width:</div>
<input type="text" name="width" id="f_width" size="5" title="Leave empty for not defined" />
<p />

<div class="fl" style="width: 5em;">Height:</div>
<input type="text" name="height" id="f_height" size="5" title="Leave empty for not defined" />
<div class="space"></div>

</fieldset>

<fieldset style="float:right; margin-right: 5px;">
<legend>Spacing</legend>

<div class="space"></div>

<div class="fr" style="width: 5em;">Horizontal:</div>
<input type="text" name="horiz" id="f_horiz" size="5"
title="Horizontal padding" />

<p />

<div class="fr" style="width: 5em;">Vertical:</div>
<input type="text" name="vert" id="f_vert" size="5"
title="Vertical padding" />

<div class="space"></div>

</fieldset>
<br clear="all" />

<div id="buttons">
  <button type="submit" name="ok" onclick="return onOK();">OK</button>
  <button type="button" name="cancel" onclick="return onCancel();">Cancel</button>
</div>
</form>
</body>
</html>