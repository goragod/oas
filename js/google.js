/**
 * Google signin Script
 *
 * @filesource js/google.js
 * @link http://www.kotchasan.com/
 * @copyright 2018 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
var auth2;

function initGooleSignin(google_client_id) {
  loadJavascript(
    "apis-google",
    "https://apis.google.com/js/platform.js?onload=googleSigninLoad"
  );
  window.google_client_id = google_client_id;
}

function googleSigninLoad() {
  gapi.load("auth2", function() {
    auth2 = gapi.auth2.init({
      client_id: window.google_client_id + ".apps.googleusercontent.com",
      cookiepolicy: "single_host_origin"
    });
  });
}

function initGoogleButton(button) {
  if ($E(button)) {
    window.setTimeout(function() {
      if (auth2) {
        auth2.attachClickHandler($E(button), {}, function(googleUser) {
          var profile = googleUser.getBasicProfile(),
            q = new Array();
          if ($E("token")) {
            q.push("token=" + encodeURIComponent($E("token").value));
          }
          q.push("id=" + encodeURIComponent(profile.getId()));
          q.push("name=" + encodeURIComponent(profile.getName()));
          q.push("image=" + encodeURIComponent(profile.getImageUrl()));
          q.push("email=" + encodeURIComponent(profile.getEmail()));
          send(WEB_URL + "index.php/" + ($E("google_action") ? $E("google_action").value : "index/model/gglogin/chklogin"), q.join("&"), ggLoginSubmit);
        });
      } else {
        initGoogleButton(button);
      }
    }, 100);
  }
}

function ggLoginSubmit(xhr) {
  var ds = xhr.responseText.toJSON();
  if (ds) {
    if (ds.alert) {
      alert(ds.alert);
    }
    if (ds.isMember == 1) {
      if ($E("login_action")) {
        window.location = $E("login_action").value;
      } else {
        window.location = window.location.href.replace("action=logout", "action=login");
      }
    }
  } else if (xhr.responseText != "") {
    console.log(xhr.responseText);
  }
}
