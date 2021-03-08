/**
 * Javascript Library for Ajax Front-end and Back-end
 *
 * @filesource js/common.js
 * @link http://www.kotchasan.com/
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
var loader,
  modal = null;

function send(target, query, callback, wait, c) {
  var req = new GAjax();
  req.initLoading(wait || 'wait', false, c);
  req.send(target, query, function(xhr) {
    if (callback) {
      callback.call(this, xhr);
    }
  });
}
var hideModal = function() {
  if (modal != null) {
    modal.hide();
  }
};

function showModal(src, qstr, doClose, className) {
  send(src, qstr, function(xhr) {
    var ds = xhr.responseText.toJSON();
    var detail = '';
    if (ds) {
      if (ds.alert) {
        alert(ds.alert);
      } else if (ds.detail) {
        detail = decodeURIComponent(ds.detail);
      } else if (ds.modal) {
        detail = ds.modal;
      }
    } else {
      detail = xhr.responseText;
    }
    if (detail != '') {
      modal = new GModal({
        onclose: doClose
      }).show(detail, className);
      detail.evalScript();
    }
  });
}

function defaultSubmit(ds) {
  var _alert = '',
    _input = false,
    _url = false,
    _location = false,
    t,
    el,
    remove = /remove([0-9]{0,})/;
  for (var prop in ds) {
    var val = ds[prop];
    if (prop == 'error') {
      _alert = eval(val);
    } else if (prop == 'debug') {
      console.log(val);
    } else if (prop == 'alert') {
      _alert = val;
    } else if (prop == 'message') {
      document.body.msgBox(trans(val));
    } else if (prop == 'warning') {
      document.body.msgBox(trans(val), 'warning');
    } else if (prop == 'tip') {
      document.body.msgBox(trans(val), 'tip', false);
    } else if (prop == 'modal') {
      if (val == 'close') {
        if (modal) {
          modal.hide();
        }
      } else {
        if (!modal) {
          modal = new GModal();
        }
        modal.show(val);
        val.evalScript();
      }
    } else if (prop == 'elem') {
      el = $E(val);
      if (el) {
        if (ds.class) {
          el.className = ds.class;
        }
        if (ds.title) {
          el.title = ds.title;
        }
      }
    } else if (prop == 'location') {
      _location = val;
    } else if (prop == 'url') {
      _url = val;
      _location = val;
    } else if (prop == 'open') {
      window.setTimeout(function() {
        window.open(val.replace(/&amp;/g, '&'));
      }, 1);
    } else if (prop == 'tab') {
      initWriteTab('accordient_menu', val);
    } else if (prop == 'valid') {
      if ($E(val)) {
        $G(val).valid();
      }
    } else if (remove.test(prop)) {
      if ($E(val)) {
        $G(val).fadeOut(function() {
          $G(val).remove();
        });
      }
    } else if ($E(prop)) {
      $G(prop).setValue(decodeURIComponent(val).replace(/\%/g, '&#37;'));
    } else if ($E(prop.replace('ret_', ''))) {
      el = $G(prop.replace('ret_', ''));
      if (el.display) {
        el = el.display;
      }
      if (val == '') {
        el.valid();
      } else {
        if (val == 'Please fill in' || val == 'Please select' || val == 'Please browse file' || val == 'already exist' || val == 'Please select at least one item' || val == 'Invalid data') {
          var label = el.findLabel();
          if (label) {
            t = label.innerHTML.strip_tags();
          } else {
            if (typeof el.placeholder != 'undefined') {
              t = el.placeholder.strip_tags();
            } else {
              t = '';
            }
            if (t == '') {
              t = el.title.strip_tags();
            }
          }
          if (t != '') {
            if (val == 'already exist') {
              val = t + ' ' + trans(val);
            } else if (val == 'Please select at least one item') {
              val = PLEASE_SELECT_AT_LEAST_ONE_ITEM.replace('XXX', t)
            } else if (val == 'Invalid data') {
              val = INVALID_DATA.replace('XXX', t)
            } else {
              val = trans(val) + ' ' + t;
            }
          } else {
            val = trans(val);
          }
        } else if (val == 'this') {
          t = '';
          if (typeof el.title != 'undefined') {
            t = el.title.strip_tags();
          }
          if (t == '' && typeof el.placeholder != 'undefined') {
            t = el.placeholder.strip_tags();
          }
          val = t;
        }
        if (_input != el) {
          el.invalid(val);
        }
        if (_alert == '') {
          _alert = val;
          _input = el;
        }
      }
    }
  }
  if (_alert != '') {
    alert(_alert);
  }
  if (_input) {
    _input.focus();
    var tag = _input.tagName.toLowerCase();
    if (tag != 'select') {
      _input.highlight();
    }
    if (tag == 'input') {
      var type = _input.get('type').toLowerCase();
      if (type == 'text' || type == 'password') {
        _input.select();
      }
    }
  }
  if (_location) {
    if (_location == 'reload') {
      if (loader) {
        loader.reload();
      } else {
        window.location.reload();
      }
    } else if (_location == 'back') {
      if (loader) {
        loader.back();
      } else {
        window.history.go(-1);
      }
    } else if (loader && _location != _url) {
      loader.location(_location);
    } else {
      window.location = _location.replace(/&amp;/g, '&');
    }
  }
}

function doFormSubmit(xhr) {
  var datas = xhr.responseText.toJSON();
  if (datas) {
    defaultSubmit(datas);
  } else if (xhr.responseText != '') {
    console.log(xhr.responseText);
  }
}

function initWriteTab(id, sel) {
  function _doclick(sel) {
    forEach($E(id).getElementsByTagName('a'), function() {
      var a = this.id.replace('tab_', '');
      if ($E(a)) {
        this.className = a == sel ? 'select' : '';
        $E(a).style.display = a == sel ? 'block' : 'none';
      }
    });
    $E('tab').value = sel;
  }
  forEach($G(id).elems('a'), function() {
    if ($E(this.id.replace('tab_', ''))) {
      callClick(this, function() {
        _doclick(this.id.replace('tab_', ''));
        return false;
      });
    }
  });
  _doclick(sel);
}
var dataTableActionCallback = function(xhr) {
  var el,
    prop,
    val,
    ds = xhr.responseText.toJSON();
  if (ds) {
    for (prop in ds) {
      val = ds[prop];
      if (prop == 'location') {
        if (val == 'reload') {
          if (loader) {
            loader.reload();
          } else {
            window.location.reload();
          }
        } else {
          window.location = val;
        }
      } else if (prop == 'open') {
        window.setTimeout(function() {
          window.open(val.replace(/&amp;/g, '&'));
        }, 1);
      } else if (prop == 'remove') {
        if ($E(val)) {
          $G(val).remove();
        }
      } else if (prop == 'alert') {
        alert(val);
      } else if (prop == 'elem') {
        el = $E(val);
        if (el) {
          el.className = ds.class;
          if (ds.title) {
            el.title = ds.title;
          }
        }
      } else if (prop == 'modal') {
        if (val == 'close') {
          if (modal) {
            modal.hide();
          }
        } else {
          modal = new GModal().show(val);
          val.evalScript();
        }
      } else if ($E(prop)) {
        $G(prop).setValue(val);
      }
    }
  } else if (xhr.responseText != '') {
    console.log(xhr.responseText);
  }
};

function checkEmail() {
  var value = this.value;
  var ids = this.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  if (value == '') {
    this.invalid(this.title);
  } else if (/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/.test(value)) {
    return 'value=' + encodeURIComponent(value) + id;
  } else {
    this.invalid(this.title);
  }
}

function checkUsername() {
  var patt = /[a-zA-Z0-9@\.\-_]+/;
  var value = this.value;
  var ids = this.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  if (value == '') {
    this.invalid(this.title);
  } else if (patt.test(value)) {
    return 'value=' + encodeURIComponent(value) + id;
  } else {
    this.invalid(this.title);
  }
}

function checkPassword() {
  var ids = this.id.split('_');
  var id = '&id=' + floatval($E(ids[0] + '_id').value);
  var Password = $E(ids[0] + '_password');
  var Repassword = $E(ids[0] + '_repassword');
  if (Password.value == '' && Repassword.value == '') {
    if (id == 0) {
      this.Validator.invalid(this.Validator.title);
    } else {
      this.Validator.reset();
    }
    this.Validator.reset();
  } else if (Password.value == Repassword.value) {
    Password.Validator.valid();
    Repassword.Validator.valid();
  } else {
    this.Validator.invalid(this.Validator.title);
  }
}

function checkIdcard() {
  var value = this.value,
    ids = this.id.split('_'),
    id = '&id=' + floatval($E(ids[0] + '_id').value);
  if (value.length == 0) {
    this.reset();
  } else if (value.length < 9) {
    this.invalid(this.title);
  } else {
    return 'value=' + encodeURIComponent(value) + '&id=' + id;
  }
}

function initMailserver() {
  var doChanged = function() {
    var a = this.value.toInt();
    $E('email_SMTPSecure').disabled = a == 0;
    $E('email_Username').disabled = a == 0;
    $E('email_Password').disabled = a == 0;
  };
  var el = $G('email_SMTPAuth');
  el.addEvent('change', doChanged);
  doChanged.call(el);
}

function replaceURL(key, value, url) {
  var q,
    prop,
    new_url = new Object(),
    qs = Array();
  url = url || window.location.toString();
  var urls = url.replace(/\#/g, '&')
    .replace(/\?/g, '&')
    .split('&');
  var l = urls.length;
  if (l > 1) {
    for (var n = 1; n < l; n++) {
      if (urls[n] != 'action=login' && urls[n] != 'action=logout') {
        q = urls[n].split('=');
        if (q.length == 2) {
          new_url[q[0]] = q[1];
        }
      }
    }
  }
  new_url[key] = value;
  for (prop in new_url) {
    if (new_url[prop]) {
      qs.push(prop + '=' + new_url[prop]);
    } else {
      qs.push(prop);
    }
  }
  return urls[0] + '?' + qs.join('&');
}

function initSystem() {
  new Clock('local_time');
  new Clock('server_time');
}

function selectMenu(module) {
  forEach(document.querySelectorAll('#topmenu > ul > li'), function() {
    if ($G(this).hasClass(module)) {
      this.addClass('select');
    } else {
      this.removeClass('select');
    }
  });
  forEach(document.querySelectorAll('.sidemenu > ul > li'), function() {
    if ($G(this).hasClass(module)) {
      this.addClass('select');
    } else {
      this.removeClass('select');
    }
  });
}

function loadJavascript(id, src) {
  var js,
    fjs = document.getElementsByTagName('script')[0];
  if (document.getElementById(id)) {
    return;
  }
  js = document.createElement('script');
  js.id = id;
  js.src = src;
  fjs.parentNode.insertBefore(js, fjs);
}

function initEditInplace(id, model, addbtn) {
  var patt = /list_([a-z]+)_([0-9]+)(_([0-9]+))?/;
  var o = {
    onSave: function(v, editor) {
      var req = new GAjax({
        asynchronous: false
      });
      req.initLoading(editor, false);
      req.send(
        'index.php/' + model,
        'action=' + this.id + '&value=' + encodeURIComponent(v)
      );
      ds = req.responseText.toJSON();
      if (ds) {
        if (ds.alert) {
          alert(ds.alert);
        }
        if (ds.editId) {
          $E(ds.editId).innerHTML = ds.edit;
        }
        return true;
      } else if (req.responseText != '') {
        alert(req.responseText);
      }
      return false;
    }
  };

  function _doAction(c) {
    var q = '',
      hs = patt.exec(this.id);
    if (hs[1] == 'add') {
      q = 'action=' + this.id;
    } else if (
      hs[1] == 'delete' &&
      confirm(trans('You want to XXX ?').replace(/XXX/, trans('delete')))
    ) {
      q = 'action=' + this.id;
    } else if (hs[1] == 'color') {
      q = 'action=' + this.id + '&value=' + encodeURIComponent(c);
    } else if (hs[1] == 'published') {
      q =
        'action=' +
        this.id +
        '&value=' +
        this.className.replace('icon-published', '');
    } else if (hs[1] == 'status') {
      q = 'action=' + this.id + '&value=' + this.value;
    }
    if (q != '') {
      send(
        'index.php/' + model,
        q,
        function(xhr) {
          var ds = xhr.responseText.toJSON();
          if (ds) {
            if (ds.data) {
              $G(id).appendChild(ds.data.toDOM());
              _doInitEditInplaceMethod(ds.newId);
              $E(ds.newId.replace('list_', 'list_name_')).focus();
            } else if (ds.del) {
              $G(ds.del).remove();
            } else if (ds.editId) {
              hs = patt.exec(ds.editId);
              if (hs[1] == 'color') {
                $E(ds.editId).title =
                  trans('change color') + ' (' + ds.edit + ')';
                $E(ds.editId).style.color = ds.edit;
              } else if (hs[1] == 'published') {
                $E(ds.editId).className = 'icon-published' + ds.edit;
                $E(ds.editId).title = ds.edit == 1 ? DISABLE : ENABLE;
              }
            }
            if (ds.alert) {
              alert(ds.alert);
            }
          } else if (xhr.responseText != '') {
            alert(xhr.responseText);
          }
        },
        this
      );
    }
  }

  function _initOrder() {
    new GDragDrop(id, {
      dragClass: 'icon-move',
      endDrag: function() {
        var trs = new Array();
        forEach($G(id).elems('li'), function() {
          if (this.id) {
            trs.push(this.id);
          }
        });
        if (trs.length > 1) {
          send(
            'index.php/' + model,
            'action=move&value=' + trs.join(',').replace(/list_/g, ''),
            doFormSubmit
          );
        }
      }
    });
  }

  function _doInitEditInplaceMethod(src) {
    var loading = true,
      move = false;
    forEach($G(src).elems('*'), function() {
      var hs = patt.exec(this.id);
      if (hs) {
        if ($G(this).hasClass('editinplace')) {
          new EditInPlace(this, o);
        } else if (hs[1] == 'published') {
          callClick(this, _doAction);
          this.title = this.className == 'icon-published1' ? DISABLE : ENABLE;
        } else if (hs[1] == 'color') {
          var t = this.title;
          this.title = trans('change color') + ' (' + t + ')';
          new GDDColor(this, function(c) {
            $E(this.input.id).style.color = c;
            if (!loading) {
              _doAction.call(this.input, c);
            }
          }).setColor(t);
        } else if (hs[1] == 'order') {
          move = true;
        } else {
          callClick(this, _doAction);
        }
      }
    });
    if (move) {
      _initOrder();
    }
    loading = false;
  }
  callClick(addbtn, _doAction);
  _doInitEditInplaceMethod(id);
}

function initCopyToClipboard(id) {
  forEach($E(id).querySelectorAll('.icon-copy'), function() {
    callClick(this, function() {
      copyToClipboard(this.title);
      document.body.msgBox(trans('successfully copied to clipboard'));
      return false;
    });
  });
}

function initFirstRowNumberOnly(tr) {
  forEach($G(tr).elems('input'), function(item, index) {
    if (index == 0) {
      new GMask(item, function() {
        return /^[0-9]+$/.test(this.value);
      });
    }
  });
}

function initPageWrite() {
  $G('write_language').addEvent('change', function() {
    loader.location('index.php?module=write&language=' + this.value + '&src=' + $E('write_src').value);
  });
}

function initEditProfile(prefix) {
  prefix += prefix == '' ? '' : '_';
  $G(prefix + 'country').addEvent('change', function(evt) {
    var self = this;
    self.addClass('wait');
    new GAjax().send(WEB_URL + 'index.php/index/model/province/toJSON', 'country=' + this.selectedIndex, function(xhr) {
      self.removeClass('wait');
      var items = xhr.responseText.toJSON(),
        province = $E(prefix + 'province');
      if (items && province) {
        province.setDatalist(items['province']);
      }
    });
  });
}

var createLikeButton;

function initWeb(module) {
  module = module ? module + '/' : '';
  if (navigator.userAgent.indexOf('MSIE') > -1) {
    document.body.addClass('ie');
  }
  forEach(document.body.elems('nav'), function() {
    if ($G(this).hasClass('topmenu sidemenu slidemenu gddmenu')) {
      new GDDMenu(this);
    }
  });
  var _scrolltop = 0;
  var toTop = 100;
  if ($E('toTop')) {
    if ($G('toTop').hasClass('fixed_top')) {
      document.addEvent('toTopChange', function() {
        if (document.body.hasClass('toTop')) {
          var _toTop = $G('toTop').copy();
          _toTop.zIndex = -1;
          _toTop.id = 'toTop_temp';
          _toTop.setStyle('opacity', 0);
          _toTop.removeClass('fixed_top');
          $G('toTop').after(_toTop);
        } else if ($E('toTop_temp')) {
          $G('toTop_temp').remove();
        }
      });
    }
    toTop = $E('toTop').getTop();
    document.addEvent('scroll', function() {
      var c = this.viewport.getscrollTop() > toTop;
      if (_scrolltop != c) {
        _scrolltop = c;
        if ($E('body')) {
          if (c) {
            $E('body').className = 'toTop';
          } else {
            $E('body').className = '';
          }
        } else {
          if (c) {
            document.body.addClass('toTop');
          } else {
            document.body.removeClass('toTop');
          }
        }
        document.callEvent('toTopChange');
      }
    });
  }
  var fontSize = floatval(Cookie.get(module + 'fontSize'));
  document.body.set('data-fontSize', floatval(document.body.getStyle('fontSize')));
  if (fontSize > 5) {
    document.body.setStyle('fontSize', fontSize + 'px');
  }
  forEach(document.body.elems('a'), function() {
    if (/^lang_([a-z]{2,2})$/.test(this.id)) {
      callClick(this, function(e) {
        var hs = /^lang_([a-z]{2,2})$/.exec(this.id);
        window.location = replaceURL('lang', hs[1]);
        GEvent.stop(e);
      });
    } else if (/font_size\s(small|normal|large)/.test(this.className)) {
      callClick(this, function(e) {
        fontSize = floatval(document.body.getStyle('fontSize'));
        var hs = /font_size\s(small|normal|large)/.exec(this.className);
        if (hs[1] == 'small') {
          fontSize = Math.max(6, fontSize - 2);
        } else if (hs[1] == 'large') {
          fontSize = Math.min(24, fontSize + 2);
        } else {
          fontSize = document.body.get('data-fontSize');
        }
        document.body.setStyle('fontSize', fontSize + 'px');
        Cookie.set(module + 'fontSize', fontSize);
        GEvent.stop(e);
      });
    }
  });
  loader = new GLoader(
    WEB_URL + module + 'loader.php/index/controller/loader/index',
    function(xhr) {
      var scroll_to = 'scroll-to',
        content = $G('content'),
        datas = xhr.responseText.toJSON();
      document.body.onkeydown = null;
      if (datas) {
        for (var prop in datas) {
          var value = datas[prop];
          if (prop == 'detail') {
            content.setHTML(value);
            loader.init(content);
            content.replaceClass('loading', 'animation');
            content.Ready(function() {
              $K.init(content);
              value.evalScript();
            });
          } else if (prop == 'title') {
            document.title = value.unentityify();
          } else if (prop == 'menu') {
            selectMenu(value);
          } else if (prop == 'to') {
            scroll_to = value;
          } else if ($E(prop)) {
            $E(prop).innerHTML = value;
          }
        }
        if (Object.isFunction(createLikeButton)) {
          createLikeButton();
        }
        if ($E(scroll_to)) {
          window.scrollTo(0, $G(scroll_to).getTop() - 10);
        }
      } else if (xhr.responseText != '') {
        console.log(xhr.responseText);
      }
    },
    null,
    function() {
      $G('content').replaceClass('animation', 'loading');
      return true;
    }
  );
  loader.initLoading('wait', false);
  loader.init(document);
  $K.init(document.body);
}
if (navigator.userAgent.match(/(iPhone|iPod|iPad)/i)) {
  document.addEventListener('touchstart', function() {}, false);
}

function barcodeEnabled(inputs) {
  $G(window).Ready(function() {
    forEach(inputs, function(item) {
      $G(item).addEvent('keydown', function(e) {
        if (GEvent.keyCode(e) == 13) {
          GEvent.stop(e);
          return false;
        }
      });
    });
  });
}

function initCalendarRange(minDate, maxDate, minChanged) {
  if ($E(minDate) && $E(maxDate)) {
    $G(minDate).addEvent('change', function() {
      if (this.value != '') {
        $E(maxDate).min = this.value;
        if (Object.isFunction(minChanged)) {
          minChanged.call($E(minDate), $E(maxDate));
        }
      }
    });
    $G(maxDate).addEvent('change', function() {
      if (this.value != '') {
        $E(minDate).max = this.value;
      }
    });
  }
}

function initCompany() {
  var doChanged = function() {
    var t = $E('company_type').value;
    $E('tax_id').disabled = t == 0;
    $E('idcard').disabled = t == 1;
    $E('tax_id').parentNode.parentNode.className = t == 0 ? 'hidden' : 'item';
    $E('idcard').parentNode.parentNode.className = t == 1 ? 'hidden' : 'item';
  };
  initEditProfile('company');
  $G('company_type').addEvent('change', doChanged);
  doChanged();
}
