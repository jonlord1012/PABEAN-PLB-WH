var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.mst_rack.FRMmst_rack", {
  extend: "Ext.window.Window",
  alias: "widget.FRMmst_rack",
  reference: "FRMmst_rack",
  title: "Master Rack",
  modal: true,
  closeAction: "destroy",
  centered: true,
  controller: "Cmst_rack",
  bodyPadding: "5 5 5 5",
  flex: 1,
  width: mainpanel.getWidth() * 0.9,
  height: mainpanel.getHeight() * 0.9,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
  items: [
    {
      xtype: "form",
      frame: false,
      border: false,
      fieldDefaults: {
        labelAlign: "left",
        labelWidth: 110,
        margin: "0 10 5 0",
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
              layout: "hbox",
              margin: "5 5 5 5",
              items: [
                {
                  xtype: "container",
                  layout: "vbox",
                  margin: "5 0 0 0",
                  items: [
                    // Part Numbers Section
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
                          fieldLabel: "Rack Location",
                          name: "rack_location",
                          allowBlank: false,
                          maxLength: 50,
                          width: 470
                        },
                        { xtype: "tbspacer", width: 20 },
                        {
                          xtype: "textfield",
                          fieldLabel: "Part No",
                          name: "part_no",
                          maxLength: 50,
                          width: 400
                        },
                      ],
                    },

                    // Part Aliases Sections
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "textfield",
                          fieldLabel: "Rack No",
                          name: "rack_no",
                          maxLength: 50,
                          width: 470
                        },
                        { xtype: "tbspacer", width: 20 },
                        {
                          xtype: "numberfield",
                          fieldLabel: "Safety Qty",
                          name: "safety_qty",
                          minValue: 0,
                          hideTrigger: true,
                          allowBlank: false,
                          width: 200
                        },
                      ],
                    },
                    // Part Details Section
                    {
                      xtype: "container",
                      layout: "hbox",
                      margin: "0 0 5 0",
                      items: [
                        {
                          xtype: "combobox",
                          name: "rack_category",
                          fieldLabel: "Rack Category",
                          displayField: "defcode",
                          valueField: "defcode",
                          fieldCls: "fieldinput",
                          allowBlank: false,
                          queryMode: "local",
                          width: 300,
                          forceSelection: true,
                          typeAhead: true,
                          minChars: 0,
                          anyMatch: true,
                          store: {
                            autoLoad: true,
                            remoteSort: true,
                            remoteFilter: true,
                            pageSize: 17,
                            fields: ["defcode", "defname"],
                            proxy: {
                              type: "ajax",
                              disableCaching: false,
                              noCache: false,
                              headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                              actionMethods: { read: "POST" },
                              url: vconfig.service_api + "global/globals",
                              extraParams: {
                                method: "read_part_rackcategory",
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
                        { xtype: "tbspacer", width: 190 },
                        {
                          xtype: "numberfield",
                          fieldLabel: "Max Qty",
                          width: 200,
                          hideTrigger: true,
                          name: "max_qty",
                          minValue: 0,
                          allowBlank: false,
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
      pid: "GRIDmst_item",
      emptyText: "No Matching Records",
      autoScroll: true,
      flex: 1,
      plugins: ["filterfield"],
      viewConfig: {
        enableTextSelection: true,
        columnLines: true,
      },
      store: {
        autoLoad: true,
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
          url: vconfig.service_api + "mst_rack/mst_racks",
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
              var FRMmain = store.FRMmain ? store.FRMmain : '';
              var FRM = FRMmain.query("form")[0];
              var dtval = FRM.getValues(false, false, false, true);
              operation.setParams({
                method: "read_data_item_byrack",
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
        },
        items: [
          { xtype: "rownumberer", width: 50, filter: false, sortable: false },
          { header: "PART NO", dataIndex: "part_no", width: 150 },
          { header: "PART SAP NO", dataIndex: "part_sapno", width: 150 },
          { header: "PART ALIAS", dataIndex: "part_alias", width: 150 },
          { header: "BASE PART", dataIndex: "base_part", width: 150 },
          { header: "NOMOR HS", dataIndex: "nomor_hs", width: 80 },
          { header: "PART NAME", dataIndex: "part_name", width: 80 },
          { header: "PART DESCRIPTION", dataIndex: "part_description", width: 100 },
          { header: "PART GROUP", dataIndex: "part_group", width: 100 },
          { header: "PART CATEGORY", dataIndex: "part_category", width: 80 },
          { header: "PART TYPE", dataIndex: "part_type", width: 100 },
          { header: "PART CONSUMEABLE", dataIndex: "part_consumeable", width: 100 },
          { header: "PART UOM IN", dataIndex: "part_uom_in", width: 120 },
          { header: "PART UOM OUT", dataIndex: "part_uom_out", width: 120 },
          { header: "PART MIN QTY", dataIndex: "part_min_qty", width: 150 },
          { header: "PART SVC LEVEL", dataIndex: "part_svc_level", width: 150 },
          {
            header: "CREATE",
            dataIndex: "create_user",
            width: 150,
          },
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
          tooltip: "Save Master Rack Data",
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
          tooltip: "Delete Master Rack Data",
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
      var GRIDmst_rack = MODULEmain.query("grid[pid=GRIDmst_rack]")[0];

      var FRM = PAGEthis.query("form")[0];
      var dtval = FRM.getValues(false, false, false, true);

      // Validation
      if (Ext.isEmpty(dtval.rack_location)) {
        COMP.TipToast.msgbox("Error", "Rack Location cannot be empty", { cls: "danger", delay: 3000 });
        return false;
      }
      if (Ext.isEmpty(dtval.rack_no)) {
        COMP.TipToast.msgbox("Error", "Rack No cannot be empty", { cls: "danger", delay: 3000 });
        return false;
      }
      if (Ext.isEmpty(dtval.rack_category)) {
        COMP.TipToast.msgbox("Error", "Rack category cannot be empty", { cls: "danger", delay: 3000 });
        return false;
      }
      if (Ext.isEmpty(dtval.part_no)) {
        COMP.TipToast.msgbox("Error", "Part No cannot be empty", { cls: "danger", delay: 3000 });
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

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_rack/mst_rack", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                var vdata = Ext.decode(val.vdata, true);
                FRM.getForm().setValues(vdata);
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                GRIDmst_rack.getStore().load();
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
      var GRIDmst_rack = MODULEmain.query("grid[pid=GRIDmst_rack]")[0];

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

            var hasil = COMP.run.getservice(vconfig.service_api + "mst_rack/mst_rack", params, "POST", localStorage.getItem("NJC_JWT"));
            hasil.then(function (content) {
              var val = Ext.decode(content, true);
              if (val.success === "true") {
                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                PAGEthis.close();
                GRIDmst_rack.getStore().load();
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