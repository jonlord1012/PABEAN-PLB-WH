Ext.define("NJC.mst_category_rack.mst_category_rack", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_category_rack",
  reference: "mst_category_rack",
  config: {},
  requires: [
    //
    "NJC.mst_category_rack.GRIDmst_category_rack",
    "NJC.mst_category_rack.Cmst_category_rack",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cmst_category_rack",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_category_rack",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_category_rack" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
