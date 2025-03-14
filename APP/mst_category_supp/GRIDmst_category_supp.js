Ext.define("NJC.mst_category_supp.GRIDmst_category_supp", {
  extend: "Ext.form.Panel",
  alias: "widget.GRIDmst_category_supp",
  reference: "GRIDmst_category_supp",
  frame: false,
  border: false,
  autoScroll: true,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  requires: [],
  items: [
    {
      xtype: "grid",
      pid: "GRIDmst_category_supp",
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
          url: vconfig.service_api + "mst_category_supp/mst_category_supps",
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
          width: 150,
        },
        items: [
          { xtype: "rownumberer", width: 50, filter: false, sortable: false },
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
                handler: "GRIDmst_category_supp_click",
                tooltip: "Detail Dokumen",
              },
            ],
          },
          { header: "KODE KATEGORI", dataIndex: "CATEGORY_CODE" },
          { header: "NAMA KATEGORI", dataIndex: "CATEGORY_NAME" },
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
          pid: "btinput_mst_category_supp",
          handler: "btinput_mst_category_supp_click",
        },
        "->",
        {
          xtype: "button_download",
          nvdata: {
            modelpath: "mst_category_supp/mst_category_supp",
            method: "download_data",
            title: "Download Data Master Category Item",
            grid_pid: "GRIDmst_category_supp",
          },
        },
      ],
    },
  ],
});
