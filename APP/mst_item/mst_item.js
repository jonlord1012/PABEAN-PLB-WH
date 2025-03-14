Ext.define("NJC.mst_item.mst_item", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_item",
  reference: "mst_item",
  config: {},
  requires: [
    //
    "NJC.mst_item.GRIDmst_item",
    "NJC.mst_item.Cmst_item",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cmst_item",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_item",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_item" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
