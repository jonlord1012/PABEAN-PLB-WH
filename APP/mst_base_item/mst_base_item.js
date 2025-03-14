Ext.define("NJC.mst_base_item.mst_base_item", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_base_item",
  reference: "mst_base_item",
  config: {},
  requires: [
    //
    "NJC.mst_base_item.GRIDmst_base_item",
    "NJC.mst_base_item.Cmst_base_item",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cmst_base_item",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_base_item",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_base_item" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
