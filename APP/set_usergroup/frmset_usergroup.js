Ext.define("TDK.set_usergroup.FRMset_usergroup", {
  extend: "Ext.form.Panel",
  alias: "widget.FRMset_usergroup",
  reference: "FRMset_usergroup",
  pid: "FRMset_usergroup",
  frame: false,
  border: true,
  layout: { type: "hbox", pack: "start", align: "stretch" },
  items: [
    {
      xtype: "grid",
      pid: "GRIDgroup_user",
      emptyText: "No Matching Records",
      autoScroll: true,
      width: 250,
      store: {
        autoLoad: true,
        remoteSort: false,
        remoteFilter: false,
        pageSize: 0,
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: { Authorization: "Bearer " + localStorage.getItem("TDK_JWT") },
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
      listeners: {
        itemclick: function (grid, record) {
          var form = grid.up("form");

          if (form) {
            if (!form.chosenGroup) {
              form.chosenGroup = {};
            }
            form.chosenGroup.value = record.data.defcode;
          }
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
          { text: "GROUP USER", dataIndex: "defcode", flex: 1, filter: { xtype: "textfield" } },
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
                    var FRM = xgrid.up("form");
                    var treepanel = FRM.query("treepanel")[0];

                    // Get the 'GROUPNAME' from the selected record (rec)
                    var groupname = rec.get("defcode"); // Ensure 'GROUPNAME' is a valid field
                    Ext.MessageBox.show(
                      {
                        width: 500,
                        title: "Konfirmasi",
                        msg: "Konfirmasi Hapus Group User Login",
                        buttons: Ext.MessageBox.YESNO,
                        defaultFocus: "no",
                        fn: function (btn, text) {
                          if (btn === "yes") {
                            // Correctly structure vdata without double encoding
                            var params = Ext.encode({
                              method: "delete_group",
                              vdata: Ext.encode(groupname.toUpperCase()),
                            });

                            // Call the service and process the result
                            var hasil = COMP.run.getservice(vconfig.service_api + "cpuser/cpuser", params, "POST", localStorage.getItem("TDK_JWT"));
                            hasil.then(function (content) {
                              if (content.includes("success")) {
                                treepanel.getStore().setRoot(null);
                                xgrid.getStore().load();
                              } else {
                                COMP.TipToast.msgbox("Error", content.message, { cls: "danger", delay: 2000 });
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
          },
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
              text: "Create New Group",
              icon: vconfig.getstyle + "icon/new.ico",
              tooltip: "Pembuatan Group Baru",
              handler: function (cmp) {
                try {
                  var FRM = cmp.up("form");
                  var GRIDgroup_user = FRM.query("grid[pid=GRIDgroup_user]")[0];
                  var treepanel = FRM.query("treepanel")[0];
                  Ext.MessageBox.prompt(
                    "Create Group",
                    "Input Nama Group:",
                    function (btn, text) {
                      if (btn === "ok") {
                        var params = Ext.encode({
                          method: "create_group",
                          vdata: Ext.encode(text.toUpperCase()),
                        });
                        var hasil = COMP.run.getservice(vconfig.service_api + "cpuser/cpuser", params, "POST", localStorage.getItem("TDK_JWT"));
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
      store: {
        autoLoad: true,
        remoteSort: false,
        remoteFilter: false,
        pageSize: 0,
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: { Authorization: "Bearer " + localStorage.getItem("TDK_JWT") },
          actionMethods: { read: "POST" },
          url: vconfig.service_api + "cpuser/cpusers",
          extraParams: {
            method: "",
          },
          reader: {
            type: "json",
            rootProperty: "children",
            // totalProperty: "TotalRows",
            successProperty: "success",
          },
        },
      },
      columns: [
        {
          xtype: "treecolumn",
          text: "MENU AKSES",
          dataIndex: "mname",
          flex: 2,
          sortable: false,
          menuDisabled: true,
        },
        { text: "CONTROL", dataIndex: "mcontrol", flex: 1, sortable: false, align: "left", menuDisabled: true },
        { text: "MENU NAME", dataIndex: "mname", flex: 1, sortable: false, align: "left", menuDisabled: true },
        { text: "CODE", dataIndex: "mcode", width: 75, sortable: false, align: "left", menuDisabled: true },
        { text: "mparrent", dataIndex: "mparrent", width: 75, sortable: false, align: "left", menuDisabled: true, hidden: true },
      ],
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
                  var FRM = cmp.up("FRMset_usergroup");
                  var treepanel = FRM.query("treepanel")[0];
                  var choosenGroup = FRM.chosenGroup ? FRM.chosenGroup.value : null;
                  var checkedNodes = treepanel.getChecked();
                  var vdt = checkedNodes.map(function (node) {
                    var { rmodule, mcontrol, mname, mcode, checked, mparrent } = node.data;
                    return { rmodule, mcontrol, mname, mcode, checked, mparrent, user_group: choosenGroup };
                  });

                  if (vdt.length < 1) {
                    COMP.TipToast.msgbox("Error", "Menu Akses belum dipilih", { cls: "danger", delay: 2000 });
                    return false;
                  }
                  if (vdt[0].rmodule === "" || vdt[0].rmodule === null) {
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
                            chosen: choosenGroup,
                            vdata: Ext.encode(vdt),
                          });
                          var hasil = COMP.run.getservice(vconfig.service_api + "cpuser/cpuser", params, "POST", localStorage.getItem("TDK_JWT"));
                          hasil.then(function (content) {
                            var val = Ext.decode(content, true);
                            if (val.success == 'true') {
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