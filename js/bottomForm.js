function renderForm(id){
  var bottomForm;
  var data;
  if(id== 'new')
  {
    data = {}
  }
  else{

  }
  $("#pma-tree-bottom").alpaca('destroy');
	$("#pma-tree-bottom").alpaca({
			"data": pmas.find(function(elem){
				return elem['id'] == id;
			}),
			"schema": {
					"type": "object",
					"properties":{
						"ru_name":{
							required: true
						},
            "id":{
              // type: "hidden"
            },
						"en_name":{
							required: true
						},
            "type":
            {
              enum: [0,1,2,3,4,5]
            },
						"parents_ids":{
              enum: rendered_pmas.map(function(e){ return e.id;})
						},
            "hidden_parents_ids":{
            }

					}
			},
			"options": {
        "form": {
              "attributes": {
                  "method": "post",
                  "action": window.location.href.replace(/edit/,'update')
              },
              "buttons": {
                  "submit": {
                      "title": "<?php echo $this->msg('pmatree-actions-save')?>",
                      "click": function() {
                         this.validate(true);
                         this.refreshValidationState(true);
                         if (!this.isValid(true)) {
                             empty_preview();
                             return;
                         }
                         var new_ids = bottomForm.getControlByPath('parents_ids').getValue().map(function(elem){
                           return elem.value;
                         }).join(',');
                         bottomForm.getControlByPath('hidden_parents_ids').setValue(new_ids);
                         console.log(this.getValue());
                         this.submit();
                         return;
                      }
                  }
          }
      },
        "hideInitValidationError":true,
				"fields":{
          "hidden_parents_ids":{
            type: 'hidden'
          },
          "ru_name":{
						label: "<?php echo $this->msg('pmatree-ru_name')?>",
						helper: "<?php echo $this->msg('pmatree-ru_name-helper')?>",
					},
					"en_name":{
						label: "<?php echo $this->msg('pmatree-en_name')?>",
						helper: "<?php echo $this->msg('pmatree-en_name-helper')?>",
					},
          "id":{
            type: 'hidden'
          },
					"parents_ids":{
						label: "<?php echo $this->msg('pmatree-parents')?>",
            hideNone: true,
						optionLabels: rendered_pmas.map(function(e){ return e.text;}),
						multiple: true,
						"sort": false,
            "onFieldChange": function(e) {
              bottomForm.getControlByPath('type').getControlEl().trigger('change');
            }
					},
          "type":
          {
            hideNone: true,
            label: "<?php echo $this->msg('pmatree-type')?>",
            optionLabels: type_maps,
            "sort": false,
            // validate: false,
            validator: function(callback){
              var order = ['problem','method','algorithm'];
              if(!bottomForm.getControlByPath('parents_ids').getValue().length && this.getValue() != type_maps.indexOf('without_page')){
                callback({status:false,
                          message: "<?php echo $this->msg('pmatree-error-null_not_without_page')?>"
                        });
                return;
              }
              var errors = false;
              var value = this.getValue();
               bottomForm.getControlByPath('parents_ids').getValue().forEach(function(idcon){
                  var pma = pmas.find(function(elem){
                      				return elem['id'] == idcon.value;
                      			});
                  if( value == type_maps.indexOf('without_page') && type_maps[pma.type] != 'without_page'){
                    callback({status:false,
                              message: "<?php echo $this->msg('pmatree-error-not_without_page_parent')?>"
                            });
                    errors = true;
                    return;
                  }
                  var current_pma = pmas.find(function(elem){return elem.id == id;});
                  var current_parent = pmas.find(function(elem){return elem.id == idcon.value;});
                  var parent = find_parent(current_parent,function(parent){
                    return (order.indexOf(type_maps[parent.type]) != -1 && order.indexOf(type_maps[current_pma.type]) != -1  && order.indexOf(type_maps[parent.type]) >
                        order.indexOf(type_maps[current_pma.type]))
                      });
                  if(parent){
                    callback({status:false,
                              message: "<?php echo $this->msg('pmatree-error-order')?>"
                            });
                    errors = true;
                    return;
                  }
                  var source_error;
                  if(source_error = find_children_with_id(current_pma,idcon.value)) {
                    callback({status:false,
                              message: "<?php echo $this->msg('pmatree-error-cycle')?>"
                            });
                    errors = true;
                    return;
                  }
               });
              if(!errors)
                callback({status:true});

            }
          }
				}
			},
			"postRender": function(control) {
        control.getControlByPath('parents_ids').getControlEl().select2();
        control.getControlByPath('type').getControlEl().select2();
        control.getControlByPath('type').getControlEl().trigger("change");
        bottomForm = $("#pma-tree-bottom").alpaca('get');
			}
	});
}
