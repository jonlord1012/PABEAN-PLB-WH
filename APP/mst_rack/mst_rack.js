Ext.define("NJC.mst_rack.mst_rack", {
  extend: "Ext.form.Panel",
  alias: "widget.mst_rack",
  reference: "mst_rack",
  config: {},
  requires: [
    //
    "NJC.mst_rack.GRIDmst_rack",
    "NJC.mst_rack.Cmst_rack",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controllers
  controller: "Cmst_rack",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelmst_rack",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDmst_rack" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
