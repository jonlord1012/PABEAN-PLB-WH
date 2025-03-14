Ext.define("NJC.Cmainpage", {
  extend: "Ext.app.ViewController",
  alias: "controller.Cmainpage",
  init: function (view) {
    this.control({
      "mainpage Vloginpage button[pid=btlogin]": { click: this.btlogin_click },
      "mainpage Vloginpage button[pid=btdashboard_pv]": { click: this.btdashboard_pv },
      "mainpage button[btid=btid]": { click: this.btmenu_click },
      "mainpage button >>[pid=btlogout]": { click: this.btlogout_click },
      "mainpage button >>[pid=btchange_password]": { click: this.btchange_password_click },
      "mainpage Vmainpage tabpanel[pid=modmasterTAB]": { add: this.tab_panelmenu_add },
      "Vprofile button[pid=btsave_profile]": { click: this.btsave_profile_click },
      //"Vloginpage textfield[name=UserLogin]": { specialkey: this.dokeyUserLogin },
      //"Vloginpage textfield[name=UserPassword]": { specialkey: this.dokeyUserPassword },
    });
    this.renderpage();
  },
  renderpage: function () {
    try {
      console.log("renderer mainpage");

      var me = this;
      var mainpage = me.getView();

      var Vmainpage = mainpage.query("Vmainpage")[0];
      var Vuserlogin = Vmainpage.query("[pid=Vuserlogin]")[0];

      var taskemail = new Ext.util.TaskRunner();
      if (localStorage.getItem("NJC_JWT") === null) {
        this.SetActivepanel(0);
      } else {
        var hasil = COMP.run.gethide(vconfig.service_main + "reload", "", "POST", localStorage.getItem("NJC_JWT"));
        hasil.then(function (content) {
          var val = Ext.decode(content, true);
          if (val.success === "true") {
            var valprofile = Ext.decode(val.profile, true)[0];
            me.SetActivepanel(1);
            Vuserlogin.setHtml(valprofile.USERNAME);
            var dtmenu_header = Ext.decode(val.dtmenu_header, true);
            var dtmenu = Ext.decode(val.dtmenu, true);

            me.configuration_menu(dtmenu_header, dtmenu);
          }
        }, this);
      }
    } catch (err) {
      COMP.TipToast.msgbox("Error", err.message, { cls: "danger", delay: 2000 });
    }
  },
  GetActivepanel: function () {
    try {
      var panel = Ext.ComponentQuery.query("mainpage")[0];
      var actindex = panel.getLayout().activeItem;
      var idx = panel.items.indexOf(actindex);
      return idx;
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  SetActivepanel: function (val) {
    try {
      var panel = Ext.ComponentQuery.query("mainpage")[0];
      panel.setActiveItem(val);
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  dokeyUserLogin: function (field, event, options) {
    var form = this.lookupReference("Vloginpage");
    if (event.getKey() === event.ENTER) {
      form.getForm().findField("UserPassword").focus(true);
    }
  },
  dokeyUserPassword: function (field, event, options) {
    if (event.getKey() === event.ENTER) {
      this.btlogin_click(field);
    }
  },
  btchange_password_click: function () {
    try {
      var me = this;
      var popup = Ext.create("NJC.Vprofile");
      var local_profile = localStorage.getItem("NJC_PROFILE");
      var vprofile = Ext.decode(local_profile, true)[0];
      var FRM = popup.query("form")[0];
      FRM.getForm().setValues({
        USERLOGIN: vprofile.userlogin,
        USERNAME: vprofile.username,
        USERGROUP: vprofile.usergroup,
      });
      return popup.show();
    } catch (err) {
      COMP.TipToast.msgbox("Error", err.message, { cls: "danger", delay: 2000 });
    }
  },
  btsave_profile_click: function () {
    try {
      var me = this;
      var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
      var FRM = Ext.ComponentQuery.query("Vprofile form")[0];
      var vdt = FRM.getValues(false, false, false, true);
      if (vdt.PASSWORD_LAMA === "") {
        COMP.TipToast.msgbox("Error", "Input Password lama lebih dulu", { cls: "danger", delay: 2000 });
        return false;
      }
      if (vdt.PASSWORD_BARU === "") {
        COMP.TipToast.msgbox("Error", "Input Password lebih dulu", { cls: "danger", delay: 2000 });
        return false;
      }
      if (vdt.PASSWORD_CONFIRM === "") {
        COMP.TipToast.msgbox("Error", "Input Password Confirm lebih dulu", { cls: "danger", delay: 2000 });
        return false;
      }
      if (vdt.PASSWORD_LAMA === vdt.PASSWORD_BARU) {
        COMP.TipToast.msgbox("Error", "Password Baru tidak boleh sama", { cls: "danger", delay: 2000 });
        return false;
      }
      if (vdt.PASSWORD_BARU !== vdt.PASSWORD_CONFIRM) {
        COMP.TipToast.msgbox("Error", "Password Confirm tidak sama", { cls: "danger", delay: 2000 });
        return false;
      }

      var params = Ext.encode({
        method: "change_password",
        data: Ext.encode(vdt),
      });
      var hasil = COMP.run.getservice(vconfig.service_api + "myprofile/myprofile", params, "POST", localStorage.getItem("NJC_JWT"));
      hasil.then(function (content) {
        var val = Ext.decode(content, true);
        if (val.success == "true") {
          COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 2000 });
          //me.btlogout_click();
        } else {
          COMP.TipToast.msgbox("Error", val.message, { cls: "danger", delay: 2000 });
        }
      });
    } catch (err) {
      COMP.TipToast.toamsgboxst("Error", err.message, { cls: "danger", delay: 2000 });
    }
  },
  btlogin_click: function (cmp) {
    try {
      var FRM = cmp.up("Vloginpage");
      var me = this;
      Ext.create("Ext.data.Store", {
        id: "NJC_JWT_PROFILE",
        proxy: {
          type: "localstorage",
          id: "NJC_JWT_PROFILE",
        },
      });
      Ext.create("Ext.data.Store", {
        id: "NJC_JWT",
        proxy: {
          type: "localstorage",
          id: "NJC_JWT",
        },
      });

      var dtval = FRM.getForm().getValues(false, false, false, true);

      var params = {
        method: "login",
        data: {
          username: dtval.UserLogin,
          password: dtval.UserPassword,
        },
      };

      var hasil = COMP.run.gethide(vconfig.service_url, Ext.encode(params), "POST");
      hasil.then(function (content) {
        var val = Ext.decode(content, true);
        if (val.success === "true") {
          COMP.TipToast.msgbox("success", val.messages, { cls: "success", delay: 3000 });

          localStorage.setItem("NJC_JWT", val.token);
          localStorage.setItem("NJC_PROFILE", val.profile);
          location.reload();
        } else {
          COMP.TipToast.msgbox("Error", val.message, { cls: "danger", delay: 3000 });
        }
      }, this);
    } catch (err) {
      COMP.TipToast.msgbox("Error", err.message, { cls: "danger", delay: 2000 });
    }
  },
  configuration_menu: function (dtmenu_header, dtmenu) {
    try {
      var me = this;
      var mainpage = me.getView();
      var Vmainpage = mainpage.query("Vmainpage")[0];
      var Vaccordion = Vmainpage.query("[pid=MAIN_ACCORDION]")[0];

      const sortedData = _.sortBy(dtmenu, "defshort");
      const menu_group = _.groupBy(sortedData, "defname");
      var menu_accordion = [];

      function find_menu(data_object, data_filter) {
        const filteredData = _.filter(data_object, function (item) {
          return Object.keys(data_filter).every(function (key) {
            return item[key] === data_filter[key];
          });
        });

        const groupedData = _.groupBy(filteredData, "MCODE");

        const sortedGroupedData = _.mapValues(groupedData, function (group) {
          return _.sortBy(group, "MSHORT");
        });

        return sortedGroupedData;
      }

      function buildMenuWithSort(moduleGroup, parentCode) {
        var menus = find_menu(dtmenu, { MODULE_GROUP: moduleGroup, MPARRENT: parentCode });

        return _.orderBy(
          Object.values(menus).map(function (group) {
            var menuItem = group[0];

            return {
              leaf: menuItem.MCHILDREN === "TRUE" ? false : true,
              pid_type: menuItem.MCHILDREN === "TRUE" ? "FOLDER" : "APP",
              text: menuItem.MNAME,
              allowclick: menuItem.MALLOWCLICK === "TRUE",
              mmodule: menuItem.MMODULE,
              mfolder: menuItem.MCONTROL,
              mcode: menuItem.MCODE,
              mcontrol: menuItem.MCONTROL,
              mtooltip: menuItem.MQTIP,
              mshort: menuItem.MSHORT,
              nvdata: menuItem,
              children: menuItem.MCHILDREN === "TRUE" ? buildMenuWithSort(moduleGroup, menuItem.MCODE) : [],
              ...(menuItem.MCHILDREN === "TRUE" ? { expanded: menuItem.MEXPAND === "TRUE" ? true : false } : {}),
            };
          }),
          "mshort"
        );
      }

      Ext.iterate(dtmenu_header, function (key) {
        var vmenu_utama = _.sortBy(buildMenuWithSort(key.MODULE_GROUP, 0), "mshort");
        menu_accordion.push({
          icon: vconfig.basepath + "style/icon/app.png",
          title: key.MODULE_NAME,
          rootVisible: false,
          border: true,
          items: [
            {
              xtype: "treepanel",
              rootVisible: false,
              pid: "treepanel_" + key.MODULE_GROUP,
              border: false,
              store: {
                type: "tree",
                data: {
                  text: "Ext JS",
                  expanded: false,
                  children: vmenu_utama,
                  allowclick: false,
                },
              },
              listeners: {
                itemclick: "mainmenu_link_click",
              },
            },
          ],
        });
      });

      Vaccordion.add(menu_accordion);
    } catch (err) {
      COMP.TipToast.msgbox("Error", err.message, { cls: "danger", delay: 2000 });
    }
  },
  mainmenu_link_click: function (cmp, rec) {
    try {
      var me = this;
      var recdt = rec.data;
      if (recdt.allowclick === false) {
        return false;
      }
      var MAINPAGE = this.getView();
      var Vmainpage = MAINPAGE.query("Vmainpage")[0];

      var Vmaintab = Vmainpage.query("tabpanel[pid=mainpage_tabpanel]")[0];
      var modulepage = "NJC." + recdt.mcontrol + "." + recdt.mcontrol;

      var tab_id = recdt.mmodule + "_" + recdt.mcontrol;

      var ntab = Vmaintab.child("#" + tab_id);

      if (ntab === null) {
        try {
          ntab = Vmaintab.add(
            Ext.create(modulepage, {
              waitMsgTarget: true,
              id: tab_id,
              itemId: tab_id,
              closable: true,
              frame: false,
              border: false,
              title: recdt.mtooltip,
            })
          );
        } catch (err) {
          COMP.TipToast.msgbox("Error Create Menu", err.message, { cls: "danger", delay: 2000 });
        }
      }
      Vmaintab.setActiveTab(ntab);
    } catch (err) {
      COMP.TipToast.msgbox("Error", err.message, { cls: "danger", delay: 2000 });
    }
  },
  btlogout_click: function () {
    try {
      localStorage.clear();
      location.reload();
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
  tab_panelmenu_add: function (cmp) {
    try {
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
});
