Ext.define("NJC.out_printbarcode_spq.out_printbarcode_spq", {
  extend: "Ext.form.Panel",
  alias: "widget.out_printbarcode_spq",
  reference: "out_printbarcode_spq",
  config: {},
  requires: [
    //
    "NJC.out_printbarcode_spq.GRIDout_printbarcode_spq",
    "NJC.out_printbarcode_spq.Cout_printbarcode_spq",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cout_printbarcode_spq",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelout_printbarcode_spq",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDout_printbarcode_spq" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
