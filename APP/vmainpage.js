Ext.define("NJC.Vmainpage", {
  extend: "Ext.panel.Panel",
  alias: "widget.Vmainpage",
  reference: "Vmainpage",
  layout: { type: "border", pack: "start", align: "stretch" },
  border: false,
  items: [
    {
      region: "north",
      frame: false,
      border: false,
      width: 165,
      height: 63,
      bodyPadding: "5 5 5 5",
      layout: { type: "hbox", pack: "start", align: "stretch" },
      items: [
        {
          xtype: "container",
          hidden: true,
          layout: { type: "vbox", pack: "start", align: "stretch" },
          items: [
            {
              xtype: "container",
              layout: "vbox",
              items: [
                {
                  xtype: "image",
                  //src:'',
                  height: 50,
                  width: 140,
                },
              ],
            },
          ],
        },
        {
          xtype: "container",
          layout: { type: "vbox", pack: "start", align: "stretch" },
          padding: "10 0 0 10",
          items: [
            {
              xtype: "container",
              layout: "hbox",
              items: [{ xtype: "component", html: "<b>PLB</b>" }],
            },
          ],
        },
        {
          xtype: "tbspacer",
          flex: 1,
        },
        {
          xtype: "container",
          width: 200,
          layout: { type: "vbox", pack: "start", align: "stretch" },
          items: [
            {
              xtype: "container",
              layout: "hbox",
              items: [
                { xtype: "component", html: "<b>User Login</b>", width: 100 },
                { xtype: "component", html: "<b>:</b>", width: 10 },
                { xtype: "component", pid: "Vuserlogin", html: "<b>---</b>" },
              ],
            },
            {
              xtype: "container",
              layout: "hbox",
              items: [
                { xtype: "component", html: "<b>Group</b>", width: 100 },
                { xtype: "component", html: "<b>:</b>", width: 10 },
                { xtype: "component", pid: "Vusergroup", html: "<b>---</b>" },
              ],
            },
            { xtype: "tbspacer", height: 3 },
            {
              xtype: "container",
              layout: "hbox",
              items: [
                {
                  xtype: "button",
                  text: "My Account",
                  icon: vconfig.getstyle + "icon/user.ico",
                  menu: [
                    {
                      text: "Log Out",
                      pid: "btlogout",
                      icon: vconfig.getstyle + "icon/logout.ico",
                    },
                    {
                      text: "Change Password",
                      pid: "btchange_password",
                      icon: vconfig.getstyle + "icon/bulb.ico",
                    },
                  ],
                },
              ],
            },
          ],
        },
      ],
    },
    {
      title: "",
      region: "east",
      border: true,
      frame: false,
      layout: "accordion",
      pid: "MAIN_ACCORDION",
      width: 230,
      items: [],
    },
    {
      collapsible: false,
      region: "center",
      xtype: "tabpanel",
      pid: "mainpage_tabpanel",
      frame: false,
      boarder: false,
      activeTab: 0,
      items: [],
    },
  ],
  dockedItems: [
    {
      xtype: "toolbar",
      height: 2,
      dock: "bottom",
      items: [],
    },
  ],
});
