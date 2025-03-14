Ext.define("COMP.TipToast", {
  singleton: true,
  msgCt: null,
  divId: "msg-div",
  options: {
    // default options
    delay: 1000,
    remove: true,
    cls: "",
  },

  createBox: function (title, msg, boxCls) {
    return Ext.String.format('<div class="msg {3} {0}border-box"><h3>{1}</h3><p>{2}</p><p>&nbsp;</p></div>', Ext.baseCSSPrefix, title, msg, boxCls ? boxCls : "");
  },

  toast: function (title, message, options) {
    options = Ext.apply(this.options, options);
    if (this.msgCt) {
      document.body.appendChild(this.msgCt.dom);
    } else {
      this.msgCt = Ext.DomHelper.append(document.body, { id: this.divId }, true);
    }
    var box = this.createBox(title, message, options.cls),
      m = Ext.DomHelper.append(this.msgCt, box, true); // Ext.DomHelper.Element
    delete options.cls; // not necessary
    m.hide();
    m.slideIn("t").ghost("t", options);
  },

  msgbox: function (title, message, option) {
    var ncolor = option.cls === "danger" ? "#FFAFEF" : "#ffffff";
    var nicon = option.cls === "danger" ? "warning.png" : "check.png";

    Ext.toast({
      align: "t",
      slideInDuration: 400,
      autoClose: 2000,

      frame: false,
      border: false,

      items: [
        {
          xtype: "panel",
          title: title,
          layout: "fit",
          icon: vconfig.getstyle + "icon/" + nicon,
          width: 600,
          height: 100,
          margin: "-10 -10 -10 -10",
          bodyPadding: "10 10 10 10",
          html: '<div style="font-family: Arial; font-size: 14px; color: #333;">' + message + "</div>",
          bodyStyle: {
            backgroundColor: ncolor,
          },
        },
      ],
    });
  },
});
