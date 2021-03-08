/**
 * Javascript Library for Kotchasan Framework
 *
 * @filesource js/gajax.js
 * @link http://www.kotchasan.com/
 * @copyright 2018 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
window.$K = (function() {
  "use strict";
  var domloaded = false;
  var $K = {
    emptyFunction: function() {},
    resultFunction: function() {
      return true;
    },
    isMobile: function() {
      return (typeof window.orientation !== "undefined") || (navigator.userAgent.indexOf('IEMobile') !== -1);
    },
    init: function(element) {
      forEach(element.querySelectorAll("input,textarea,select"), function(elem) {
        var tagName = $G(elem).tagName.toLowerCase(),
          type = elem.get("type"),
          type = type ? type.toLowerCase() : "";
        if (elem.initObj !== true) {
          if (tagName == 'select') {
            elem.initObj = true;
            if (elem.get("checkbox") !== null) {
              new GSelect(elem);
            }
          } else if (tagName == "textarea" || (type !== "hidden" && type !== "radio" && type !== "checkbox" && type !== "button" && type !== "submit")) {
            var obj = new Object();
            obj.tagName = tagName;
            obj.type = type;
            elem.initObj = true;
            obj.title = elem.title;
            obj.required = elem.get("required");
            obj.disabled = elem.get("disabled") !== null;
            obj.maxlength = floatval(elem.get("maxlength"));
            obj.pattern = elem.get("pattern");
            if (obj.pattern !== null) {
              obj.pattern = new RegExp("^(?:" + obj.pattern + ")$");
              elem.setAttribute("pattern", "(.*){0,}");
            }
            obj.dataset = elem.dataset;
            if (typeof obj.dataset == "undefined") {
              obj.dataset = {};
              forEach(elem.attributes, function() {
                var hs = this.name.match(/^data\-(.+)/);
                if (hs) {
                  obj.dataset[hs[0]] = this.value;
                }
              });
            }
            if (obj.tagName == "textarea") {
              if (obj.maxlength > 0 || obj.required || obj.pattern) {
                var _docheck = function() {
                  if (this.value == "" && obj.required !== null) {
                    this.addClass("required");
                    this.invalid(obj.title !== "" ? obj.title : trans("Please fill in") + (this.placeholder == "" ? "" : " " + this.placeholder));
                  } else if (this.value != "" && obj.pattern && !obj.pattern.test(this.value)) {
                    this.invalid(obj.title !== "" ? obj.title : trans("Invalid data"));
                  } else if (obj.required !== null || obj.pattern) {
                    this.reset();
                  }
                };
                elem.srcObj = obj;
                elem.addEvent("keyup", _docheck);
                elem.addEvent("change", _docheck);
              }
            } else if (obj.tagName == "input") {
              var c = elem.hasClass("currency number integer color inputgroup");
              if (c !== false) {
                obj.type = c;
              }
              if (elem.min) {
                obj.min = elem.min;
              }
              if (elem.max) {
                obj.max = elem.max;
              }
              var autofocus = elem.get("autofocus");
              var text = elem;
              if (obj.type == "date") {
                new GCalendar(elem);
              } else if (obj.type == "number" || obj.type == "integer" || obj.type == "tel" || obj.type == "email" || obj.type == "url" || obj.type == "color" || obj.type == "currency" || obj.type == "time") {
                var o = {
                  type: "text",
                  name: elem.name,
                  disabled: obj.disabled
                };
                if (elem.id != "") {
                  o.id = elem.id;
                }
                text = $G().create("input", o);
                if (elem.value != "") {
                  text.value = elem.value;
                }
                if (obj.title != "") {
                  text.title = obj.title;
                }
                if (elem.size) {
                  text.size = elem.size;
                }
                if (elem.placeholder) {
                  text.placeholder = elem.placeholder;
                }
                if (obj.maxlength > 0) {
                  text.maxlength = obj.maxlength;
                }
                if (elem.readOnly) {
                  text.readOnly = true;
                }
                if (obj.type == "currency") {
                  text.digit = floatval(obj.dataset['digit'] ? obj.dataset['digit'] : 2);
                }
                text.className = elem.className;
                text.initObj = true;
                elem.replace(text);
                if (obj.type == "color") {
                  new GDDColor(text, function(c) {
                    this.input.style.backgroundColor = c;
                    this.input.style.color = this.invertColor(c);
                    this.input.value = c;
                    this.input.callEvent("change");
                  });
                } else if (obj.type == "time") {
                  new GTime(text);
                } else if (obj.type == "email" || obj.type == "url") {
                  if (obj.pattern == null) {
                    if (obj.type == "email") {
                      obj.pattern = /^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/;
                    } else {
                      obj.pattern = /^[a-z0-9\-\.:\/\#%\?\&\=_@~]{3,}$/i;
                    }
                  }
                  text.addEvent("keyup", _docheck);
                  text.addEvent("change", _docheck);
                } else {
                  if (!obj.dataset["keyboard"]) {
                    if (obj.type == "integer") {
                      obj.dataset["keyboard"] = "1234567890-";
                    } else if (obj.type == "currency") {
                      obj.dataset["keyboard"] = "1234567890-.,";
                    } else if (obj.type == "number" || obj.type == "tel") {
                      obj.dataset["keyboard"] = "1234567890";
                    }
                  }
                  if (obj.dataset["keyboard"]) {
                    obj.pattern = new RegExp("^(?:[" + obj.dataset["keyboard"].preg_quote() + "]+)$");
                    if (obj.type == "integer" || obj.type == "currency" || obj.type == "number") {
                      new GInput(text, obj.dataset["keyboard"], function() {
                        if (obj.min) {
                          this.value = Math.max(obj.min, floatval(this.value));
                        }
                        if (obj.max) {
                          this.value = Math.min(obj.max, floatval(this.value));
                        }
                        if (obj.type == "currency") {
                          this.value = toCurrency(this.value, this.digit);
                        }
                      });
                    } else {
                      new GInput(text, obj.dataset["keyboard"]);
                    }
                  }
                }
              } else if (obj.type == "file") {
                if (elem.hasClass("g-file")) {
                  var p = elem.parentNode;
                  elem.setStyle("opacity", 0);
                  elem.style.cursor = "pointer";
                  elem.style.position = "absolute";
                  elem.style.left = 0;
                  elem.style.top = 1;
                  p.style.position = "relative";
                  var display = document.createElement("input");
                  display.setAttribute("type", "text");
                  display.id = elem.id;
                  elem.id = elem.id + "_tmp";
                  display.disabled = true;
                  display.placeholder = elem.placeholder;
                  p.appendChild(display);
                  elem.style.zIndex = text.style.zIndex + 1;
                  elem.style.height = "100%";
                  elem.style.width = "100%";
                  elem.addEvent("change", function() {
                    if (this.files) {
                      var input = this,
                        hs,
                        files = [],
                        preview = $E(this.get("data-preview")),
                        max = floatval(input.get("data-max")),
                        validImageTypes = ['image/gif', 'image/jpeg', 'image/jpg', 'image/png'];
                      if (preview) {
                        preview.innerHTML = '';
                      }
                      forEach(input.files, function() {
                        if (max > 0 && this.size > max) {
                          input.invalid(input.title);
                        } else {
                          files.push(this.name);
                          input.valid();
                          if (preview) {
                            hs = /\.([a-z0-9]+)$/.exec(this.name.toLowerCase());
                            var div = document.createElement('div');
                            div.className = 'file-thumb';
                            if (hs) {
                              div.innerHTML = hs[1];
                            }
                            preview.appendChild(div);
                            if (validImageTypes.includes(this.type) && window.FileReader) {
                              var r = new FileReader();
                              r.onload = function(evt) {
                                div.innerHTML = '';
                                div.style.backgroundImage = 'url(' + evt.target.result + ')';
                              };
                              r.readAsDataURL(this);
                            }
                          }
                        }
                      });
                      display.value = files.join(', ');
                      display.callEvent("change", {
                        value: this.value,
                        files: this.files
                      });
                    }
                  });
                  elem.initObj = true;
                }
              } else if (obj.type == "range") {
                new GRange(elem);
              } else if (obj.type == "inputgroup") {
                new GInputGroup(elem);
              } else if (elem.get("list")) {
                new Datalist(elem);
              } else if (obj.pattern) {
                new GMask(text, function() {
                  return obj.pattern.test(this.value);
                });
              }
              if (typeof obj.dataset !== "undefined") {
                for (var prop in obj.dataset) {
                  if (obj.dataset[prop] !== null) {
                    text.setAttribute("data-" + prop, obj.dataset[prop]);
                  }
                }
              }
              if (autofocus !== null) {
                text.focus();
                if (obj.type == "text") {
                  text.select();
                }
              }
              if (obj.pattern || obj.required) {
                text.srcObj = obj;
              }
            }
          }
        }
      });
    }
  };
  if (typeof Array.prototype.indexOf != "function") {
    Array.prototype.indexOf = function(t, i) {
      i || (i = 0);
      var l = this.length;
      if (i < 0) {
        i = l + i;
      }
      for (; i < l; i++) {
        if (this[i] == t) {
          return i;
        }
      }
      return -1;
    };
  }
  if (typeof forEach != "function") {
    window.forEach = function(a, f) {
      var i,
        l = a.length,
        x = [];
      for (i = 0; i < l; i++) {
        x.push(a[i]);
      }
      for (i = 0; i < l; i++) {
        if (f.call(x[i], x[i], i) == true) {
          break;
        }
      }
    };
  }
  window.floatval = function(val) {
    var n = parseFloat(typeof val == 'string' ? val.replace(/[^0-9\-\.]/g, '') : val);
    return isNaN(n) ? 0 : n;
  };
  window.toCurrency = function(val, digit) {
    return floatval(val).toFixed(Object.isNull(digit) ? 2 : digit).replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,')
  };
  window.round = function(val, digit) {
    var value = Math.round(val * Math.pow(10, digit)) / Math.pow(10, digit);
    if (val - value > 0) {
      return value + Math.floor((2 * Math.round((val - value) * Math.pow(10, digit + 1))) / 10) / Math.pow(10, digit);
    } else {
      return value;
    }
  };
  window.copyToClipboard = function(text) {
    var el = document.createElement("textarea");
    el.value = text;
    el.setAttribute('readonly', '');
    el.style.position = 'absolute';
    el.style.top = '-1000px';
    document.body.appendChild(el);
    el.select();
    document.execCommand("copy");
    document.body.removeChild(el);
  };
  window.trans = function(val) {
    try {
      var patt = /^[_]+|[_]+$/g;
      return eval(val.replace(/[\s]/g, "_").replace("?", "").replace(patt, "").toUpperCase());
    } catch (e) {
      return val;
    }
  };
  window.debug = function(val) {
    var p = document.createElement("p"),
      div = $E("gdebug");
    if (!div) {
      div = document.createElement("div");
      div.id = "gdebug";
      document.body.appendChild(div);
      div.style.cssText = "left:0;bottom:0;width:100%;height:100px;color:#F00;background-color:#FFF;position:fixed;line-height:1;padding:10px;overflow:auto;";
    }
    p.style.cssText = "margin:0;";
    p.innerText = val;
    div.appendChild(p);
    div.scrollTop = div.scrollHeight;
  };
  window.timeToMinute = function(time) {
    var sp = time.split(':');
    if (sp.length == 1) {
      return 0;
    } else {
      return (floatval(sp[0]) * 60) + floatval(sp[1]);
    }
  };
  window.timeToSecond = function(time) {
    var sp = time.split(':');
    if (sp.length == 1) {
      return 0;
    } else {
      return (floatval(sp[0]) * 60) + (floatval(sp[1] * 60) + floatval(sp[2]));
    }
  };
  Function.prototype.bind = function(o) {
    var __method = this;
    return function() {
      return __method.apply(o, arguments);
    };
  };
  Date.prototype.fromTime = function(mktime) {
    return new Date(mktime * 1000);
  };
  Date.prototype.format = function(fmt) {
    var result = "";
    for (var i = 0; i < fmt.length; i++) {
      result += this.formatter(fmt.charAt(i));
    }
    return result;
  };
  Date.prototype.formatter = function(c) {
    switch (c) {
      case "d":
        return this.getDate().toString().leftPad(2, "0");
      case "D":
        return Date.dayNames[this.getDay()];
      case "y":
        return this.getFullYear().toString();
      case "Y":
        return (this.getFullYear() + Date.yearOffset).toString();
      case "m":
        return (this.getMonth() + 1).toString().leftPad(2, "0");
      case "M":
        return Date.monthNames[this.getMonth()];
      case "F":
        return Date.longMonthNames[this.getMonth()];
      case "H":
        return this.getHours().toString().leftPad(2, "0");
      case "h":
        return this.getHours();
      case "A":
        return this.getHours() < 12 ? "AM" : "PM";
      case "a":
        return this.getHours() < 12 ? "am" : "pm";
      case "I":
        return this.getMinutes().toString().leftPad(2, "0");
      case "i":
        return this.getMinutes();
      case "S":
        return this.getSeconds().toString().leftPad(2, "0");
      case "s":
        return this.getSeconds();
      default:
        return c;
    }
  };
  Date.prototype.tomktime = function() {
    return Math.floor(this.getTime() / 1000);
  };
  Date.prototype.moveDate = function(value) {
    this.setDate(this.getDate() + value);
    return this;
  };
  Date.prototype.moveMonth = function(value) {
    this.setMonth(this.getMonth() + value);
    return this;
  };
  Date.prototype.moveYear = function(value) {
    this.setFullYear(this.getFullYear() + value);
    return this;
  };
  Date.prototype.isLeapYear = function() {
    var year = this.getFullYear();
    return (year & 3) == 0 && (year % 100 || (year % 400 == 0 && year));
  };
  Date.prototype.daysInMonth = function() {
    var arr = Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
    arr[1] = this.isLeapYear() ? 29 : 28;
    return arr[this.getMonth()];
  };
  Date.prototype.dayOfWeek = function() {
    var a = parseInt((14 - this.getMonth()) / 12);
    var y = this.getFullYear() - a;
    var m = this.getMonth() + 12 * a - 2;
    var d = (this.getDate() + y + parseInt(y / 4) - parseInt(y / 100) + parseInt(y / 400) + parseInt((31 * m) / 12)) % 7;
    return d;
  };
  Date.prototype.compare = function(d) {
    if (Object.isString(d)) {
      d = new Date(d.replace(/-/g, '/'));
    }
    var days = Math.floor((this.getTime() - d.getTime()) / 86400000),
      numDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
    if (days < 0) {
      var fromDate = this.getDate(),
        fromMonth = this.getMonth(),
        fromYear = this.getFullYear(),
        toDate = d.getDate(),
        toMonth = d.getMonth(),
        toYear = d.getFullYear();
      if (d.isLeapYear()) {
        numDays[1] = 29;
      }
    } else {
      var fromDate = d.getDate(),
        fromMonth = d.getMonth(),
        fromYear = d.getFullYear(),
        toDate = this.getDate(),
        toMonth = this.getMonth(),
        toYear = this.getFullYear();
      if (this.isLeapYear()) {
        numDays[1] = 29;
      }
    }
    var diffYear = toYear - fromYear,
      diffMonth = toMonth - fromMonth,
      diffDate = toDate - fromDate;
    if (diffDate < 0) {
      diffMonth--;
      toMonth = toMonth == 0 ? 11 : toMonth - 1;
      diffDate = numDays[toMonth] + diffDate;
    }
    if (diffMonth < 0) {
      diffYear--;
      diffMonth = 12 + diffMonth;
    }
    return {
      day: diffDate,
      month: diffMonth,
      year: diffYear,
      days: days
    };
  };
  Date.monthNames = [
    "Jan.",
    "Feb.",
    "Mar.",
    "Apr.",
    "May.",
    "Jun.",
    "Jul.",
    "Aug.",
    "Sep.",
    "Oct.",
    "Nov.",
    "Dec."
  ];
  Date.longMonthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"
  ];
  Date.longDayNames = [
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday"
  ];
  Date.dayNames = ["Su.", "Mo.", "We.", "Tu.", "Th.", "Fr.", "Sa."];
  Date.yearOffset = 0;
  String.prototype.entityify = function() {
    return this.replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;")
      .replace(/\\/g, "&#92;")
      .replace(/&/g, "&amp;")
      .replace(/\{/g, "&#x007B;")
      .replace(/\}/g, "&#x007D;");
  };
  String.prototype.unentityify = function() {
    return this.replace(/&lt;/g, "<")
      .replace(/&gt;/g, ">")
      .replace(/&quot;/g, '"')
      .replace(/&#[0]?39;/g, "'")
      .replace(/&#92;/g, "\\")
      .replace(/&amp;/g, "&")
      .replace(/&#x007B;/g, "{")
      .replace(/&#x007D;/g, "}");
  };
  String.prototype.toJSON = function() {
    try {
      return JSON.parse(this);
    } catch (e) {
      return false;
    }
  };
  String.prototype.toInt = function() {
    return floatval(this);
  };
  String.prototype.currFormat = function() {
    return floatval(this).toFixed(2);
  };
  String.prototype.preg_quote = function() {
    return this.replace(/([-.*+?^${}()|[\]\/\\])/g, "\\$1");
  };
  String.prototype.capitalize = function() {
    return this.replace(/\b[a-z]/g, function(m) {
      return m.toUpperCase();
    });
  };
  String.prototype.evalScript = function() {
    var regex = /<script.*?>(.*?)<\/script>/g;
    var t = this.replace(/[\r\n]/g, "").replace(/\/\/<\!\[CDATA\[/g, "").replace(/\/\/\]\]>/g, "");
    var m = regex.exec(t);
    while (m) {
      try {
        eval(m[1]);
      } catch (e) {}
      m = regex.exec(t);
    }
    return this;
  };
  String.prototype.leftPad = function(c, f) {
    var r = "";
    for (var i = 0; i < c - this.length; i++) {
      r = r + f;
    }
    return r + this;
  };
  String.prototype.trim = function() {
    return this.replace(/^(\s|&nbsp;)+|(\s|&nbsp;)+$/g, "");
  };
  String.prototype.ltrim = function() {
    return this.replace(/^(\s|&nbsp;)+/, "");
  };
  String.prototype.rtrim = function() {
    return this.replace(/(\s|&nbsp;)+$/, "");
  };
  String.prototype.strip_tags = function(allowed) {
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join("");
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
    var php = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return this.replace(php, "").replace(tags, function($0, $1) {
      return allowed.indexOf("<" + $1.toLowerCase() + ">") > -1 ? $0 : "";
    });
  };
  String.prototype.toDOM = function() {
    var s = function(a) {
      return a
        .replace(/&gt;/g, ">")
        .replace(/&lt;/g, "<")
        .replace(/&nbsp;/g, " ")
        .replace(/&quot;/g, '"')
        .replace(/&#[0]?39;/g, "'")
        .replace(/&#92;/g, "\\")
        .replace(/&amp;/g, "&");
    };
    var t = function(a) {
      return a.replace(/ /g, "");
    };
    var u = function(a) {
      var b = document.createDocumentFragment();
      var c = a.indexOf(" ");
      if (c == -1) {
        var d = a.toLowerCase();
        b.appendChild(document.createElement(d));
      } else {
        d = t(a.substring(0, c)).toLowerCase();
        if (document.all && (d == "input" || d == "iframe")) {
          try {
            b.appendChild(document.createElement("<" + a + "/>"));
            return b;
          } catch (e) {}
        }
        a = a.substring(c + 1);
        b.appendChild(document.createElement(d));
        while (a.length > 0) {
          var e = a.indexOf("=");
          if (e >= 0) {
            var f = t(a.substring(0, e)).toLowerCase();
            var g = a.indexOf('"');
            a = a.substring(g + 1);
            g = a.indexOf('"');
            var h = s(a.substring(0, g));
            a = a.substring(g + 2);
            if (document.all && f == "style") {
              b.lastChild.style.cssText = h;
            } else if (f == "class") {
              b.lastChild.className = h;
            } else {
              b.lastChild.setAttribute(f, h);
            }
          } else {
            break;
          }
        }
      }
      return b;
    };
    var v = function(a, b, c) {
      var d = a;
      var e = b;
      c = c.toLowerCase();
      var f = e.indexOf("</" + c + ">");
      d = d.concat(e.substring(0, f));
      e = e.substring(f);
      while (d.indexOf("<" + c) != -1) {
        d = d.substring(d.indexOf("<" + c));
        d = d.substring(d.indexOf(">") + 1);
        e = e.substring(e.indexOf(">") + 1);
        f = e.indexOf("</" + c + ">");
        d = d.concat(e.substring(0, f));
        e = e.substring(f);
      }
      return b.length - e.length;
    };
    var w = function(a) {
      var b = document.createDocumentFragment();
      while (a && a.length > 0) {
        var c = a.indexOf("<");
        if (c == -1) {
          a = s(a);
          b.appendChild(document.createTextNode(a));
          a = null;
        }
        if (c > 0) {
          var d = s(a.substring(0, c));
          b.appendChild(document.createTextNode(d));
          a = a.substring(c);
        }
        if (c == 0) {
          var e = a.indexOf("<!--");
          if (e == 0) {
            var f = a.indexOf("-->");
            var g = a.substring(4, f);
            g = s(g);
            b.appendChild(document.createComment(g));
            a = a.substring(f + 3);
          } else {
            var h = a.indexOf(">");
            if (a.substring(h - 1, h) == "/") {
              var i = a.indexOf("/>");
              var j = a.substring(1, i);
              b.appendChild(u(j));
              a = a.substring(i + 2);
            } else {
              var k = a.indexOf(">");
              var l = a.substring(1, k);
              var m = document.createDocumentFragment();
              m.appendChild(u(l));
              a = a.substring(k + 1);
              var n = a.substring(0, a.indexOf("</"));
              a = a.substring(a.indexOf("</"));
              if (n.indexOf("<") != -1) {
                var o = m.lastChild.nodeName;
                var p = v(n, a, o);
                n = n.concat(a.substring(0, p));
                a = a.substring(p);
              }
              a = a.substring(a.indexOf(">") + 1);
              m.lastChild.appendChild(w(n));
              b.appendChild(m);
            }
          }
        }
      }
      return b;
    };
    return w(this);
  };
  String.prototype.toDate = function() {
    var patt = /(([0-9]{4,4})-([0-9]{1,2})-([0-9]{1,2})|today|tomorrow|yesterday)([\s]{0,}([+-])[\s]{0,}([0-9]+))?/,
      hs = patt.exec(this),
      d;
    if (hs) {
      if (typeof hs[2] == "undefined") {
        d = new Date();
      } else {
        d = new Date(floatval(hs[2]), floatval(hs[3]) - 1, hs[4], 0, 0, 0, 0);
      }
      if (hs[1] == "yesterday") {
        d.setDate(d.getDate() - 1);
      } else if (hs[1] == "tomorrow") {
        d.setDate(d.getDate() + 1);
      }
      if (hs[6] == "+" && floatval(hs[7]) > 0) {
        d.setDate(d.getDate() + floatval(hs[7]));
      } else if (hs[6] == "-" && floatval(hs[7]) > 0) {
        d.setDate(d.getDate() - floatval(hs[7]));
      }
      return d;
    } else {
      return null;
    }
  };
  Number.prototype.format = function(decimals, dec_point, thousands_sep) {
    decimals = isNaN((decimals = Math.abs(decimals))) ? 2 : decimals;
    dec_point = dec_point == undefined ? "." : dec_point;
    thousands_sep = thousands_sep == undefined ? "," : thousands_sep;
    var n = this,
      s = n < 0 ? "-" : "",
      i = String(parseInt((n = Math.abs(Number(n) || 0).toFixed(decimals)))),
      j = (j = i.length) > 3 ? j % 3 : 0;
    return (s + (j ? i.substr(0, j) + thousands_sep : "") + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + thousands_sep) + (decimals ? dec_point + Math.abs(n - i).toFixed(decimals).slice(2) : ""));
  };
  document.viewport = {
    getWidth: function() {
      return (
        document.documentElement.clientWidth ||
        document.body.clientWidth ||
        self.innerWidth
      );
    },
    getHeight: function() {
      return (
        document.documentElement.clientHeight ||
        document.body.clientHeight ||
        self.innerHeight
      );
    },
    getscrollTop: function() {
      return (
        window.pageYOffset ||
        document.documentElement.scrollTop ||
        document.body.scrollTop
      );
    },
    getscrollLeft: function() {
      return (
        window.pageXOffset ||
        document.documentElement.scrollLeft ||
        document.body.scrollLeft
      );
    }
  };
  document.css = function(css, id) {
    var style = document.createElement("style");
    if (id) {
      style.id = "css_" + id;
      if ($E("css_" + id)) {
        $E("css_" + id).parentNode.removeChild($E("css_" + id));
      }
    }
    if (css !== null) {
      if (style.styleSheet) {
        style.styleSheet.cssText = css;
      } else {
        style.appendChild(document.createTextNode(css));
      }
      document.getElementsByTagName("head")[0].appendChild(style);
    }
  };
  Object.extend = function(d, s) {
    for (var property in s) {
      d[property] = s[property];
    }
    return d;
  };
  Object.extend(Object, {
    isObject: function(o) {
      return typeof o == "object";
    },
    isFunction: function(o) {
      return typeof o == "function";
    },
    isString: function(o) {
      return typeof o == "string";
    },
    isNumber: function(o) {
      return typeof o == "number";
    },
    isNull: function(o) {
      return typeof o == "undefined";
    },
    isGElement: function(o) {
      return (
        o != null && typeof o == "object" && "Ready" in o && "element" in o
      );
    },
    toArray: function(o) {
      var prop,
        result = [];
      for (prop in o) {
        result.push(o[prop]);
      }
      return result;
    }
  });
  window.GClass = {
    create: function() {
      return function() {
        this.initialize.apply(this, arguments);
      };
    }
  };
  window.GNative = GClass.create();
  GNative.prototype = {
    initialize: function() {
      this.elem = null;
    },
    Ready: function(f) {
      var s = this;
      var p = function() {
        if (domloaded && s.element()) {
          f.call($G(s.elem));
        } else {
          window.setTimeout(p, 10);
        }
      };
      p();
    },
    after: function(e) {
      var p = this.parentNode;
      if (this.nextSibling == null) {
        p.appendChild(e);
      } else {
        p.insertBefore(e, this.nextSibling);
      }
      return e;
    },
    before: function(e) {
      var p = this.parentNode;
      if (p.firstChild == this) {
        p.appendChild(e);
      } else {
        p.insertBefore(e, this);
      }
      return e;
    },
    insert: function(e) {
      e = $G(e);
      this.appendChild(e);
      return e;
    },
    copy: function(o) {
      return $G(this.cloneNode(o || true));
    },
    replace: function(e) {
      var p = this.parentNode;
      p.insertBefore(e, this.nextSibling);
      p.removeChild(this);
      return $G(e);
    },
    remove: function() {
      if (this.element()) {
        this.parentNode.removeChild(this);
      }
      return this;
    },
    setHTML: function(o) {
      try {
        this.innerHTML = o;
      } catch (e) {
        o = o
          .replace(/[\r\n\t]/g, "")
          .replace(/<script[^>]*>.*?<\/script>/gi, "");
        this.appendChild(o.toDOM());
      }
      return this;
    },
    getTop: function() {
      return this.viewportOffset().top;
    },
    getLeft: function() {
      return this.viewportOffset().left;
    },
    getWidth: function() {
      return this.getDimensions().width;
    },
    getHeight: function() {
      return this.getDimensions().height;
    },
    getClientWidth: function() {
      return (
        this.clientWidth -
        parseInt(this.getStyle("paddingLeft")) -
        parseInt(this.getStyle("paddingRight"))
      );
    },
    getClientHeight: function() {
      return (
        this.clientHeight -
        parseInt(this.getStyle("paddingTop")) -
        parseInt(this.getStyle("paddingBottom"))
      );
    },
    viewportOffset: function() {
      var t = 0,
        l = 0,
        p = this;
      while (p) {
        t += parseInt(p.offsetTop);
        l += parseInt(p.offsetLeft);
        p = p.offsetParent;
      }
      if (this.getBoundingClientRect) {
        return { top: t, left: this.getBoundingClientRect().left };
      } else {
        return { top: t, left: l };
      }
    },
    getDimensions: function() {
      var ow, oh;
      if (this == document) {
        ow = Math.max(
          Math.max(
            document.body.scrollWidth,
            document.documentElement.scrollWidth
          ),
          Math.max(
            document.body.offsetWidth,
            document.documentElement.offsetWidth
          ),
          Math.max(
            document.body.clientWidth,
            document.documentElement.clientWidth
          )
        );
        oh = Math.max(
          Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight
          ),
          Math.max(
            document.body.offsetHeight,
            document.documentElement.offsetHeight
          ),
          Math.max(
            document.body.clientHeight,
            document.documentElement.clientHeight
          )
        );
      } else {
        var d = this.getStyle("display");
        if (d != "none" && d !== null) {
          ow = this.offsetWidth;
          oh = this.offsetHeight;
        } else {
          var s = this.style;
          var ov = s.visibility;
          var op = s.position;
          var od = s.display;
          s.visibility = "hidden";
          s.position = "absolute";
          s.display = "block";
          ow = this.clientWidth;
          oh = this.clientHeight;
          s.display = od;
          s.position = op;
          s.visibility = ov;
        }
      }
      return { width: ow, height: oh };
    },
    getOffsetParent: function() {
      var e = this.offsetParent;
      if (!e) {
        e = this.parentNode;
        while (e != document.body && e.style.position == "static") {
          e = e.parentNode;
        }
      }
      return GElement(e);
    },
    getCaretPosition: function() {
      if (document.selection) {
        var range = document.selection.createRange(),
          textLength = range.text.length;
        range.moveStart("character", -this.value.length);
        var caretAt = range.text.length;
        return { start: caretAt, end: caretAt + textLength };
      } else if (this.selectionStart || this.selectionStart == "0") {
        return { start: this.selectionStart, end: this.selectionEnd };
      }
    },
    setCaretPosition: function(start, length) {
      if (this.setSelectionRange) {
        this.focus();
        this.setSelectionRange(start, start + length);
      } else if (this.createTextRange) {
        var range = this.createTextRange();
        range.collapse(true);
        range.moveEnd("character", start + length);
        range.moveStart("character", start);
        range.select();
      }
      return this;
    },
    getStyle: function(s) {
      s = s == "float" && this.currentStyle ? "styleFloat" : s;
      s = s == "borderColor" ? "borderBottomColor" : s;
      var v = this.currentStyle ? this.currentStyle[s] : null;
      v = !v && window.getComputedStyle ? document.defaultView.getComputedStyle(this, null).getPropertyValue(s.replace(/([A-Z])/g, "-$1").toLowerCase()) : v;
      if (s == "opacity") {
        return Object.isNull(v) ? 100 : floatval(v) * 100;
      } else {
        return v;
      }
    },
    setStyle: function(p, v) {
      if (p == "opacity") {
        if (window.ActiveXObject) {
          this.style.filter = "alpha(opacity=" + v * 100 + ")";
        }
        this.style.opacity = v;
      } else if (p == "float" || p == "styleFloat" || p == "cssFloat") {
        if (Object.isNull(this.style.styleFloat)) {
          this.style["cssFloat"] = v;
        } else {
          this.style["styleFloat"] = v;
        }
      } else if (
        p == "backgroundColor" &&
        this.tagName.toLowerCase() == "iframe"
      ) {
        if (document.all) {
          this.contentWindow.document.bgColor = v;
        } else {
          this.style.backgroundColor = v;
        }
      } else if (p == "borderColor") {
        this.style.borderLeftColor = v;
        this.style.borderTopColor = v;
        this.style.borderRightColor = v;
        this.style.borderBottomColor = v;
      } else {
        this.style[p] = v;
      }
      return this;
    },
    center: function() {
      var size = this.getDimensions();
      if (this.getStyle("position") == "fixed") {
        this.style.top =
          (document.viewport.getHeight() - size.height) / 2 + "px";
        this.style.left =
          (document.viewport.getWidth() - size.width) / 2 + "px";
      } else {
        this.style.top =
          document.viewport.getscrollTop() +
          (document.viewport.getHeight() - size.height) / 2 +
          "px";
        this.style.left =
          document.viewport.getscrollLeft() +
          (document.viewport.getWidth() - size.width) / 2 +
          "px";
      }
      return this;
    },
    get: function(p) {
      try {
        return this.getAttribute(p);
      } catch (e) {
        return null;
      }
    },
    set: function(p, v) {
      try {
        this.setAttribute(p, v);
      } catch (e) {}
      return this;
    },
    hasClass: function(v) {
      var vs = v.split(" ");
      var cs = this.className.split(" ");
      for (var c = 0; c < cs.length; c++) {
        for (v = 0; v < vs.length; v++) {
          if (vs[v] != "" && vs[v] == cs[c]) {
            return vs[v];
          }
        }
      }
      return false;
    },
    addClass: function(v) {
      if (this.className || this.className === '') {
        if (!v) {
          this.className = "";
        } else {
          var rm = v.split(" "),
            cs = [];
          forEach(this.className.split(" "), function(c) {
            if (c !== "" && rm.indexOf(c) == -1) {
              cs.push(c);
            }
          });
          cs.push(v);
          this.className = cs.join(" ");
        }
      }
      return this;
    },
    removeClass: function(v) {
      if (this.className || this.className === '') {
        var rm = v.split(" ");
        var cs = [];
        forEach(this.className.split(" "), function(c) {
          if (c !== "" && rm.indexOf(c) == -1) {
            cs.push(c);
          }
        });
        this.className = cs.join(" ");
      }
      return this;
    },
    replaceClass: function(source, replace) {
      if (this.className || this.className === '') {
        var rm = (replace + " " + source).split(" ");
        var cs = [];
        forEach(this.className.split(" "), function(c) {
          if (c !== "" && rm.indexOf(c) == -1) {
            cs.push(c);
          }
        });
        cs.push(replace);
        this.className = cs.join(" ");
      }
      return this;
    },
    hide: function() {
      this.display = this.getStyle("display");
      this.setStyle("display", "none");
      return this;
    },
    show: function() {
      if (this.getStyle("display") == "none") {
        this.setStyle("display", "block");
      }
      return this;
    },
    visible: function() {
      return this.getStyle("display") != "none";
    },
    toggle: function() {
      if (this.visible()) {
        this.hide();
      } else {
        this.show();
      }
      return this;
    },
    nextNode: function() {
      var n = this;
      do {
        n = n.nextSibling;
      } while (n && n.nodeType != 1);
      return n;
    },
    previousNode: function() {
      var p = this;
      do {
        p = p.previousSibling;
      } while (p && p.nodeType != 1);
      return p;
    },
    firstNode: function() {
      var p = this.firstChild;
      do {
        p = p.nextSibling;
      } while (p && p.nodeType != 1);
      return p;
    },
    nextTab: function() {
      var tag,
        result,
        self = this,
        check = null;
      forEach(document.forms, function() {
        return forEach(this.getElementsByTagName("*"), function() {
          if (this == self.elem) {
            check = this;
          } else if (check != null) {
            if (
              this.tabIndex >= 0 &&
              this.disabled != true &&
              this.style.display != "none" &&
              this.offsetParent != null
            ) {
              result = this;
              return true;
            }
          }
        });
      });
      return result;
    },
    sendKey: function(keyCode) {
      return this.callEvent("keypress", { keyCode: keyCode });
    },
    callEvent: function(t, params) {
      var evt;
      if (document.createEvent) {
        evt = document.createEvent("Events");
        evt.initEvent(t, true, true);
        for (var prop in params) {
          evt[prop] = params[prop];
        }
        this.dispatchEvent(evt);
      } else if (document.createEventObject) {
        evt = document.createEventObject();
        for (var prop in params) {
          evt[prop] = params[prop];
        }
        this.fireEvent("on" + t, evt);
      }
      return this;
    },
    addEvent: function(t, f, c) {
      var ts = t.split(/[\s,]/),
        input = this;
      forEach(ts, function(e) {
        if (input.addEventListener) {
          c = !c ? false : c;
          input.addEventListener(e, f, c);
        } else if (input.attachEvent) {
          input["e" + e + f] = f;
          input[e + f] = function() {
            input["e" + e + f](window.event);
          };
          input.attachEvent("on" + e, input[e + f]);
        }
      });
      return this;
    },
    removeEvent: function(t, f, c) {
      var ts = t.split(" "),
        input = this;
      forEach(ts, function(e) {
        if (input.removeEventListener) {
          c = !c ? false : c;
          input.removeEventListener(e == "mousewheel" && window.gecko ? "DOMMouseScroll" : e, f, c);
        } else if (input.detachEvent) {
          input.detachEvent("on" + e, input[e + f]);
          input["e" + e + f] = null;
          input[e + f] = null;
        }
      });
      return this;
    },
    highlight: function(o) {
      this.addClass("highlight");
      var self = this;
      window.setTimeout(function() {
        self.removeClass("highlight");
      }, 1);
      return this;
    },
    fadeIn: function(oncomplete) {
      this.addClass("fadein");
      var self = this;
      window.setTimeout(function() {
        self.removeClass("fadein");
        if (Object.isFunction(oncomplete)) {
          oncomplete.call(this);
        }
      }, 1000);
      return this;
    },
    fadeOut: function(oncomplete) {
      this.addClass("fadeout");
      var self = this;
      window.setTimeout(function() {
        self.removeClass("fadeout");
        if (Object.isFunction(oncomplete)) {
          oncomplete.call(this);
        }
      }, 1000);
      return this;
    },
    setValue: function(v) {
      function _find(e, a) {
        var s = e.getElementsByTagName("option");
        for (var i = 0; i < s.length; i++) {
          if (s[i].value == a) {
            return i;
          }
        }
        return -1;
      }
      if (this.time) {
        this.time.setTime(v);
      } else if (this.calendar) {
        this.value = v;
      } else if (this.datalist) {
        this.selectedIndex = v;
      } else {
        v = decodeURIComponent(v);
        var t = this.tagName.toLowerCase();
        if (t == "img") {
          this.src = v;
        } else if (t == "select") {
          this.selectedIndex = _find(this, v);
        } else if (t == "input") {
          if (this.type == "checkbox" || this.type == "radio") {
            this.checked = v == this.value;
          } else {
            this.value = v.unentityify();
          }
        } else if (t == "textarea") {
          this.value = v.unentityify();
        } else {
          this.setHTML(v);
        }
      }
      return this;
    },
    getText: function() {
      if (!Object.isNull(this.elem.selectedIndex)) {
        if (this.elem.selectedIndex == -1) {
          return null;
        }
        return this.elem.options[this.elem.selectedIndex].text;
      } else if (this.elem.innerHTML) {
        return this.elem.innerHTML;
      }
      return this.elem.value;
    },
    setOptions: function(json, value) {
      if (this.tagName.toLowerCase() == "select") {
        for (var i = this.options.length; i > 0; i--) {
          this.removeChild(this.options[i - 1]);
        }
        var selectedIndex = 0;
        if (json) {
          var i = 0;
          for (var key in json) {
            if (key == value) {
              selectedIndex = i;
            }
            var option = document.createElement("option");
            option.innerHTML = json[key];
            option.value = key;
            this.appendChild(option);
            i++;
          }
        }
        this.selectedIndex = selectedIndex;
      }
    },
    getSelectedText: function() {
      var text = "";
      if (this.selectionStart) {
        if (this.selectionStart != this.selectionEnd) {
          text = this.value.substring(this.selectionStart, this.selectionEnd);
        }
      } else if (document.selection) {
        var range = document.selection.createRange();
        if (range.parentElement() === this) {
          text = range.text;
        }
      }
      return text;
    },
    setSelectedText: function(value) {
      if (this.selectionStart) {
        if (this.selectionStart != this.selectionEnd) {
          this.value =
            this.value.substring(0, this.selectionStart) +
            value +
            this.value.substring(this.selectionEnd);
        }
      } else {
        var range = document.selection.createRange();
        if (range.parentElement() === this) {
          range.text = value;
        }
      }
      return this;
    },
    findLabel: function() {
      var result = null,
        id = this.id;
      forEach(document.getElementsByTagName("label"), function() {
        if (this.htmlFor != "" && this.htmlFor == id) {
          result = this;
          return true;
        }
      });
      return result;
    },
    element: function() {
      return Object.isString(this.elem) ?
        document.getElementById(this.elem) :
        this.elem;
    },
    elems: function(tagname) {
      return this.getElementsByTagName(tagname);
    },
    create: function(tagname, o) {
      var v;
      if (tagname == "iframe" || tagname == "input") {
        var n = o.name || o.id || "";
        var i = o.id || o.name || "";
        if (window.ActiveXObject) {
          try {
            if (tagname == "iframe") {
              v = document.createElement(
                '<iframe id="' + i + '" name="' + n + '" scrolling="no" />'
              );
            } else {
              v = document.createElement(
                '<input id="' +
                i +
                '" name="' +
                n +
                '" type="' +
                o.type +
                '" />'
              );
            }
          } catch (e) {
            v = document.createElement(tagname);
            v.name = n;
            v.id = i;
          }
        } else {
          v = document.createElement(tagname);
          v.name = n;
          v.id = i;
        }
      } else {
        v = document.createElement(tagname);
      }
      if (this.elem) {
        this.appendChild(v);
      }
      for (var p in o) {
        v[p] = o[p];
      }
      return $G(v);
    },
    msgBox: function(value, className, autohide) {
      var parent,
        tag = this.tagName.toLowerCase();
      if (tag == "body") {
        if ($E("body_msg_div")) {
          parent = $E("body_msg_div");
        } else {
          parent = document.createElement("div");
          parent.id = "body_msg_div";
          parent.style.position = "fixed";
          parent.style.right = "10px";
          parent.style.top = "10px";
          document.body.appendChild(parent);
        }
      } else {
        parent = this;
      }
      if (parent) {
        if (value && value != "") {
          var div = document.createElement("div"),
            innerDiv = document.createElement("div");
          div.className = "alert " + (className || "message");
          var span = document.createElement("span");
          span.innerHTML = "&times;";
          span.className = "closebtn";
          div.appendChild(span);
          div.appendChild(innerDiv);
          innerDiv.innerHTML = value;
          parent.appendChild(div);
        }
        forEach(parent.getElementsByClassName("closebtn"), function() {
          if (this.onclick === null) {
            var span = this;
            span.onclick = function() {
              var parent = this.parentNode;
              parent.style.opacity = "0";
              if (this.timer) {
                clearTimeout(this.timer);
              }
              setTimeout(function() {
                parent.remove();
              }, 600);
            };
            if (typeof autohide === "undefined" || autohide === true) {
              span.timer = setTimeout(function() {
                span.click();
              }, 3000);
            }
          }
        });
      }
    },
    valid: function(className) {
      if (this.ret) {
        if (this.ret.hasClass("validationResult")) {
          this.ret.remove();
          this.ret = false;
        } else {
          this.ret.replaceClass("invalid", "valid");
          this.ret.innerHTML = this.retDef ? this.retDef : "";
        }
      }
      this.replaceClass(
        "invalid wait",
        "valid" + (className ? " " + className : "")
      );
      return this;
    },
    invalid: function(value, className) {
      if (!this.ret) {
        if (
          typeof this.dataset !== "undefined" &&
          typeof this.dataset.result === "string" &&
          this.dataset.result !== "" &&
          $E(this.dataset.result)
        ) {
          this.ret = $G(this.dataset.result);
        } else {
          var id = this.id || this.name;
          if ($E("result_" + id)) {
            this.ret = $G("result_" + id);
          }
        }
        if (this.ret && !this.retDef) {
          this.retDef = this.ret.innerHTML;
        }
      }
      if (this.ret) {
        if (value && value != "") {
          this.ret.innerHTML = value;
        }
        this.ret.replaceClass(
          "valid",
          "invalid" + (className ? " " + className : "")
        );
      }
      this.replaceClass("valid wait", "invalid");
      return this;
    },
    reset: function() {
      if (this.ret) {
        if (this.ret.hasClass("validationResult")) {
          this.ret.remove();
          this.ret = false;
        } else {
          this.ret.replaceClass("invalid valid", "");
          this.ret.innerHTML = this.retDef ? this.retDef : "";
        }
      }
      this.replaceClass("invalid valid wait required", "");
      return this;
    },
    init: function(e) {
      this.elem = e;
      var elem = this.element();
      if (!elem) {
        return this;
      } else {
        this.elem = elem;
        for (var p in this) {
          if (p != "elements") {
            elem[p] = this[p];
          }
        }
        return elem;
      }
    }
  };
  var ajaxAccepts = {
    xml: "application/xml, text/xml",
    html: "text/html",
    text: "text/plain",
    json: "application/json, text/javascript",
    all: "text/html, text/plain, application/xml, text/xml, application/json, text/javascript"
  };
  window.GAjax = GClass.create();
  GAjax.prototype = {
    initialize: function(options) {
      this.options = {
        method: "post",
        cache: false,
        asynchronous: true,
        contentType: "application/x-www-form-urlencoded",
        encoding: "UTF-8",
        Accept: "all",
        onTimeout: $K.emptyFunction,
        onError: $K.emptyFunction,
        onProgress: $K.emptyFunction,
        timeout: 0,
        loadingClass: "show",
        headers: {}
      };
      for (var property in options) {
        this.options[property] = options[property];
      }
      this.options.method = this.options.method.toLowerCase();
      this.loader = null;
    },
    xhr: function() {
      var xmlHttp = null;
      try {
        xmlHttp = new XMLHttpRequest();
      } catch (e) {
        try {
          xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
          xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
        }
      }
      return xmlHttp;
    },
    send: function(url, parameters, callback) {
      var self = this;
      this._xhr = this.xhr();
      this._abort = false;
      if (!Object.isNull(this._xhr)) {
        var option = this.options;
        if (option.method == "get") {
          url += "?" + parameters;
          parameters = null;
        } else {
          parameters = parameters === null ? "" : parameters;
        }
        if (option.cache == false) {
          var match = /\?/;
          url += (match.test(url) ? "&" : "?") + new Date().getTime();
        }
        this._xhr.open(option.method, url, option.asynchronous);
        if (option.method == "post") {
          this._xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
          this._xhr.setRequestHeader("Accept", ajaxAccepts[option.Accept]);
          if (option.contentType && option.encoding) {
            this._xhr.setRequestHeader("Content-Type", option.contentType + "; charset=" + option.encoding);
          }
        }
        for (var prop in option.headers) {
          this._xhr.setRequestHeader(prop, option.headers[prop]);
        }
        if (option.timeout > 0) {
          this.calltimeout = window.setTimeout(_calltimeout, option.timeout);
        }
        this._xhr.onreadystatechange = function() {
          if (self._xhr.readyState == 4) {
            self.hideLoading();
            window.clearTimeout(self.calltimeout);
            if (
              self._xhr.status == 200 &&
              !self._abort &&
              Object.isFunction(callback)
            ) {
              self.responseText = self._xhr.responseText;
              self.responseXML = self._xhr.responseXML;
              callback(self);
            } else {
              option.onError(self);
            }
          }
        };
        if (this._xhr.upload) {
          $G(this._xhr.upload).addEvent("progress", function(e) {
            option.onProgress.call(e, Math.ceil((100 * e.loaded) / e.total));
          });
        }
        var _calltimeout = function() {
          window.clearTimeout(self.calltimeout);
          self.hideLoading();
          option.onTimeout.bind(self);
        };
        self.showLoading();
        this._xhr.send(parameters);
        if (!option.asynchronous) {
          window.clearTimeout(this.calltimeout);
          this.responseText = this._xhr.responseText;
          this.responseXML = this._xhr.responseXML;
        }
      }
      return this;
    },
    autoupdate: function(url, interval, getRequest, callback) {
      this._xhr = this.xhr();
      this.interval = interval * 1000;
      if (!Object.isNull(this._xhr)) {
        this.url = url;
        this.getRequest = getRequest;
        this.callback = callback;
        this._abort = false;
        this._getupdate();
      }
      return this;
    },
    _getupdate: function() {
      if (this._abort == false) {
        var parameters = null;
        var url = this.url;
        var option = this.options;
        if (Object.isFunction(this.getRequest)) {
          if (option.method == "get") {
            url += "?" + this.getRequest();
          } else {
            parameters = this.getRequest();
          }
        }
        parameters = option.method == "post" && parameters == null ? "" : parameters;
        if (option.cache == false) {
          var match = /\?/;
          url += (match.test(url) ? "&" : "?") + new Date().getTime();
        }
        var xhr = this._xhr;
        var temp = this;
        xhr.open(option.method, url, true);
        if (option.method == "post") {
          xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
          xhr.setRequestHeader("Accept", ajaxAccepts[option.Accept]);
          if (option.contentType && option.encoding) {
            xhr.setRequestHeader("Content-Type", option.contentType + "; charset=" + option.encoding);
          }
        }
        for (var prop in option.headers) {
          this._xhr.setRequestHeader(prop, option.headers[prop]);
        }
        temp.showLoading();
        xhr.send(parameters);
        xhr.onreadystatechange = function() {
          if (xhr.readyState == 4 && xhr.status == 200) {
            temp.hideLoading();
            if (temp.callback) {
              temp.callback(xhr);
            }
            window.clearTimeout(temp.calltimeout);
            _nextupdate();
          }
        };
        var _nextupdate = function() {
          temp.timeinterval = window.setTimeout(
            temp._getupdate.bind(temp),
            temp.interval
          );
        };
        this.calltimeout = window.setTimeout(function() {
          window.clearTimeout(temp.timeinterval);
          xhr.abort();
          _nextupdate();
        }, this.interval);
      }
    },
    getRequestBody: function(pForm) {
      pForm = $E(pForm);
      var nParams = [];
      forEach(pForm.getElementsByTagName("*"), function() {
        if (!this.disabled) {
          var t = this.tagName.toLowerCase();
          if (t == "input") {
            if (
              (this.checked == true && this.type == "radio") ||
              (this.checked == true && this.type == "checkbox") ||
              (this.type != "radio" && this.type != "checkbox")
            ) {
              nParams.push(this.name + "=" + this.value);
            }
          } else if (t == "select") {
            nParams.push(this.name + "=" + this.value);
          } else if (t == "textarea") {
            nParams.push(this.name + "=" + encodeURIComponent(this.innerHTML));
          }
        }
      });
      return nParams.join("&");
    },
    showLoading: function() {
      if (this.loading) {
        if (this.loading == "wait" && this.center == false) {
          if (this.loader == null) {
            this.loader = new GLoading();
          }
          this.loader.show();
        } else if ($E(this.loading)) {
          this.wait = $G(this.loading);
          if (this.center) {
            this.wait.center();
          }
          this.wait.addClass(this.options.loadingClass);
        }
      }
      return this;
    },
    hideLoading: function() {
      if (this.loading) {
        if (this.loader) {
          this.loader.hide();
        } else if (this.wait) {
          this.wait.removeClass(this.options.loadingClass);
        }
      }
      return this;
    },
    initLoading: function(loading, center, c) {
      this.loading = loading;
      this.center = center;
      if (c) {
        this.options.loadingClass = c;
      }
      return this;
    },
    abort: function() {
      clearTimeout(this.timeinterval);
      this._abort = true;
      return this;
    }
  };
  var gform_id = 0;
  window.GForm = GClass.create();
  GForm.prototype = {
    initialize: function(frm, frmaction, loading, center, onbeforesubmit) {
      frm = $G(frm);
      if (frmaction) {
        frm.set("action", frmaction);
      }
      this.loader = null;
      this.submitButton = frm.querySelector("[type=submit]");
      this.loading = loading || this.submitButton || "wait";
      this.center = center;
      this.onbeforesubmit = Object.isFunction(onbeforesubmit) ?
        onbeforesubmit :
        $K.resultFunction;
      var self = this;
      frm.onsubmit = function() {
        var loading = true;
        var ret = true;
        if (self.onbeforesubmit.call(this)) {
          forEach(this.querySelectorAll("input,textarea"), function(elem) {
            if (elem.srcObj) {
              var title = elem.srcObj.title,
                val = elem.value;
              if (elem.srcObj.required !== null && val == "") {
                title = (title !== "" ? trans("Please fill in") + " " + title : elem.placeholder == "" ? "" : elem.placeholder).strip_tags();
                alert(title);
                elem
                  .addClass("required")
                  .highlight()
                  .focus();
                ret = false;
                return true;
              } else if (elem.srcObj.pattern && val !== "" && !elem.srcObj.pattern.test(val)) {
                title = (title !== "" ? trans("Invalid data") + " " + title : elem.placeholder == "" ? "" : elem.placeholder).strip_tags();
                elem.invalid(title);
                alert(title);
                elem.highlight().focus();
                elem.select();
                ret = false;
                return true;
              } else {
                elem.reset();
              }
            }
          });
          if (ret && Object.isFunction(self.callback)) {
            self.showLoading();
            var uploadCallback = function() {
              if (!loading) {
                try {
                  self.responseText = io.contentWindow.document.body ? io.contentWindow.document.body.innerHTML : null;
                  self.responseXML = io.contentWindow.document.XMLDocument ? io.contentWindow.document.XMLDocument : io.contentWindow.document;
                } catch (e) {}
                self.hideLoading();
                self.form.method = old_method;
                self.form.target = old_target;
                if (self.form.encoding) {
                  self.form.encoding = old_enctype;
                } else {
                  self.form.enctype = old_enctype;
                }
                window.setTimeout(function() {
                  io.removeEvent("load", uploadCallback);
                  io.remove();
                }, 1);
                window.setTimeout(function() {
                  self.callback(self);
                }, 1);
              }
            };
            var io = self.createIframe();
            io.addEvent("load", uploadCallback);
            var old_target = this.target || "";
            var old_method = this.method || "post";
            var old_enctype = this.encoding ? this.encoding : this.enctype;
            if (this.encoding) {
              this.encoding = "multipart/form-data";
            } else {
              this.enctype = "multipart/form-data";
            }
            this.target = io.id;
            this.method = "post";
            window.setTimeout(function() {
              loading = false;
              frm.submit();
            }, 1);
            ret = false;
          }
        } else {
          ret = false;
        }
        return ret;
      };
      frm.GForm = this;
      this.form = frm;
      $K.init(frm);
    },
    onsubmit: function(callback) {
      this.callback = callback;
      return this;
    },
    submit: function(callback) {
      var loading = true;
      var self = this;
      this.showLoading();
      var uploadCallback = function() {
        if (!loading) {
          self.hideLoading();
          try {
            self.responseText = io.contentWindow.document.body ?
              io.contentWindow.document.body.innerHTML :
              null;
            self.responseXML = io.contentWindow.document.XMLDocument ?
              io.contentWindow.document.XMLDocument :
              io.contentWindow.document;
          } catch (e) {}
          self.form.method = old_method;
          self.form.target = old_target;
          window.setTimeout(function() {
            io.removeEvent("load", uploadCallback);
            io.remove();
          }, 1);
          window.setTimeout(function() {
            callback(self);
          }, 1);
        }
      };
      if (this.form.encoding) {
        this.form.encoding = "multipart/form-data";
      } else {
        this.form.enctype = "multipart/form-data";
      }
      var io = this.createIframe();
      io.addEvent("load", uploadCallback);
      var old_target = this.form.target || "";
      var old_method = this.form.method || "post";
      this.form.target = io.id;
      this.form.method = "post";
      window.setTimeout(function() {
        loading = false;
        self.form.submit();
      }, 1);
      return this;
    },
    createIframe: function() {
      var frameId =
        "GForm_Submit_" + gform_id + "_" + (this.form.id || this.form.name);
      gform_id++;
      var io = $G(document.body).create("iframe", {
        id: frameId,
        name: frameId
      });
      io.setStyle("position", "absolute");
      io.setStyle("top", "-1000px");
      io.setStyle("left", "-1000px");
      return io;
    },
    showLoading: function() {
      if (this.submitButton) {
        this.submitButton.disabled = true;
      }
      if (this.loading && $E(this.loading)) {
        this.loading = $G(this.loading);
        if (this.center) {
          this.loading.center();
        }
        this.loading.addClass("show");
      }
      return this;
    },
    hideLoading: function() {
      if (this.loading && $E(this.loading)) {
        this.loading.removeClass("show");
      } else if (this.loader) {
        this.loader.removeClass("wait");
      }
      if (this.submitButton) {
        this.submitButton.disabled = false;
      }
      return this;
    },
    initLoading: function(loading, center) {
      this.loading = loading;
      this.center = center;
      return this;
    }
  };
  window.GModal = GClass.create();
  GModal.prototype = {
    initialize: function(options) {
      this.id = "modaldiv";
      this.btnclose = "btnclose";
      this.backgroundClass = "modalbg";
      this.onhide = $K.emptyFunction;
      this.onclose = $K.emptyFunction;
      for (var property in options) {
        this[property] = options[property];
      }
      var self = this;
      var checkESCkey = function(e) {
        if (GEvent.keyCode(e) == 27 || e.key == "Escape" || e.key == "Esc") {
          self.hide();
          GEvent.stop(e);
        }
      };
      var container_div = "GModal_" + this.id;
      var doc = $G(document);
      doc.addEvent("keydown", checkESCkey);
      if (!$E(container_div)) {
        var div = doc.createElement("div");
        div.id = container_div;
        doc.body.appendChild(div);
        var c = doc.createElement("div");
        div.appendChild(c);
        c.className = this.id;
        var s = doc.createElement("span");
        div.appendChild(s);
        s.className = this.btnclose;
        s.title = trans("Close");
        s.onclick = function() {
          self.hide();
        };
      }
      this.div = $G(container_div);
      this.body = $G(this.div.firstChild);
    },
    content: function() {
      return this.body;
    },
    show: function(value, className) {
      this.body.setHTML(value);
      this.overlay();
      if (className) {
        this.div.className = className + " show";
      } else {
        this.div.className = "show";
      }
      var self = this;
      window.setTimeout(function() {
        var dm = self.body.getDimensions(),
          hOffset = dm.height - self.body.getClientHeight() + parseInt(self.body.getStyle("marginTop")) + parseInt(self.body.getStyle("marginBottom")) + 100,
          h = document.viewport.getHeight() - hOffset;
        if (dm.height > h) {
          self.div.style.height = h + "px";
        }
        self.div.center();
      }, 500);
      return this;
    },
    hide: function() {
      this.div.style.height = null;
      this.div.style.width = null;
      this.div.style.top = "-100%";
      this.div.className = "";
      var self = this;
      this.iframe.fadeOut(function() {
        self._hide.call(self);
      });
      return this;
    },
    overlay: function() {
      var frameId = "iframe_" + this.div.id,
        self = this;
      if (!$E(frameId)) {
        var io = $G(document.body).create("iframe", {
          id: frameId,
          height: "100%",
          frameBorder: 0
        });
        io.setStyle("position", "fixed");
        io.setStyle("zIndex", 999);
        io.className = this.backgroundClass;
        io.style.display = "none";
      }
      this.iframe = $G(frameId);
      if (this.iframe.style.display == "none") {
        this.iframe.style.left = "0px";
        this.iframe.style.top = "0px";
        this.iframe.style.display = "block";
        this.iframe.fadeIn();
        $G(self.iframe.contentWindow.document).addEvent("click", function(e) {
          self.hide();
        });
        var d = $G(document).getDimensions();
        this.iframe.style.height = d.height + "px";
        this.iframe.style.width = "100%";
      }
      return this;
    },
    _hide: function() {
      this.iframe.style.display = "none";
      this.body.innerHTML = "";
      if (Object.isFunction(this.onclose)) {
        this.onclose.call(this);
      }
    }
  };
  window.GFx = $K.emptyFunction;
  GFx.prototype = {
    _run: function() {
      this.playing = true;
      this.step();
    },
    stop: function() {
      this.playing = false;
      this.options.onComplete.call(this.Element);
    }
  };
  window.GScroll = GClass.create();
  GScroll.prototype = Object.extend(new GFx(), {
    initialize: function(container, scroller) {
      this.options = {
        speed: 20,
        duration: 1,
        pauseit: 1,
        scrollto: "top"
      };
      this.container = $G(container);
      this.scroller = $G(scroller);
      this.container.addEvent("mouseover", function() {
        this.rel = "pause";
      });
      this.container.addEvent("mouseout", function() {
        this.rel = "play";
      });
      this.container.rel = "play";
      this.playing = false;
    },
    play: function(options) {
      for (var property in options) {
        this.options[property] = options[property];
      }
      this.scrollerTop = 0;
      this.scrollerLeft = 0;
      this._run();
      return this;
    },
    step: function() {
      if (this.container.rel == "play" || this.options.pauseit != 1) {
        var size = this.container.getDimensions();
        if (this.options.scrollto == "bottom") {
          this.scrollerTop =
            this.scrollerTop > size.height ?
            0 - this.scroller.getHeight() :
            this.scrollerTop + this.options.duration;
          this.scroller.style.top = this.scrollerTop + "px";
        } else if (this.options.scrollto == "left") {
          this.scrollerLeft =
            this.scrollerLeft + this.scroller.getWidth() < 0 ?
            size.width :
            this.scrollerLeft - this.options.duration;
          this.scroller.style.left = this.scrollerLeft + "px";
        } else if (this.options.scrollto == "right") {
          this.scrollerLeft =
            this.scrollerLeft > size.width ?
            0 - this.scroller.getWidth() :
            this.scrollerLeft + this.options.duration;
          this.scroller.style.left = this.scrollerLeft + "px";
        } else {
          this.scrollerTop =
            this.scrollerTop + this.scroller.getHeight() < 0 ?
            size.height :
            this.scrollerTop - this.options.duration;
          this.scroller.style.top = this.scrollerTop + "px";
        }
      }
      this.timer = window.setTimeout(this.step.bind(this), this.options.speed);
    }
  });
  window.preload = GClass.create();
  preload.prototype = {
    initialize: function(img, onComplete) {
      var temp = new Image();
      if (img.src) {
        temp.src = img.src;
        temp.original = img;
      } else {
        temp.src = img;
      }
      var _preload = function() {
        if (temp.complete) {
          onComplete.call(temp);
        } else {
          window.setTimeout(_preload, 30);
        }
      };
      window.setTimeout(_preload, 30);
    }
  };
  window.GEvent = {
    isButton: function(e, code) {
      var button;
      e = window.event || e;
      if (e.which == null) {
        button = e.button < 2 ? 0 : e.button == 4 ? 1 : 2;
      } else {
        button = e.which < 2 ? 0 : e.which == 2 ? 1 : 2;
      }
      return button === code;
    },
    isLeftClick: function(e) {
      return GEvent.isButton(e, 0);
    },
    isMiddleClick: function(e) {
      return GEvent.isButton(e, 1);
    },
    isRightClick: function(e) {
      return GEvent.isButton(e, 2);
    },
    isCtrlKey: function(e) {
      return window.event ? window.event.ctrlKey : e.ctrlKey;
    },
    isShiftKey: function(e) {
      return window.event ? window.event.shiftKey : e.shiftKey;
    },
    isAltKey: function(e) {
      return window.event ? window.event.altKey : e.altKey;
    },
    element: function(e) {
      e = window.event || e;
      var node = e.target ? e.target : e.srcElement;
      return e.nodeType == 3 ? node.parentNode : node;
    },
    keyCode: function(e) {
      e = window.event || e;
      return e.which || e.keyCode;
    },
    stop: function(e) {
      e = window.event || e;
      if (e.stopPropagation) {
        e.stopPropagation();
      }
      e.cancelBubble = true;
      if (e.preventDefault) {
        e.preventDefault();
      }
      e.returnValue = false;
    },
    pointer: function(e) {
      e = window.event || e;
      return {
        x: e.pageX ||
          e.clientX +
          (document.documentElement.scrollLeft || document.body.scrollLeft),
        y: e.pageY ||
          e.clientY +
          (document.documentElement.scrollTop || document.body.scrollTop)
      };
    },
    pointerX: function(e) {
      return GEvent.pointer(e).x;
    },
    pointerY: function(e) {
      return GEvent.pointer(e).y;
    }
  };
  window.Cookie = {
    get: function(k) {
      var v = document.cookie.match(
        "(?:^|;)\\s*" + k.preg_quote() + "=([^;]*)"
      );
      return v ? decodeURIComponent(v[1]) : null;
    },
    set: function(k, v, options) {
      var _options = {
        path: false,
        domain: false,
        duration: false,
        secure: false
      };
      for (var property in options) {
        _options[property] = options[property];
      }
      v = encodeURIComponent(v);
      if (_options.domain) {
        v += "; domain=" + _options.domain;
      }
      if (_options.path) {
        v += "; path=" + _options.path;
      }
      if (_options.duration) {
        var date = new Date();
        date.setTime(date.getTime() + _options.duration * 24 * 60 * 60 * 1000);
        v += "; expires=" + date.toGMTString();
      }
      if (_options.secure) {
        v += "; secure";
      }
      document.cookie = k + "=" + v;
      return this;
    },
    remove: function(k) {
      Cookie.set(k, "", {
        duration: -1
      });
      return this;
    }
  };
  window.GLoading = GClass.create();
  GLoading.prototype = {
    initialize: function() {
      this.waittime = 0;
      this.loading = null;
    },
    show: function() {
      window.clearTimeout(this.waittime);
      if (this.loading == null && !$E("wait")) {
        var div = document.createElement("dl");
        div.id = "wait";
        div.innerHTML = "<dt></dt><dd></dd>";
        document.body.appendChild(div);
      }
      this.loading = $G("wait");
      this.loading.addClass("show");
      return this;
    },
    hide: function() {
      if (this.loading) {
        this.loading.replaceClass("show", "complete");
        var self = this;
        this.waittime = window.setTimeout(function() {
          self.loading.removeClass("wait show complete");
        }, 500);
      }
      return this;
    }
  };
  window.GValidator = GClass.create();
  GValidator.prototype = {
    initialize: function(input, events, validtor, action, callback, form) {
      this.timer = 0;
      this.req = new GAjax();
      this.interval = 1000;
      this.input = $G(input);
      this.input.Validator = this;
      this.title = this.input.get("title");
      this.validtor = validtor;
      this.action = action;
      this.callback = callback;
      this.form = form;
      var temp = this;
      if (form && form !== "") {
        form = $G(form);
        form.addEvent("submit", function() {
          temp.abort();
        });
      }
      forEach(events.split(","), function() {
        temp.input.addEvent(this, temp.validate.bind(temp));
      });
    },
    validate: function() {
      this.abort();
      var ret = Object.isFunction(this.validtor) ?
        this.validtor.call(this.input) :
        true;
      if (this.form && ret && this.action && ret !== "" && this.action !== "") {
        this.input.addClass("wait");
        var temp = this;
        this.timer = window.setTimeout(function() {
          temp.req.send(temp.action, ret, function(xhr) {
            temp.input.removeClass("wait");
            if (temp.callback) {
              ret = temp.callback.call(temp, xhr);
            } else {
              ret = xhr.responseText;
            }
            if (!ret || ret == "") {
              temp.valid();
            } else {
              try {
                ret = eval(ret);
              } catch (e) {}
              temp.invalid(ret);
            }
          });
        }, this.interval);
      }
    },
    abort: function() {
      window.clearTimeout(this.timer);
      this.req.abort();
      this.input.reset();
      return this;
    },
    interval: function(value) {
      this.interval = value;
      return this;
    },
    valid: function(className) {
      this.input.valid(className);
    },
    invalid: function(value, className) {
      this.input.invalid(value, className);
    },
    reset: function() {
      this.input.set("title", this.title);
      this.input.reset();
    }
  };
  window.GDrag = GClass.create();
  GDrag.prototype = {
    initialize: function(src, options) {
      this.options = {
        beginDrag: $K.emptyFunction,
        moveDrag: $K.emptyFunction,
        endDrag: $K.emptyFunction,
        srcOnly: true
      };
      for (var property in options) {
        this.options[property] = options[property];
      }
      this.src = $G(src);
      var self = this;

      function _mousemove(e) {
        self.mousePos = GEvent.pointer(e);
        self.options.moveDrag.call(self);
      }

      function cancelEvent(e) {
        GEvent.stop(e);
      }

      function _mouseup(e) {
        document.removeEvent("mouseup", _mouseup);
        document.removeEvent("mousemove", _mousemove);
        document.removeEvent("selectstart dragstart", cancelEvent);
        if (self.src.releaseCapture) {
          self.src.releaseCapture();
        }
        self.mousePos = GEvent.pointer(e);
        GEvent.stop(e);
        self.options.endDrag.call(self.src);
      }

      function _mousedown(e) {
        var delay,
          src = GEvent.element(e),
          temp = this;

        function _cancelClick() {
          window.clearTimeout(delay);
          this.removeEvent("mouseup", _cancelClick);
        }
        if ((!self.options.srcOnly || src == self.src) && GEvent.isLeftClick(e)) {
          GEvent.stop(e);
          self.mousePos = GEvent.pointer(e);
          if (this.setCapture) {
            this.setCapture();
          }
          delay = window.setTimeout(function() {
            document.addEvent("mouseup", _mouseup);
            document.addEvent("mousemove", _mousemove);
            document.addEvent("selectstart dragstart", cancelEvent);
            self.options.beginDrag.call(self);
          }, 100);
          temp.addEvent("mouseup", _cancelClick);
        } else if ($K.isMobile()) {
          src.callEvent('click');
        }
      }
      this.src.addEvent("mousedown", _mousedown);

      function touchHandler(e) {
        var touches = e.changedTouches,
          first = touches[0],
          type = "";
        switch (e.type) {
          case "touchstart":
            type = "mousedown";
            break;
          case "touchmove":
            type = "mousemove";
            break;
          case "touchend":
            type = "mouseup";
            break;
          default:
            return;
        }
        var simulatedEvent = document.createEvent("MouseEvent");
        simulatedEvent.initMouseEvent(
          type,
          true,
          false,
          window,
          1,
          first.screenX,
          first.screenY,
          first.clientX,
          first.clientY,
          false,
          false,
          false,
          false,
          0,
          null
        );
        first.target.dispatchEvent(simulatedEvent);
        e.preventDefault();
      }
      this.src.addEvent("touchstart touchmove touchend", touchHandler, false);
    }
  };
  window.GDragMove = GClass.create();
  GDragMove.prototype = {
    initialize: function(move_id, drag_id, options) {
      this.options = {
        beginDrag: $K.resultFunction,
        moveDrag: $K.resultFunction,
        endDrag: $K.emptyFunction,
        srcOnly: true
      };
      for (var property in options) {
        this.options[property] = options[property];
      }
      this.dragObj = $G(drag_id);
      this.dragObj.style.cursor = "move";
      this.moveObj = $G(move_id);
      var Hinstance = this;

      function _beginDrag() {
        if (
          Hinstance.options.beginDrag.call(Hinstance.moveObj, {
            mousePos: this.mousePos,
            mouseOffset: Hinstance.mouseOffset
          })
        ) {
          Hinstance.mouseOffset = {
            x: this.mousePos.x - Hinstance.moveObj.getStyle("left").toInt(),
            y: this.mousePos.y - Hinstance.moveObj.getStyle("top").toInt()
          };
        }
      }

      function _moveDrag() {
        if (
          Hinstance.options.moveDrag.call(Hinstance.moveObj, {
            mousePos: this.mousePos,
            mouseOffset: Hinstance.mouseOffset
          })
        ) {
          Hinstance.moveObj.style.top =
            this.mousePos.y - Hinstance.mouseOffset.y + "px";
          Hinstance.moveObj.style.left =
            this.mousePos.x - Hinstance.mouseOffset.x + "px";
        }
      }

      function _endDrag() {
        Hinstance.options.endDrag.call(Hinstance.moveObj, {
          mousePos: this.mousePos,
          mouseOffset: Hinstance.mouseOffset
        });
      }
      var o = {
        beginDrag: _beginDrag,
        moveDrag: _moveDrag,
        endDrag: _endDrag,
        srcOnly: this.options.srcOnly
      };
      new GDrag(this.dragObj, o);
    }
  };
  window.GTime = GClass.create();
  GTime.prototype = {
    initialize: function(id, onchanged) {
      this.input = $G(id);
      this.input.addClass("gtime ginput");
      this.onchanged = onchanged || $K.emptyFunction;
      this.firstKey = null;
      this.highlight = "";
      this.mouse_click = false;
      if ($K.isMobile()) {
        this.panel = new GDropdown(this.input);
        this.input.readOnly = true;
        $G(document.body).addEvent("click", function(e) {
          if (!$G(GEvent.element(e)).hasClass("ginput")) {
            self.panel.hide();
          }
        });
      }
      this.onchanged.call(this);
      var self = this;
      var doSetCaret = function() {
        if (self.input.readOnly) {
          self._draw();
        }
        var caret = self.input.getCaretPosition();
        if (caret.start == 5 && caret.end == 5) {
          caret.start = 0;
          caret.end = 2;
        }
        self._setCaret(caret.start);
        self.firstKey = null;
      };
      this.input.addEvent("focus", function() {
        if (self.mouse_click === false) {
          window.setTimeout(doSetCaret, 1);
        }
        self.mouse_click = false;
      });
      this.input.addEvent("click", doSetCaret);
      this.input.addEvent("paste", function(e) {
        GEvent.stop(e);
      });
      this.input.addEvent("keydown", function(e) {
        var key = GEvent.keyCode(e);
        var stop = false;
        if (key == 8) {
          self.input.value = "--:--";
          self._setCaret(0);
          self.firstKey = null;
          stop = true;
        } else if (key == 37) {
          self._setCaret(0);
          self.firstKey = null;
          stop = true;
        } else if (key == 38) {
          var times = self.input.value.split(":");
          var caret = 0;
          if (self.highlight == "hour") {
            var t = Math.min(23, floatval(times[0]) + 1);
            if (t < 10) {
              times[0] = "0" + t;
            } else {
              times[0] = t;
            }
          } else if (self.highlight == "minute") {
            var t = Math.min(59, floatval(times[1]) + 1);
            if (t < 10) {
              times[1] = "0" + t;
            } else {
              times[1] = t;
            }
            caret = 3;
          }
          self.input.value = times[0] + ":" + times[1];
          self._setCaret(caret);
          stop = true;
        } else if (key == 39) {
          self._setCaret(3);
          self.firstKey = null;
          stop = true;
        } else if (key == 40) {
          var times = self.input.value.split(":");
          var caret = 0;
          if (self.highlight == "hour") {
            var t = Math.max(0, floatval(times[0]) - 1);
            if (t < 10) {
              times[0] = "0" + t;
            } else {
              times[0] = t;
            }
          } else if (self.highlight == "minute") {
            var t = Math.max(0, floatval(times[1]) - 1);
            if (t < 10) {
              times[1] = "0" + t;
            } else {
              times[1] = t;
            }
            caret = 3;
          }
          self.input.value = times[0] + ":" + times[1];
          self._setCaret(caret);
          stop = true;
        }
        if (stop) {
          GEvent.stop(e);
          return false;
        }
      });
      new GMask(this.input, function(e) {
        if (/[0-9]/.test(e.key)) {
          self._set(floatval(e.key));
          return true;
        }
        return false;
      });
      this.input.value = this._toTime(this.input.value);
      this.input.time = this;
    },
    _set: function(c) {
      var times = this.input.value.split(":");
      var caret = 0;
      if (this.highlight == "hour") {
        if (this.firstKey == null) {
          times[0] = "0" + c;
          if (c < 3) {
            this.firstKey = c;
            caret = 0;
          } else {
            caret = 3;
            this.firstKey = null;
          }
        } else {
          c = floatval(String(this.firstKey) + c);
          times[0] = c < 10 ? "0" + c : String(Math.min(23, c));
          caret = 3;
          this.firstKey = null;
        }
      } else if (this.highlight == "minute") {
        if (this.firstKey == null) {
          times[1] = "0" + c;
          this.firstKey = c;
        } else {
          c = floatval(String(this.firstKey) + c);
          times[1] = c < 10 ? "0" + c : String(Math.min(59, c));
          this.firstKey = null;
        }
        caret = 3;
      }
      this.input.value = (times[0] == "" ? "--" : times[0]) + ":" + (times[1] || "--");
      this._setCaret(caret);
    },
    getTime: function() {
      return this.input.value == "--:--" ? "" : this.input.value + ":00";
    },
    setTime: function(time) {
      this.input.value = this._toTime(time);
      this.onchanged.call(this);
      return this;
    },
    _toTime: function(time) {
      if (time) {
        time = /([0-9]{1,2})(:([0-9]{1,2}))?(:([0-9]{1,2}))?/.exec(time);
        if (time) {
          var h = Math.min(23, floatval(time[1]));
          var m = Math.min(59, floatval(time[3]));
          return (h < 10 ? "0" + h : h) + ":" + (m < 10 ? "0" + m : m);
        }
      }
      return "--:--";
    },
    _setCaret: function(pos) {
      if (pos < 3) {
        this.input.setCaretPosition(0, 2);
        this.highlight = "hour";
      } else {
        this.input.setCaretPosition(3, 2);
        this.highlight = "minute";
      }
    },
    _draw: function() {
      var dropdown = this.panel.getDropdown(),
        panel = document.createElement('div');
      self = this;
      dropdown.innerHTML = "";
      panel.className = 'ginput';
      dropdown.appendChild(panel);
      forEach(["1", "2", "3", "4", "5", "6", "7", "8", "9", "0", "&crarr;", "&lArr;"], function() {
        var a = document.createElement("a");
        a.innerHTML = this;
        if (this == "&crarr;") {
          a.className = "enter";
        } else if (this == "&lArr;") {
          a.className = "backspace";
        }
        panel.appendChild(a);
        $G(a).addEvent("click", function(e) {
          var elem = GEvent.element(e);
          if (elem.className == "enter") {
            self.panel.hide();
          } else if (elem.className == "backspace") {
            self.input.value = "--:--";
            self._setCaret(0);
            self.firstKey = null;
          } else {
            self._set(floatval(elem.innerHTML));
          }
          self.mouse_click = true;
          self.input.focus();
          GEvent.stop(e);
          return false;
        });
      });
      this.panel.show();
    }
  };
  window.GMask = GClass.create();
  GMask.prototype = {
    initialize: function(id, onkeypress) {
      var input = $G(id),
        tmp = this;
      this.maxlength = floatval(input.maxlength);
      this.disabled = input.disabled;
      this.required = input.required;
      if (input.min) {
        this.min = floatval(input.min);
      }
      if (input.max) {
        this.max = floatval(input.max);
      }
      if (input.pattern) {
        this.pattern = new RegExp(input.pattern);
        input.setAttribute("pattern", "(.*){0,}");
      }

      function checkKey(e) {
        if (e.key) {
          return /^(Backspace|(Arrow)?Left|(Arrow)?Right|Enter|Tab|Delete)$/.test(
            e.key
          );
        } else {
          var key = GEvent.keyCode(e);
          if (
            key == 8 ||
            key == 37 ||
            key == 39 ||
            key == 13 ||
            key == 9 ||
            key == 46
          ) {
            return true;
          } else {
            return false;
          }
        }
      }
      var doActive = function(e) {
        var el = e.target;
        tmp.oldCursor = el.getCaretPosition();
        tmp.key = false;
        tmp.oldValue = el.value;
      };
      var doKeydown = function(e) {
        var el = e.target;
        tmp.oldCursor = el.getCaretPosition();
        tmp.key = checkKey(e);
        tmp.oldValue = el.value;
        if (
          tmp.maxlength > 0 &&
          !tmp.key &&
          !GEvent.isCtrlKey(e) &&
          tmp.oldCursor.start == tmp.oldCursor.end
        ) {
          if (el.value.length >= tmp.maxlength) {
            GEvent.stop(e);
            return false;
          }
        }
      };
      var doInput = function(e) {
        var ret = true,
          el = e.target,
          value = el.value;
        if (value != "" && !tmp.key && !GEvent.isCtrlKey(e)) {
          e.key = value.substr(tmp.oldCursor.start, 1);
          if (Object.isFunction(onkeypress)) {
            ret = onkeypress.call(el, e);
          } else if (tmp.pattern) {
            ret = tmp.pattern.test(value);
          }
          if (tmp.maxlength > 0 && value.length > tmp.maxlength) {
            el.value = value.substr(0, tmp.maxlength);
            el.setSelectionRange(tmp.oldCursor.end, tmp.oldCursor.end);
          } else if (!ret) {
            el.value = tmp.oldValue;
            el.setSelectionRange(tmp.oldCursor.end, tmp.oldCursor.end);
          }
        }
      };
      input.addEvent("focus", doActive);
      input.addEvent("keydown", doKeydown);
      input.addEvent("input", doInput);
    }
  };
  window.GInput = GClass.create();
  GInput.prototype = {
    initialize: function(id, inputchar, onchanged) {
      this.input = $G(id);
      this.input.addClass("ginput");
      this.keyboard = inputchar.split("");
      this.keyboard.push("&crarr;");
      this.keyboard.push("&lArr;");
      this.onchanged = onchanged || $K.emptyFunction;
      this.maxlength = floatval(this.input.maxlength);
      var self = this;
      if ($K.isMobile()) {
        this.panel = new GDropdown(this.input);
        this.input.readOnly = true;
        this.input.addEvent("click", function(e) {
          self.input.setCaretPosition(self.input.value.length, 1);
          self._draw();
          GEvent.stop(e);
          return false;
        });
        $G(document.body).addEvent("click", function(e) {
          if (!$G(GEvent.element(e)).hasClass("ginput")) {
            self.panel.hide();
            self._dochanged();
            if (self.panel.input) {
              if (self.panel.input_value != self.panel.input.value) {
                self.panel.input.callEvent("change");
              }
              self.panel.input = null;
            }
          }
        });
      } else {
        this.input.addEvent("focus", function() {
          self.input.select();
        });
      }
      new GMask(this.input, function(e) {
        return self.keyboard.indexOf(e.key) > -1;
      });
      this.input.addEvent("change", function() {
        self._dochanged();
      });
      this._dochanged();
    },
    _dochanged: function() {
      this.onchanged.call(this.input);
    },
    _draw: function() {
      var dropdown = this.panel.getDropdown(),
        self = this,
        panel = document.createElement('div');
      dropdown.innerHTML = "";
      panel.className = 'ginput';
      dropdown.appendChild(panel);
      forEach(this.keyboard, function() {
        var a = document.createElement("a");
        a.innerHTML = this;
        if (this == "&crarr;") {
          a.className = "enter";
        } else if (this == "&lArr;") {
          a.className = "backspace";
        }
        panel.appendChild(a);
        $G(a).addEvent("click", function(e) {
          var elem = GEvent.element(e);
          if (elem.className == "backspace") {
            if (document.selection) {
              self.input.focus();
              document.selection.empty();
            } else {
              var text = self.input.value,
                startPos = self.input.selectionStart,
                endPos = self.input.selectionEnd;
              if ((startPos > 0 && endPos > 0) || endPos > startPos) {
                startPos = startPos == endPos ? startPos - 1 : startPos;
                text = text.slice(0, startPos) + text.slice(endPos);
                self.input.value = text;
                self.input.selectionStart = startPos;
                self.input.selectionEnd = startPos;
              }
              self.input.focus();
            }
            GEvent.stop(e);
          } else if (elem.className != "enter") {
            var text = this.innerHTML;
            self.input.focus();
            if (document.selection) {
              sel.setSelectedText(text);
            } else if (
              self.input.selectionStart ||
              self.input.selectionStart === 0
            ) {
              var startPos = self.input.selectionStart,
                endPos = self.input.selectionEnd,
                value =
                self.input.value.substring(0, startPos) +
                text +
                self.input.value.substring(endPos, self.input.value.length);
              if (self.maxlength == 0 || value.length <= self.maxlength) {
                self.input.value = value;
                self.input.selectionStart = startPos + text.length;
                self.input.selectionEnd = startPos + text.length;
              } else {
                self.input.selectionStart = startPos;
                self.input.selectionEnd = endPos;
              }
            } else {
              text = self.input.value + text;
              if (self.maxlength == 0 || text.length <= self.maxlength) {
                self.input.value = text;
              }
            }
            GEvent.stop(e);
          }
        });
      });
      this.panel.show();
      this.panel.input = this.input;
      this.panel.input_value = this.input.value;
    }
  };

  window.GDropdown = GClass.create();
  GDropdown.prototype = {
    initialize: function(src, o) {
      this.src = src;
      this.options = {
        autoHeight: false,
        id: 'gdropdown',
        className: 'gdropdown'
      };
      for (var prop in o) {
        this.options[prop] = o[prop];
      }
      if (!$E(this.options.id)) {
        var div = document.createElement("div");
        document.body.appendChild(div);
        div.id = this.options.id;
      }
      this.dropdown = $G(this.options.id);
      this.dropdown.style.position = "absolute";
      this.dropdown.style.display = "none";
      this.dropdown.style.zIndex = 1001;
    },
    getDropdown: function() {
      return this.dropdown;
    },
    show: function() {
      if (this.options.className) {
        this.dropdown.classList.add(this.options.className);
      }
      var vpo = this.src.viewportOffset(),
        input_height = this.src.getHeight(),
        dm = this.dropdown.getDimensions(),
        scrolltop = document.viewport.getscrollTop(),
        doc_height = document.viewport.getHeight(),
        top_space = vpo.top - scrolltop,
        bottom_space = doc_height - top_space - input_height;
      if (this.options.autoHeight) {
        var space = Math.max(top_space, bottom_space);
        if (dm.height > space) {
          this.dropdown.style.height = (space - 10) + 'px';
          dm = this.dropdown.getDimensions();
        }
      }
      if (top_space >= bottom_space) {
        this.dropdown.style.top = Math.max(vpo.top - dm.height - 5, 0) + "px";
      } else {
        this.dropdown.style.top = (vpo.top + input_height + 5) + "px";
      }
      var l = Math.max(
        vpo.left + dm.width > document.viewport.getWidth() ?
        vpo.left + this.src.getWidth() - dm.width :
        vpo.left, document.viewport.getscrollLeft() + 5
      );
      this.dropdown.style.left = l + "px";
      this.dropdown.style.display = "block";
    },
    hide: function() {
      if (this.options.className) {
        this.dropdown.classList.remove(this.options.className);
      }
      this.dropdown.style.display = "none";
      this.dropdown.style.height = 'auto';
    },
    showing: function() {
      return this.dropdown.style.display == "block";
    }
  };

  window.GCalendar = GClass.create();
  GCalendar.prototype = {
    initialize: function(elem) {
      elem = $E(elem);
      this.input = document.createElement("div");
      this.input.id = elem.id;
      this.input.className = 'input-gcalendar input-select';
      this.input.tabIndex = 0;
      this.input.style.cursor = "pointer";
      elem.parentNode.appendChild(this.input);
      this.display = document.createElement("div");
      this.display.id = elem.id + "_display";
      this.input.appendChild(this.display);
      this.display.innerHTML = 'Please';
      this.hidden = document.createElement("input");
      this.hidden.type = 'hidden';
      this.hidden.name = elem.name;
      this.input.appendChild(this.hidden);
      this.placeholder = document.createElement("div");
      this.placeholder.className = "placeholder";
      this.placeholder.style.position = "absolute";
      this.input.appendChild(this.placeholder);
      this.calendar = new GDropdown(this.input);
      this.mdate = null;
      this.xdate = null;
      this.mode = 0;
      this.displayFormat = 'd M Y';
      this.format = 'y-m-d';
      this.date = null;
      this.cdate = new Date();
      var self = this;
      $G(this.input).addEvent("click", function(e) {
        self.mode = 0;
        self.cdate.setTime(self.date ? self.date.valueOf() : new Date());
        self._draw();
        GEvent.stop(e);
        return false;
      });
      this.input.addEvent("keydown", function(e) {
        var key = GEvent.keyCode(e);
        if (key == 9) {
          self.calendar.hide();
        } else if (key == 32) {
          self._toogle(e);
        } else if (key == 37 || key == 39) {
          self.moveDate(key == 39 ? 1 : -1);
          self._draw();
          GEvent.stop(e);
        } else if (key == 38 || key == 40) {
          if (GEvent.isShiftKey(e)) {
            self.moveYear(key == 40 ? 1 : -1);
          } else if (GEvent.isCtrlKey(e)) {
            self.moveMonth(key == 40 ? 1 : -1);
          } else {
            self.moveDate(key == 40 ? 7 : -7);
          }
          self._draw();
          GEvent.stop(e);
        } else if (key == 8 && self.input.hasClass('readonly disabled') == false) {
          self.setDate(null);
          GEvent.stop(e);
        } else {
          GEvent.stop(e);
        }
      });
      $G(document.body).addEvent("click", function(e) {
        if (!(
            $G(GEvent.element(e)).hasClass("input-gcalendar") ||
            $G(GEvent.element(e).parentNode).hasClass("input-gcalendar")
          )) {
          self.calendar.hide();
        }
      });
      Object.defineProperty(this.input, 'min', {
        get: function() {
          return self.mdate;
        },
        set: function(value) {
          self.minDate(value);
        }
      });
      Object.defineProperty(this.input, 'max', {
        get: function() {
          return self.xdate;
        },
        set: function(value) {
          self.maxDate(value);
        }
      });
      Object.defineProperty(this.input, 'placeholder', {
        get: function() {
          return self.placeholder.innerHTML;
        },
        set: function(value) {
          self.placeholder.innerHTML = value;
        }
      });
      Object.defineProperty(this.input, 'text', {
        get: function() {
          return self.display.innerHTML;
        },
        set: function(value) {
          self.display.innerHTML = value;
        }
      });
      Object.defineProperty(this.input, 'value', {
        get: function() {
          return self.getDateFormat(self.format);
        },
        set: function(value) {
          self.setDate(value);
        }
      });
      Object.defineProperty(this.input, 'disabled', {
        get: function() {
          return self.input.hasClass('disabled') ? true : false;
        },
        set: function(value) {
          if (value) {
            self.input.addClass('disabled');
          } else {
            self.input.removeClass('disabled');
          }
        },
      });
      Object.defineProperty(this.input, 'readOnly', {
        get: function() {
          return self.input.hasClass('readonly') ? true : false;
        },
        set: function(value) {
          if (value) {
            self.input.addClass('readonly');
          } else {
            self.input.removeClass('readonly');
          }
        },
      });
      this.input.calendar = this;
      if (elem.min) {
        this.input.min = elem.min;
      }
      if (elem.max) {
        this.input.max = elem.max;
      }
      if (elem.disabled) {
        this.input.disabled = true;
      }
      if (elem.readOnly) {
        this.input.readOnly = true;
      }
      this.input.placeholder = elem.placeholder;
      elem.parentNode.removeChild(elem);
      this.input.value = elem.value;
    },
    _dochanged: function() {
      if (this.xdate && this.date && this.date > this.xdate) {
        this.date.setTime(this.xdate.valueOf());
      } else if (this.mdate && this.date && this.date < this.mdate) {
        this.date.setTime(this.mdate.valueOf());
      }
      if (this.date) {
        this.cdate.setTime(this.date.valueOf());
        this.display.innerHTML = this.date.format(this.displayFormat);
        this.hidden.value = this.date.format(this.format);
      } else {
        this.cdate.setTime(new Date());
        this.display.innerHTML = '';
        this.hidden.value = '';
      }
      this.placeholder.style.display = this.hidden.value == '' && this.placeholder.innerHTML != '' ? "block" : "none";
      var self = this;
      window.setTimeout(function() {
        self.input.callEvent('change');
      });
    },
    _toogle: function(e) {
      if (this.calendar.showing()) {
        this.calendar.hide();
      } else {
        this.mode = 0;
        if (this.date) {
          this.cdate.setTime(this.date.valueOf());
        } else {
          this.cdate.setTime(new Date());
        }
        this._draw();
      }
      GEvent.stop(e);
    },
    _draw: function() {
      if (this.input.hasClass('readonly disabled') == false) {
        var self = this,
          calendar = this.calendar.getDropdown();
        calendar.innerHTML = "";
        var div = document.createElement("div");
        calendar.appendChild(div);
        div.className = "gcalendar";
        var p = document.createElement("p");
        div.appendChild(p);
        var a = document.createElement("a");
        p.appendChild(a);
        a.innerHTML = "&larr;";
        a.style.cursor = "pointer";
        $G(a).addEvent("click", function(e) {
          self._move(e, -1);
          GEvent.stop(e);
          return false;
        });
        if (this.mode < 2) {
          a = document.createElement("a");
          a.innerHTML = this.cdate.format(this.mode == 1 ? "Y" : "M Y");
          $G(a).addEvent("click", function(e) {
            self.mode++;
            self._draw();
            GEvent.stop(e);
            return false;
          });
        } else {
          var start_year = this.cdate.getFullYear() - 6;
          a = document.createElement("span");
          a.appendChild(
            document.createTextNode(
              start_year +
              Date.yearOffset +
              "-" +
              (start_year + 11 + Date.yearOffset)
            )
          );
        }
        p.appendChild(a);
        a.style.cursor = "pointer";
        a = document.createElement("a");
        p.appendChild(a);
        a.innerHTML = "&rarr;";
        a.style.cursor = "pointer";
        $G(a).addEvent("click", function(e) {
          self._move(e, 1);
          GEvent.stop(e);
          return false;
        });
        var table = document.createElement("table");
        div.appendChild(table);
        var thead = document.createElement("thead");
        table.appendChild(thead);
        var tbody = document.createElement("tbody");
        table.appendChild(tbody);
        var intmonth = this.cdate.getMonth() + 1;
        var intyear = this.cdate.getFullYear();
        var cls = "";
        var today = new Date();
        var today_month = today.getMonth() + 1;
        var today_year = today.getFullYear();
        var today_date = today.getDate();
        var sel_month = this.date ? this.date.getMonth() + 1 : today_month;
        var sel_year = this.date ? this.date.getFullYear() : today_year;
        var sel_date = this.date ? this.date.getDate() : today_date;
        var r = 0;
        var c = 0;
        var bg, row, cell;
        if (this.mode == 2) {
          for (var i = start_year; i < start_year + 12; i++) {
            c = (i - start_year) % 4;
            if (c == 0) {
              row = tbody.insertRow(r);
              bg = bg == "bg1" ? "bg2" : "bg1";
              row.className = "gcalendar_" + bg;
              r++;
            }
            cell = row.insertCell(c);
            cls = "month";
            if (i == sel_year) {
              cls = cls + " select";
            }
            if (i == today_year) {
              cls = cls + " today";
            }
            cell.className = cls;
            cell.appendChild(document.createTextNode(i + Date.yearOffset));
            cell.oDate = new Date(i, 1, 1, 12, 0, 0, 0);
            $G(cell).addEvent("click", function(e) {
              self.cdate.setTime(this.oDate.valueOf());
              self.mode--;
              self._draw();
              GEvent.stop(e);
              return false;
            });
          }
        } else if (this.mode == 1) {
          forEach(Date.monthNames, function(month, i) {
            c = i % 4;
            if (c == 0) {
              row = tbody.insertRow(r);
              bg = bg == "bg1" ? "bg2" : "bg1";
              row.className = "gcalendar_" + bg;
              r++;
            }
            cell = row.insertCell(c);
            cls = "month";
            if (intyear == sel_year && i + 1 == sel_month) {
              cls = cls + " select";
            }
            if (intyear == today_year && i + 1 == today_month) {
              cls = cls + " today";
            }
            cell.className = cls;
            cell.appendChild(document.createTextNode(month));
            cell.oDate = new Date(intyear, i, 1, 0, 0, 0, 0);
            $G(cell).addEvent("click", function(e) {
              self.cdate.setTime(this.oDate.valueOf());
              self.mode--;
              self._draw();
              GEvent.stop(e);
              return false;
            });
          });
        } else {
          row = thead.insertRow(0);
          forEach(Date.dayNames, function(item, i) {
            cell = document.createElement("th");
            row.appendChild(cell);
            cell.appendChild(document.createTextNode(item));
          });
          var tmp_prev_month = intmonth - 1;
          var tmp_next_month = intmonth + 1;
          var tmp_next_year = intyear;
          var tmp_prev_year = intyear;
          if (tmp_prev_month == 0) {
            tmp_prev_month = 12;
            tmp_prev_year--;
          }
          if (tmp_next_month == 13) {
            tmp_next_month = 1;
            tmp_next_year++;
          }
          var initial_day = 1;
          var tmp_init = new Date(intyear, intmonth, 1, 0, 0, 0, 0).dayOfWeek();
          var max_prev = new Date(tmp_prev_year, tmp_prev_month, 0, 0, 0, 0, 0).daysInMonth();
          var max_this = new Date(intyear, intmonth, 0, 0, 0, 0, 0).daysInMonth();
          if (tmp_init !== 0) {
            initial_day = max_prev - (tmp_init - 1);
          }
          tmp_next_year = tmp_next_year.toString();
          tmp_prev_year = tmp_prev_year.toString();
          tmp_next_month = tmp_next_month.toString();
          tmp_prev_month = tmp_prev_month.toString();
          var pointer = initial_day;
          var flag_init = initial_day == 1 ? 1 : 0;
          var tmp_month = initial_day == 1 ? intmonth : parseInt(tmp_prev_month);
          var tmp_year = initial_day == 1 ? intyear : parseInt(tmp_prev_year);
          var flag_end = 0;
          r = 0;
          for (var x = 0; x < 42; x++) {
            if (tmp_init !== 0 && pointer > max_prev && flag_init == 0) {
              flag_init = 1;
              pointer = 1;
              tmp_month = intmonth;
              tmp_year = intyear;
            }
            if (flag_init == 1 && flag_end == 0 && pointer > max_this) {
              flag_end = 1;
              pointer = 1;
              tmp_month = parseInt(tmp_next_month);
              tmp_year = parseInt(tmp_next_year);
            }
            c = x % 7;
            if (c == 0) {
              row = tbody.insertRow(r);
              r++;
            }
            cell = row.insertCell(c);
            cell.oDate = new Date(tmp_year, tmp_month - 1, pointer, 0, 0, 0, 0);
            cell.title = cell.oDate.format(self.displayFormat);
            cell.appendChild(document.createTextNode(pointer));
            var canclick = true;
            if (this.mdate !== null && this.xdate !== null) {
              canclick = cell.oDate >= this.mdate && cell.oDate <= this.xdate;
            } else if (this.mdate !== null) {
              canclick = cell.oDate >= this.mdate;
            } else if (this.xdate !== null) {
              canclick = cell.oDate <= this.xdate;
            }
            if (canclick) {
              cls = tmp_month == intmonth ? "curr" : "ex";
            } else {
              cls = "ex";
            }
            $G(cell).addEvent("click", function(e) {
              var c = this.hasClass('curr ex');
              if (c == 'curr') {
                if (self.date === null) {
                  self.date = new Date();
                }
                self.date.setTime(this.oDate.valueOf());
              } else if (c == 'ex') {
                self.date = null;
              }
              self._dochanged();
              $E(self.input).focus();
            });
            if (tmp_year == sel_year && tmp_month == sel_month && pointer == sel_date) {
              cls = cls + " select";
            }
            if (tmp_year == today_year && tmp_month == today_month && pointer == today_date) {
              cls = cls + " today";
            }
            cell.className = cls;
            pointer++;
          }
        }
        this.calendar.show();
      }
    },
    _move: function(e, value) {
      if (this.mode == 2) {
        this.cdate.setFullYear(this.cdate.getFullYear() + value * 12);
      } else if (this.mode == 1) {
        this.cdate.setFullYear(this.cdate.getFullYear() + value);
      } else {
        this.cdate.setMonth(this.cdate.getMonth() + value);
      }
      this._draw();
      GEvent.stop(e);
    },
    moveDate: function(day) {
      if (this.date === null) {
        this.date = new Date();
      }
      this.date.setDate(this.date.getDate() + day);
      this._dochanged();
      return this;
    },
    moveMonth: function(month) {
      if (this.date === null) {
        this.date = new Date();
      }
      this.date.setMonth(this.date.getMonth() + month);
      this._dochanged();
      return this;
    },
    moveYear: function(year) {
      if (this.date === null) {
        this.date = new Date();
      }
      this.date.setFullYear(this.date.getFullYear() + year);
      this._dochanged();
      return this;
    },
    setFormat: function(value) {
      this.format = value;
      this._dochanged();
      return this;
    },
    setDisplayFormat: function(value) {
      this.displayFormat = value;
      this._dochanged();
      return this;
    },
    setDate: function(date) {
      if (date === null || !/[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}(\s[0-9:]+)?/.test(date)) {
        this.date = null;
      } else {
        this.date = this._toDate(date);
      }
      this._dochanged();
      return this;
    },
    getDate: function() {
      if (this.date) {
        var d = new Date();
        d.setTime(this.date.valueOf());
        return d;
      }
      return null;
    },
    getDateFormat: function(format) {
      if (this.date) {
        format = format || this.format;
        return this.getDate().format(format);
      }
      return null;
    },
    minDate: function(date) {
      if (date === null) {
        this.mdate = null;
      } else if (Object.isString(date) && date === '') {
        if (this.mdate == null) {
          this.mdate = new Date();
        }
        this.mdate.setTime(this.date ? this.date.valueOf() : new Date()).setHours(0, 0, 0);
      } else {
        this.mdate = this._toDate(date).setHours(0, 0, 0);
      }
      return this;
    },
    maxDate: function(date) {
      if (date === null) {
        this.xdate = null;
      } else if (Object.isString(date) && date === '') {
        if (this.xdate == null) {
          this.xdate = new Date();
        }
        this.xdate.setTime(this.date ? this.date.valueOf() : new Date()).setHours(23, 59, 59);
      } else {
        this.xdate = this._toDate(date).setHours(23, 59, 59);
      }
      return this;
    },
    setText: function(value) {
      this.display.innerHTML = value;
    },
    _toDate: function(date) {
      var d = null;
      if (Object.isString(date)) {
        d = date.toDate();
        d = d == null ? new Date() : d;
      } else {
        d = new Date();
        if (!Object.isNull(date)) {
          d.setTime(date.valueOf());
        }
      }
      return d;
    }
  };
  window.GFxZoom = GClass.create();
  GFxZoom.prototype = Object.extend(new GFx(), {
    initialize: function(elem, options) {
      this.options = {
        duration: 2,
        speed: 1,
        offset: 0,
        fitdoc: true,
        onComplete: $K.emptyFunction,
        onResize: $K.emptyFunction
      };
      for (var property in options) {
        this.options[property] = options[property];
      }
      this.options.duration = this.options.duration > 8 ? 8 : this.options.duration;
      this.options.duration -= this.options.duration % 2 == 0 ? 0 : 1;
      this.Player = $G(elem);
      this.Player.style.zIndex = 9999999;
      var tmp = this.Player.viewportOffset();
      this.t = tmp.top;
      this.l = tmp.left;
      tmp = this.Player.getDimensions();
      this.w = tmp.width;
      this.h = tmp.height;
    },
    play: function(dw, dh, dl, dt) {
      var cw = document.viewport.getWidth();
      var ch = document.viewport.getHeight();
      if (this.options.fitdoc) {
        if (dw > cw) {
          dh = Math.round((cw * dh) / dw);
          dw = cw;
        }
        if (dh > ch) {
          dw = Math.round((ch * dw) / dh);
          dh = ch;
        }
        dw = dw - this.options.offset;
        dh = dh - this.options.offset;
      }
      this.dw = dw;
      this.dh = dh;
      if (dl == null) {
        dl = document.viewport.getscrollLeft() + (cw - dw) / 2;
      }
      if (dt == null) {
        dt = document.viewport.getscrollTop() + (ch - dh) / 2;
      }
      this.lStep = (dl - this.l) / 2 / this.options.duration;
      this.tStep = (dt - this.t) / 2 / this.options.duration;
      this.wStep = (dw - this.w) / 2 / this.options.duration;
      this.hStep = (dh - this.h) / 2 / this.options.duration;
      this.timer = window.setInterval(this.step.bind(this), this.options.speed);
      this.options.onResize.call(this);
    },
    step: function() {
      if (this.w != this.dw || this.h != this.dh) {
        this.l += this.lStep;
        this.t += this.tStep;
        this.w += this.wStep;
        this.h += this.hStep;
        this.Player.style.left = this.l + "px";
        this.Player.style.top = this.t + "px";
        this.Player.style.width = this.w + "px";
        this.Player.style.height = this.h + "px";
        this.options.onResize.call(this);
      } else {
        this.stop();
      }
    },
    stop: function() {
      window.clearInterval(this.timer);
      this.options.onComplete.call(this);
    }
  });
  window.Color = GClass.create();
  Color.prototype = {
    initialize: function(value) {
      if (Array.isArray(value)) {
        this.r = value[0];
        this.g = value[1];
        this.b = value[2];
        this.a = value.length > 3 ? value[3] : null;
      } else {
        var rgb = /#?([a-zA-Z0-9]{1,2})([a-zA-Z0-9]{1,2})([a-zA-Z0-9]{1,2})([a-zA-Z0-9]{0,2})$/.exec(
          value
        );
        if (rgb) {
          this.r =
            rgb[1].length == 2 ?
            parseInt(rgb[1], 16) :
            parseInt(rgb[1] + rgb[1], 16);
          this.g =
            rgb[2].length == 2 ?
            parseInt(rgb[2], 16) :
            parseInt(rgb[2] + rgb[2], 16);
          this.b =
            rgb[3].length == 2 ?
            parseInt(rgb[3], 16) :
            parseInt(rgb[3] + rgb[3], 16);
          this.a =
            rgb[4] == "" ?
            null :
            rgb[4].length == 2 ?
            parseInt(rgb[4], 16) :
            parseInt(rgb[4] + rgb[4], 16);
        } else {
          this.r = 0;
          this.g = 0;
          this.b = 0;
          this.a = null;
        }
      }
    },
    darken: function(amount) {
      return new Color([
        Math.max(0, Math.round(this.r - amount)),
        Math.max(0, Math.round(this.g - amount)),
        Math.max(0, Math.round(this.b - amount)),
        this.a
      ]);
    },
    lighten: function(amount) {
      return new Color([
        Math.min(255, Math.round(this.r + amount)),
        Math.min(255, Math.round(this.g + amount)),
        Math.min(255, Math.round(this.b + amount)),
        this.a
      ]);
    },
    invert: function() {
      return new Color([
        this.r > 128 ? 0 : 255,
        this.g > 128 ? 0 : 255,
        this.b > 128 ? 0 : 255,
        this.a
      ]);
    },
    toString: function() {
      return (
        "#" +
        this.r
        .toString(16)
        .toUpperCase()
        .leftPad(2, "0") +
        this.g
        .toString(16)
        .toUpperCase()
        .leftPad(2, "0") +
        this.b
        .toString(16)
        .toUpperCase()
        .leftPad(2, "0") +
        (this.a !== null && this.a !== 1 ?
          this.a
          .toString(16)
          .toUpperCase()
          .leftPad(2, "0") :
          "")
      );
    },
    toRGB: function() {
      return this.a !== null && this.a !== 1 ?
        "rgba(" + this.r + ", " + this.g + ", " + this.b + ", " + this.a + ")" :
        "rgb(" + this.r + ", " + this.g + ", " + this.b + ")";
    },
    toArray: function() {
      return [this.r, this.g, this.b, this.a];
    }
  };
  window.GDDColor = GClass.create();
  GDDColor.prototype = {
    initialize: function(id, onchanged) {
      this.Colors = Array(
        "B71C1C",
        "880E4F",
        "4A148C",
        "311B92",
        "1A237E",
        "0D47A1",
        "304FFE",
        "01579B",
        "2387CA",
        "006064",
        "004D40",
        "1B5E20",
        "33691E",
        "827717",
        "FFD600",
        "FF6F00",
        "E65100",
        "BF360C",
        "3E2723",
        "263238",
        "FFFFFF",
        "000000",
        "T",
        "C"
      );
      this.cols = 6;
      this.input = $G(id);
      this.input.addClass("gddcolor");
      if ($K.isMobile()) {
        this.input.readOnly = true;
      }
      this.onchanged = onchanged || $K.emptyFunction;
      this.color = "";
      this.color_format = /^((transparent)|(\#[0-9a-fA-F]{6,6}))$/i;
      this.ddcolor = new GDropdown(this.input);
      var self = this;
      this.input.addEvent("click", function(e) {
        self.createColors();
        self.ddcolor.show();
        self.showDemo(self.color);
        self.pickColor(self.color);
        GEvent.stop(e);
        return false;
      });
      new GMask(this.input, function(e) {
        return /[0-9a-fA-F]/.test(e.key);
      });
      this.input.addEvent("keydown", function(e) {
        var key = GEvent.keyCode(e);
        if (key == 38 || key == 39 || key == 40 || /^(Arrow)?(Up|Down|Right)$/.test(e.key)) {
          self.createColors();
          self.ddcolor.show();
          $E('gddcolor_div').firstChild.firstChild.focus();
          GEvent.stop(e);
          return false;
        }
      });
      if (this.input.type == "text") {
        this.input.addEvent("keyup", function() {
          var value = this.value.toUpperCase();
          if (value != "TRANSPARENT") {
            var c = value.replace("#", "").replace(/[^0-9A-F]+/, "");
            if (c != "") {
              this.value = "#" + c;
            }
          }
        });
      } else {
        this.input.style.cursor = "pointer";
        self.input.tabIndex = 0;
      }
      $G(document.body).addEvent("click", function(e) {
        if (!$G(GEvent.element(e)).hasClass("gddcolor")) {
          self.ddcolor.hide();
        }
      });
      if (self.input.value) {
        self.timer = window.setInterval(function() {
          if (!$E(self.input)) {
            window.clearInterval(self.timer);
          } else if (self.input.value !== self.color) {
            if (self.input.value == "") {
              self.color = "";
              self.input.style.color = "#000000";
              self.input.style.backgroundColor = "#FFFFFF";
            } else if (self.color_format.test(self.input.value)) {
              self.color = self.input.value;
              self.input.style.color = self.invertColor(self.color);
              self.input.style.backgroundColor = self.color;
            } else {
              return;
            }
            self.pickColor(self.color);
            self.showDemo(self.color);
            self.input.callEvent("change");
          }
        }, 50);
      }
    },
    createColors: function() {
      var r = this.Colors.length / this.cols,
        t = this.input.tabIndex + 1,
        dropdown = this.ddcolor.getDropdown(),
        ddcolor = document.createElement('div'),
        self = this,
        patt = /((color_)([0-9]+)_)([0-9]+)/;
      dropdown.innerHTML = "";
      ddcolor.className = 'gddcolor';
      ddcolor.id = 'gddcolor_div';
      dropdown.appendChild(ddcolor);
      var _dokeydown = function(e) {
        var key = GEvent.keyCode(e),
          hs = patt.exec(this.id),
          z = floatval(hs[3]),
          x = floatval(hs[4]);
        if (key > 36 && key < 41) {
          if (key == 37) {
            x = x - 1;
          } else if (key == 38) {
            if (z == self.cols + 1) {
              x = x < 5 ? 0 : 1;
            }
            z = z - 1;
          } else if (key == 39) {
            x = x + 1;
          } else if (key == 40) {
            if (z == self.cols - 1) {
              x = x < 5 ? 0 : 1;
            }
            z = z + 1;
          }
          var el = $E(hs[2] + z + "_" + x);
          if (el) {
            el.focus();
            self.showDemo(el.title);
          }
        } else if (key == 13) {
          self.doClick(this.title);
          GEvent.stop(e);
        } else if (key == 32) {
          if (r - z > 1) {
            self.pickColor(this.title);
            $E("color_" + (self.cols - 1) + "_0").focus();
          }
          GEvent.stop(e);
        } else if (key == 27 || key == 9) {
          self.ddcolor.hide();
          self.input.focus();
          GEvent.stop(e);
        }
      };
      var c = 0,
        a,
        p,
        z;
      forEach(this.Colors, function(color, n) {
        if (n % self.cols == 0) {
          p = document.createElement("p");
          ddcolor.appendChild(p);
          c++;
        }
        a = $G(document.createElement("a"));
        a.id = "color_" + c + "_" + (n % self.cols);
        p.appendChild(a);
        a.tabIndex = t;
        t++;
        if (color == "T") {
          a.title = "Transparent";
          a.innerHTML = "T";
          a.className = "item dark";
        } else if (color == "C") {
          a.title = "Clear";
          a.style.backgroundColor = "#EEEEEE";
          a.innerHTML = "C";
          a.className = "item dark";
        } else {
          z = "#" + color;
          a.style.backgroundColor = z;
          a.title = z;
          a.className = color == "#FFFFFF" ? "item dark" : "item";
        }
        a.addEvent("click", function(e) {
          if (
            this.title == "Clear" ||
            this.title == "Transparent" ||
            this.title == "#FFFFFF"
          ) {
            self.doClick(this.title);
          } else {
            self.pickColor(this.title);
          }
          GEvent.stop(e);
          return false;
        });
        a.addEvent("mouseover", function() {
          self.showDemo(this.title);
        });
        a.addEvent("keydown", _dokeydown);
      });
      this.demoColor = $G(document.createElement("p"));
      ddcolor.appendChild(this.demoColor);
      this.customColor = $G(document.createElement("p"));
      ddcolor.appendChild(this.customColor);
      t++;
      c++;
      for (r = 0; r < self.cols; r++) {
        a = $G(document.createElement("a"));
        this.customColor.appendChild(a);
        a.id = "color_" + c + "_" + r;
        a.tabIndex = t;
        a.className = "item";
        a.addEvent("click", function() {
          self.doClick(this.title);
        });
        a.addEvent("mouseover", function() {
          self.showDemo(this.title);
        });
        a.addEvent("keydown", _dokeydown);
      }
    },
    doClick: function(c) {
      this.ddcolor.hide();
      if (c == "Clear") {
        c = "";
      }
      this.color = c;
      this.onchanged.call(this, c);
      this.input.focus();
    },
    pickColor: function(c) {
      if (this.customColor) {
        var n,
          c = new Color(c),
          rgb = c.toArray(),
          m = Math.min(rgb[0], rgb[1], rgb[2]),
          o = Math.floor((255 - m) / this.cols);
        forEach(this.customColor.elems("a"), function(item, index) {
          n = c.lighten(o * index);
          item.title = n.toString();
          item.style.backgroundColor = n.toString();
          item.style.color = n.invert().toString();
        });
      }
    },
    showDemo: function(c) {
      if (this.demoColor) {
        var a;
        if (c == "Transparent") {
          c = "transparent";
          a = trans("Transparent");
        } else if (c == "Clear") {
          c = "transparent";
          a = trans("Remove Color");
        } else {
          a = c;
        }
        this.demoColor.style.backgroundColor = c;
        this.demoColor.innerHTML = a;
        this.demoColor.style.color = this.invertColor(c);
      }
    },
    setColor: function(c) {
      if (c != "" && c != this.color && this.color_format.test(c)) {
        this.doClick(c.toUpperCase());
      }
    },
    getColor: function() {
      return this.color;
    },
    invertColor: function(c) {
      if (c.toLowerCase() == "transparent") {
        return this.ddcolor.getDropdown().style.color;
      } else {
        return new Color(c).invert().toString();
      }
    }
  };
  window.GLightbox = GClass.create();
  GLightbox.prototype = {
    initialize: function(options) {
      this.id = "gslide_div";
      this.btnclose = "btnclose";
      this.backgroundClass = "modalbg";
      this.previewClass = "gallery_preview";
      this.loadingClass = "spinner";
      this.onshow = null;
      this.onhide = null;
      this.onclose = null;
      for (var property in options) {
        this[property] = options[property];
      }
      var self = this;
      var checkESCkey = function(e) {
        var k = GEvent.keyCode(e);
        if (k == 27) {
          self.hide(e);
        } else if (k == 37) {
          self.showPrev(e);
        } else if (k == 39) {
          self.showNext(e);
        }
      };
      var container_div = "GLightbox_" + this.id;
      var doc = $G(document);
      doc.addEvent("keydown", checkESCkey);
      if (!$E(container_div)) {
        var div = doc.createElement("div");
        doc.body.appendChild(div);
        div.id = container_div;
        div.style.left = "-1000px";
        div.style.position = "fixed";
        var c = doc.createElement("div");
        div.appendChild(c);
        c.className = this.id;
        var c2 = doc.createElement("figure");
        c.appendChild(c2);
        c2.className = this.previewClass;
        this.img = doc.createElement("img");
        c2.appendChild(this.img);
        c = doc.createElement("figcaption");
        c2.appendChild(c);
        var s = doc.createElement("span");
        c2.appendChild(s);
        s.className = this.loadingClass;
        c2 = doc.createElement("p");
        c.appendChild(c2);
        s = doc.createElement("span");
        div.appendChild(s);
        s.className = this.btnclose;
        s.title = trans("Close");
        callClick(s, function() {
          self.hide();
        });
        var a = doc.createElement("a");
        div.appendChild(a);
        a.id = "GLightbox_zoom";
        callClick(a, function(e) {
          self._fullScreen(e);
        });
        this.zoom = a;
        this.prev = doc.createElement("a");
        div.appendChild(this.prev);
        this.prev.className = "hidden";
        this.prev.title = trans("Prev");
        callClick(this.prev, function() {
          self.showPrev();
        });
        this.next = doc.createElement("a");
        div.appendChild(this.next);
        this.next.className = "hidden";
        this.next.title = trans("Next");
        callClick(this.next, function() {
          self.showNext();
        });
      }
      this.zoom = $E("GLightbox_zoom");
      this.div = $G(container_div);
      this.body = $G(this.div.firstChild);
      this.preview = $G(this.body.firstChild);
      this.img = this.preview.firstChild;
      this.caption = this.img.nextSibling.firstChild;
      this.loading = this.img.nextSibling.nextSibling;
      this.body.style.overflow = "hidden";
      this.currentId = 0;
      this.imgs = [];
    },
    clear: function() {
      this.currentId = 0;
      this.imgs.length = 0;
    },
    add: function(a) {
      var img = $E(a);
      img.alt = this.imgs.length;
      this.imgs.push(img);
      var self = this;
      callClick(img, function() {
        self.currentId = floatval(this.alt);
        self.show(this, false);
        return false;
      });
    },
    showNext: function() {
      if (this.div.style.display == "block" && this.imgs.length > 0) {
        this.currentId++;
        if (this.currentId >= this.imgs.length) {
          this.currentId = 0;
        }
        var img = this.imgs[this.currentId];
        this.show(img, false);
      }
    },
    showPrev: function() {
      if (this.div.style.display == "block" && this.imgs.length > 0) {
        this.currentId--;
        if (this.currentId < 0) {
          this.currentId = this.imgs.length - 1;
        }
        var img = this.imgs[this.currentId];
        this.show(img, false);
      }
    },
    _fullScreen: function() {
      if (this.div.style.display == "block" && this.imgs.length > 0) {
        var img = this.imgs[this.currentId];
        this.show(img, this.zoom.className == "btnnav zoomout");
      }
    },
    show: function(obj, fullscreen) {
      var img,
        title,
        self = this;
      if (obj.href) {
        img = obj.href;
        title = obj.title;
      } else if (obj.src) {
        img = obj.src;
        title = obj.alt;
      } else {
        img = obj.style.backgroundImage.substr(
          5,
          obj.style.backgroundImage.length - 7
        );
        title = obj.title;
      }
      this.overlay();
      this.zoom.className = fullscreen ? "btnnav zoomin" : "btnnav zoomout";
      this.zoom.title = trans(fullscreen ? "fit screen" : "full image");
      this.loading.className = this.loadingClass + " show";
      this.prev.className = this.currentId == 0 ? "hidden" : "btnnav prev";
      this.next.className =
        this.currentId == this.imgs.length - 1 ? "hidden" : "btnnav next";
      new preload(img, function() {
        self.loading.className = self.loadingClass;
        self.img.src = this.src;
        if (!fullscreen) {
          var w = this.width;
          var h = this.height;
          var dm = self.body.getDimensions();
          var hOffset =
            dm.height -
            self.body.getClientHeight() +
            parseInt(self.body.getStyle("marginTop")) +
            parseInt(self.body.getStyle("marginBottom"));
          var wOffset =
            dm.width -
            self.body.getClientWidth() +
            parseInt(self.body.getStyle("marginLeft")) +
            parseInt(self.body.getStyle("marginRight"));
          var src_h = document.viewport.getHeight() - hOffset - 20;
          var src_w = document.viewport.getWidth() - wOffset - 20;
          var nw, nh;
          if (h > src_h) {
            nh = src_h;
            nw = (src_h * w) / h;
          } else if (w > src_w) {
            nw = src_w;
            nh = (src_w * h) / w;
          } else {
            nw = w;
            nh = h;
          }
          if (nw > src_w) {
            nw = src_w;
            nh = (src_w * h) / w;
          } else if (nh > src_h) {
            nh = src_h;
            nw = (src_h * w) / h;
          }
          self.img.style.width = nw + "px";
          self.img.style.height = nh + "px";
        } else {
          self.img.style.width = "auto";
          self.img.style.height = "auto";
        }
        new GDragMove("GLightbox_" + self.id, self.img);
        if (title && title != "") {
          self.caption.innerHTML = title.replace(/[\n]/g, "<br>");
          self.caption.parentNode.className = "show";
        } else {
          self.caption.parentNode.className = "";
        }
        self.div.style.display = "block";
        self.div.center();
        self.div.style.zIndex = 1000;
        self.div.fadeIn(function() {
          self._show.call(self);
        });
      });
      return this;
    },
    hide: function() {
      if (Object.isFunction(this.onhide)) {
        this.onhide.call(this);
      }
      var self = this;
      this.div.fadeOut();
      this.iframe.fadeOut(function() {
        self._hide.call(self);
      });
      return this;
    },
    overlay: function() {
      var frameId = "iframe_" + this.div.id,
        self = this;
      if (!$E(frameId)) {
        var io = $G(document.body).create("iframe", {
          id: frameId,
          height: "100%",
          frameBorder: 0
        });
        io.setStyle("position", "absolute");
        io.setStyle("zIndex", 999);
        io.className = this.backgroundClass;
        io.style.display = "none";
      }
      this.iframe = $G(frameId);
      if (this.iframe.style.display == "none") {
        this.iframe.style.left = "0px";
        this.iframe.style.top = "0px";
        this.iframe.style.display = "block";
        this.iframe.fadeIn();
        $G(self.iframe.contentWindow.document).addEvent("click", function(e) {
          self.hide();
        });
        var d = $G(document).getDimensions();
        this.iframe.style.height = d.height + "px";
        this.iframe.style.width = "100%";
      }
      return this;
    },
    _show: function() {
      if (Object.isFunction(this.onshow)) {
        this.onshow.call(this);
      }
    },
    _hide: function() {
      this.iframe.style.display = "none";
      this.div.style.display = "none";
      if (Object.isFunction(this.onclose)) {
        this.onclose.call(this);
      }
    }
  };
  window.callClick = function(input, func) {
    var doKeyDown = function(e) {
      if (GEvent.keyCode(e) == 13 || e.key == "Enter") {
        if (func.call(this, e) !== true) {
          GEvent.stop(e);
          return false;
        }
      }
    };
    input = $E(input);
    if (input && input.onclick == null) {
      input.style.cursor = "pointer";
      input.tabIndex = 0;
      input.onclick = func;
      $G(input).addEvent("keydown", doKeyDown);
    }
  };
  var GElement = new GNative();
  window.$G = function(e) {
    return Object.isGElement(e) ? e : GElement.init(e);
  };
  window.$E = function(e) {
    e = Object.isString(e) ? document.getElementById(e) : e;
    return Object.isObject(e) ? e : null;
  };
  var loadCompleted = function() {
    domloaded = true;
    if (document.addEventListener) {
      document.removeEventListener("DOMContentLoaded", loadCompleted, false);
      window.removeEventListener("load", loadCompleted, false);
    } else {
      document.detachEvent("onreadystatechange", loadCompleted);
      window.detachEvent("onload", loadCompleted);
    }
    $G(document);
    $G(document.body);
  };
  if (document.addEventListener) {
    document.addEventListener("DOMContentLoaded", loadCompleted, false);
    window.addEventListener("load", loadCompleted, false);
  } else {
    document.attachEvent("onreadystatechange", loadCompleted);
    window.attachEvent("onload", loadCompleted);
  }
  return $K;
})();