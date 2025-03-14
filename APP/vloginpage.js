Ext.define("NJC.Vloginpage", {
  extend: "Ext.form.Panel",
  alias: "widget.Vloginpage",
  reference: "Vloginpage",
  layout: "fit",
  border: false,
  bodyPadding: 5,
  items: [
    {
      items: false,
      border: false,
      layout: "center",
      items: [
        {
          title: "Login Application",
          frame: true,
          width: 400,
          height: 320,
          bodyPadding: 10,
          bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
          layout: { type: "vbox", pack: "start", align: "stretch" },
          items: [
            { xtype: "tbspacer", height: 170 },
            { xtype: "textfield", name: "UserLogin", fieldLabel: "User Login", allowBlank: false, labelAlign: "left", emptyText: "user Login" },
            { xtype: "textfield", name: "UserPassword", fieldLabel: "Password", allowBlank: false, labelAlign: "left", emptyText: "password", inputType: "password" },
          ],

          buttons: [{ text: "Login", pid: "btlogin" }],
        },
      ],
    },
    { xtype: "tbspacer", width: "2%" },
  ],
});
