Ext.define("NJC.cp_usergroup.FRMcp_usergroup", {
  extend: "Ext.form.Panel",
  alias: "widget.FRMcp_usergroup",
  reference: "FRMcp_usergroup",
  pid: "FRMcp_usergroup",
  frame: false,
  border: true,
  layout: { type: "hbox", pack: "start", align: "stretch" },
  items: [
    {
      xtype: "grid",
      pid: "GRIDgroup_user",
      emptyText: "No Matching Records",
      autoScroll: true,
      width: 300,
      store: {
        autoLoad: true,
        remoteSort: false,
        remoteFilter: false,
        pageSize: 0,
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
          actionMethods: { read: "POST" },
          url: vconfig.service_api + "cp_usergroup/cp_usergroups",
          extraParams: {
            method: "read_data",
          },
          reader: {
            type: "json",
            rootProperty: "Rows",
            totalProperty: "TotalRows",
            successProperty: "success",
          },
        },
      },
      plugins: ["filterfield"],
      viewConfig: {
        enableTextSelection: true,
      },
      columns: {
        default: {
          width: 70,
          sortable: true,
        },
        items: [
          //
          { xtype: "rownumberer" },
          { text: "GROUP USER", dataIndex: "GROUPNAME", flex: 1, filter: { xtype: "textfield" } },
          {
            xtype: "actioncolumn",
            width: 35,
            align: "center",
            menuDisabled: true,
            sortable: false,
            items: [
              {
                icon: vconfig.getstyle + "icon/delete.ico",
                tooltip: "Delete Group",
                handler: function (xgrid, rowIndex, colIndex, e, a, rec) {
                  try {
                    var FRM = xgrid.up("panel[pid=FRMcp_usergroup]");
                    var treepanel = FRM.query("treepanel")[0];
                    Ext.MessageBox.show(
                      {
                        width: 500,
                        title: "Konfirmasi",
                        msg: "Konfirmasi Hapus Group User Login",
                        buttons: Ext.MessageBox.YESNO,
                        defaultFocus: "no",
                        fn: function (btn, text) {
                          if (btn === "yes") {
                            var params = Ext.encode({
                              method: "delete_group",
                              vdata: rec.data.GROUPNAME,
                            });
                            var hasil = COMP.run.getservice(vconfig.service_api + "cp_usergroup/cp_usergroup", params, "POST", localStorage.getItem("NJC_JWT"));
                            hasil.then(function (content) {
                              treepanel.getStore().setRoot(null);
                              xgrid.getStore().load();
                            }, this);
                          }
                        },
                        icon: Ext.MessageBox.QUESTION,
                        maskClickAction: false,
                      },
                      this
                    );
                  } catch (ex) {
                    COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
                  }
                },
              },
            ],
          },
        ],
      },
      bbar: {
        xtype: "pagingtoolbar",
        displayInfo: true,
        displayMsg: "Displaying topics {0} - {1} of {2}",
        emptyMsg: "No topics to display",
      },
      dockedItems: [
        {
          xtype: "toolbar",
          height: 30,
          dock: "top",
          items: [
            "-", //
            // { xtype: "component", html: "Refresh" },
            {
              xtype: "button",
              pid: "btgroup_new",
              text: "Create New Group",
              icon: vconfig.getstyle + "icon/new.ico",
              tooltip: "Pembuatan Group Baru",
              handler: function (cmp) {
                try {
                  var FRM = cmp.up("panel[pid=FRMcp_usergroup]");
                  var GRIDgroup_user = FRM.query("grid[pid=GRIDgroup_user]")[0];
                  var treepanel = FRM.query("treepanel")[0];
                  Ext.MessageBox.prompt(
                    "Create Group",
                    "Input Nama Group:",
                    function (btn, text) {
                      if (btn === "ok") {
                        var params = Ext.encode({
                          method: "create_group",
                          vdata: text.toUpperCase(),
                        });
                        var hasil = COMP.run.getservice(vconfig.service_api + "cp_usergroup/cp_usergroup", params, "POST", localStorage.getItem("NJC_JWT"));
                        hasil.then(function (content) {
                          treepanel.getStore().setRoot(null);
                          GRIDgroup_user.getStore().load();
                        });
                      }
                    },
                    this,
                    false, // Multiline input (false = single line)
                    "" // Nilai default dari input
                  );
                } catch (ex) {
                  COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
                }
              },
            },
          ],
          // other options....
        },
      ],
    },
    {
      xtype: "treepanel",
      pid: "treepanel_menu",
      flex: 1,
      checkPropagation: "both",
      rootVisible: false,
      useArrows: true,
      store: {},
      columns: {
        defaults: {
          sortable: false,
          menuDisabled: true,
          align: "left",
        },
        items: [
          { xtype: "treecolumn", text: "MENU AKSES", dataIndex: "MENU_AKSES", flex: 2, sortable: false },
          { text: "MODULE", dataIndex: "MMODULE", flex: 1 },
          { text: "CONTROL", dataIndex: "MCONTROL", flex: 1 },
          { text: "MENU NAME", dataIndex: "MNAME", flex: 1 },
          { text: "CODE", dataIndex: "MCODE", width: 75 },
          { text: "GROUP USER", dataIndex: "GROUP_USER", flex: 1 },
        ],
      },
      dockedItems: [
        {
          xtype: "toolbar",
          height: 30,
          dock: "top",
          items: [
            "-", //
            // { xtype: "component", html: "Refresh" },
            {
              xtype: "button",
              pid: "btgroup_new",
              text: "Update Access Menu",
              icon: vconfig.getstyle + "icon/save.png",
              tooltip: "Update Access Menu",
              handler: function (cmp) {
                try {
                  var FRM = cmp.up("FRMcp_usergroup");
                  var treepanel = FRM.query("treepanel")[0];
                  var checkedNodes = treepanel.getChecked();
                  var vdt = checkedNodes.map(function (node) {
                    var { GROUP_USER, MCODE, MCONTROL, MENU_AKSES, MNAME, MMODULE } = node.data;
                    return { GROUP_USER, MCODE, MCONTROL, MENU_AKSES, MNAME, MMODULE };
                  });

                  if (vdt.length < 1) {
                    COMP.TipToast.msgbox("Error", "Menu Akses belum dipilih", { cls: "danger", delay: 2000 });
                    return false;
                  }
                  if (vdt[0].GROUP_USER === "" || vdt[0].GROUP_USER === null) {
                    COMP.TipToast.msgbox("Error", "Group User belum dipilih", { cls: "danger", delay: 2000 });
                    return false;
                  }
                  Ext.MessageBox.show(
                    {
                      width: 500,
                      title: "Konfirmasi",
                      msg: "Konfirmasi Update Group Access",
                      buttons: Ext.MessageBox.YESNO,
                      defaultFocus: "no",
                      fn: function (btn, text) {
                        if (btn === "yes") {
                          var params = Ext.encode({
                            method: "update_groupaccess",
                            vdata: Ext.encode(vdt),
                          });
                          var hasil = COMP.run.getservice(vconfig.service_api + "cp_usergroup/cp_usergroup", params, "POST", localStorage.getItem("NJC_JWT"));
                          hasil.then(function (content) {
                            var val = Ext.decode(content, true);
                            if (val.success === "true") {
                              COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                            }
                          }, this);
                        }
                      },
                      icon: Ext.MessageBox.QUESTION,
                      maskClickAction: false,
                    },
                    this
                  );
                } catch (ex) {
                  COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
                }
              },
            },
          ],
          // other options....
        },
      ],
    },
  ],
});
