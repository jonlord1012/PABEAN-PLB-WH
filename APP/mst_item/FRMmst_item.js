var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.mst_item.FRMmst_item", {
  extend: "Ext.window.Window",
  alias: "widget.FRMmst_item",
  reference: "FRMmst_item",
  title: "Master Item",
  modal: true,
  closeAction: "destroy",
  centered: true,
  controller: "Cmst_item",
  bodyPadding: "5 5 5 5",
  flex: 1,
  width: mainpanel.getWidth() * 0.75,
  height: mainpanel.getHeight() * 0.95,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
  items: [
    {
      xtype: "form",
      frame: false,
      border: false,
      fieldDefaults: {
        labelAlign: "right",
        labelWidth: 80,
        width: 220,
        margin: "0 10 0 0",
        fieldCls: "fieldinput",
      },
      items: [
        {
          xtype: "container",
          layout: "hbox",
          bodyPadding: "5 0 0 0",
          items: [
            {
              xtype: "fieldset",
              flex: 1,
              title: "Part Information",
              layout: { type: "hbox", pack: "start", align: "stretch" },
              margin: "5 5 5 5",
              items: [
                {
                  xtype: "container",
                  layout: "vbox",
                  margin: "5 0 0 0",
                  items: [
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "numberfield",
                          fieldLabel: "id",
                          name: "id",
                          allowBlank: false,
                          maxLength: 50,
                          value: 0,
                          hidden: true,
                        },
                        {
                          xtype: "textfield",
                          fieldLabel: "Part No",
                          name: "part_no",
                          allowBlank: false,
                          maxLength: 70,
                          width: 470,
                        },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "textfield",
                          fieldLabel: "Part SAP No",
                          name: "part_sapno",
                          maxLength: 50,
                          width: 340,
                        },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "textfield",
                          fieldLabel: "Part Alias",
                          name: "part_alias",
                          maxLength: 50,
                          width: 470,
                        },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "textfield",
                          fieldLabel: "Part Name",
                          name: "part_name",
                          allowBlank: false,
                          maxLength: 255,
                          width: 470,
                        },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "textfield",
                          fieldLabel: "Nomor HS",
                          name: "nomor_hs",
                          maxLength: 50,
                        },
                        { xtype: "tbspacer", width: 20 },
                        {
                          xtype: "combobox",
                          name: "base_part",
                          fieldLabel: "Base Part",
                          displayField: "defcode",
                          valueField: "defcode",
                          fieldCls: "fieldinput",
                          queryMode: "local",
                          allowBlank: false,
                          forceSelection: true,
                          typeAhead: true,
                          anyMatch: true,
                          minChars: 0,
                          flex: 1,
                          store: {
                            autoLoad: true,
                            remoteSort: false,
                            remoteFilter: false,
                            pageSize: 0,
                            fields: [
                              { name: "defcode", type: "string" },
                              { name: "defname", type: "string" },
                            ],
                            proxy: {
                              type: "ajax",
                              disableCaching: false,
                              noCache: false,
                              headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                              actionMethods: { read: "POST" },
                              url: vconfig.service_api + "global/global",
                              extraParams: {
                                method: "read_part_base_part",
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
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 8 0",
                      items: [
                        {
                          xtype: "textareafield",
                          fieldLabel: "Description",
                          name: "part_description",
                          width: 470,
                        },
                      ],
                    },

                  ],
                },
                { xtype: "tbspacer", flex: 1 },
                {
                  xtype: "container",
                  layout: { type: "vbox", pack: "start", align: "stretch" },
                  margin: "5 0 0 0",
                  items: [
                    {
                      xtype: "container",
                      layout: { type: "hbox", pack: "start", align: "stretch" },
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "combobox",
                          name: "part_category",
                          fieldLabel: "Part Category",
                          displayField: "defcode",
                          valueField: "defcode",
                          fieldCls: "fieldinput",
                          queryMode: "local",
                          allowBlank: false,
                          forceSelection: true,
                          typeAhead: true,
                          anyMatch: true,
                          minChars: 0,
                          flex: 1,
                          store: {
                            autoLoad: true,
                            remoteSort: false,
                            remoteFilter: false,
                            pageSize: 0,
                            fields: [
                              { name: "defcode", type: "string" },
                              { name: "defname", type: "string" },
                            ],
                            proxy: {
                              type: "ajax",
                              disableCaching: false,
                              noCache: false,
                              headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                              actionMethods: { read: "POST" },
                              url: vconfig.service_api + "global/global",
                              extraParams: {
                                method: "read_part_category",
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
                        {
                          xtype: "combobox",
                          name: "part_uom_in",
                          fieldLabel: "UOM In",
                          displayField: "defcode",
                          valueField: "defcode",
                          fieldCls: "fieldinput",
                          queryMode: "local",
                          allowBlank: false,
                          forceSelection: true,
                          typeAhead: true,
                          anyMatch: true,
                          minChars: 0,
                          flex: 0.5,
                          store: {
                            autoLoad: true,
                            remoteSort: false,
                            remoteFilter: false,
                            pageSize: 0,
                            fields: [
                              { name: "defcode", type: "string" },
                              { name: "defname", type: "string" },
                            ],
                            proxy: {
                              type: "ajax",
                              disableCaching: false,
                              noCache: false,
                              headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                              actionMethods: { read: "POST" },
                              url: vconfig.service_api + "global/global",
                              extraParams: {
                                method: "read_part_uom",
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
                    {
                      xtype: "container",
                      layout: { type: "hbox", pack: "start", align: "stretch" },
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "combobox",
                          name: "part_group",
                          fieldLabel: "Part Group",
                          displayField: "defcode",
                          valueField: "defcode",
                          fieldCls: "fieldinput",
                          queryMode: "local",
                          allowBlank: false,
                          forceSelection: true,
                          typeAhead: true,
                          anyMatch: true,
                          minChars: 0,
                          flex: 1,
                          store: {
                            autoLoad: true,
                            remoteSort: false,
                            remoteFilter: false,
                            pageSize: 0,
                            fields: [
                              { name: "defcode", type: "string" },
                              { name: "defname", type: "string" },
                            ],
                            proxy: {
                              type: "ajax",
                              disableCaching: false,
                              noCache: false,
                              headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                              actionMethods: { read: "POST" },
                              url: vconfig.service_api + "global/global",
                              extraParams: {
                                method: "read_part_group",
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
                        {
                          xtype: "combobox",
                          fieldLabel: "UOM Out",
                          name: "part_uom_out",
                          displayField: "defcode",
                          valueField: "defcode",
                          fieldCls: "fieldinput",
                          queryMode: "local",
                          allowBlank: false,
                          forceSelection: true,
                          typeAhead: true,
                          anyMatch: true,
                          minChars: 0,
                          flex: 0.5,
                          store: {
                            autoLoad: true,
                            remoteSort: false,
                            remoteFilter: false,
                            pageSize: 0,
                            fields: [
                              { name: "defcode", type: "string" },
                              { name: "defname", type: "string" },
                            ],
                            proxy: {
                              type: "ajax",
                              disableCaching: false,
                              noCache: false,
                              headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                              actionMethods: { read: "POST" },
                              url: vconfig.service_api + "global/global",
                              extraParams: {
                                method: "read_part_uom",
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
                    {
                      xtype: "container",
                      layout: { type: "hbox", pack: "start", align: "stretch" },
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "combobox",
                          fieldLabel: "Part Type",
                          name: "part_type",
                          displayField: "defcode",
                          valueField: "defcode",
                          fieldCls: "fieldinput",
                          queryMode: "local",
                          allowBlank: false,
                          forceSelection: true,
                          typeAhead: true,
                          anyMatch: true,
                          minChars: 0,
                          flex: 1,
                          store: {
                            autoLoad: true,
                            remoteSort: false,
                            remoteFilter: false,
                            pageSize: 0,
                            fields: [
                              { name: "defcode", type: "string" },
                              { name: "defname", type: "string" },
                            ],
                            proxy: {
                              type: "ajax",
                              disableCaching: false,
                              noCache: false,
                              headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                              actionMethods: { read: "POST" },
                              url: vconfig.service_api + "global/global",
                              extraParams: {
                                method: "read_part_type",
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
                        {
                          xtype: "numberfield",
                          fieldLabel: "Min Qty",
                          name: "part_min_qty",
                          flex: 0.5,
                          minValue: 0,
                          allowBlank: false,
                          hideTrigger: true,
                        },
                      ],
                    },
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "textfield",
                          fieldLabel: "Consumeable",
                          name: "part_consumable",
                          maxLength: 50,
                        },
                        { xtype: "tbspacer", width: 20 },
                        {
                          xtype: "numberfield",
                          fieldLabel: "Service Level",
                          name: "part_svc_level",
                          minValue: 0,
                          maxValue: 100,
                          hideTrigger: true,
                        },
                      ],
                    },
                    
                  ],
                },
              ],
            },
          ],
        },
      ],
    },
    { xtype: "tbspacer", height: 5 },
    {
      xtype: "grid",
      pid: "GRIDitem_rack",
      emptyText: "No Matching Records",
      autoScroll: true,
      flex: 1,
      plugins: ["filterfield"],
      viewConfig: {
        enableTextSelection: true,
        columnLines: true,
      },
      store: {
        autoLoad: false,
        remoteSort: true,
        remoteFilter: true,
        pageSize: 15,
        fields: [
          { name: "TANGGAL_AJU", type: "date" },
          { name: "TANGGAL_DAFTAR", type: "date" },
        ],
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
          actionMethods: { read: "POST" },
          url: vconfig.service_api + "mst_item/mst_items",
          reader: {
            type: "json",
            rootProperty: "Rows",
            totalProperty: "TotalRows",
            successProperty: "success",
          },
        },
        listeners: {
          beforeload: function (store, operation, eOpts) {
            try {
              var FRMmain = store.FRMmain;
              var FRM = FRMmain.query("form")[0];
              var dtval = FRM.getValues(false, false, false, true);
              console.log(dtval);
              operation.setParams({
                method: "read_item_rack",
                vdata: Ext.encode(dtval),
              });
            } catch (ex) {
              COMP.TipToast.msgbox("Error", ex.message, {
                cls: "danger",
                delay: 2000,
              });
            }
          },
        },
      },
      columns: {
        defaults: {
          sortable: true,
          filter: { xtype: "textfield" },
          width: 150,
        },
        items: [
          { xtype: "rownumberer", width: 50, sortable: false, filter: false },
          {
            xtype: "actioncolumn",
            width: 35,
            align: "center",
            menuDisabled: true,
            sortable: false,
            filter: false,
            items: [
              {
                icon: vconfig.getstyle + "icon/grid.png",
                handler: "GRIDmst_rack_click",
                tooltip: "Detail Dokumen",
              },
            ],
          },
          { header: "LOKASI RACK", dataIndex: "rack_location" },
          { header: "RACK NO", dataIndex: "rack_no" },
          { header: "KATEGORI RACK", dataIndex: "rack_category" },
          { header: "PART NO", dataIndex: "part_no", width: 120 },
          { header: "MAX QTY", dataIndex: "max_qty", width: 100 },
          { header: "SAFETY QTY", dataIndex: "safety_qty", width: 100 },
          { header: "CREATE", dataIndex: "create_user" },
          {
            header: "DATE",
            dataIndex: "create_date",
            width: 120,
            renderer: function (value) {
              var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
              return text;
            },
          },
          { header: "UPDATE", dataIndex: "update_user", width: 150 },
          {
            header: "DATE",
            dataIndex: "update_date",
            width: 120,
            renderer: function (value) {
              var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
              return text;
            },
          },
        ],
      },
      bbar: {
        xtype: "pagingtoolbar",
        displayInfo: true,
        displayMsg: "Displaying topics {0} - {1} of {2}",
        emptyMsg: "No topics to display",
      },
    },
  ],
  dockedItems: [
    {
      xtype: "toolbar",
      height: 30,
      dock: "top",
      items: [
        { xtype: "tbspacer", width: 5 },
        {
          xtype: "button",
          text: "Save",
          pid: "btsimpan_draft",
          icon: vconfig.getstyle + "icon/save.png",
          tooltip: "Save Master Item Data",
          cls: "fontblack-button",
          handler: function (btn) {
            var me = btn.up("window");
            me.handler_btsave_data(btn);
          },
        },
        {
          xtype: "button",
          text: "Delete",
          pid: "bthapus_draft",
          icon: vconfig.getstyle + "icon/delete.png",
          tooltip: "Delete Master Item Data",
          cls: "fontblack-button",
          handler: function (btn) {
            var me = btn.up("window");
            me.handler_btdelete_data(btn);
          },
        },
      ],
    },
  ],
  handler_btsave_data: function (btn) {
    try {
      var PAGEthis = btn.up("window");
      var MODULEmain = PAGEthis.MODULEmain;
      var GRIDmst_item = MODULEmain.query("grid[pid=GRIDmst_item]")[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);

      // Validation
      if (Ext.isEmpty(dtval.part_no)) {
        COMP.TipToast.msgbox("Error", "Part No cannot be empty", { cls: "danger", delay: 3000 });
        return false;
      }
      if (Ext.isEmpty(dtval.part_alias)) {
        COMP.TipToast.msgbox("Error", "Part Alias cannot be empty", { cls: "danger", delay: 3000 });
        return false;
      }
      if (Ext.isEmpty(dtval.part_name)) {
        COMP.TipToast.msgbox("Error", "Part Name cannot be empty", { cls: "danger", delay: 3000 });
        return false;
      }

      Ext.MessageBox.confirm(
        "Confirmation",
        "Confirm Save Data",
        function (button) {
          if (button === "yes") {
            var params = Ext.encode({
              method: "save_data",
              vdata: Ext.encode(dtval),
              VUSERLOGIN,
            });

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_item/mst_item", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                var vdata = Ext.decode(val.vdata, true);
                FRM.getForm().setValues(vdata);
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                GRIDmst_item.getStore().load();
                PAGEthis.close();
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
  handler_btdelete_data: function (btn) {
    try {
      var PAGEthis = btn.up("window");
      var MODULEmain = PAGEthis.MODULEmain;
      var GRIDmst_item = MODULEmain.query("grid[pid=GRIDmst_item]")[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);

      if (Ext.isEmpty(dtval.part_no)) {
        return false;
      }

      Ext.MessageBox.confirm(
        "Confirmation",
        "Confirm Delete Data",
        function (button) {
          if (button === "yes") {
            var params = Ext.encode({
              method: "delete_data",
              vdata: Ext.encode(dtval),
            });

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_item/mst_item", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                PAGEthis.close();
                GRIDmst_item.getStore().load();
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
});