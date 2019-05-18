$(document).ready(function() {

  var els = <?php echo $changes_json?>;
  var user_rights = <?php echo $rights?>;
  var lang = <?php echo $lang?>;

//===========================
//======== FUNCTIONS ========
//===========================

  function getStatus(stat) {
    if (lang == 0) {
      if (stat == 'waiting')
        return 'Ожидает подтверждения';
      else if (stat == 'confirmed')
        return 'Утверждено';
      else
        return 'Отклонено';
    }
    else {
      if (stat == 'waiting')
        return 'Waiting for confirmation';
      else if (stat == 'confirmed')
        return 'Confirmed';
      else
        return 'Denied';
    }
  }

  function getAction(act) {
    if (lang == 0) {
      if (act == 0)
        return 'Добавление';
      else
        return 'Удаление';
    }
    else {
      if (act == 0)
        return 'Adding';
      else
        return 'Deleting';
    }
  }

  if (user_rights) {
    if (lang == 0) {
      $('#change_author').text("Автор");
      $('#type_of_change').text("Действие");
      $('#changed_article').text("Измененная статья");
      $('#changed_parent').text("Измененный родитель");
    }
    else {
      $('#change_author').text("Author");
      $('#type_of_change').text("Action");
      $('#changed_article').text("Changed article");
      $('#changed_parent').text("Changed parent");
    }
  }
  else {
    if (lang == 0) {
      $('#type_of_change').text("Действие");
      $('#changed_article').text("Измененная статья");
      $('#changed_parent').text("Измененный родитель");
      $('#change_state').text("Статус изменения");
    }
    else {
      $('#type_of_change').text("Action");
      $('#changed_article').text("Changed article");
      $('#changed_parent').text("Changed parent");
      $('#change_state').text("Change state");
    }
  }

//=========================
//======== ACTIONS ========
//=========================

  document.change_log.setAttribute('action', window.location.href.replace(/journal/,'log'));

  $('<link/>', {
    rel: 'stylesheet',
    href: 'extensions/PmaTree/js/log_table_style.css'
  }).appendTo($('html > head'));

  $('#confirm_button').text("<?php echo $this->msg('pmatree-journal-confirm')?>");
  $('#deny_button').text("<?php echo $this->msg('pmatree-journal-deny')?>");
  $('#cancel_button').text("<?php echo $this->msg('pmatree-journal-cancel')?>");

  els.forEach(function(element) {
    var rendered_article = '';
    var rendered_parent = '';
    if (lang == 0) {
      rendered_article = element['child_ru_name'].replace(/_/, ' ');
      rendered_parent = element['parent_ru_name'].replace(/_/, ' ');
    }
    else {
      rendered_article = element['child_en_name'].replace(/_/g, ' ');
      rendered_parent = element['parent_en_name'].replace(/_/g, ' ');
    }

    var row = $('<tr/>', {
      value: element['id'],
      class: 'log_table_row'
    });

    if (user_rights) {
      $('<td/>', {
        class: 'log_table_cell_user',
        text: element['user_name']
      }).appendTo(row);
    }

    $('<td/>', {
      class: 'log_table_cell_action',
      text: getAction(element['type'])
    }).appendTo(row);

    $('<td/>', {
      class: 'log_table_cell_article',
      text: rendered_article
    }).appendTo(row);

    $('<td/>', {
      class: 'log_table_cell_parent',
      text: rendered_parent
    }).appendTo(row);

    if (!user_rights) {
      $('<td/>', {
        class: 'log_table_cell_state',
        text: getStatus(element['status'])
      }).appendTo(row);
    }

    row.appendTo('#new_log_table > tbody');
  });

  $('.log_table_row').click(function() {
    $(this).toggleClass('log_table_row_clicked');
  })

  $('#confirm_button').click(function() {
    var str = '';
    $('.log_table_row_clicked').each(function() {
      str = str + $(this).attr('value') + ',';
    });
    str = str.substr(0, str.length - 1);
    $('#log_form_selection').val(str);
    $('#log_form_type_of_change').val('confirm');
    document.change_log.submit();
  })

  $('#deny_button').click(function() {
    var str = '';
    $('.log_table_row_clicked').each(function() {
      str = str + $(this).attr('value') + ',';
    });
    str = str.substr(0, str.length - 1);
    $('#log_form_selection').val(str);
    $('#log_form_type_of_change').val('deny');
    document.change_log.submit();
  })

  $('#cancel_button').click(function() {
    var str = '';
    $('.log_table_row_clicked').each(function() {
      str = str + $(this).attr('value') + ',';
    });
    str = str.substr(0, str.length - 1);
    $('#log_form_selection').val(str);
    $('#log_form_type_of_change').val('cancel');
    document.change_log.submit();
  })

});
