<?php
  // ==========================================================================
  // Altered for the K611 web
  // ==========================================================================

  /* Global configuration */
  require ( '../../../../config.php' );

  /* First include classes implementing parts of the interface to our
     web application. */
  require ( REQUIRE_DIR . 'CPPSmarty.class.php');
  require ( REQUIRE_DIR . 'DBWrap.class.php' );
  require ( REQUIRE_DIR . 'BaseBean.class.php');
  require ( REQUIRE_DIR . 'DatabaseBean.class.php');
  //require ( REQUIRE_DIR . 'ArticleBean.class.php');
  require ( REQUIRE_DIR . 'FileBean.class.php');
  require ( REQUIRE_DIR . 'SessionDataBean.class.php');
  //require ( REQUIRE_DIR . 'PersonBean.class.php');
  //require ( REQUIRE_DIR . 'ProjectBean.class.php');
  //require ( REQUIRE_DIR . 'SectionBean.class.php');
  //require ( REQUIRE_DIR . 'UserBean.class.php');
  //require ( REQUIRE_DIR . 'LoginBean.class.php');
  //require ( REQUIRE_DIR . 'MenuBean.class.php');

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

  /* Find out which section we are editing so that just the relevant section
     files can be displayed. */
  $sectionId = SessionDataBean::getLastSectionId();
     
  /* Query the list of files related to this section. */
  $fileList = $fileBean->dbQuerySectionFiles ( $sectionId );
?>
<html>

<head>
  <title>Insert/Modify CMS File Link</title>
  <script type="text/javascript" src="../../../popups/popup.js"></script>
  <link rel="stylesheet" type="text/css" href="../../../popups/popup.css" />

  <script type="text/javascript">
window.resizeTo(400, 200);

HTMLArea = window.opener.HTMLArea;

/* Create a list of alternative file descriptions. */
var _link_titles = new Array();
<?php
  if ( ! empty ( $fileList ))
  {
    foreach ( $fileList as $val )
    {
      echo '_link_titles[' . $val['id']. '] = "' . $val['description'] .'";';
      echo "\n";
    }
  }
?>

function i18n(str) {
  return (HTMLArea._lc(str, 'HTMLArea'));
}

function onTargetChanged() {
  var f = document.getElementById("f_other_target");
  if (this.value == "_other") {
    f.style.visibility = "visible";
    f.select();
    f.focus();
  } else f.style.visibility = "hidden";
}

function Init() {
  __dlg_translate('HTMLArea');
  __dlg_init();

  // Make sure the translated string appears in the drop down. (for gecko)
  document.getElementById("f_target").selectedIndex = 1;
  document.getElementById("f_target").selectedIndex = 0;

  var param = window.dialogArguments;
  var target_select = document.getElementById("f_target");
  var use_target = true;
  if (param) {
    if ( typeof param["f_usetarget"] != "undefined" ) {
      use_target = param["f_usetarget"];
    }
    if ( typeof param["f_href"] != "undefined" ) {
      document.getElementById("f_href").value = param["f_href"];
      document.getElementById("f_title").value = param["f_title"];
      comboSelectValue(target_select, param["f_target"]);
      if (target_select.value != param.f_target) {
        var opt = document.createElement("option");
        opt.value = param.f_target;
        opt.innerHTML = opt.value;
        target_select.appendChild(opt);
        opt.selected = true;
      }
    }
  }
  if (! use_target) {
    document.getElementById("f_target_label").style.visibility = "hidden";
    document.getElementById("f_target").style.visibility = "hidden";
    document.getElementById("f_target_other").style.visibility = "hidden";
  }
  var opt = document.createElement("option");
  opt.value = "_other";
  opt.innerHTML = i18n("Other");
  target_select.appendChild(opt);
  target_select.onchange = onTargetChanged;
  document.getElementById("f_href").focus();
  document.getElementById("f_href").select();
}

function onOK() {
  var required = {
    // f_href shouldn't be required or otherwise removing the link by entering an empty
    // url isn't possible anymore.
    // "f_href": i18n("You must enter the URL where this link points to")
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
  var el = document.getElementById('f_href');
  var param = new Object();
  param['f_href']  = "?act=show,file," + el.value;
  param['f_title'] = _link_titles[el.value];
  
  if (param.f_target == "_other")
    param.f_target = document.getElementById("f_other_target").value;
  __dlg_close(param);
  return false;
}

function onCancel() {
  __dlg_close(null);
  return false;
}

</script>
</head>

<body class="dialog" onload="Init()">
<div class="title">Vložit odkaz na soubor v CMS</div>
<form>
<table border="0" style="width: 100%;">
  <tr>
    <td class="label">Soubor:</td>
    <td><select id="f_href">
    <option value="">Smazat existující odkaz ...</option>
<?php
  if ( ! empty ( $fileList ))
  {
    foreach ( $fileList as $val )
    {
      echo '    <option value="' . $val['id']. '">' .
           $val['origfname'] . ' (' . $val['description'] .')</option>' . "\n";
    }
  }
?>
    </select></td>
  </tr>
</table>

<div id="buttons">
  <button type="submit" name="ok" onclick="return onOK();">OK</button>
  <button type="button" name="cancel" onclick="return onCancel();">Cancel</button>
</div>
</form>
</body>
</html>
