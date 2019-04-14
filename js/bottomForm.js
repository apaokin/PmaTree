function renderForm(id){
  var bottomForm;
  var data;
  if(updated() && attrs['id'] == id ){
    data = attrs;
  }
  else{
    if(id== 'new')
    {
      if(attrs.parent_id){
        data = {
                "type": type_maps.indexOf('implementation'),
                "parents_ids": [attrs.parent_id],
                "id": 'new'
              }
      }
      else{
        data = {
                "type": type_maps.indexOf('without_page'),
              	"parents_ids": [],
                "id": 'new'
                }
    }
    }
    else{
      data = pmas.find(function(elem){
              return elem['id'] == id;
            });
    }
  }

  $("#pma-tree-bottom").alpaca('destroy');
	$("#pma-tree-bottom").alpaca({
			"data": data,
			"schema": {
					"type": "object",
					"properties":{
						"ru_name":{
						},
            "id":{
              // type: "hidden"
            },
						"en_name":{
						},
            "ru_short":{
            },
            "en_short":{
						},
            "perform_delete":{
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
                  "delete":{
                    "title": "<?php echo $this->msg('pmatree-actions-delete')?>",
                    "click": function() {
                      bottomForm.getControlByPath('perform_delete').setValue('true');
                      this.submit();
                     }
                  },
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
                         bottomForm.getControlByPath('perform_delete').setValue('false');
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
          "perform_delete":{
            type: 'hidden'
          },
          "ru_name":{
						label: "<?php echo $this->msg('pmatree-ru_name')?>",
						helper: "<?php echo $this->msg('pmatree-ru_name-helper')?>",
            "readonly": "<?php echo $rights?>",
            validator: function(callback){
              if(bottomForm.getControlByPath('ru_name').getValue() == '' && bottomForm.getControlByPath('en_name').getValue() == ''){
                callback({
                  status: false,
                  message: "<?php echo $this->msg('pmatree-error-empty')?>"
                });
                return;
              }
              callback({
                status: true
              });
            }
					},
					"en_name":{
						label: "<?php echo $this->msg('pmatree-en_name')?>",
						helper: "<?php echo $this->msg('pmatree-en_name-helper')?>",
            "readonly": "<?php echo $rights?>",
            validator: function(callback){
              if(bottomForm.getControlByPath('ru_name').getValue() == '' && bottomForm.getControlByPath('en_name').getValue() == ''){
                callback({
                  status: false,
                  message: "<?php echo $this->msg('pmatree-error-empty')?>"
                });
                return;
              }
              callback({
                status: true
              });
            }
					},
          "ru_short":{
            label: "<?php echo $this->msg('pmatree-ru_short')?>",
            "readonly": "<?php echo $rights?>",
          },
          "en_short":{
            label: "<?php echo $this->msg('pmatree-en_short')?>",
            "readonly": "<?php echo $rights?>",
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
            "readonly": "<?php echo $rights?>",
            // validate: false,
            validator: function(callback){
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
function get_sub_array(array, field, searched_value)
{
  sub_array = [];
  array.forEach(function(element) {
    if ( element[field] == searched_value )
      sub_array.push(element);
  });
  return sub_array;
}
var rendered_pmas = [];
function render_element(pma,level,parent = null){
	pma['ru_name'] = pma['ru_name'].replace(/_/g,' ')
	pma['en_name'] = pma['en_name'].replace(/_/g,' ')
  rendered_pmas.push( {id: pma['id'],text: '='.repeat(level) + pma['ru_name'] + '|' + pma['en_name']});
  if(pma['parents_ids'] )
	{
    if(typeof(pma['parents_ids']) == 'string')
      pma['parents_ids'] = pma['parents_ids'].split(',');
	}
  else{
    pma['parents_ids'] = [];
  }
  if(pma['childs_ids']){
		if(typeof(pma['childs_ids']) == 'string')
	    pma['childs_ids'] = pma['childs_ids'].split(',');
    pma['childs_ids'].forEach(function(child_id) {
      render_element(find_or_raise_exception(pmas, 'id',child_id),level + 1,pma);
    });
  }
  else{
    pma['childs_ids'] = [];
  }
}

function find_children_with_id(elem, id){
  var found = null;
  elem['childs_ids'].forEach(function(child_id){
    var pma = pmas.find(function(el){
        return el.id == child_id;
      });
    if(pma.id == id) {
      found = pma;
      return;
    }
    else{
      if(!found)
        found = find_children_with_id(pma,id);
    }
  });
  return found;
}

function find_parent(elem, func_bool){
  var found = null;
  if(func_bool(elem)){
    return elem;
  }
  elem['parents_ids'].forEach(function(parent_id){
    var pma = pmas.find(function(el){
        return el.id == parent_id;
      });
      if(!found)
      {
        found = find_parent(pma,func_bool);
        return;
      }
  });
  return found;
}



function find_or_raise_exception(array, field, searched_value)
{
   var element = array.find(function(element) {
      return searched_value == element[field];
   });
   if(!element)
     throw new Error('no value with this field');
   return element;
}
