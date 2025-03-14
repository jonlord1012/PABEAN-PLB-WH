Ext.define("NJC.mst_group_item.mst_group_item", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_group_item",
  reference: "mst_group_item",
  config: {},
  requires: [
    //
    "NJC.mst_group_item.GRIDmst_group_item",
    "NJC.mst_group_item.Cmst_group_item",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cmst_group_item",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_group_item",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_group_item" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
