Ext.define("NJC.mst_tipe_item.mst_tipe_item", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_tipe_item",
  reference: "mst_tipe_item",
  config: {},
  requires: [
    //
    "NJC.mst_tipe_item.GRIDmst_tipe_item",
    "NJC.mst_tipe_item.Cmst_tipe_item",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cmst_tipe_item",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_tipe_item",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_tipe_item" }],
      dockedItems: [],
    });
       
    // "->",
    // {
    //   xtype: "button_download",
    //   nvdata: {
    //     modelpath: "wh_receiving_itempart/wh_receiving_itempart",
    //     method: "download_file",
    //     title: "Download Data Master Item",
    //     grid_pid: "GRIDwh_receiving_itempart",
    //   },
    // },

    this.callParent(arguments);
  },
});
