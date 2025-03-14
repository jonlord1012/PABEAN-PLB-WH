Ext.define("NJC.out_reqmat_pemi.out_reqmat_pemi", {
  extend: "Ext.form.Panel",
  alias: "widget.out_reqmat_pemi",
  reference: "out_reqmat_pemi",
  config: {},
  requires: [
    //
    "NJC.out_reqmat_pemi.GRIDout_reqmat_pemi",
    "NJC.out_reqmat_pemi.Cout_reqmat_pemi",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Cout_reqmat_pemi",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelout_reqmat_pemi",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDout_reqmat_pemi" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
