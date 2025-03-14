var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.cp_userlogin.FRMcp_userlogin", {
  extend: "Ext.window.Window",
  alias: "widget.FRMcp_userlogin",
  reference: "FRMcp_userlogin",
  modal: true,
  closeAction: "destroy",
  centered: true,
  title: "INPUT USERLOGIN",
  width: mainpanel.getWidth() * 0.65,
  height: mainpanel.getHeight() * 0.85,
  // width: mainpanel.getWidth() * 0.4,
  // height: mainpanel.getHeight() * 0.45,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
  bodyBorder: false,
  items: [
    {
      xtype: "form",
      pid: "frm_user_create",
      frame: false,
      border: false,
      bodyPadding: "5 5 5 5",
      fieldDefaults: {
        labelAlign: "left",
        labelWidth: 85,
        fieldCls: "fieldinput",
        margin: "0 10 0 0",
      },
      items: [
        {
          xtype: "container",
          layout: { type: "vbox", pack: "start", align: "stretch" },
          items: [
            {
              xtype: "container",
              layout: { type: "vbox" },
              width: 300,
              margin: "0 10 0 0",
              items: [
                { xtype: "numberfield", name: "USERID", hidden: true, value: 0 },
                { xtype: "textfield", name: "DTPROCESS", hidden: true, value: "" },
                { xtype: "textfield", name: "USERDEPT", hidden: true, value: "" },
                {
                  xtype: "container",
                  layout: "hbox",
                  margin: "5 5 5 0",
                  items: [
                    { xtype: "textfield", name: "USERLOGIN", fieldLabel: "User Login", width: 200, readOnly: false },
                    { xtype: "textfield", name: "USERNAME", fieldLabel: "Username", width: 400, readOnly: false },
                  ],
                },
                {
                  xtype: "container",
                  layout: "hbox",
                  margin: "0 5 5 0",
                  items: [
                    {
                      xtype: "combobox",
                      name: "USERACTIVE",
                      fieldLabel: "Status Aktif",
                      width: 200,
                      displayField: "DEFNAME",
                      valueField: "DEFCODE",
                      allowBlank: false,
                      queryMode: "local",
                      forceSelection: true,
                      typeAhead: true,
                      minChars: 0,
                      anyMatch: true,
                      value: "NO",
                      store: new Ext.data.Store({
                        data: [
                          { DEFCODE: "YES", DEFNAME: "YES" },
                          { DEFCODE: "NO", DEFNAME: "NO" },
                        ],
                        fields: ["DEFCODE", "DEFVAL"],
                      }),
                    },
                    {
                      xtype: "combobox",
                      name: "USERGROUP",
                      width: 250,
                      fieldLabel: "User Group",
                      fieldCls: "fieldinput",
                      displayField: "DEFCODE",
                      valueField: "DEFCODE",
                      fieldCls: "fieldinput",
                      queryMode: "local",
                      allowBlank: false,
                      forceSelection: true,
                      typeAhead: true,
                      anyMatch: true,
                      minChars: 0,
                      store: {
                        autoLoad: true,
                        remoteSort: false,
                        remoteFilter: false,
                        pageSize: 0,
                        fields: [
                          { name: "DEFCODE", type: "string" },
                          { name: "DEFNAME", type: "string" },
                        ],
                        proxy: {
                          type: "ajax",
                          disableCaching: false,
                          noCache: false,
                          headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                          actionMethods: { read: "POST" },
                          url: vconfig.service_api + "cpuser/cpusers",
                          extraParams: {
                            method: "list_group_user",
                          },
                          reader: {
                            type: "json",
                            rootProperty: "Rows",
                            totalProperty: "TotalRows",
                            successProperty: "success",
                          },
                        },
                      },
                    },
                  ],
                },
                { xtype: "textfield", name: "USEREMAIL", fieldLabel: "Email", width: 400, readOnly: false },
                { xtype: "textfield", fieldLabel: "Password", name: "USERPASSWORD", readOnly: false, inputType: "password", width: 400, margin: "5 0 0 0" },
              ],
            },
          ],
        },
      ],
    },
    { xtype: "tbspacer", height: 5 },
    {
      xtype: "panel",
      layout: { type: "hbox", pack: "start", align: "stretch" },
      flex: 1,
      border: false,
      frame: false,
      items: [
        {
          xtype: "grid",
          pid: "GRIDsetting_akses",
          emptyText: "No Matching Records",
          autoScroll: true,
          flex: 1,
          store: {
            autoLoad: false,
            remoteSort: false,
            remoteFilter: false,
            pageSize: 0,
            proxy: {
              type: "ajax",
              disableCaching: false,
              noCache: false,
              headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
              actionMethods: { read: "POST" },
              url: vconfig.service_api + "mdepartment/mdepartment",
              reader: {
                type: "json",
                rootProperty: "Rows",
                totalProperty: "TotalRows",
                successProperty: "success",
              },
            },
            listeners: {
              beforeload: function (store, operation, eopts) {
                try {
                  operation.setParams({
                    method: "list_groupdepartment",
                    vdata: Ext.encode(store.nvdata),
                  });
                } catch (ex) {
                  COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
                }
              },
            },
          },
          plugins: ["filterfield", { ptype: "cellediting", clicksToEdit: 2 }],
          viewConfig: { enableTextSelection: true },
          columnLines: true,
          columns: [
            { text: "KODE", dataIndex: "deptcode", width: 70, filter: { xtype: "textfield" } },
            { text: "DEPARTMENT", dataIndex: "deptname", flex: 1, filter: { xtype: "textfield" } },
          ],
          dockedItems: [
            {
              xtype: "toolbar",
              height: 30,
              dock: "top",
              items: [
                "-",
                {
                  xtype: "button",
                  pid: "btdepartment_reset",
                  text: "Reset",
                  icon: vconfig.getstyle + "icon/back.ico",
                  tooltip: "Reset Department",
                  handler: function (cmp) {
                    try {
                      var FRMmain = cmp.up("window");
                      var GRID = FRMmain.query("grid[pid=GRIDsetting_akses]")[0];
                      GRID.getStore().removeAll();
                      GRID.getStore().commitChanges();
                    } catch (ex) {
                      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
                    }
                  },
                },
                {
                  xtype: "button",
                  pid: "btdepartment_add",
                  text: "Tambah Department",
                  icon: vconfig.getstyle + "icon/add.ico",
                  tooltip: "Tambah Department",
                  handler: function (cmp) {
                    try {
                      var me = this;
                      var MDLdepartment = Ext.create("NJC.cp_userlogin.GRIDmdepartment");
                      var GRIDdepartment = MDLdepartment.query("grid[pid=GRIDmdepartment]")[0];
                      var GRIDsetting_akses = cmp.up("window").query("grid[pid=GRIDsetting_akses]")[0];
                      var FRMcreate_user = cmp.up("window").query("form[pid=frm_user_create]")[0];
                      GRIDdepartment.getStore().pageSize = 0;
                      GRIDdepartment.getStore().setRemoteSort(false);
                      GRIDdepartment.getStore().setRemoteFilter(false);
                      var ncolumn = ["defcode", "defname"];
                      Ext.each(GRIDdepartment.columns, function (column) {
                        column.menuDisabled = true;
                        column.sortable = false;
                        if (!Ext.Array.contains(ncolumn, column.dataIndex)) {
                          column.setVisible(false);
                        }
                      });
                      GRIDdepartment.headerCt.insert(0, {
                        xtype: "checkcolumn",
                        menuDisabled: true,
                        sortable: false,
                        dataIndex: "colselect",
                        headerCheckbox: true,
                        width: 50,
                      });
                      var pagingToolbar = GRIDdepartment.down("pagingtoolbar");
                      if (pagingToolbar) {
                        GRIDdepartment.removeDocked(pagingToolbar);
                      }
                      GRIDdepartment.updateLayout();
                      var popup = Ext.create("Ext.window.Window", {
                        alias: "widget.FRMdepartment",
                        reference: "FRMdepartment",
                        title: "Pilih Department",
                        modal: true,
                        closeAction: "destroy",
                        centered: true,
                        width: mainpanel.getWidth() * 0.4,
                        height: mainpanel.getHeight() * 0.6,
                        layout: { type: "vbox", pack: "start", align: "stretch" },
                        bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
                        items: [{ xtype: GRIDdepartment }],
                        dockedItems: [
                          {
                            xtype: "toolbar",
                            height: 30,
                            dock: "top",
                            items: [
                              "-",
                              {
                                xtype: "button",
                                pid: "btsave",
                                text: "Save Department",
                                icon: vconfig.getstyle + "icon/save.png",
                                tooltip: "Save Department",
                                handler: function (cmp) {
                                  try {
                                    var FRMmain = cmp.up("window");
                                    var GRIDselect = FRMmain.query("grid[pid=GRIDmdepartment]")[0];
                                    var ndata = GRIDselect.getStore().findRecord("defcode", "0000");
                                    if (ndata) {
                                      GRIDselect.getStore().remove(ndata);
                                    }
                                    GRIDselect.getStore()
                                      .getDataSource()
                                      .each(function (record) {
                                        if (record.data.colselect === true) {
                                          if (GRIDsetting_akses.getStore().find("deptcode", record.data.defcode) === -1) {
                                            GRIDsetting_akses.getStore().add({
                                              deptcode: record.data.defcode,
                                              deptname: record.data.defname,
                                            });
                                          }
                                        }
                                      });
                                    popup.close();
                                  } catch (ex) {
                                    COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
                                  }
                                },
                              },
                            ],
                          },
                        ],
                      });
                      return popup.show();
                    } catch (ex) {
                      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
                    }
                  },
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
      items: [{ xtype: "tbspacer", width: 10 }, "-", { xtype: "button", icon: vconfig.getstyle + "icon/save.png", text: "Save", tooltip: "Save data", pid: "btsave" }, { xtype: "button", icon: vconfig.getstyle + "icon/delete.png", text: "Delete", tooltip: "Delete data", pid: "btdelete" }, "-", "->"],
    },
  ],
  listeners: {
    afterlayout: function (cmp) {
      try {
      } catch (ex) {
        COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
      }
    },
  },
});
