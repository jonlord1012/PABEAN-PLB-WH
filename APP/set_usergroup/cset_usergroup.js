Ext.define("TDK.set_usergroup.Cset_usergroup", {
  extend: "Ext.app.ViewController",
  alias: "controller.Cset_usergroup",
  init: function (view) {
    this.control({
      //
    });
    this.listen({
      store: {},
    });
    this.var_global = {
      jwt: localStorage.getItem("TDK_JWT"),
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
      var FRMmain = me.getView().query("FRMset_usergroup")[0];
      var GRIDgroup_user = FRMmain.query("grid[pid=GRIDgroup_user]")[0];
      GRIDgroup_user.on("itemclick", function (cmp, rec) {
        me.GRIDgroup_user_itemclick(cmp, rec, FRMmain);
      });
      var treepanel_menu = FRMmain.query("treepanel[pid=treepanel_menu]")[0];
      treepanel_menu.on("beforecheckchange", function (node, checked) {
        if (!node.isLeaf() && !node.isExpanded()) {
          node.expand();
        }
        return true;
      });
      treepanel_menu.nvdata = null;
      // treepanel_menu.on("afterrender", this.treepanel_menu_load_data, this);
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  treepanel_menu_load_data: async function (cmp) {
    try {
      var me = this;
      var params = Ext.encode({
        method: "list_menu_access",
        vdata: cmp.nvdata,
      });
      var params2 = Ext.encode({
        method: "list_my_menu_access",
        vdata: cmp.nvdata,
      });

      // Fetch the data
      var [hasilContent, hasil2Content] = await Promise.all([COMP.run.getservice(vconfig.service_api + "cpuser/cpuser", params, "POST", me.var_global.jwt), COMP.run.getservice(vconfig.service_api + "cpuser/cpuser", params2, "POST", me.var_global.jwt)]);

      var val = Ext.decode(hasilContent, true); // Full menu list
      var val2 = Ext.decode(hasil2Content, true).Rows; // Current access

      var uniqueMenus = [];
      var seenMenuIds = new Set();

      val.Rows.forEach(function (menuItem) {
        if (!seenMenuIds.has(menuItem.rmenuid)) {
          seenMenuIds.add(menuItem.rmenuid);
          uniqueMenus.push(menuItem);
        }
      });

      var currentAccessMenu = Ext.Array.pluck(val2, "rmenuid");

      // Filter only level 1 items (mparrent = "0")
      var topLevelMenus = _.filter(uniqueMenus, function (item) {
        return item.mparrent == "0";
      });

      var buildTree = function (parentMenu) {
        var children = _.filter(uniqueMenus, function (item) {
          return item.mparrent == parentMenu.mcode;
        });

        if (children.length > 0) {
          return _.map(children, function (child) {
            var isChecked = currentAccessMenu.includes(child.rmenuid);

            return {
              rmodule: child.rmodule,
              mcontrol: child.mcontrol,
              mname: child.mname,
              mcode: child.mcode,
              mparrent: child.mparrent,
              leaf: child.mleaf,
              expanded: child.mexpand == "TRUE",
              checked: isChecked,
              rgroup: child.rgroup,
              children: buildTree(child),
            };
          });
        } else {
          return null;
        }
      };

      var hasil_menu = _.map(topLevelMenus, function (menuItem) {
        var isChecked = currentAccessMenu.includes(menuItem.rmenuid);

        return {
          rmodule: menuItem.rmodule,
          mcontrol: menuItem.mcontrol,
          mname: menuItem.mname,
          mcode: menuItem.mcode,
          leaf: menuItem.mleaf,
          expanded: menuItem.mexpand == "TRUE",
          checked: isChecked,
          rgroup: menuItem.rgroup,
          children: buildTree(menuItem),
        };
      });

      var data = {
        children: hasil_menu,
      };

      // Set the tree's root data
      cmp.getStore().setRoot(data);
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },

  GRIDgroup_user_itemclick: function (cmp, rec, FRMmain) {
    try {
      var treepanel_menu = FRMmain.query("treepanel[pid=treepanel_menu]")[0];
      treepanel_menu.nvdata = Ext.encode(rec.data);
      this.treepanel_menu_load_data(treepanel_menu);
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
});
