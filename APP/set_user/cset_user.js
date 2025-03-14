Ext.define("TDK.set_user.Cset_user", {
  extend: "Ext.app.ViewController",
  alias: "controller.Cset_user",
  init: function (view) {
    this.control({});
    this.listen({
      store: {},
    });
    this.var_global = {
      jwt: localStorage.getItem("TDK_JWT"),
      vprofile: Ext.decode(localStorage.getItem("TDK_PROFILE"))[0],
    };
    this.var_definition = {};
    this.renderpage();
  },
  formatAmount: function (value) {
    var text = Ext.util.Format.number(value, "0,000.00/i");
    return text;
  },
  formatqty: function (value) {
    var text = Ext.util.Format.number(value, "0,000/i");
    return text;
  },
  renderpage: function () {
    try {
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btrefresh_click: function () {
    try {
      var me = this;
      var MODmain = me.getView();
      var GRIDmain = MODmain.query("grid[pid=GRIDset_user]")[0];
      GRIDmain.getStore().load();
      GRIDmain.updateLayout();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btsetting_group_click: function () {
    try {
      var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
      var MDLgroup_user = Ext.create("TDK.mgroup_user.mgroup_user", {});
      var popup = Ext.create("Ext.window.Window", {
        alias: "widget.FRMgroup_user",
        reference: "FRMgroup_user",
        title: "User Group Access",
        modal: true,
        closeAction: "destroy",
        centered: true,
        width: mainpanel.getWidth() * 0.8,
        height: mainpanel.getHeight() * 0.9,
        layout: "fit",
        bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
        items: [
          //
          { xtype: MDLgroup_user },
        ],
      });
      return popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btnew_click: function () {
    try {
      var me = this;
      var popup = Ext.create("TDK.set_user.FRMset_user", {});
      popup.query("button[pid=btsave]")[0].on("click", this.btsave_click, this);
      popup.query("button[pid=btdelete]")[0].on("click", this.btdelete_click, this);
      popup.on(
        "beforeclose",
        function () {
          try {
            var me = this;
            var MODmain = me.getView();
            var GRIDmain = MODmain.query("grid[pid=GRIDset_user]")[0];
            GRIDmain.getStore().load();
          } catch (ex) {
            COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
          }
        },
        this
      );
      popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btrow_itemclick: function (xgrid, rowIndex) {
    try {
      var me = this;
      var vdt = xgrid.getStore().getAt(rowIndex).data;
      xgrid.getSelectionModel().select(rowIndex);

      var popup = Ext.create("TDK.set_user.FRMset_user", {});
      popup.query("button[pid=btsave]")[0].on("click", this.btsave_click, this);
      popup.query("button[pid=btdelete]")[0].on("click", this.btdelete_click, this);
      var GRIDdept = popup.query("grid[pid=GRIDsetting_akses]")[0];
      GRIDdept.getStore().nvdata = vdt;
      GRIDdept.getStore().load();
      popup.on(
        "beforeclose",
        function () {
          try {
            var me = this;
            var MODmain = me.getView();
            var GRIDmain = MODmain.query("grid[pid=GRIDset_user]")[0];
            GRIDmain.getStore().load();
          } catch (ex) {
            COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
          }
        },
        this
      );

      var FRM = popup.query("form")[0];
      FRM.getForm().reset();
      FRM.getForm().setValues(vdt);
      popup.show();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btsave_click: function (cmp) {
    try {
      var FRMmain = cmp.up("window");
      var FRM = FRMmain.query("form")[0];

      var vdt = FRM.getValues(false, false, false, true);
      if (vdt.userlogin === "") {
        COMP.TipToast.msgbox("Error", "Userlogin cannot be empty", { cls: "danger", delay: 2000 });
        return false;
      }
      if (vdt.username === "") {
        COMP.TipToast.msgbox("Error", "Username cannot be empty", { cls: "danger", delay: 2000 });
        return false;
      }

      if (vdt.useractive === "" || vdt.useractive === null) {
        COMP.TipToast.msgbox("Error", "Active Status cannot be empty", { cls: "danger", delay: 2000 });
        return false;
      }
      if (vdt.usergroup === "" || vdt.usergroup === null) {
        COMP.TipToast.msgbox("Error", "User Group cannot be empty", { cls: "danger", delay: 2000 });
        return false;
      }
      if (vdt.userpassword === "") {
        COMP.TipToast.msgbox("Error", "Password cannot be empty", { cls: "danger", delay: 2000 });
        return false;
      }
      FRM.getForm().setValues({
        DTPROCESS: vdt.userid === 0 ? "create_user" : "update_user",
      });
      return this.send_toserver("Save Data", "Confirm Save Data", FRM, FRMmain);
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  send_toserver: function (title, titledesc, FRM, popup) {
    try {
      var vdt = FRM.getValues(false, false, false, true);
      var me = this;
      var ndepartment = [];
      var GRIDsetting_akses = popup.query("grid[pid=GRIDsetting_akses]")[0];
      GRIDsetting_akses.getStore()
        .getDataSource()
        .each(function (record) {
          ndepartment.push(record.data);
        });
      if (ndepartment.length < 1) {
        COMP.TipToast.msgbox("Error", "Pilih Department lebih dulu", { cls: "danger", delay: 2000 });
        return false;
      }
      console.log((vdt.userdept = ndepartment[0].userdept));
      console.log(ndepartment);

      Ext.MessageBox.confirm(
        title,
        titledesc,
        function (button) {
          if (button === "yes") {
            var params = Ext.encode({
              method: "process_data",
              module: vdt.DTPROCESS,
              vdata: Ext.encode(vdt),
              vdept: Ext.encode(ndepartment),
            });
            var hasil = COMP.run.getservice(vconfig.service_api + "cpuser/cpuser", params, "POST", me.var_global.jwt);
            hasil.then(function (content) {
              var val = Ext.decode(content, true)[0];
              if (val.success === "true") {
                COMP.TipToast.msgbox("success", val.message, { cls: "success", delay: 3000 });
                popup.close();
              } else {
                COMP.TipToast.msgbox("Error", val.message, { cls: "danger", delay: 3000 });
              }
            }, this);
          }
        },
        this
      );
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  btdelete_click: function (cmp) {
    try {
      var FRMmain = cmp.up("window");
      var FRM = FRMmain.query("form")[0];
      FRM.getForm().setValues({
        DTPROCESS: "delete_user",
      });
      return this.send_toserver("Delete Data", "Confirm Delete data", FRM, FRMmain);
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
});
