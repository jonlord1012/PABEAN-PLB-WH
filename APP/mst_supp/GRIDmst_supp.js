Ext.define("NJC.mst_supp.GRIDmst_supp", {
  extend: "Ext.form.Panel",
  alias: "widget.GRIDmst_supp",
  reference: "GRIDmst_supp",
  frame: false,
  border: false,
  autoScroll: true,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  requires: [],
  items: [
    {
      xtype: "grid",
      pid: "GRIDmst_supp",
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
          url: vconfig.service_api + "mst_supp/mst_supps",
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
                handler: "GRIDmst_supp_click",
                tooltip: "Detail Dokumen",
              },
            ],
          },
          { hidden: true, header: "ID", dataIndex: "ID", width: 200 },
          // { header: "KODE INTERNAL", dataIndex: "KODE_INTERNAL",  width: 100, },
          { header: "NAMA", dataIndex: "NAMA", width: 300 },
          { header: "ALAMAT", dataIndex: "ALAMAT", width: 400 },
          { header: "KATEGORI", dataIndex: "SUPP_CATEGORY", width: 150 },
          { header: "NPWP", dataIndex: "NPWP", width: 150 },
          { header: "NIB", dataIndex: "NIB", width: 150 },
          { header: "NOMOR IJIN", dataIndex: "NOMOR_IJIN", width: 150 },
          { header: "TANGGAL IJIN", dataIndex: "TANGGAL_IJIN", width: 90 },
          { header: "KODE ID", dataIndex: "KODE_ID", width: 70 },
          { header: "KODE NEGARA", dataIndex: "KODE_NEGARA", width: 90 },
          { header: "ID CEISA", dataIndex: "ID_CEISA", width: 70 },
          { header: "ID COMPANY", dataIndex: "ID_COMPANY", width: 80 },
          { header: "NIPERENTITAS", dataIndex: "NIPERENTITAS", width: 90 },
          { header: "KODE JENIS API", dataIndex: "KODEJENISAPI", width: 100 },
          { header: "KODE STATUS", dataIndex: "KODESTATUS", width: 90 },
          { header: "KODE JENIS IDENTITAS", dataIndex: "KODEJENISIDENTITAS", width: 120 },
          { header: "CREATE", dataIndex: "SYSCREATEUSER", sortable: false, width: 150 },
          {
            header: "DATE",
            dataIndex: "SYSCREATEDATE",
            sortable: false,
            width: 120,
            renderer: function (value) {
              var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
              return text;
            },
          },
          { header: "UPDATE", dataIndex: "SYSUPDATEUSER", sortable: false, width: 150 },
          {
            header: "DATE",
            dataIndex: "SYSUPDATEDATE",
            sortable: false,
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
          pid: "btinput_mst_supp",
          handler: "btinput_mst_supp_click",
        },
        "->",
        {
          xtype: "button_download",
          nvdata: {
            modelpath: "mst_supp/mst_supp",
            method: "download_data",
            title: "Download Data Master Supplier",
            grid_pid: "GRIDmst_supp",
          },
        },
      ],
    },
  ],
});
