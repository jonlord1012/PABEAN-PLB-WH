 Ext.define('COMP.download',{
    singleton: true,
    gdate: function(modulename,modulepath,getview,title) {
        try{
            Ext.MessageBox.show({
                msg : 'Processing...',
                progressText : 'process...',
                width : 300,
                wait : true
            });

            
            var form = Ext.ComponentQuery.query(modulename)[0];//this.lookupReference('FRMposales');
            
            
            if(!form){
                form =Ext.define(modulepath, {
                        extend: Ext.window.Window,
                        alias:'widget.'+modulename,
                        title: title,
                        modal: true,
                        closeAction: 'hide',
                        centered : true,
                        autoScroll:true,
                        width :400,
                        height:160,
                        bodyPadding:'5 5 5 5',
                        layout: { type: 'vbox', pack: 'start', align: 'stretch' },
                        items:[
                                {
                                    xtype: 'fieldset',
                                    fieldDefaults: {
                                        labelAlign: 'right',
                                        labelWidth:50,
                                    },
                                    items:[
                                        {
                                            xtype:'container',
                                            layout:'vbox',
                                            items:[
                                                {xtype: 'datefield',fieldLabel: 'Start',name: 'tfromdate',width:250,format: 'Y-m-d',value:new Date(),altFormats: 'Y-m-d',},
                                                {xtype: 'datefield',fieldLabel: 'End',name: 'ttodate',width:250,format: 'Y-m-d',value:new Date(),altFormats: 'Y-m-d',},
                                            ]
                                        },

                                    ]
                                }

                        ],
                        bbar:[
                            {xtype:'tbspacer',width:10},
                            {xtype: 'button',text:'Download',pid:'btdownload',icon:extjs_url + 'icon/download.png',tooltip:'Download'}
                        ]
                }); 
                


            }
            getview.add(form).show(); 
            Ext.MessageBox.hide();
            return form;
        }catch(ex){
            Ext.MessageBox.hide();
            COMP.TipToast.toast('Error', ex.statusText + ' module = ' + modulepath , {cls: "danger", delay: 2000});
        }
           
    },
    gall: function(modulename,modulepath,getview,title) {
        try{
            Ext.MessageBox.show({
                msg : 'Processing...',
                progressText : 'process...',
                width : 300,
                wait : true
            });

            
            var form = Ext.ComponentQuery.query(modulename)[0];//this.lookupReference('FRMposales');
            
            
            if(!form){
                form =Ext.define(modulepath, {
                        extend: Ext.window.Window,
                        alias:'widget.'+modulename,
                        title: title,
                        modal: true,
                        closeAction: 'hide',
                        centered : true,
                        autoScroll:true,
                        width :400,
                        height:160,
                        bodyPadding:'5 5 5 5',
                        layout: { type: 'vbox', pack: 'start', align: 'stretch' },
                        items:[
                                {
                                    xtype: 'fieldset',
                                    fieldDefaults: {
                                        labelAlign: 'right',
                                        labelWidth:50,
                                    },
                                    items:[
                                        {
                                            xtype:'container',
                                            layout:'vbox',
                                            items:[
                                                {xtype:'displayfield',value:'Download Semua data..',margin: '0 0 0 10'},
                                            ]
                                        },

                                    ]
                                }

                        ],
                        bbar:[
                            {xtype:'tbspacer',width:10},
                            {xtype: 'button',text:'Download',pid:'btdownload',icon:extjs_url + 'icon/download.png',tooltip:'Download'}
                        ]
                }); 
                


            }
            getview.add(form).show(); 
            Ext.MessageBox.hide();
            return form;
        }catch(ex){
            Ext.MessageBox.hide();
            COMP.TipToast.toast('Error', ex.statusText + ' module = ' + modulepath , {cls: "danger", delay: 2000});
        }
           
    },
    toexcel:function(url,modulename){
        try{
            Ext.MessageBox.show({
                msg : 'Exporting, please wait...',
                progressText : 'process...',
                width : 300,
                wait : true
            });
            Ext.Ajax.request({
                url: url,
                method:'POST',
                timeout:1200000,
                
                success : function (r) {    
                if (r.status===200){
                    Ext.DomHelper.append(Ext.getBody(), {
                            tag:          'iframe',
                            frameBorder:  0,
                            width:        0,
                            height:       0,
                            css:          'display:none;visibility:hidden;height:0px;',
                            src:         url
                        });   
                    Ext.MessageBox.hide(); 
                }else{
                     Ext.MessageBox.hide();
                }
                return true;
                },
                failure:function(e) {
                    
                    COMP.TipToast.toast("Error Process", e.statusText, {cls: "danger", delay: 2000});
                    Ext.MessageBox.hide();
                    
                }
                
            });
            
            
        }catch(e){
            COMP.TipToast.toast("Error Process", e.message, {cls: "danger", delay: 2000});
            Ext.MessageBox.hide();
        }
    },
    topdf:function(modulename,modulepath,title,url){
        try{
            Ext.MessageBox.show({
                msg : 'Exporting, please wait...',
                progressText : 'process...',
                width : 300,
                wait : true
            });
            Ext.define(modulepath, {
                extend: 'Ext.window.Window',
                xtype: 'modalview',
                alias:'widget.'+modulename,
                title: title,
                modal: true,
                items: {
                    xtype:'panel',
                    width: 800,
                    height: 450,
                    items:{
                        xtype: 'component',
                        autoEl: {
                            tag: 'iframe',
                            style: 'height: 100%; width: 100%; border: none',
                            src:  url
                        }
                    }

                }
            });
            this.dialog = Ext.widget('modalview');
            this.dialog.show();
            Ext.MessageBox.hide();
        }catch(err){
            Ext.MessageBox.hide();
            TDK.config.RUN.errhandler("Error Process Export",e.message);
        }
    },
    process_download: function(url,param) {
        var deferred = new Ext.Deferred();
        try{
            Ext.MessageBox.show({
                msg : 'Processing...',
                progressText : 'process...',
                width : 300,
                wait : true
            });
            
            Ext.Ajax.request({
                url: url,
                method: 'POST',
                params: param,
                timeout:120000,
                success: function(response){
                    var data = response.responseText;
                    deferred.resolve(data);
                },
                failure: function(response){
                    
                    deferred.reject(response.status);
                    COMP.TipToast.toast('Error', response.statusText + ' ' + response.status, {cls: "danger", delay: 2000});
                }
            });
            
            Ext.MessageBox.hide();
            return deferred.promise;
            
        }catch(e){
            Ext.MessageBox.hide();
            COMP.TipToast.toast('Error', e.statusText , {cls: "danger", delay: 2000});
        }
        
    },
});