Ext.define("NJC.rpt_out_material.rpt_out_material", {
  extend: "Ext.form.Panel",
  alias: "widget.rpt_out_material",
  reference: "rpt_out_material",
  config: {},
  requires: [
    //
    "NJC.rpt_out_material.GRIDrpt_out_material",
    "NJC.rpt_out_material.Crpt_out_material",
  ],
  constructor: function (config) {
    return this.callParent(arguments);
  },
  //untuk include controller
  controller: "Crpt_out_material",
  initComponent: function () {
    Ext.apply(this, {
      xtype: "panel",
      pid: "panelrpt_out_material",
      layout: "card",
      frame: false,
      border: false,
      items: [{ xtype: "GRIDrpt_out_material" }],
      dockedItems: [],
    });

    this.callParent(arguments);
  },
});
