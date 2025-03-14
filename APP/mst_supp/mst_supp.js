Ext.define("NJC.mst_supp.mst_supp", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_supp",
  reference: "mst_supp",
  config: {},
  requires: [
    //
    "NJC.mst_supp.GRIDmst_supp",
    "NJC.mst_supp.Cmst_supp",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cmst_supp",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_supp",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_supp" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
