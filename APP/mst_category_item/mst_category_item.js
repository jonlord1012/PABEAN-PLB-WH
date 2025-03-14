Ext.define("NJC.mst_category_item.mst_category_item", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_category_item",
  reference: "mst_category_item",
  config: {},
  requires: [
    //
    "NJC.mst_category_item.GRIDmst_category_item",
    "NJC.mst_category_item.Cmst_category_item",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cmst_category_item",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_category_item",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_category_item" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
