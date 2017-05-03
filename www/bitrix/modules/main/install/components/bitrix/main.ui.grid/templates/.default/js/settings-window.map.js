{"version":3,"file":"settings-window.min.js","sources":["settings-window.js"],"names":["BX","namespace","Grid","SettingsWindow","parent","this","settingsButton","applyBottom","items","popup","sourceContent","lastColumns","init","prototype","bind","getContainer","proxy","_onContainerClick","destroy","unbind","getPopup","close","event","hasClass","target","settings","get","_onSettingsButtonClick","show","getSourceContent","Utils","getByClass","getPopupItems","popupContainer","contentContainer","getColumns","columns","checkbox","forEach","current","getByTag","checked","push","data","restoreColumns","getParam","name","input","label","defaultColumn","default","value","util","htmlspecialchars","html","restoreLastColumns","indexOf","getBySelector","saveColumns","callback","tableFade","getUserOptions","setColumns","delegate","setColumnsNames","getColumnNames","reloadTable","resetSettings","button","getActionsPanel","confirmDialog","CONFIRM","CONFIRM_MESSAGE","arParams","CONFIRM_RESET_MESSAGE","addClass","buttonNode","removeClass","reset","popupWindow","_onColumnClick","column","currentTarget","preventDefault","stopPropagation","focus","htmlspecialcharsback","_onColumnKeydown","code","self","tmpDiv","create","innerHTML","innerText","titleBar","firstChild","PopupWindow","getContainerId","autoHide","overlay","width","closeIcon","closeByEsc","contentNoPaddings","events","onPopupClose","buttons","PopupWindowButtonLink","text","id","className","click","PopupWindowButton","setContent"],"mappings":"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"}