var mainpanel = Ext.ComponentQuery.query("mainpage")[0];
Ext.define("NJC.sto_periode.FRMsto_periode", {
    extend: "Ext.window.Window",
    alias: "widget.FRMsto_periode",
    reference: "FRMsto_periode",
    title: "Sto Periode",
    modal: true,
    closeAction: "destroy",
    centered: true,
    bodyPadding: "5 5 5 5",
    flex: 1,
    width: mainpanel.getWidth() * 0.9,
    height: mainpanel.getHeight() * 0.9,
    layout: { type: "vbox", pack: "start", align: "stretch" },
    bodyStyle: "background:#FFFFFF;background-color:#FFFFFF",
    items: [
        {
            xtype: "form",
            frame: false,
            border: false,
            fieldDefaults: {
                labelAlign: "left",
                labelWidth: 80,
                margin: "0 10 5 0",
                fieldCls: "fieldinput",
            },
            items: [
                {
                    xtype: "container",
                    layout: "hbox",
                    bodyPadding: "5 0 0 0",
                    items: [
                        {
                            xtype: "fieldset",
                            flex: 1,
                            title: "Periode Information",
                            layout: "hbox",
                            margin: "5 5 5 5",
                            items: [
                                {
                                    xtype: "container",
                                    layout: "vbox",
                                    margin: "5 0 0 0",
                                    items: [
                                        // Part Numbers Section
                                        {
                                            xtype: "container",
                                            layout: "hbox",
                                            margin: "0 0 5 0",
                                            items: [
                                                {
                                                    xtype: "numberfield",
                                                    fieldLabel: "id",
                                                    name: "id",
                                                    allowBlank: false,
                                                    maxLength: 50,
                                                    value: 0,
                                                    hidden: true,
                                                },
                                                {
                                                    xtype: "textfield",
                                                    fieldLabel: "Periode",
                                                    name: "period",
                                                    fieldCls: "fieldlock",
                                                    allowBlank: false,
                                                    readOnly: true,
                                                    maxLength: 50,
                                                    width: 220
                                                },
                                                {
                                                    xtype: "textfield",
                                                    name: "status",
                                                    fieldCls: "fieldlock",
                                                    allowBlank: false,
                                                    readOnly: true,
                                                    maxLength: 50,
                                                    width: 60,
                                                },
                                                { xtype: "tbspacer", width: 20 },
                                                {
                                                    xtype: "datefield",
                                                    fieldLabel: "Tanggal",
                                                    name: "tgl_mulai",
                                                    margin: "0 5 5 0",
                                                    labelAlign: "right",
                                                    labelWidth: 50,
                                                    maxLength: 50,
                                                    format: "d - F - Y",
                                                    submitFormat: "Y-m-d",
                                                    minValue: new Date(),
                                                },
                                                {
                                                    xtype: "datefield",
                                                    fieldLabel: "s/d ",
                                                    name: "tgl_selesai",
                                                    labelAlign: "right",
                                                    labelWidth: 25,
                                                    maxLength: 50,
                                                    format: "d - F - Y",
                                                    submitFormat: "Y-m-d",
                                                    minValue: new Date(),
                                                },

                                            ],
                                        },
                                    ],
                                },
                            ],
                        },
                    ],
                },
            ],
        },
        { xtype: "tbspacer", height: 5 },
        {
            xtype: "grid",
            pid: "GRIDsto_item",
            emptyText: "No Matching Records",
            autoScroll: true,
            flex: 1,
            plugins: ["filterfield"],
            viewConfig: {
                enableTextSelection: true,
                columnLines: true,
            },
            store: {
                autoLoad: true,
                remoteSort: true,
                remoteFilter: true,
                pageSize: 15,
                fields: [
                    { name: "TANGGAL_AJU", type: "date" },
                    { name: "TANGGAL_DAFTAR", type: "date" },
                ],
                proxy: {
                    type: "ajax",
                    disableCaching: false,
                    noCache: false,
                    headers: { Authorization: "Bearer " + localStorage.getItem("NJC_JWT") },
                    actionMethods: { read: "POST" },
                    url: vconfig.service_api + "sto_periode/sto_periodes",
                    reader: {
                        type: "json",
                        rootProperty: "Rows",
                        totalProperty: "TotalRows",
                        successProperty: "success",
                    },
                },
                listeners: {
                    beforeload: function (store, operation, eOpts) {
                        try {
                            var FRMmain = store.FRMmain ? store.FRMmain : '';
                            var FRM = FRMmain.query("form")[0];
                            var dtval = FRM.getValues(false, false, false, true);
                            operation.setParams({
                                method: "read_sto_data_item",
                                vdata: Ext.encode(dtval),
                            });
                        } catch (ex) {
                            COMP.TipToast.msgbox("Error", ex.message, {
                                cls: "danger",
                                delay: 2000,
                            });
                        }
                    },
                },
            },
            columns: {
                defaults: {
                    sortable: true,
                    filter: { xtype: "textfield" },
                },
                items: [
                    { xtype: "rownumberer", width: 50, filter: false, sortable: false },
                    { header: "RECEIPT NO", dataIndex: "receipt_no", width: 150 },
                    { header: "RECEIPT DATE", dataIndex: "receipt_date", width: 150 },
                    { header: "INVOICE NO", dataIndex: "invoice_no", width: 150 },
                    { header: "INVOICE DATE", dataIndex: "invoice_date", width: 150 },
                    { header: "PART NO", dataIndex: "part_no", width: 150 },
                    { header: "MAPP PART NO", dataIndex: "mapp_partno", width: 150 },
                    { header: "PART NAME", dataIndex: "part_name", width: 150 },
                    { header: "GROUP ITEM", dataIndex: "group_item", width: 150 },
                    { header: "INVOICE QTY", dataIndex: "invoice_qty", width: 100 },
                    { header: "RECEIPT QTY", dataIndex: "receipt_qty", width: 100 },
                    { header: "MENU INPUT", dataIndex: "menu_input", width: 100 },
                    { header: "JENIS INPUT", dataIndex: "jenis_input", width: 100 },
                    { header: "SUMBER DATA", dataIndex: "sumber_data", width: 150 },
                    { header: "BC TYPE", dataIndex: "bc_type", width: 100 },
                    { header: "NOMOR AJU", dataIndex: "nomor_aju", width: 150 },
                    { header: "TANGGAL AJU", dataIndex: "tanggal_aju", width: 150 },
                    { header: "NOMOR DAFTAR", dataIndex: "nomor_daftar", width: 150 },
                    { header: "TANGGAL DAFTAR", dataIndex: "tanggal_daftar", width: 150 },
                    { header: "SERI BARANG", dataIndex: "seri_barang", width: 100 },
                    { header: "CTN QTY", dataIndex: "ctn_qty", width: 100 },
                    { header: "PACKING QTY", dataIndex: "packing_qty", width: 100 },
                    { header: "BARCODE", dataIndex: "barcode", width: 200 },
                    { header: "BARCODE SEQ NO", dataIndex: "barcode_seqno", width: 110 },
                    { header: "RACK NO", dataIndex: "rack_no", width: 100 },
                    { header: "ACTUAL QTY", dataIndex: "actual_qty", width: 100 },
                    { header: "STO QTY", dataIndex: "sto_qty", width: 100 },
                    {
                        header: "CREATE",
                        dataIndex: "create_user",
                        width: 150,
                    },
                    {
                        header: "DATE",
                        dataIndex: "create_date",
                        width: 120,

                        renderer: function (value) {
                            var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
                            return text;
                        },
                    },
                    { header: "UPDATE", dataIndex: "update_user", width: 150 },
                    {
                        header: "DATE",
                        dataIndex: "update_date",
                        width: 120,
                        renderer: function (value) {
                            var text = value === null || value === "" ? null : moment(value).format("YYYY-MM-DD HH:mm:ss");
                            return text;
                        },
                    },
                ],
            },
            bbar: {
                xtype: "pagingtoolbar",
                displayInfo: true,
                displayMsg: "Displaying topics {0} - {1} of {2}",
                emptyMsg: "No topics to display",
            },
            dockedItems: [
                {
                    xtype: "toolbar",
                    height: 30,
                    dock: "top",
                    items: [
                        {
                            xtype: "button",
                            text: "Syncronize STO Data",
                            pid: "btsync",
                            icon: vconfig.getstyle + "icon/update.ico",
                            tooltip: "Save Ulang STO Data",
                            cls: "fontwhite-button",
                            handler: function (btn) {
                                var me = btn.up("window");
                                me.handler_btsync_data(btn);
                            },
                        },
                    ]
                }
            ]
        },
    ],
    dockedItems: [
        {
            xtype: "toolbar",
            height: 30,
            dock: "top",
            items: [
                { xtype: "tbspacer", width: 5 },
                {
                    xtype: "button",
                    text: "Save",
                    pid: "btsimpan_draft",
                    icon: vconfig.getstyle + "icon/save.png",
                    tooltip: "Save Master Rack Data",
                    cls: "fontblack-button",
                    handler: function (btn) {
                        var me = btn.up("window");
                        me.handler_btsave_data(btn);
                    },
                },
                {
                    xtype: "button",
                    text: "Delete",
                    pid: "bthapus_draft",
                    icon: vconfig.getstyle + "icon/delete.png",
                    tooltip: "Delete Master Rack Data",
                    cls: "fontblack-button",
                    hidden: true,
                    handler: function (btn) {
                        var me = btn.up("window");
                        me.handler_btdelete_data(btn);
                    },
                },
                "->",
                {
                    xtype: "button",
                    text: "Download Excel",
                    pid: "btdownload_excel",
                    icon: vconfig.getstyle + "icon/excel.ico",
                    handler: function (btn) {
                        var me = btn.up("window");
                        me.handler_btdownload_excel(btn);
                    },
                },
            ],
        },
    ],

    handler_btsave_data: function (btn) {
        try {
            var PAGEthis = btn.up("window");
            var MODULEmain = PAGEthis.MODULEmain;
            var GRIDsto_periode = MODULEmain.query("grid[pid=GRIDsto_periode]")[0];

            var FRM = PAGEthis.query("form")[0];
            var dtval = FRM.getValues(false, false, false, true);

            // Validation
            if (Ext.isEmpty(dtval.tgl_mulai)) {
                COMP.TipToast.msgbox("Error", "Tanggal selesai harus diisi", { cls: "danger", delay: 2000 });
                return false;
            }
            if (Ext.isEmpty(dtval.tgl_selesai)) {
                COMP.TipToast.msgbox("Error", "Tanggal selesai harus diisi", { cls: "danger", delay: 2000 });
                return false;
            }
            dtval.tgl_mulai = dtval.tgl_mulai ? moment(dtval.tgl_mulai).format("YYYY-MM-DD") : null;
            dtval.tgl_selesai = dtval.tgl_selesai ? moment(dtval.tgl_selesai).format("YYYY-MM-DD") : null;

            Ext.MessageBox.confirm(
                "Confirmation",
                "Confirm Save Data",
                function (button) {
                    if (button === "yes") {
                        var params = Ext.encode({
                            method: "save_data",
                            vdata: Ext.encode(dtval),
                            VUSERLOGIN: Ext.decode(localStorage.getItem("NJC_PROFILE"))[0].USERLOGIN,
                        });

                        var hasil = COMP.run.getservice(vconfig.service_api + "sto_periode/sto_periode", params, "POST", localStorage.getItem("NJC_JWT"));
                        hasil.then(function (content) {
                            var val = Ext.decode(content, true);
                            if (val.success === "true") {
                                var vdata = Ext.decode(val.vdata, true);
                                FRM.getForm().setValues(vdata);
                                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                                GRIDsto_periode.getStore().load();
                                PAGEthis.close();
                            } else {
                                COMP.TipToast.msgbox("Error", val.message, { cls: "danger", delay: 3000 });
                            }
                        }, this);
                    }
                },
                this
            );
        } catch (ex) {
            COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
        }
    },
    handler_btdelete_data: function (btn) {
        try {
            var PAGEthis = btn.up("window");
            var MODULEmain = PAGEthis.MODULEmain;
            var GRIDsto_periode = MODULEmain.query("grid[pid=GRIDsto_periode]")[0];

            var FRM = PAGEthis.query("form")[0];
            var dtval = FRM.getValues(false, false, false, true);

            if (Ext.isEmpty(dtval.period)) {
                return false;
            }

            Ext.MessageBox.confirm(
                "Confirmation",
                "Confirm Delete Data",
                function (button) {
                    if (button === "yes") {
                        var params = Ext.encode({
                            method: "delete_data",
                            vdata: Ext.encode(dtval),
                        });

                        var hasil = COMP.run.getservice(vconfig.service_api + "sto_periode/sto_periode", params, "POST", localStorage.getItem("NJC_JWT"));
                        hasil.then(function (content) {
                            var val = Ext.decode(content, true);
                            if (val.success === "true") {
                                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                                PAGEthis.close();
                                GRIDsto_periode.getStore().load();
                            } else {
                                COMP.TipToast.msgbox("Error", val.message, { cls: "danger", delay: 3000 });
                            }
                        }, this);
                    }
                },
                this
            );
        } catch (ex) {
            COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
        }
    },
    handler_btsync_data: function (btn) {
        try {
            var PAGEthis = btn.up("window");
            var MODULEmain = PAGEthis.MODULEmain;
            var GRIDsto_periode = MODULEmain.query("grid[pid=GRIDsto_periode]")[0];
            var GRIDsto_item = PAGEthis.query("grid[pid=GRIDsto_item]")[0];

            var FRM = PAGEthis.query("form")[0];
            var dtval = FRM.getValues(false, false, false, true);

            Ext.MessageBox.confirm(
                "Confirmation",
                "Confirm Syncronize STO Data",
                function (button) {
                    if (button === "yes") {
                        var params = Ext.encode({
                            method: "syncronize_sto_data",
                            vdata: Ext.encode(dtval),
                        });

                        var hasil = COMP.run.getservice(vconfig.service_api + "sto_periode/sto_periode", params, "POST", localStorage.getItem("NJC_JWT"));
                        hasil.then(function (content) {
                            var val = Ext.decode(content, true);
                            if (val.success === "true") {
                                COMP.TipToast.msgbox("Success", val.message, { cls: "success", delay: 3000 });
                                GRIDsto_periode.getStore().load();
                                GRIDsto_item.getStore().load();
                            } else {
                                COMP.TipToast.msgbox("Error", val.message, { cls: "danger", delay: 3000 });
                            }
                        }, this);
                    }
                },
                this
            );
        } catch (ex) {
            COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
        }
    },
    handler_btdownload_excel: function (btn) {
        try {
            var FRMsto_periode = btn.up("window");
            var FRM = FRMsto_periode.query("form")[0];
            var vdata = FRM.getValues(false, false, false, true);
            Ext.MessageBox.show(
                {
                    title: "Konfirmasi Download",
                    width: mainpanel.getWidth() * 0.3,
                    height: mainpanel.getHeight() * 0.2,
                    msg: [
                        //
                        "<ul>",
                        "<li>Konfirmasi proses download Data STO</li>",
                        "</ul>",
                    ].join(""),
                    buttons: Ext.MessageBox.YESNO,
                    animateTarget: btn,
                    buttonText: {
                        yes: "Cancel Proses",
                        no: "Proses Download",
                    },
                    scope: this,
                    fn: function (btconfirm) {
                        if (btconfirm === "no") {
                            var params = Ext.encode({
                                method: "download_sto_data_by_period",
                                vdata: Ext.encode(vdata),
                            });
                            var hasil = COMP.run.getservice(vconfig.service_api + "sto_periode/sto_periode", params, "POST", localStorage.getItem("NJC_JWT"));
                            hasil.then(function (content) {
                                var val = Ext.decode(content, true);
                                if (val.success === "true") {
                                    COMP.run.getlinkfile(val.filename);
                                } else {
                                    COMP.TipToast.toast("Error", val.message, { cls: "danger", delay: 3000 });
                                }
                            }, this);
                        }
                    },
                },
                this
            );
        } catch (ex) {
            COMP.TipToast.msgbox("Error", ex.message, { cls: "danger", delay: 2000 });
        }
    }

});