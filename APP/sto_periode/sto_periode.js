Ext.define("NJC.sto_periode.sto_periode", {
  extend: "Ext.form.Panel",
  alias: "widget.sto_periode",
  reference: "sto_periode",
  config: {},
  requires: [
    //
    "NJC.sto_periode.GRIDsto_periode",
    "NJC.sto_periode.Csto_periode",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Csto_periode",
  initComponent: function () {
    // validasi department yang digunakan
    Ext.apply(this, {
      xtype: "panel",
      pid: "panel_sto_periode",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDsto_periode" }],
    });

    this.callParent(arguments);
  },
});
