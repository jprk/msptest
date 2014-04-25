function InsertCmsFileLink ( editor )
{
  this.editor = editor;
  var cfg = editor.config;
  var self = this;

  //alert('InsertCmsFileLink registration');
  // Register the toolbar buttons provided by this plugin
  cfg.registerButton({
  id       : "insert-cms-file-link",
  tooltip  : this._lc("Insert CMS File Link"),
  image    : editor.imgURL("insert-cmsfile-link.gif", "InsertCmsFileLink"),
  textMode : false,
  action   : function(editor) {
               self.buttonPress(editor);
             }
  });
  cfg.addToolbarElement("insert-cms-file-link", "insertcmsfilelink", 0);
}

InsertCmsFileLink._pluginInfo =
{
  name          : "InsertCmsFileLink",
  origin        : "based on InsertAnchor version: 2.0, by Udo Schmal, L.N.Schaffrath NeueMedien, http://www.schaffrath-neuemedien.de",
  version       : "0.1",
  developer     : "Jan Prikryl",
  developer_url : "",
  c_owner       : "JanPrikryl",
  sponsor       : "",
  sponsor_url   : "",
  license       : "htmlArea"
};

InsertCmsFileLink.prototype._lc = function(string)
{
    return HTMLArea._lc(string, 'InsertCmsFileLink');
};

InsertCmsFileLink.prototype.onGenerate = function()
{
  var style_id = "IA-style";
  var style = this.editor._doc.getElementById(style_id);
  if (style == null)
  {
    style = this.editor._doc.createElement("link");
    style.id = style_id;
    style.rel = 'stylesheet';
    style.href = _editor_url + 'plugins/InsertCmsFileLink/insert-cms-file-link.css';
    this.editor._doc.getElementsByTagName("HEAD")[0].appendChild(style);
  }
};

InsertCmsFileLink.prototype.buttonPress = function(editor)
{
  var outparam = null;
  var link;

  link = editor.getParentElement();
  if ( link )
  {
    while ( link && !/^a$/i.test(link.tagName) ) link = link.parentNode;
  }
  
  if ( !link )
  {
    /* Link does not exist yet. */
    var sel = editor._getSelection();
    var range = editor._createRange(sel);
    var compare = 0;
    if ( HTMLArea.is_ie )
    {
      if ( sel.type == "Control" )
      {
        compare = range.length;
      }
      else
      {
        compare = range.compareEndPoints ( "StartToEnd", range );
      }
    }
    else
    {
      compare = range.compareBoundaryPoints ( range.START_TO_END, range );
    }
    
    /* If start == end, user did not select anything. */
    if ( compare == 0 )
    {
      alert(HTMLArea._lc("You need to select some text before creating a link"));
      return;
    }
    
    /* Initialise `outparam` to empty. */
    outparam =
    {
      f_href : '',
      f_title : '',
      f_target : '',
      f_usetarget : editor.config.makeLinkShowsTarget
    };
  }
  else
  {
    outparam =
    {
      f_href   : HTMLArea.is_ie ? editor.stripBaseURL(link.href) : link.getAttribute("href"),
      f_title  : link.title,
      f_target : link.target,
      f_usetarget : editor.config.makeLinkShowsTarget
    };
  }
  
  /* Display popup. In order to prevent the system from adding a .html file
    extension, we just specify the PHP file name of the popup window code.
    Xinha will recognize is as a custom popup window and creare an appropriate
    path to it.*/
  editor._popupDialog ( _editor_url + 'plugins/InsertCmsFileLink/popups/insert_cmsfile_link.php',
  function(param)
  {
    if (!param)
      return false;
    var a = link;
    if ( ! a )
    {
      /* Creating a new link. */
      try
      {
        editor._doc.execCommand ( "createlink", false, param.f_href );
        a = editor.getParentElement();
        var sel = editor._getSelection();
        var range = editor._createRange(sel);
        if (!HTMLArea.is_ie) {
          a = range.startContainer;
          if (!/^a$/i.test(a.tagName)) {
            a = a.nextSibling;
            if (a == null)
              a = range.startContainer.parentNode;
          }
        }
    } catch(e) {}
    }
    else
    {
      var href = param.f_href.trim();
      editor.selectNodeContents(a);
      if (href == "") {
        editor._doc.execCommand("unlink", false, null);
        editor.updateToolbar();
        return false;
      }
      else {
        a.href = href;
      }
    }
    if (!(a && /^a$/i.test(a.tagName)))
      return false;
    //a.target = param.f_target.trim();
    a.title = param.f_title.trim();
    editor.selectNodeContents(a);
    editor.updateToolbar();
  }, outparam);
};
