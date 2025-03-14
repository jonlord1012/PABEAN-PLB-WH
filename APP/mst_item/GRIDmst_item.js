var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.mst_item.GRIDmst_item", {
  extend: "Ext.form.Panel",
  alias: "widget.GRIDmst_item",
  reference: "GRIDmst_item",
  frame: false,
  border: false,
  autoScroll: true,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  requires: [],
  items: [
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
              operation.setParams({
                method: "read_data",
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
          {
            xtype: "actioncolumn",
            width: 35,
            align: "center",
            menuDisabled: true,
            filter: false,
            sortable: false,
            items: [
              {
                icon: vconfig.getstyle + "icon/grid.png",
                handler: "GRIDmst_item_click",
                tooltip: "Detail Dokumen",
              },
            ],
          },
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
      dock: "top",
      items: [
        { xtype: "tbspacer", width: 5 },
        {
          xtype: "button",
          text: "Refresh",
          pid: "btrefresh",
          icon: vconfig.getstyle + "icon/update.ico",
          tooltip: "Refresh Data",
          handler: "btrefresh_click",
        },

        {
          xtype: "button",
          text: "New Input",
          icon: vconfig.getstyle + "icon/add.png",
          tooltip: "New Input",
          pid: "btinput_mst_item",
          handler: "btinput_mst_item_click",
        },
        "->",
        {
          xtype: "button",
          text: "Download Excel",
          pid: "btdownload_excel",
          icon: vconfig.getstyle + "icon/excel.ico",
          handler: function (btn) {
            var me = btn.up("panel");
            me.handler_btdownload_excel(btn);
          },
        },
      ],
    },
  ],
  // handler
  handler_btdownload_excel: function (btn) {
    try {
      Ext.MessageBox.show(
        {
          title: "Konfirmasi Download",
          width: mainpanel.getWidth() * 0.3,
          height: mainpanel.getHeight() * 0.2,
          msg: [
            //
            "<ul>",
            "<li>Konfirmasi proses download Data Item/Material</li>",
            "</ul>",
          ].join(""),
          buttons: Ext.MessageBox.YESNO,
          animateTarget: btn,
          buttonText: {
            yes: "Cancel Proses",
            no: "Proses Download",
          },
          scope: this,
          fn: function (btconfirm) {
            if (btconfirm === "no") {
              var params = Ext.encode({
                method: "download_data",
              });
              var hasil = COMP.run.getservice(vconfig.service_api + "mst_item/mst_item", params, "POST", localStorage.getItem("NJC_JWT"));
              hasil.then(function (content) {
                var val = Ext.decode(content, true);
                if (val.success === "true") {
                  COMP.run.getlinkfile(val.filename);
                } else {
                  COMP.TipToast.toast("Error", val.message, { cls: "danger", delay: 3000 });
                }
              }, this);
            }
          },
        },
        this
      );
    } catch (ex) {
      COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
    }
  },
});
