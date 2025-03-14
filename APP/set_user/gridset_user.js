Ext.define("TDK.set_user.GRIDset_user", {
  extend: "Ext.form.Panel",
  alias: "widget.GRIDset_user",
  reference: "GRIDset_user",
  frame: false,
  border: false,
  layout: { type: "vbox", pack: "start", align: "stretch" },
  requires: [],
  items: [
    {
      xtype: "grid",
      pid: "GRIDset_user",
      emptyText: "No Matching Records",
      autoScroll: true,
      border: false,
      frame: false,
      flex: 1,
      store: {
        autoLoad: true,
        remoteSort: true,
        remoteFilter: true,
        pageSize: 20,
        proxy: {
          type: "ajax",
          disableCaching: false,
          noCache: false,
          headers: { Authorization: "Bearer " + localStorage.getItem("TDK_JWT") },
          actionMethods: { read: "POST" },
          url: vconfig.service_api + "cpuser/cpusers",
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

      plugins: ["filterfield"],
      viewConfig: {
        enableTextSelection: true,
      },
      columns: {
        defaults: {
          sortable: true,
          filter: { xtype: "textfield" },
        },
        items: [
          { xtype: "rownumberer", width: 50, filter: false },
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
                handler: "btrow_itemclick",
                tooltip: "Data Details",
              },
            ],
          },
          { width: 50, header: "ID", dataIndex: "userid", hidden: true },
          { width: 110, header: "GROUP", dataIndex: "usergroup" },
          { width: 150, header: "USERLOGIN", dataIndex: "userlogin" },
          { flex: 1, header: "NAME", dataIndex: "username" },
          { width: 200, header: "EMAIL", dataIndex: "useremail" },
          { width: 65, header: "AKTIF", dataIndex: "useractive" },
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
        "-", //
        { xtype: "tbspacer", width: 5 },
        { xtype: "button", text: "Refresh", pid: "btrefresh", icon: vconfig.getstyle + "icon/update.ico", tooltip: "Refresh Data", handler: "btrefresh_click" },
        { xtype: "button", pid: "btnew", text: "User Baru", icon: vconfig.getstyle + "icon/add.png", tooltip: "Create Term Baru", handler: "btnew_click" },
        "->",
        {
          xtype: "button_download",
          pid: "btdownload",
          nvdata: {
            modelpath: "cpuser/cpuser",
            method: "download_data",
            title: "Download User Login",
            grid_pid: "GRIDset_user",
          },
        },
      ],
      // other options....
    },
  ],
});
