Ext.define("NJC.sto_data.sto_data", {
  extend: "Ext.form.Panel",
  alias: "widget.sto_data",
  reference: "sto_data",
  config: {},
  requires: [
    //
    "NJC.sto_data.GRIDsto_data",
    "NJC.sto_data.Csto_data",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Csto_data",
  initComponent: function () {
    // validasi department yang digunakan
    Ext.apply(this, {
      xtype: "panel",
      pid: "panel_sto_data",
      layout: "card",
      frame: false,
      border: false,
      items: [
        {
          xtype: "GRIDsto_data",
          flex: 1,
        },
      ],
    });

    this.callParent(arguments);
  },
});
