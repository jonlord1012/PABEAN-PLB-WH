Ext.define("NJC.mst_category_supp.mst_category_supp", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_category_supp",
  reference: "mst_category_supp",
  config: {},
  requires: [
    //
    "NJC.mst_category_supp.GRIDmst_category_supp",
    "NJC.mst_category_supp.Cmst_category_supp",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cmst_category_supp",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_category_supp",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_category_supp" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
