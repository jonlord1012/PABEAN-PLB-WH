Ext.define("NJC.cp_usergroup.Ccp_usergroup", {
  extend: "Ext.app.ViewController",
  alias: "controller.Ccp_usergroup",
  init: function (view) {
    this.control({
      //
    });
    this.listen({
      store: {},
    });
    this.var_global = {
      jwt: localStorage.getItem("NJC_JWT"),
    };
    this.var_definition = {};
    this.renderpage();
  },
  formatAmount: function (value) {
    var text = Ext.util.Format.number(value, "0,000.00/i");
    return text;
  },
  renderpage: function () {
    try {
      var me = this;
      var FRMmain = me.getView().query("FRMcp_usergroup")[0];
      var GRIDgroup_user = FRMmain.query("grid[pid=GRIDgroup_user]")[0];
      GRIDgroup_user.on("itemclick", this.GRIDgroup_user_itemclick, this);
      var treepanel_menu = FRMmain.query("treepanel[pid=treepanel_menu]")[0];
      treepanel_menu.nvdata = null;
      //treepanel_menu.on("afterrender", this.treepanel_menu_load_data, this);
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  treepanel_menu_load_data: function (cmp) {
    try {
      var me = this;
      var params = Ext.encode({
        method: "list_menuaccess",
        vdata: cmp.nvdata,
      });
      var hasil = COMP.run.getservice(vconfig.service_api + "cp_usergroup/cp_usergroup", params, "POST", me.var_global.jwt);
      hasil.then(function (content) {
        var val = Ext.decode(content, true);
        var data = {
          expanded: true,
          checked: false,
        };
        var GROUP_MODULE_NAME = _.chain(val)
          .filter(function (item) {
            return item.MODULE_NAME !== "";
          })
          .sortBy("MODULE_SHORT")
          .value();
        var grouped = _.chain(GROUP_MODULE_NAME).groupBy("MMODULE").values().sortBy("MODULE_SHORT").value();
        var hasil_menu = [];
        Ext.Array.each(grouped, function (item) {
          //LEVEL 1 TURUNAN KE 1
          var turunan1 = [];
          var GROUP_TURUNAN1 = _.chain(val)
            .filter(function (dt1) {
              return dt1.MODULE_NAME === item[0].MODULE_NAME && dt1.MPARRENT === "0";
            })
            .sortBy("MSHORT")
            .value();
          Ext.Array.each(GROUP_TURUNAN1, function (item) {
            //LEVEL 2 TURUNAN KE 2
            var turunan2 = [];
            var GROUP_TURUNAN2 = _.chain(val)
              .filter(function (dt2) {
                return dt2.MPARRENT === item.MCODE;
              })
              .sortBy("MSHORT")
              .value();
            Ext.Array.each(GROUP_TURUNAN2, function (item) {
              //LEVEL 3 TURUNAN KE 3
              var turunan3 = [];
              var GROUP_TURUNAN3 = _.chain(val)
                .filter(function (dt3) {
                  return dt3.MPARRENT === item.MCODE;
                })
                .sortBy("MSHORT")
                .value();
              Ext.Array.each(GROUP_TURUNAN3, function (item) {
                turunan3.push({
                  MENU_AKSES: item.MNAME,
                  MMODULE: item.MMODULE,
                  MCONTROL: item.MCONTROL,
                  MNAME: item.MNAME,
                  MQTIP: item.MQTIP,
                  MCODE: item.MCODE,
                  leaf: item.MCHILDREN === "TRUE" ? false : true,
                  expanded: true,
                  checked: item.CHECKED === "TRUE" ? true : false,
                  children: [],
                  GROUP_USER: item.GROUP_USER,
                });
              });
              turunan2.push({
                MENU_AKSES: item.MNAME,
                MMODULE: item.MMODULE,
                MCONTROL: item.MCONTROL,
                MNAME: item.MNAME,
                MQTIP: item.MQTIP,
                MCODE: item.MCODE,
                leaf: item.MCHILDREN === "TRUE" ? false : true,
                expanded: true,
                checked: item.CHECKED === "TRUE" ? true : false,
                children: turunan3,
                GROUP_USER: item.GROUP_USER,
              });
            });
            turunan1.push({
              MENU_AKSES: item.MNAME,
              MMODULE: item.MMODULE,
              MCONTROL: item.MCONTROL,
              MNAME: item.MNAME,
              MQTIP: item.MQTIP,
              MCODE: item.MCODE,
              leaf: item.MCHILDREN === "TRUE" ? false : true,
              expanded: true,
              checked: item.CHECKED === "TRUE" ? true : false,
              children: turunan2,
              GROUP_USER: item.GROUP_USER,
            });
          });

          hasil_menu.push({
            MENU_AKSES: item[0].MODULE_NAME,
            MMODULE: item[0].MMODULE,
            MCONTROL: "",
            MNAME: "",
            MQTIP: "",
            MCODE: 0,
            leaf: false,
            expanded: item[0].CHECKED === "TRUE" ? true : false,
            checked: item[0].CHECKED === "TRUE" ? true : false,
            children: turunan1,
            GROUP_USER: item[0].GROUP_USER,
          });
        });
        data.children = hasil_menu;
        cmp.getStore().setRoot(data);
      }, this);
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  GRIDgroup_user_itemclick: function (cmp, rec) {
    try {
      var FRMmain = cmp.up("panel[pid=FRMcp_usergroup]");
      var treepanel_menu = FRMmain.query("treepanel[pid=treepanel_menu]")[0];
      treepanel_menu.nvdata = Ext.encode(rec.data);
      this.treepanel_menu_load_data(treepanel_menu);
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
});
