
+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
var Base=$.oc.foundation.base,BaseProto=Base.prototype
var DataRegistry=function(){this.data={}
this.requestCache={}
this.callbackCache={}
Base.call(this)}
DataRegistry.prototype.set=function(plugin,type,subtype,data,params){this.storeData(plugin,type,subtype,data)
if(type=='localization'&&!subtype){this.localizationUpdated(plugin,params)}}
DataRegistry.prototype.get=function($formElement,plugin,type,subtype,callback){if(this.data[plugin]===undefined||this.data[plugin][type]===undefined||this.data[plugin][type][subtype]===undefined||this.isCacheObsolete(this.data[plugin][type][subtype].timestamp)){return this.loadDataFromServer($formElement,plugin,type,subtype,callback)}
callback(this.data[plugin][type][subtype].data)}
DataRegistry.prototype.makeCacheKey=function(plugin,type,subtype){var key=plugin+'-'+type
if(subtype){key+='-'+subtype}
return key}
DataRegistry.prototype.isCacheObsolete=function(timestamp){return(Date.now()-timestamp)>60000*5}
DataRegistry.prototype.loadDataFromServer=function($formElement,plugin,type,subtype,callback){var self=this,cacheKey=this.makeCacheKey(plugin,type,subtype)
if(this.requestCache[cacheKey]===undefined){this.requestCache[cacheKey]=$formElement.request('onPluginDataRegistryGetData',{data:{registry_plugin_code:plugin,registry_data_type:type,registry_data_subtype:subtype}}).done(function(data){if(data.registryData===undefined){throw new Error('Invalid data registry response.')}
self.storeData(plugin,type,subtype,data.registryData)
self.applyCallbacks(cacheKey,data.registryData)
self.requestCache[cacheKey]=undefined})}
this.addCallbackToQueue(callback,cacheKey)
return this.requestCache[cacheKey]}
DataRegistry.prototype.addCallbackToQueue=function(callback,key){if(this.callbackCache[key]===undefined){this.callbackCache[key]=[]}
this.callbackCache[key].push(callback)}
DataRegistry.prototype.applyCallbacks=function(key,registryData){if(this.callbackCache[key]===undefined){return}
for(var i=this.callbackCache[key].length-1;i>=0;i--){this.callbackCache[key][i](registryData);}
delete this.callbackCache[key]}
DataRegistry.prototype.storeData=function(plugin,type,subtype,data){if(this.data[plugin]===undefined){this.data[plugin]={}}
if(this.data[plugin][type]===undefined){this.data[plugin][type]={}}
var dataItem={timestamp:Date.now(),data:data}
this.data[plugin][type][subtype]=dataItem}
DataRegistry.prototype.clearCache=function(plugin,type){if(this.data[plugin]===undefined){return}
if(this.data[plugin][type]===undefined){return}
this.data[plugin][type]=undefined}
DataRegistry.prototype.getLocalizationString=function($formElement,plugin,key,callback){this.get($formElement,plugin,'localization',null,function(data){if(data[key]!==undefined){callback(data[key])
return}
callback(key)})}
DataRegistry.prototype.localizationUpdated=function(plugin,params){$.oc.builder.localizationInput.updatePluginInputs(plugin)
if(params===undefined||!params.suppressLanguageEditorUpdate){$.oc.builder.indexController.entityControllers.localization.languageUpdated(plugin)}
$.oc.builder.indexController.entityControllers.localization.updateOnScreenStrings(plugin)}
$.oc.builder.dataRegistry=new DataRegistry()}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.foundation.base,BaseProto=Base.prototype
var EntityBase=function(typeName,indexController){if(typeName===undefined){throw new Error('The Builder entity type name should be set in the base constructor call.')}
if(indexController===undefined){throw new Error('The Builder index controller should be set when creating an entity controller.')}
this.typeName=typeName
this.indexController=indexController
Base.call(this)}
EntityBase.prototype=Object.create(BaseProto)
EntityBase.prototype.constructor=EntityBase
EntityBase.prototype.registerHandlers=function(){}
EntityBase.prototype.invokeCommand=function(command,ev){if(/^cmd[a-zA-Z0-9]+$/.test(command)){if(this[command]!==undefined){this[command].apply(this,[ev])}
else{throw new Error('Unknown command: '+command)}}
else{throw new Error('Invalid command: '+command)}}
EntityBase.prototype.newTabId=function(){return this.typeName+Math.random()}
EntityBase.prototype.makeTabId=function(objectName){return this.typeName+'-'+objectName}
EntityBase.prototype.getMasterTabsActivePane=function(){return this.indexController.getMasterTabActivePane()}
EntityBase.prototype.getMasterTabsObject=function(){return this.indexController.masterTabsObj}
EntityBase.prototype.getSelectedPlugin=function(){var activeItem=$('#PluginList-pluginList-plugin-list > ul > li.active')
return activeItem.data('id')}
EntityBase.prototype.getIndexController=function(){return this.indexController}
EntityBase.prototype.updateMasterTabIdAndTitle=function($tabPane,responseData){var tabsObject=this.getMasterTabsObject()
tabsObject.updateIdentifier($tabPane,responseData.tabId)
tabsObject.updateTitle($tabPane,responseData.tabTitle)}
EntityBase.prototype.unhideFormDeleteButton=function($tabPane){$('[data-control=delete-button]',$tabPane).removeClass('hide')}
EntityBase.prototype.forceCloseTab=function($tabPane){$tabPane.trigger('close.oc.tab',[{force:true}])}
EntityBase.prototype.unmodifyTab=function($tabPane){this.indexController.unchangeTab($tabPane)}
$.oc.builder.entityControllers.base=EntityBase;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var Plugin=function(indexController){Base.call(this,'plugin',indexController)
this.popupZIndex=5050}
Plugin.prototype=Object.create(BaseProto)
Plugin.prototype.constructor=Plugin
Plugin.prototype.cmdMakePluginActive=function(ev){var $target=$(ev.currentTarget),selectedPluginCode=$target.data('pluginCode')
this.makePluginActive(selectedPluginCode)}
Plugin.prototype.cmdCreatePlugin=function(ev){var $target=$(ev.currentTarget)
$target.one('shown.oc.popup',this.proxy(this.onPluginPopupShown))
$target.popup({handler:'onPluginLoadPopup',zIndex:this.popupZIndex})}
Plugin.prototype.cmdApplyPluginSettings=function(ev){var $form=$(ev.currentTarget),self=this
$.oc.stripeLoadIndicator.show()
$form.request('onPluginSave').always($.oc.builder.indexController.hideStripeIndicatorProxy).done(function(data){$form.trigger('close.oc.popup')
self.applyPluginSettingsDone(data)})}
Plugin.prototype.cmdEditPluginSettings=function(ev){var $target=$(ev.currentTarget)
$target.one('shown.oc.popup',this.proxy(this.onPluginPopupShown))
$target.popup({handler:'onPluginLoadPopup',zIndex:this.popupZIndex,extraData:{pluginCode:$target.data('pluginCode')}})}
Plugin.prototype.onPluginPopupShown=function(ev,button,popup){$(popup).find('input[name=name]').focus()}
Plugin.prototype.applyPluginSettingsDone=function(data){if(data.responseData!==undefined&&data.responseData.isNewPlugin!==undefined){this.makePluginActive(data.responseData.pluginCode,true)}}
Plugin.prototype.makePluginActive=function(pluginCode,updatePluginList){var $form=$('#builder-plugin-selector-panel form').first()
$.oc.stripeLoadIndicator.show()
$form.request('onPluginSetActive',{data:{pluginCode:pluginCode,updatePluginList:(updatePluginList?1:0)}}).always($.oc.builder.indexController.hideStripeIndicatorProxy).done(this.proxy(this.makePluginActiveDone))}
Plugin.prototype.makePluginActiveDone=function(data){var pluginCode=data.responseData.pluginCode
$('#builder-plugin-selector-panel [data-control=filelist]').fileList('markActive',pluginCode)}
$.oc.builder.entityControllers.plugin=Plugin;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var DatabaseTable=function(indexController){Base.call(this,'databaseTable',indexController)}
DatabaseTable.prototype=Object.create(BaseProto)
DatabaseTable.prototype.constructor=DatabaseTable
DatabaseTable.prototype.cmdCreateTable=function(ev){var result=this.indexController.openOrLoadMasterTab($(ev.target),'onDatabaseTableCreateOrOpen',this.newTabId())
if(result!==false){result.done(this.proxy(this.onTableLoaded,this))}}
DatabaseTable.prototype.cmdOpenTable=function(ev){var table=$(ev.currentTarget).data('id'),result=this.indexController.openOrLoadMasterTab($(ev.target),'onDatabaseTableCreateOrOpen',this.makeTabId(table),{table_name:table})
if(result!==false){result.done(this.proxy(this.onTableLoaded,this))}}
DatabaseTable.prototype.cmdSaveTable=function(ev){var $target=$(ev.currentTarget)
if(!this.validateTable($target)){return}
var data={'columns':this.getTableData($target)}
$target.popup({extraData:data,handler:'onDatabaseTableValidateAndShowPopup'})}
DatabaseTable.prototype.cmdSaveMigration=function(ev){var $target=$(ev.currentTarget)
$.oc.stripeLoadIndicator.show()
$target.request('onDatabaseTableMigrationApply').always($.oc.builder.indexController.hideStripeIndicatorProxy).done(this.proxy(this.saveMigrationDone))}
DatabaseTable.prototype.cmdDeleteTable=function(ev){var $target=$(ev.currentTarget)
$.oc.confirm($target.data('confirm'),this.proxy(this.deleteConfirmed))}
DatabaseTable.prototype.cmdUnModifyForm=function(){var $masterTabPane=this.getMasterTabsActivePane()
this.unmodifyTab($masterTabPane)}
DatabaseTable.prototype.cmdAddIdColumn=function(ev){var $target=$(ev.currentTarget),added=this.addIdColumn($target)
if(!added){alert($target.closest('form').attr('data-lang-id-exists'))}}
DatabaseTable.prototype.cmdAddTimestamps=function(ev){var $target=$(ev.currentTarget),added=this.addTimeStampColumns($target,['created_at','updated_at'])
if(!added){alert($target.closest('form').attr('data-lang-timestamps-exist'))}}
DatabaseTable.prototype.cmdAddSoftDelete=function(ev){var $target=$(ev.currentTarget),added=this.addTimeStampColumns($target,['deleted_at'])
if(!added){alert($target.closest('form').attr('data-lang-soft-deleting-exist'))}}
DatabaseTable.prototype.onTableCellChanged=function(ev,column,value,rowIndex){var $target=$(ev.target)
if($target.data('alias')!='columns'){return}
if($target.closest('form').data('entity')!='database'){return}
var updatedRow={}
if(column=='auto_increment'&&value){updatedRow.unsigned=1
updatedRow.primary_key=1}
if(column=='unsigned'&&!value){updatedRow.auto_increment=0}
if(column=='primary_key'&&value){updatedRow.allow_null=0}
if(column=='allow_null'&&value){updatedRow.primary_key=0}
if(column=='primary_key'&&!value){updatedRow.auto_increment=0}
$target.table('setRowValues',rowIndex,updatedRow)}
DatabaseTable.prototype.onTableLoaded=function(){$(document).trigger('render')
var $masterTabPane=this.getMasterTabsActivePane(),$form=$masterTabPane.find('form'),$toolbar=$masterTabPane.find('div[data-control=table] div.toolbar'),$addIdButton=$('<a class="btn oc-icon-clock-o builder-custom-table-button" data-builder-command="databaseTable:cmdAddIdColumn"></a>'),$addTimestampsButton=$('<a class="btn oc-icon-clock-o builder-custom-table-button" data-builder-command="databaseTable:cmdAddTimestamps"></a>'),$addSoftDeleteButton=$('<a class="btn oc-icon-refresh builder-custom-table-button" data-builder-command="databaseTable:cmdAddSoftDelete"></a>')
$addIdButton.text($form.attr('data-lang-add-id'));$toolbar.append($addIdButton)
$addTimestampsButton.text($form.attr('data-lang-add-timestamps'));$toolbar.append($addTimestampsButton)
$addSoftDeleteButton.text($form.attr('data-lang-add-soft-delete'));$toolbar.append($addSoftDeleteButton)}
DatabaseTable.prototype.registerHandlers=function(){this.indexController.$masterTabs.on('oc.tableCellChanged',this.proxy(this.onTableCellChanged))}
DatabaseTable.prototype.validateTable=function($target){var tableObj=this.getTableControlObject($target)
tableObj.unfocusTable()
return tableObj.validate()}
DatabaseTable.prototype.getTableData=function($target){var tableObj=this.getTableControlObject($target)
return tableObj.dataSource.getAllData()}
DatabaseTable.prototype.getTableControlObject=function($target){var $form=$target.closest('form'),$table=$form.find('[data-control=table]'),tableObj=$table.data('oc.table')
if(!tableObj){throw new Error('Table object is not found on the database table tab')}
return tableObj}
DatabaseTable.prototype.saveMigrationDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
$('#builderTableMigrationPopup').trigger('close.oc.popup')
var $masterTabPane=this.getMasterTabsActivePane(),tabsObject=this.getMasterTabsObject()
if(data.builderResponseData.operation!='delete'){$masterTabPane.find('input[name=table_name]').val(data.builderResponseData.builderObjectName)
this.updateMasterTabIdAndTitle($masterTabPane,data.builderResponseData)
this.unhideFormDeleteButton($masterTabPane)
this.getTableList().fileList('markActive',data.builderResponseData.tabId)
this.getIndexController().unchangeTab($masterTabPane)
this.updateTable(data.builderResponseData)}
else{this.forceCloseTab($masterTabPane)}
$.oc.builder.dataRegistry.clearCache(data.builderResponseData.pluginCode,'model-columns')}
DatabaseTable.prototype.getTableList=function(){return $('#layout-side-panel form[data-content-id=database] [data-control=filelist]')}
DatabaseTable.prototype.deleteConfirmed=function(){var $masterTabPane=this.getMasterTabsActivePane()
$masterTabPane.find('form').popup({handler:'onDatabaseTableShowDeletePopup'})}
DatabaseTable.prototype.getColumnNames=function($target){var tableObj=this.getTableControlObject($target)
tableObj.unfocusTable()
var data=this.getTableData($target),result=[]
for(var index in data){if(data[index].name!==undefined){result.push($.trim(data[index].name))}}
return result}
DatabaseTable.prototype.addIdColumn=function($target){var existingColumns=this.getColumnNames($target),added=false
if(existingColumns.indexOf('id')===-1){var tableObj=this.getTableControlObject($target),currentData=this.getTableData($target),rowData={name:'id',type:'integer',unsigned:true,auto_increment:true,primary_key:true,}
if(currentData.length-1||currentData[0].name||currentData[0].type||currentData[0].length||currentData[0].unsigned||currentData[0].nullable||currentData[0].auto_increment||currentData[0].primary_key||currentData[0].default){tableObj.addRecord('bottom',true)}
tableObj.setRowValues(currentData.length-1,rowData)
tableObj.addRecord('bottom',false)
tableObj.deleteRecord()
added=true}
if(added){$target.trigger('change')}
return added}
DatabaseTable.prototype.addTimeStampColumns=function($target,columns)
{var existingColumns=this.getColumnNames($target),added=false
for(var index in columns){var column=columns[index]
if(existingColumns.indexOf(column)===-1){this.addTimeStampColumn($target,column)
added=true}}
if(added){$target.trigger('change')}
return added}
DatabaseTable.prototype.addTimeStampColumn=function($target,column){var tableObj=this.getTableControlObject($target),currentData=this.getTableData($target),rowData={name:column,type:'timestamp','default':null,allow_null:true}
tableObj.addRecord('bottom',true)
tableObj.setRowValues(currentData.length-1,rowData)
tableObj.addRecord('bottom',false)
tableObj.deleteRecord()}
DatabaseTable.prototype.updateTable=function(data){var tabsObject=this.getMasterTabsObject(),tabs=$('#builder-master-tabs').data('oc.tab'),tab=tabs.findByIdentifier(data.tabId)
tabsObject.updateTab(tab,data.tableName,data.tab)
this.onTableLoaded()}
$.oc.builder.entityControllers.databaseTable=DatabaseTable;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var Model=function(indexController){Base.call(this,'model',indexController)}
Model.prototype=Object.create(BaseProto)
Model.prototype.constructor=Model
Model.prototype.cmdCreateModel=function(ev){var $target=$(ev.currentTarget)
$target.one('shown.oc.popup',this.proxy(this.onModelPopupShown))
$target.popup({handler:'onModelLoadPopup'})}
Model.prototype.cmdApplyModelSettings=function(ev){var $form=$(ev.currentTarget),self=this
$.oc.stripeLoadIndicator.show()
$form.request('onModelSave').always($.oc.builder.indexController.hideStripeIndicatorProxy).done(function(data){$form.trigger('close.oc.popup')
self.applyModelSettingsDone(data)})}
Model.prototype.onModelPopupShown=function(ev,button,popup){$(popup).find('input[name=className]').focus()}
Model.prototype.applyModelSettingsDone=function(data){if(data.builderResponseData.registryData!==undefined){var registryData=data.builderResponseData.registryData
$.oc.builder.dataRegistry.set(registryData.pluginCode,'model-classes',null,registryData.models)}}
$.oc.builder.entityControllers.model=Model;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var ModelForm=function(indexController){Base.call(this,'modelForm',indexController)}
ModelForm.prototype=Object.create(BaseProto)
ModelForm.prototype.constructor=ModelForm
ModelForm.prototype.cmdCreateForm=function(ev){var $link=$(ev.currentTarget),data={model_class:$link.data('modelClass')}
this.indexController.openOrLoadMasterTab($link,'onModelFormCreateOrOpen',this.newTabId(),data)}
ModelForm.prototype.cmdSaveForm=function(ev){var $target=$(ev.currentTarget),$form=$target.closest('form'),$rootContainer=$('[data-root-control-wrapper] > [data-control-container]',$form),$inspectorContainer=$form.find('.inspector-container'),controls=$.oc.builder.formbuilder.domToPropertyJson.convert($rootContainer.get(0))
if(!$.oc.inspector.manager.applyValuesFromContainer($inspectorContainer)){return}
if(controls===false){$.oc.flashMsg({'text':$.oc.builder.formbuilder.domToPropertyJson.getLastError(),'class':'error','interval':5})
return}
var data={controls:controls}
$target.request('onModelFormSave',{data:data}).done(this.proxy(this.saveFormDone))}
ModelForm.prototype.cmdOpenForm=function(ev){var form=$(ev.currentTarget).data('form'),model=$(ev.currentTarget).data('modelClass')
this.indexController.openOrLoadMasterTab($(ev.target),'onModelFormCreateOrOpen',this.makeTabId(model+'-'+form),{file_name:form,model_class:model})}
ModelForm.prototype.cmdDeleteForm=function(ev){var $target=$(ev.currentTarget)
$.oc.confirm($target.data('confirm'),this.proxy(this.deleteConfirmed))}
ModelForm.prototype.cmdAddControl=function(ev){$.oc.builder.formbuilder.controlPalette.addControl(ev)}
ModelForm.prototype.cmdUndockControlPalette=function(ev){$.oc.builder.formbuilder.controlPalette.undockFromContainer(ev)}
ModelForm.prototype.cmdDockControlPalette=function(ev){$.oc.builder.formbuilder.controlPalette.dockToContainer(ev)}
ModelForm.prototype.cmdCloseControlPalette=function(ev){$.oc.builder.formbuilder.controlPalette.closeInContainer(ev)}
ModelForm.prototype.saveFormDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var $masterTabPane=this.getMasterTabsActivePane()
$masterTabPane.find('input[name=file_name]').val(data.builderResponseData.builderObjectName)
this.updateMasterTabIdAndTitle($masterTabPane,data.builderResponseData)
this.unhideFormDeleteButton($masterTabPane)
this.getModelList().fileList('markActive',data.builderResponseData.tabId)
this.getIndexController().unchangeTab($masterTabPane)
this.updateDataRegistry(data)}
ModelForm.prototype.updateDataRegistry=function(data){if(data.builderResponseData.registryData!==undefined){var registryData=data.builderResponseData.registryData
$.oc.builder.dataRegistry.set(registryData.pluginCode,'model-forms',registryData.modelClass,registryData.forms)}}
ModelForm.prototype.deleteConfirmed=function(){var $masterTabPane=this.getMasterTabsActivePane(),$form=$masterTabPane.find('form')
$.oc.stripeLoadIndicator.show()
$form.request('onModelFormDelete').always($.oc.builder.indexController.hideStripeIndicatorProxy).done(this.proxy(this.deleteDone))}
ModelForm.prototype.deleteDone=function(data){var $masterTabPane=this.getMasterTabsActivePane()
this.getIndexController().unchangeTab($masterTabPane)
this.forceCloseTab($masterTabPane)
this.updateDataRegistry(data)}
ModelForm.prototype.getModelList=function(){return $('#layout-side-panel form[data-content-id=models] [data-control=filelist]')}
$.oc.builder.entityControllers.modelForm=ModelForm;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var ModelList=function(indexController){this.cachedModelFieldsPromises={}
Base.call(this,'modelList',indexController)}
ModelList.prototype=Object.create(BaseProto)
ModelList.prototype.constructor=ModelList
ModelList.prototype.registerHandlers=function(){$(document).on('autocompleteitems.oc.table','form[data-sub-entity="model-list"] [data-control=table]',this.proxy(this.onAutocompleteItems))}
ModelList.prototype.cmdCreateList=function(ev){var $link=$(ev.currentTarget),data={model_class:$link.data('modelClass')}
var result=this.indexController.openOrLoadMasterTab($link,'onModelListCreateOrOpen',this.newTabId(),data)
if(result!==false){result.done(this.proxy(this.onListLoaded,this))}}
ModelList.prototype.cmdSaveList=function(ev){var $target=$(ev.currentTarget),$form=$target.closest('form')
if(!this.validateTable($target)){return}
$target.request('onModelListSave',{data:{columns:this.getTableData($target)}}).done(this.proxy(this.saveListDone))}
ModelList.prototype.cmdOpenList=function(ev){var list=$(ev.currentTarget).data('list'),model=$(ev.currentTarget).data('modelClass')
var result=this.indexController.openOrLoadMasterTab($(ev.target),'onModelListCreateOrOpen',this.makeTabId(model+'-'+list),{file_name:list,model_class:model})
if(result!==false){result.done(this.proxy(this.onListLoaded,this))}}
ModelList.prototype.cmdDeleteList=function(ev){var $target=$(ev.currentTarget)
$.oc.confirm($target.data('confirm'),this.proxy(this.deleteConfirmed))}
ModelList.prototype.cmdAddDatabaseColumns=function(ev){var $target=$(ev.currentTarget)
$.oc.stripeLoadIndicator.show()
$target.request('onModelListLoadDatabaseColumns').done(this.proxy(this.databaseColumnsLoaded)).always($.oc.builder.indexController.hideStripeIndicatorProxy)}
ModelList.prototype.saveListDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var $masterTabPane=this.getMasterTabsActivePane()
$masterTabPane.find('input[name=file_name]').val(data.builderResponseData.builderObjectName)
this.updateMasterTabIdAndTitle($masterTabPane,data.builderResponseData)
this.unhideFormDeleteButton($masterTabPane)
this.getModelList().fileList('markActive',data.builderResponseData.tabId)
this.getIndexController().unchangeTab($masterTabPane)
this.updateDataRegistry(data)}
ModelList.prototype.deleteConfirmed=function(){var $masterTabPane=this.getMasterTabsActivePane(),$form=$masterTabPane.find('form')
$.oc.stripeLoadIndicator.show()
$form.request('onModelListDelete').always($.oc.builder.indexController.hideStripeIndicatorProxy).done(this.proxy(this.deleteDone))}
ModelList.prototype.deleteDone=function(data){var $masterTabPane=this.getMasterTabsActivePane()
this.getIndexController().unchangeTab($masterTabPane)
this.forceCloseTab($masterTabPane)
this.updateDataRegistry(data)}
ModelList.prototype.getTableControlObject=function($target){var $form=$target.closest('form'),$table=$form.find('[data-control=table]'),tableObj=$table.data('oc.table')
if(!tableObj){throw new Error('Table object is not found on the model list tab')}
return tableObj}
ModelList.prototype.getModelList=function(){return $('#layout-side-panel form[data-content-id=models] [data-control=filelist]')}
ModelList.prototype.validateTable=function($target){var tableObj=this.getTableControlObject($target)
tableObj.unfocusTable()
return tableObj.validate()}
ModelList.prototype.getTableData=function($target){var tableObj=this.getTableControlObject($target)
return tableObj.dataSource.getAllData()}
ModelList.prototype.loadModelFields=function(table,callback){var $form=$(table).closest('form'),modelClass=$form.find('input[name=model_class]').val(),cachedFields=$form.data('oc.model-field-cache')
if(cachedFields!==undefined){callback(cachedFields)
return}
if(this.cachedModelFieldsPromises[modelClass]===undefined){this.cachedModelFieldsPromises[modelClass]=$form.request('onModelFormGetModelFields',{data:{'as_plain_list':1}})}
if(callback===undefined){return}
this.cachedModelFieldsPromises[modelClass].done(function(data){$form.data('oc.model-field-cache',data.responseData.options)
callback(data.responseData.options)})}
ModelList.prototype.updateDataRegistry=function(data){if(data.builderResponseData.registryData!==undefined){var registryData=data.builderResponseData.registryData
$.oc.builder.dataRegistry.set(registryData.pluginCode,'model-lists',registryData.modelClass,registryData.lists)
$.oc.builder.dataRegistry.clearCache(registryData.pluginCode,'plugin-lists')}}
ModelList.prototype.databaseColumnsLoaded=function(data){if(!$.isArray(data.responseData.columns)){alert('Invalid server response')}
var $masterTabPane=this.getMasterTabsActivePane(),$form=$masterTabPane.find('form'),existingColumns=this.getColumnNames($form),columnsAdded=false
for(var i in data.responseData.columns){var column=data.responseData.columns[i],type=this.mapType(column.type)
if($.inArray(column.name,existingColumns)!==-1){continue}
this.addColumn($form,column.name,type)
columnsAdded=true}
if(!columnsAdded){alert($form.attr('data-lang-all-database-columns-exist'))}
else{$form.trigger('change')}}
ModelList.prototype.mapType=function(type){switch(type){case'integer':return'number'
case'timestamp':return'datetime'
default:return'text'}}
ModelList.prototype.addColumn=function($target,column,type){var tableObj=this.getTableControlObject($target),currentData=this.getTableData($target),rowData={field:column,label:column,type:type}
tableObj.addRecord('bottom',true)
tableObj.setRowValues(currentData.length-1,rowData)
tableObj.addRecord('bottom',false)
tableObj.deleteRecord()}
ModelList.prototype.getColumnNames=function($target){var tableObj=this.getTableControlObject($target)
tableObj.unfocusTable()
var data=this.getTableData($target),result=[]
for(var index in data){if(data[index].field!==undefined){result.push($.trim(data[index].field))}}
return result}
ModelList.prototype.onAutocompleteItems=function(ev,data){if(data.columnConfiguration.fillFrom==='model-fields'){ev.preventDefault()
this.loadModelFields(ev.target,data.callback)
return false}}
ModelList.prototype.onListLoaded=function(){$(document).trigger('render')
var $masterTabPane=this.getMasterTabsActivePane(),$form=$masterTabPane.find('form'),$toolbar=$masterTabPane.find('div[data-control=table] div.toolbar'),$button=$('<a class="btn oc-icon-magic builder-custom-table-button" data-builder-command="modelList:cmdAddDatabaseColumns"></a>')
$button.text($form.attr('data-lang-add-database-columns'));$toolbar.append($button)}
$.oc.builder.entityControllers.modelList=ModelList;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var Permission=function(indexController){Base.call(this,'permissions',indexController)}
Permission.prototype=Object.create(BaseProto)
Permission.prototype.constructor=Permission
Permission.prototype.registerHandlers=function(){this.indexController.$masterTabs.on('oc.tableNewRow',this.proxy(this.onTableRowCreated))}
Permission.prototype.cmdOpenPermissions=function(ev){var currentPlugin=this.getSelectedPlugin()
if(!currentPlugin){alert('Please select a plugin first')
return}
this.indexController.openOrLoadMasterTab($(ev.target),'onPermissionsOpen',this.makeTabId(currentPlugin))}
Permission.prototype.cmdSavePermissions=function(ev){var $target=$(ev.currentTarget),$form=$target.closest('form')
if(!this.validateTable($target)){return}
$target.request('onPermissionsSave',{data:{permissions:this.getTableData($target)}}).done(this.proxy(this.savePermissionsDone))}
Permission.prototype.getTableControlObject=function($target){var $form=$target.closest('form'),$table=$form.find('[data-control=table]'),tableObj=$table.data('oc.table')
if(!tableObj){throw new Error('Table object is not found on permissions tab')}
return tableObj}
Permission.prototype.validateTable=function($target){var tableObj=this.getTableControlObject($target)
tableObj.unfocusTable()
return tableObj.validate()}
Permission.prototype.getTableData=function($target){var tableObj=this.getTableControlObject($target)
return tableObj.dataSource.getAllData()}
Permission.prototype.savePermissionsDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var $masterTabPane=this.getMasterTabsActivePane()
this.getIndexController().unchangeTab($masterTabPane)
$.oc.builder.dataRegistry.clearCache(data.builderResponseData.pluginCode,'permissions')}
Permission.prototype.onTableRowCreated=function(ev,recordData){var $target=$(ev.target)
if($target.data('alias')!='permissions'){return}
var $form=$target.closest('form')
if($form.data('entity')!='permissions'){return}
var pluginCode=$form.find('input[name=plugin_code]').val()
recordData.permission=pluginCode.toLowerCase()+'.';}
$.oc.builder.entityControllers.permission=Permission;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var Menus=function(indexController){Base.call(this,'menus',indexController)}
Menus.prototype=Object.create(BaseProto)
Menus.prototype.constructor=Menus
Menus.prototype.cmdOpenMenus=function(ev){var currentPlugin=this.getSelectedPlugin()
if(!currentPlugin){alert('Please select a plugin first')
return}
this.indexController.openOrLoadMasterTab($(ev.target),'onMenusOpen',this.makeTabId(currentPlugin))}
Menus.prototype.cmdSaveMenus=function(ev){var $target=$(ev.currentTarget),$form=$target.closest('form'),$inspectorContainer=$form.find('.inspector-container')
if(!$.oc.inspector.manager.applyValuesFromContainer($inspectorContainer)){return}
var menus=$.oc.builder.menubuilder.controller.getJson($form.get(0))
$target.request('onMenusSave',{data:{menus:menus}}).done(this.proxy(this.saveMenusDone))}
Menus.prototype.cmdAddMainMenuItem=function(ev){$.oc.builder.menubuilder.controller.addMainMenuItem(ev)}
Menus.prototype.cmdAddSideMenuItem=function(ev){$.oc.builder.menubuilder.controller.addSideMenuItem(ev)}
Menus.prototype.cmdDeleteMenuItem=function(ev){$.oc.builder.menubuilder.controller.deleteMenuItem(ev)}
Menus.prototype.saveMenusDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var $masterTabPane=this.getMasterTabsActivePane()
this.getIndexController().unchangeTab($masterTabPane)}
$.oc.builder.entityControllers.menus=Menus;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var Version=function(indexController){Base.call(this,'version',indexController)
this.hiddenHints={}}
Version.prototype=Object.create(BaseProto)
Version.prototype.constructor=Version
Version.prototype.cmdCreateVersion=function(ev){var $link=$(ev.currentTarget),versionType=$link.data('versionType')
this.indexController.openOrLoadMasterTab($link,'onVersionCreateOrOpen',this.newTabId(),{version_type:versionType})}
Version.prototype.cmdSaveVersion=function(ev){var $target=$(ev.currentTarget),$form=$target.closest('form')
$target.request('onVersionSave').done(this.proxy(this.saveVersionDone))}
Version.prototype.cmdOpenVersion=function(ev){var versionNumber=$(ev.currentTarget).data('id'),pluginCode=$(ev.currentTarget).data('pluginCode')
this.indexController.openOrLoadMasterTab($(ev.target),'onVersionCreateOrOpen',this.makeTabId(pluginCode+'-'+versionNumber),{original_version:versionNumber})}
Version.prototype.cmdDeleteVersion=function(ev){var $target=$(ev.currentTarget)
$.oc.confirm($target.data('confirm'),this.proxy(this.deleteConfirmed))}
Version.prototype.cmdApplyVersion=function(ev){var $target=$(ev.currentTarget),$pane=$target.closest('div.tab-pane'),self=this
this.showHintPopup($pane,'builder-version-apply',function(){$target.request('onVersionApply').done(self.proxy(self.applyVersionDone))})}
Version.prototype.cmdRollbackVersion=function(ev){var $target=$(ev.currentTarget),$pane=$target.closest('div.tab-pane'),self=this
this.showHintPopup($pane,'builder-version-rollback',function(){$target.request('onVersionRollback').done(self.proxy(self.rollbackVersionDone))})}
Version.prototype.saveVersionDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var $masterTabPane=this.getMasterTabsActivePane()
this.updateUiAfterSave($masterTabPane,data)
if(!data.builderResponseData.isApplied){this.showSavedNotAppliedHint($masterTabPane)}}
Version.prototype.showSavedNotAppliedHint=function($masterTabPane){this.showHintPopup($masterTabPane,'builder-version-save-unapplied')}
Version.prototype.showHintPopup=function($masterTabPane,code,callback){if(this.getDontShowHintAgain(code,$masterTabPane)){if(callback){callback.apply(this)}
return}
$masterTabPane.one('hide.oc.popup',this.proxy(this.onHintPopupHide))
if(callback){$masterTabPane.one('shown.oc.popup',function(ev,$element,$modal){$modal.find('form').one('submit',function(ev){callback.apply(this)
ev.preventDefault()
$(ev.target).trigger('close.oc.popup')
return false})})}
$masterTabPane.popup({content:this.getPopupContent($masterTabPane,code)})}
Version.prototype.onHintPopupHide=function(ev,$element,$modal){var cbValue=$modal.find('input[type=checkbox][name=dont_show_again]').is(':checked'),code=$modal.find('input[type=hidden][name=hint_code]').val()
$modal.find('form').off('submit')
if(!cbValue){return}
var $form=this.getMasterTabsActivePane().find('form[data-entity="versions"]')
$form.request('onHideBackendHint',{data:{name:code}})
this.setDontShowHintAgain(code)}
Version.prototype.setDontShowHintAgain=function(code){this.hiddenHints[code]=true}
Version.prototype.getDontShowHintAgain=function(code,$pane){if(this.hiddenHints[code]!==undefined){return this.hiddenHints[code]}
return $pane.find('input[type=hidden][data-hint-hidden="'+code+'"]').val()=="true"}
Version.prototype.getPopupContent=function($pane,code){var template=$pane.find('script[data-version-hint-template="'+code+'"]')
if(template.length===0){throw new Error('Version popup template not found: '+code)}
return template.html()}
Version.prototype.updateUiAfterSave=function($masterTabPane,data){$masterTabPane.find('input[name=original_version]').val(data.builderResponseData.savedVersion)
this.updateMasterTabIdAndTitle($masterTabPane,data.builderResponseData)
this.unhideFormDeleteButton($masterTabPane)
this.getVersionList().fileList('markActive',data.builderResponseData.tabId)
this.getIndexController().unchangeTab($masterTabPane)}
Version.prototype.deleteConfirmed=function(){var $masterTabPane=this.getMasterTabsActivePane(),$form=$masterTabPane.find('form')
$.oc.stripeLoadIndicator.show()
$form.request('onVersionDelete').always($.oc.builder.indexController.hideStripeIndicatorProxy).done(this.proxy(this.deleteDone))}
Version.prototype.deleteDone=function(){var $masterTabPane=this.getMasterTabsActivePane()
this.getIndexController().unchangeTab($masterTabPane)
this.forceCloseTab($masterTabPane)}
Version.prototype.applyVersionDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var $masterTabPane=this.getMasterTabsActivePane()
this.updateUiAfterSave($masterTabPane,data)
this.updateVersionsButtons()}
Version.prototype.rollbackVersionDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var $masterTabPane=this.getMasterTabsActivePane()
this.updateUiAfterSave($masterTabPane,data)
this.updateVersionsButtons()}
Version.prototype.getVersionList=function(){return $('#layout-side-panel form[data-content-id=version] [data-control=filelist]')}
Version.prototype.updateVersionsButtons=function(){var tabsObject=this.getMasterTabsObject(),$tabs=tabsObject.$tabsContainer.find('> li'),$versionList=this.getVersionList()
for(var i=$tabs.length-1;i>=0;i--){var $tab=$($tabs[i]),tabId=$tab.data('tabId')
if(!tabId||String(tabId).length==0){continue}
var $versionLi=$versionList.find('li[data-id="'+tabId+'"]')
if(!$versionLi.length){continue}
var isApplied=$versionLi.data('applied'),$pane=tabsObject.findPaneFromTab($tab)
if(isApplied){$pane.find('[data-builder-command="version:cmdApplyVersion"]').addClass('hide')
$pane.find('[data-builder-command="version:cmdRollbackVersion"]').removeClass('hide')}
else{$pane.find('[data-builder-command="version:cmdApplyVersion"]').removeClass('hide')
$pane.find('[data-builder-command="version:cmdRollbackVersion"]').addClass('hide')}}}
$.oc.builder.entityControllers.version=Version;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var Localization=function(indexController){Base.call(this,'localization',indexController)}
Localization.prototype=Object.create(BaseProto)
Localization.prototype.constructor=Localization
Localization.prototype.cmdCreateLanguage=function(ev){this.indexController.openOrLoadMasterTab($(ev.target),'onLanguageCreateOrOpen',this.newTabId())}
Localization.prototype.cmdOpenLanguage=function(ev){var language=$(ev.currentTarget).data('id'),pluginCode=$(ev.currentTarget).data('pluginCode')
this.indexController.openOrLoadMasterTab($(ev.target),'onLanguageCreateOrOpen',this.makeTabId(pluginCode+'-'+language),{original_language:language})}
Localization.prototype.cmdSaveLanguage=function(ev){var $target=$(ev.currentTarget),$form=$target.closest('form')
$target.request('onLanguageSave').done(this.proxy(this.saveLanguageDone))}
Localization.prototype.cmdDeleteLanguage=function(ev){var $target=$(ev.currentTarget)
$.oc.confirm($target.data('confirm'),this.proxy(this.deleteConfirmed))}
Localization.prototype.cmdCopyMissingStrings=function(ev){var $form=$(ev.currentTarget),language=$form.find('select[name=language]').val(),$masterTabPane=this.getMasterTabsActivePane()
$form.trigger('close.oc.popup')
$.oc.stripeLoadIndicator.show()
$masterTabPane.find('form').request('onLanguageCopyStringsFrom',{data:{copy_from:language}}).always($.oc.builder.indexController.hideStripeIndicatorProxy).done(this.proxy(this.copyStringsFromDone))}
Localization.prototype.languageUpdated=function(plugin){var languageForm=this.findDefaultLanguageForm(plugin)
if(!languageForm){return}
var $languageForm=$(languageForm)
if(!$languageForm.hasClass('oc-data-changed')){this.updateLanguageFromServer($languageForm)}
else{this.mergeLanguageFromServer($languageForm)}}
Localization.prototype.updateOnScreenStrings=function(plugin){var stringElements=document.body.querySelectorAll('span[data-localization-key][data-plugin="'+plugin+'"]')
$.oc.builder.dataRegistry.get($('#builder-plugin-selector-panel form'),plugin,'localization',null,function(data){for(var i=stringElements.length-1;i>=0;i--){var stringElement=stringElements[i],stringKey=stringElement.getAttribute('data-localization-key')
if(data[stringKey]!==undefined){stringElement.textContent=data[stringKey]}
else{stringElement.textContent=stringKey}}})}
Localization.prototype.saveLanguageDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var $masterTabPane=this.getMasterTabsActivePane()
$masterTabPane.find('input[name=original_language]').val(data.builderResponseData.language)
this.updateMasterTabIdAndTitle($masterTabPane,data.builderResponseData)
this.unhideFormDeleteButton($masterTabPane)
this.getLanguageList().fileList('markActive',data.builderResponseData.tabId)
this.getIndexController().unchangeTab($masterTabPane)
if(data.builderResponseData.registryData!==undefined){var registryData=data.builderResponseData.registryData
$.oc.builder.dataRegistry.set(registryData.pluginCode,'localization',null,registryData.strings,{suppressLanguageEditorUpdate:true})
$.oc.builder.dataRegistry.set(registryData.pluginCode,'localization','sections',registryData.sections)}}
Localization.prototype.getLanguageList=function(){return $('#layout-side-panel form[data-content-id=localization] [data-control=filelist]')}
Localization.prototype.getCodeEditor=function($tab){return $tab.find('div[data-field-name=strings] div[data-control=codeeditor]').data('oc.codeEditor').editor}
Localization.prototype.deleteConfirmed=function(){var $masterTabPane=this.getMasterTabsActivePane(),$form=$masterTabPane.find('form')
$.oc.stripeLoadIndicator.show()
$form.request('onLanguageDelete').always($.oc.builder.indexController.hideStripeIndicatorProxy).done(this.proxy(this.deleteDone))}
Localization.prototype.deleteDone=function(){var $masterTabPane=this.getMasterTabsActivePane()
this.getIndexController().unchangeTab($masterTabPane)
this.forceCloseTab($masterTabPane)}
Localization.prototype.copyStringsFromDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var responseData=data.builderResponseData,$masterTabPane=this.getMasterTabsActivePane(),$form=$masterTabPane.find('form'),codeEditor=this.getCodeEditor($masterTabPane),newStringMessage=$form.data('newStringMessage'),mismatchMessage=$form.data('structureMismatch')
codeEditor.getSession().setValue(responseData.strings)
var annotations=[]
for(var i=responseData.updatedLines.length-1;i>=0;i--){var line=responseData.updatedLines[i]
annotations.push({row:line,column:0,text:newStringMessage,type:'warning'})}
codeEditor.getSession().setAnnotations(annotations)
if(responseData.mismatch){$.oc.alert(mismatchMessage)}}
Localization.prototype.findDefaultLanguageForm=function(plugin){var forms=document.body.querySelectorAll('form[data-entity=localization]')
for(var i=forms.length-1;i>=0;i--){var form=forms[i],pluginInput=form.querySelector('input[name=plugin_code]'),languageInput=form.querySelector('input[name=original_language]')
if(!pluginInput||pluginInput.value!=plugin){continue}
if(!languageInput){continue}
if(form.getAttribute('data-default-language')==languageInput.value){return form}}
return null}
Localization.prototype.updateLanguageFromServer=function($languageForm){var self=this
$languageForm.request('onLanguageGetStrings').done(function(data){self.updateLanguageFromServerDone($languageForm,data)})}
Localization.prototype.updateLanguageFromServerDone=function($languageForm,data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var responseData=data.builderResponseData,$tabPane=$languageForm.closest('.tab-pane'),codeEditor=this.getCodeEditor($tabPane)
if(!responseData.strings){return}
codeEditor.getSession().setValue(responseData.strings)
this.unmodifyTab($tabPane)}
Localization.prototype.mergeLanguageFromServer=function($languageForm){var language=$languageForm.find('input[name=original_language]').val(),self=this
$languageForm.request('onLanguageCopyStringsFrom',{data:{copy_from:language}}).done(function(data){self.mergeLanguageFromServerDone($languageForm,data)})}
Localization.prototype.mergeLanguageFromServerDone=function($languageForm,data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var responseData=data.builderResponseData,$tabPane=$languageForm.closest('.tab-pane'),codeEditor=this.getCodeEditor($tabPane)
codeEditor.getSession().setValue(responseData.strings)
codeEditor.getSession().setAnnotations([])}
$.oc.builder.entityControllers.localization=Localization;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
if($.oc.builder.entityControllers===undefined)
$.oc.builder.entityControllers={}
var Base=$.oc.builder.entityControllers.base,BaseProto=Base.prototype
var Controller=function(indexController){Base.call(this,'controller',indexController)}
Controller.prototype=Object.create(BaseProto)
Controller.prototype.constructor=Controller
Controller.prototype.cmdCreateController=function(ev){var $form=$(ev.currentTarget),self=this,pluginCode=$form.data('pluginCode'),behaviorsSelected=$form.find('input[name="behaviors[]"]:checked').length,promise=null
if(behaviorsSelected){promise=this.indexController.openOrLoadMasterTab($form,'onControllerCreate',this.makeTabId(pluginCode+'-new-controller'),{})}
else{promise=$form.request('onControllerCreate')}
promise.done(function(data){$form.trigger('close.oc.popup')
self.updateDataRegistry(data)}).always($.oc.builder.indexController.hideStripeIndicatorProxy)}
Controller.prototype.cmdOpenController=function(ev){var controller=$(ev.currentTarget).data('id'),pluginCode=$(ev.currentTarget).data('pluginCode')
this.indexController.openOrLoadMasterTab($(ev.target),'onControllerOpen',this.makeTabId(pluginCode+'-'+controller),{controller:controller})}
Controller.prototype.cmdSaveController=function(ev){var $target=$(ev.currentTarget),$form=$target.closest('form'),$inspectorContainer=$form.find('.inspector-container')
if(!$.oc.inspector.manager.applyValuesFromContainer($inspectorContainer)){return}
$target.request('onControllerSave').done(this.proxy(this.saveControllerDone))}
Controller.prototype.saveControllerDone=function(data){if(data['builderResponseData']===undefined){throw new Error('Invalid response data')}
var $masterTabPane=this.getMasterTabsActivePane()
this.getIndexController().unchangeTab($masterTabPane)}
Controller.prototype.updateDataRegistry=function(data){if(data.builderResponseData.registryData!==undefined){var registryData=data.builderResponseData.registryData
$.oc.builder.dataRegistry.set(registryData.pluginCode,'controller-urls',null,registryData.urls)}}
Controller.prototype.getControllerList=function(){return $('#layout-side-panel form[data-content-id=controller] [data-control=filelist]')}
$.oc.builder.entityControllers.controller=Controller;}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
var Base=$.oc.foundation.base,BaseProto=Base.prototype
var Builder=function(){Base.call(this)
this.$masterTabs=null
this.masterTabsObj=null
this.hideStripeIndicatorProxy=null
this.entityControllers={}
this.init()}
Builder.prototype=Object.create(BaseProto)
Builder.prototype.constructor=Builder
Builder.prototype.dispose=function(){BaseProto.dispose.call(this)}
Builder.prototype.openOrLoadMasterTab=function($form,serverHandlerName,tabId,data){if(this.masterTabsObj.goTo(tabId))
return false
var requestData=data===undefined?{}:data
$.oc.stripeLoadIndicator.show()
var promise=$form.request(serverHandlerName,{data:requestData}).done(this.proxy(this.addMasterTab)).always(this.hideStripeIndicatorProxy)
return promise}
Builder.prototype.getMasterTabActivePane=function(){return this.$masterTabs.find('> .tab-content > .tab-pane.active')}
Builder.prototype.unchangeTab=function($pane){$pane.find('form').trigger('unchange.oc.changeMonitor')}
Builder.prototype.triggerCommand=function(command,ev){var commandParts=command.split(':')
if(commandParts.length===2){var entity=commandParts[0],commandToExecute=commandParts[1]
if(this.entityControllers[entity]===undefined){throw new Error('Unknown entity type: '+entity)}
this.entityControllers[entity].invokeCommand(commandToExecute,ev)}}
Builder.prototype.init=function(){this.$masterTabs=$('#builder-master-tabs')
this.$sidePanel=$('#builder-side-panel')
this.masterTabsObj=this.$masterTabs.data('oc.tab')
this.hideStripeIndicatorProxy=this.proxy(this.hideStripeIndicator)
new $.oc.tabFormExpandControls(this.$masterTabs)
this.createEntityControllers()
this.registerHandlers()}
Builder.prototype.createEntityControllers=function(){for(var controller in $.oc.builder.entityControllers){if(controller=="base"){continue}
this.entityControllers[controller]=new $.oc.builder.entityControllers[controller](this)}}
Builder.prototype.registerHandlers=function(){$(document).on('click','[data-builder-command]',this.proxy(this.onCommand))
$(document).on('submit','[data-builder-command]',this.proxy(this.onCommand))
this.$masterTabs.on('changed.oc.changeMonitor',this.proxy(this.onFormChanged))
this.$masterTabs.on('unchanged.oc.changeMonitor',this.proxy(this.onFormUnchanged))
this.$masterTabs.on('shown.bs.tab',this.proxy(this.onTabShown))
this.$masterTabs.on('afterAllClosed.oc.tab',this.proxy(this.onAllTabsClosed))
this.$masterTabs.on('closed.oc.tab',this.proxy(this.onTabClosed))
this.$masterTabs.on('autocompleteitems.oc.inspector',this.proxy(this.onDataRegistryItems))
this.$masterTabs.on('dropdownoptions.oc.inspector',this.proxy(this.onDataRegistryItems))
for(var controller in this.entityControllers){if(this.entityControllers[controller].registerHandlers!==undefined){this.entityControllers[controller].registerHandlers()}}}
Builder.prototype.hideStripeIndicator=function(){$.oc.stripeLoadIndicator.hide()}
Builder.prototype.addMasterTab=function(data){this.masterTabsObj.addTab(data.tabTitle,data.tab,data.tabId,'oc-'+data.tabIcon)
if(data.isNewRecord){var $masterTabPane=this.getMasterTabActivePane()
$masterTabPane.find('form').one('ready.oc.changeMonitor',this.proxy(this.onChangeMonitorReady))}}
Builder.prototype.updateModifiedCounter=function(){var counters={database:{menu:'database',count:0},models:{menu:'models',count:0},permissions:{menu:'permissions',count:0},menus:{menu:'menus',count:0},versions:{menu:'versions',count:0},localization:{menu:'localization',count:0},controller:{menu:'controllers',count:0}}
$('> div.tab-content > div.tab-pane[data-modified] > form',this.$masterTabs).each(function(){var entity=$(this).data('entity')
counters[entity].count++})
$.each(counters,function(type,data){$.oc.sideNav.setCounter('builder/'+data.menu,data.count);})}
Builder.prototype.getFormPluginCode=function(formElement){var $form=$(formElement).closest('form'),$input=$form.find('input[name="plugin_code"]'),code=$input.val()
if(!code){throw new Error('Plugin code input is not found in the form.')}
return code}
Builder.prototype.setPageTitle=function(title){$.oc.layout.setPageTitle(title.length?(title+' | '):title)}
Builder.prototype.getFileLists=function(){return $('[data-control=filelist]',this.$sidePanel)}
Builder.prototype.dataToInspectorArray=function(data){var result=[]
for(var key in data){var item={title:data[key],value:key}
result.push(item)}
return result}
Builder.prototype.onCommand=function(ev){if(ev.currentTarget.tagName=='FORM'&&ev.type=='click'){return}
var command=$(ev.currentTarget).data('builderCommand')
this.triggerCommand(command,ev)
var $target=$(ev.currentTarget)
if(ev.currentTarget.tagName==='A'&&$target.attr('role')=='menuitem'&&$target.attr('href')=='javascript:;'){return}
ev.preventDefault()
return false}
Builder.prototype.onFormChanged=function(ev){$('.form-tabless-fields',ev.target).trigger('modified.oc.tab')
this.updateModifiedCounter()}
Builder.prototype.onFormUnchanged=function(ev){$('.form-tabless-fields',ev.target).trigger('unmodified.oc.tab')
this.updateModifiedCounter()}
Builder.prototype.onTabShown=function(ev){var $tabControl=$(ev.target).closest('[data-control=tab]')
if($tabControl.attr('id')!=this.$masterTabs.attr('id')){return}
var dataId=$(ev.target).closest('li').attr('data-tab-id'),title=$(ev.target).attr('title')
if(title){this.setPageTitle(title)}
this.getFileLists().fileList('markActive',dataId)
$(window).trigger('resize')}
Builder.prototype.onAllTabsClosed=function(ev){this.setPageTitle('')
this.getFileLists().fileList('markActive',null)}
Builder.prototype.onTabClosed=function(ev,tab,pane){$(pane).find('form').off('ready.oc.changeMonitor',this.proxy(this.onChangeMonitorReady))
this.updateModifiedCounter()}
Builder.prototype.onChangeMonitorReady=function(ev){$(ev.target).trigger('change')}
Builder.prototype.onDataRegistryItems=function(ev,data){var self=this
if(data.propertyDefinition.fillFrom=='model-classes'||data.propertyDefinition.fillFrom=='model-forms'||data.propertyDefinition.fillFrom=='model-lists'||data.propertyDefinition.fillFrom=='controller-urls'||data.propertyDefinition.fillFrom=='model-columns'||data.propertyDefinition.fillFrom=='plugin-lists'||data.propertyDefinition.fillFrom=='permissions'){ev.preventDefault()
var subtype=null,subtypeProperty=data.propertyDefinition.subtypeFrom
if(subtypeProperty!==undefined){subtype=data.values[subtypeProperty]}
$.oc.builder.dataRegistry.get($(ev.target),this.getFormPluginCode(ev.target),data.propertyDefinition.fillFrom,subtype,function(response){data.callback({options:self.dataToInspectorArray(response)})})}}
$(document).ready(function(){$.oc.builder.indexController=new Builder()})}(window.jQuery);+function($){"use strict";if($.oc.builder===undefined)
$.oc.builder={}
var Base=$.oc.foundation.base,BaseProto=Base.prototype
var LocalizationInput=function(input,form,options){this.input=input
this.form=form
this.options=$.extend({},LocalizationInput.DEFAULTS,options)
this.disposed=false
this.initialized=false
this.newStringPopupMarkup=null
Base.call(this)
this.init()}
LocalizationInput.prototype=Object.create(BaseProto)
LocalizationInput.prototype.constructor=LocalizationInput
LocalizationInput.prototype.dispose=function(){this.unregisterHandlers()
this.form=null
this.options.beforePopupShowCallback=null
this.options.afterPopupHideCallback=null
this.options=null
this.disposed=true
this.newStringPopupMarkup=null
if(this.initialized){$(this.input).autocomplete('destroy')}
$(this.input).removeData('localization-input')
this.input=null
BaseProto.dispose.call(this)}
LocalizationInput.prototype.init=function(){if(!this.options.plugin){throw new Error('The options.plugin value should be set in the localization input object.')}
var $input=$(this.input)
$input.data('localization-input',this)
$input.attr('data-builder-localization-input','true')
$input.attr('data-builder-localization-plugin',this.options.plugin)
this.getContainer().addClass('localization-input-container')
this.registerHandlers()
this.loadDataAndBuild()}
LocalizationInput.prototype.buildAddLink=function(){var $container=this.getContainer()
if($container.find('a.localization-trigger').length>0){return}
var trigger=document.createElement('a')
trigger.setAttribute('class','oc-icon-plus localization-trigger')
trigger.setAttribute('href','#')
var pos=$container.position()
$(trigger).css({top:pos.top+4,right:7})
$container.append(trigger)}
LocalizationInput.prototype.loadDataAndBuild=function(){this.showLoadingIndicator()
var result=$.oc.builder.dataRegistry.get(this.form,this.options.plugin,'localization',null,this.proxy(this.dataLoaded)),self=this
if(result){result.always(function(){self.hideLoadingIndicator()})}}
LocalizationInput.prototype.reload=function(){$.oc.builder.dataRegistry.get(this.form,this.options.plugin,'localization',null,this.proxy(this.dataLoaded))}
LocalizationInput.prototype.dataLoaded=function(data){if(this.disposed){return}
var $input=$(this.input),autocomplete=$input.data('autocomplete')
if(!autocomplete){this.hideLoadingIndicator()
var autocompleteOptions={source:this.preprocessData(data),matchWidth:true}
autocompleteOptions=$.extend(autocompleteOptions,this.options.autocompleteOptions)
$(this.input).autocomplete(autocompleteOptions)
this.initialized=true}
else{autocomplete.source=this.preprocessData(data)}}
LocalizationInput.prototype.preprocessData=function(data){var dataClone=$.extend({},data)
for(var key in dataClone){dataClone[key]=key+' - '+dataClone[key]}
return dataClone}
LocalizationInput.prototype.getContainer=function(){return $(this.input).closest('.autocomplete-container')}
LocalizationInput.prototype.showLoadingIndicator=function(){var $container=this.getContainer()
$container.addClass('loading-indicator-container size-small')
$container.loadIndicator()}
LocalizationInput.prototype.hideLoadingIndicator=function(){var $container=this.getContainer()
$container.loadIndicator('hide')
$container.loadIndicator('destroy')
$container.removeClass('loading-indicator-container')}
LocalizationInput.prototype.loadAndShowPopup=function(){if(this.newStringPopupMarkup===null){$.oc.stripeLoadIndicator.show()
$(this.input).request('onLanguageLoadAddStringForm').done(this.proxy(this.popupMarkupLoaded)).always(function(){$.oc.stripeLoadIndicator.hide()})}
else{this.showPopup()}}
LocalizationInput.prototype.popupMarkupLoaded=function(responseData){this.newStringPopupMarkup=responseData.markup
this.showPopup()}
LocalizationInput.prototype.showPopup=function(){var $input=$(this.input)
$input.popup({content:this.newStringPopupMarkup})
var $content=$input.data('oc.popup').$content,$keyInput=$content.find('#language_string_key')
$.oc.builder.dataRegistry.get(this.form,this.options.plugin,'localization','sections',function(data){$keyInput.autocomplete({source:data,matchWidth:true})})
$content.find('form').on('submit',this.proxy(this.onSubmitPopupForm))}
LocalizationInput.prototype.stringCreated=function(data){if(data.localizationData===undefined||data.registryData===undefined){throw new Error('Invalid server response.')}
var $input=$(this.input)
$input.val(data.localizationData.key)
$.oc.builder.dataRegistry.set(this.options.plugin,'localization',null,data.registryData.strings)
$.oc.builder.dataRegistry.set(this.options.plugin,'localization','sections',data.registryData.sections)
$input.data('oc.popup').hide()
$input.trigger('change')}
LocalizationInput.prototype.onSubmitPopupForm=function(ev){var $form=$(ev.target)
$.oc.stripeLoadIndicator.show()
$form.request('onLanguageCreateString',{data:{plugin_code:this.options.plugin}}).done(this.proxy(this.stringCreated)).always(function(){$.oc.stripeLoadIndicator.hide()})
ev.preventDefault()
return false}
LocalizationInput.prototype.onPopupHidden=function(ev,link,popup){$(popup).find('#language_string_key').autocomplete('destroy')
$(popup).find('form').on('submit',this.proxy(this.onSubmitPopupForm))
if(this.options.afterPopupHideCallback){this.options.afterPopupHideCallback()}}
LocalizationInput.updatePluginInputs=function(plugin){var inputs=document.body.querySelectorAll('input[data-builder-localization-input][data-builder-localization-plugin="'+plugin+'"]')
for(var i=inputs.length-1;i>=0;i--){$(inputs[i]).data('localization-input').reload()}}
LocalizationInput.prototype.unregisterHandlers=function(){this.input.removeEventListener('focus',this.proxy(this.onInputFocus))
this.getContainer().off('click','a.localization-trigger',this.proxy(this.onTriggerClick))
$(this.input).off('hidden.oc.popup',this.proxy(this.onPopupHidden))}
LocalizationInput.prototype.registerHandlers=function(){this.input.addEventListener('focus',this.proxy(this.onInputFocus))
this.getContainer().on('click','a.localization-trigger',this.proxy(this.onTriggerClick))
$(this.input).on('hidden.oc.popup',this.proxy(this.onPopupHidden))}
LocalizationInput.prototype.onInputFocus=function(){this.buildAddLink()}
LocalizationInput.prototype.onTriggerClick=function(ev){if(this.options.beforePopupShowCallback){this.options.beforePopupShowCallback()}
this.loadAndShowPopup()
ev.preventDefault()
return false}
LocalizationInput.DEFAULTS={plugin:null,autocompleteOptions:{},beforePopupShowCallback:null,afterPopupHideCallback:null}
$.oc.builder.localizationInput=LocalizationInput}(window.jQuery);+function($){"use strict";var Base=$.oc.inspector.propertyEditors.string,BaseProto=Base.prototype
var LocalizationEditor=function(inspector,propertyDefinition,containerCell,group){this.localizationInput=null
Base.call(this,inspector,propertyDefinition,containerCell,group)}
LocalizationEditor.prototype=Object.create(BaseProto)
LocalizationEditor.prototype.constructor=Base
LocalizationEditor.prototype.dispose=function(){this.removeLocalizationInput()
BaseProto.dispose.call(this)}
LocalizationEditor.prototype.build=function(){var container=document.createElement('div'),editor=document.createElement('input'),placeholder=this.propertyDefinition.placeholder!==undefined?this.propertyDefinition.placeholder:'',value=this.inspector.getPropertyValue(this.propertyDefinition.property)
editor.setAttribute('type','text')
editor.setAttribute('class','string-editor')
editor.setAttribute('placeholder',placeholder)
container.setAttribute('class','autocomplete-container')
if(value===undefined){value=this.propertyDefinition.default}
if(value===undefined){value=''}
editor.value=value
$.oc.foundation.element.addClass(this.containerCell,'text autocomplete')
container.appendChild(editor)
this.containerCell.appendChild(container)
this.buildLocalizationEditor()}
LocalizationEditor.prototype.buildLocalizationEditor=function(){this.localizationInput=new $.oc.builder.localizationInput(this.getInput(),this.getForm(),{plugin:this.getPluginCode(),beforePopupShowCallback:this.proxy(this.onPopupShown,this),afterPopupHideCallback:this.proxy(this.onPopupHidden,this)})}
LocalizationEditor.prototype.removeLocalizationInput=function(){this.localizationInput.dispose()
this.localizationInput=null}
LocalizationEditor.prototype.supportsExternalParameterEditor=function(){return false}
LocalizationEditor.prototype.registerHandlers=function(){BaseProto.registerHandlers.call(this)
$(this.getInput()).on('change',this.proxy(this.onInputKeyUp))}
LocalizationEditor.prototype.unregisterHandlers=function(){BaseProto.unregisterHandlers.call(this)
$(this.getInput()).off('change',this.proxy(this.onInputKeyUp))}
LocalizationEditor.prototype.getForm=function(){var inspectableElement=this.getRootSurface().getInspectableElement()
if(!inspectableElement){throw new Error('Cannot determine inspectable element in the Builder localization editor.')}
return $(inspectableElement).closest('form')}
LocalizationEditor.prototype.getPluginCode=function(){var $form=this.getForm(),$input=$form.find('input[name=plugin_code]')
if(!$input.length){throw new Error('The input "plugin_code" should be defined in the form in order to use the localization Inspector editor.')}
return $input.val()}
LocalizationEditor.prototype.onPopupShown=function(){this.getRootSurface().popupDisplayed()}
LocalizationEditor.prototype.onPopupHidden=function(){this.getRootSurface().popupHidden()}
$.oc.inspector.propertyEditors.builderLocalization=LocalizationEditor}(window.jQuery);+function($){"use strict";if($.oc.table===undefined)
throw new Error("The $.oc.table namespace is not defined. Make sure that the table.js script is loaded.");if($.oc.table.processor===undefined)
throw new Error("The $.oc.table.processor namespace is not defined. Make sure that the table.processor.base.js script is loaded.");var Base=$.oc.table.processor.string,BaseProto=Base.prototype
var LocalizationProcessor=function(tableObj,columnName,columnConfiguration){this.localizationInput=null
this.popupDisplayed=false
Base.call(this,tableObj,columnName,columnConfiguration)}
LocalizationProcessor.prototype=Object.create(BaseProto)
LocalizationProcessor.prototype.constructor=LocalizationProcessor
LocalizationProcessor.prototype.dispose=function(){this.removeLocalizationInput()
BaseProto.dispose.call(this)}
LocalizationProcessor.prototype.onUnfocus=function(){if(!this.activeCell||this.popupDisplayed)
return
this.removeLocalizationInput()
BaseProto.onUnfocus.call(this)}
LocalizationProcessor.prototype.onBeforePopupShow=function(){this.popupDisplayed=true}
LocalizationProcessor.prototype.onAfterPopupHide=function(){this.popupDisplayed=false}
LocalizationProcessor.prototype.renderCell=function(value,cellContentContainer){BaseProto.renderCell.call(this,value,cellContentContainer)}
LocalizationProcessor.prototype.buildEditor=function(cellElement,cellContentContainer,isClick){BaseProto.buildEditor.call(this,cellElement,cellContentContainer,isClick)
$.oc.foundation.element.addClass(cellContentContainer,'autocomplete-container')
this.buildLocalizationEditor()}
LocalizationProcessor.prototype.buildLocalizationEditor=function(){var input=this.getInput()
this.localizationInput=new $.oc.builder.localizationInput(input,$(input),{plugin:this.getPluginCode(input),beforePopupShowCallback:$.proxy(this.onBeforePopupShow,this),afterPopupHideCallback:$.proxy(this.onAfterPopupHide,this),autocompleteOptions:{menu:'<ul class="autocomplete dropdown-menu table-widget-autocomplete localization"></ul>',bodyContainer:true}})}
LocalizationProcessor.prototype.getInput=function(){if(!this.activeCell){return null}
return this.activeCell.querySelector('.string-input')}
LocalizationProcessor.prototype.getPluginCode=function(input){var $form=$(input).closest('form'),$input=$form.find('input[name=plugin_code]')
if(!$input.length){throw new Error('The input "plugin_code" should be defined in the form in order to use the localization table processor.')}
return $input.val()}
LocalizationProcessor.prototype.removeLocalizationInput=function(){if(!this.localizationInput){return}
this.localizationInput.dispose()
this.localizationInput=null}
$.oc.table.processor.builderLocalization=LocalizationProcessor;}(window.jQuery);
