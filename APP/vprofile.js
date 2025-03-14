var mainpanel = Ext.ComponentQuery.query("mainpage")[0];

Ext.define("NJC.Vprofile", {
  extend: "Ext.window.Window",
  alias: "widget.Vprofile",
  reference: "Vprofile",
  title: "My Profile",
  modal: true,
  closeAction: "destroy",
  centered: true,
  autoScroll: true,
  //y: -110,
  bodyPadding: "3 3 3 3",
  width: mainpanel.getWidth() * 0.5,
  height: mainpanel.getHeight() * 0.6,
  bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
  items: [
    {
      xtype: "form",
      layout: { type: "vbox", pack: "start", align: "stretch" },
      border: false,
      frame: false,
      fieldDefaults: {
        labelAlign: "left",
        labelWidth: 90,
        margin: "0 10 5 0",
      },
      items: [
        {
          xtype: "fieldset",
          layout: "hbox",
          frame: false,
          border: false,
          bodyPadding: "5 0 0 0",
          items: [
            {
              xtype: "container",
              layout: "vbox",
              margin: "5 0 0 0",
              items: [
                {
                  xtype: "container",
                  layout: "hbox",
                  items: [
                    { xtype: "textfield", labelWidth: 100, width: 250, fieldLabel: "User Login", name: "USERLOGIN", fieldCls: "fieldlock", readOnly: true },
                    { xtype: "textfield", labelWidth: 50, width: 300, fieldLabel: "Name", labelAlign: "right", name: "USERNAME", fieldCls: "fieldlock", readOnly: true },
                  ],
                },
                {
                  xtype: "container",
                  layout: "hbox",
                  items: [{ xtype: "textfield", labelWidth: 100, width: 250, fieldLabel: "Group", name: "USERGROUP", fieldCls: "fieldlock", readOnly: true }],
                },
                { xtype: "tbspacer", height: 10 },
                {
                  xtype: "component",
                  html: "<b>--Change Password--</b>",
                },
                { xtype: "tbspacer", height: 10 },
                {
                  xtype: "container",
                  layout: "hbox",
                  items: [{ xtype: "textfield", labelWidth: 100, width: 400, fieldLabel: "Old Password", name: "PASSWORD_LAMA", fieldCls: "fieldinput", readOnly: false, inputType: "password" }],
                },
                {
                  xtype: "container",
                  layout: "hbox",
                  items: [{ xtype: "textfield", labelWidth: 100, width: 400, fieldLabel: "New Password", name: "PASSWORD_BARU", fieldCls: "fieldinput", readOnly: false, inputType: "password" }],
                },
                {
                  xtype: "container",
                  layout: "hbox",
                  items: [{ xtype: "textfield", labelWidth: 100, width: 400, fieldLabel: "Confirm New Password", name: "PASSWORD_CONFIRM", fieldCls: "fieldinput", readOnly: false, inputType: "password" }],
                },
              ],
            },
          ],
        },
      ],
    },
  ],
  dockedItems: [
    {
      xtype: "toolbar",
      height: 30,
      dock: "top",
      items: [
        //
        "-",
        { xtype: "button", text: "Save", pid: "btsave_profile", icon: vconfig.getstyle + "icon/save.ico", tooltip: "Save Data", handler: "btsave_outlet_click" },
      ],
      // other options....
    },
  ],
});
