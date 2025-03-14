Ext.define("NJC.mst_unit_item.mst_unit_item", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_unit_item",
  reference: "mst_unit_item",
  config: {},
  requires: [
    //
    "NJC.mst_unit_item.GRIDmst_unit_item",
    "NJC.mst_unit_item.Cmst_unit_item",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cmst_unit_item",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_unit_item",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_unit_item" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
