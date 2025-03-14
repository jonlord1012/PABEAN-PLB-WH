Ext.define("NJC.wh_returnitem.GRIDwh_returnitem", {
  extend: "Ext.form.Panel",
  alias: "widget.GRIDwh_returnitem",
  reference: "GRIDwh_returnitem",
  frame: false,
  border: false,
  autoScroll: true,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  requires: [],
  items: [
    {
      xtype: "grid",
      pid: "GRIDwh_returnitem",
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
        pageSize: 17,
        field: [{ dataIndex: "POSTINGSTATUS", type: "string" }],
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
          actionMethods: { read: "POST" },
          url: vconfig.service_api + "wh_returnitem/wh_returnitems",
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
      columns: [
        { xtype: "rownumberer", width: 50 },
        {
          xtype: "actioncolumn",
          width: 35,
          align: "center",
          menuDisabled: true,
          sortable: false,
          items: [
            {
                
              icon: vconfig.getstyle + "icon/grid.png",
              tooltip: "Detail Dokumen",
              getClass: function (v, meta, record) {
                if (record.get("POSTINGSTATUS") === "OPEN") {
                  return "icon-grid";
                } else {
                  return "icon-lock";
                }
              },
              handler: "btdetail_rows_click",
              getTip: function (value, meta, record) {
                return record.get("POSTINGSTATUS") === "OPEN" ? "Dokumen Status Open" : "Dokumen Status Posting";
              },
            },
          ],
        },
        { hidden: true, header: "ID", dataIndex: "id", sortable: true, width: 200, filter: { xtype: "textfield" } },
        { header: "RECEIPT NO", dataIndex: "receipt_no", sortable: true, width: 180, filter: { xtype: "textfield" } },
        { header: "RECEIPT DATE", dataIndex: "receipt_date", sortable: true, width: 100, filter: { xtype: "textfield" } },
        { header: "RECEIPT USER", dataIndex: "receipt_user", sortable: true, width: 150, filter: { xtype: "textfield" } },
        { header: "APPROVE USER", dataIndex: "approve_user", sortable: true, width: 100, filter: { xtype: "textfield" } },
        { header: "APPROVE DATE", dataIndex: "approve_date", sortable: true, width: 140, filter: { xtype: "textfield" } },
        { header: "UPDATE", dataIndex: "create_user", sortable: true, width: 80, filter: { xtype: "textfield" } },
        { header: "DATE", dataIndex: "update_date", sortable: true, width: 120, filter: { xtype: "textfield" } },
        { header: "STATUS", dataIndex: "postingstatus", sortable: true, width: 80, filter: { xtype: "textfield" } },
        { header: "POSTING", dataIndex: "postinguser", sortable: true, width: 80, filter: { xtype: "textfield" } },
        { header: "DATE", dataIndex: "postingdate", sortable: true, width: 120, filter: { xtype: "textfield" } },

      ],
      bbar: {
        xtype: "pagingtoolbar",
        displayInfo: true,
        displayMsg: "Displaying topics {0} - {1} of {2}",
        emptyMsg: "No topics to display",
      },
      listeners: {
        afterrender: "GRIDinv_material_in_load",
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
        // {
        //   xtype: "button",
        //   text: "Receiving Part",
        //   icon: vconfig.getstyle + "icon/add.png",
        //   tooltip: "Receiving Part",
        //   pid: "btinput_receiving_part",
        //   handler: "btinput_receiving_partclick",
        // },
      ],
    },
  ],
});
