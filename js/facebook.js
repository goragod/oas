/**
 * Facebook Script
 *
 * @filesource js/facebook.js
 * @link http://www.kotchasan.com/
 * @copyright 2018 Goragod.com
 * @license http://www.kotchasan.com/license/
 */
function initFacebookButton(button) {
  callClick(button, function() {
    FB.login(
      function(response) {
        if (response.authResponse) {
          var accessToken = response.authResponse.accessToken;
          var uid = response.authResponse.userID;
          FB.api(
            "/" + uid, {
              access_token: accessToken,
              fields: "id,first_name,last_name,email"
            },
            function(response) {
              if (!response.error) {
                var q = new Array();
                if ($E("token")) {
                  q.push("token=" + encodeURIComponent($E("token").value));
                }
                for (var prop in response) {
                  q.push(prop + "=" + encodeURIComponent(response[prop]));
                }
                send(WEB_URL + "index.php/" + ($E("facebook_action") ? $E("facebook_action").value : "index/model/fblogin/chklogin"), q.join("&"), fbLoginSubmit);
              }
            }
          );
        }
      }, { scope: "email,public_profile" }
    );
  });
}

function initFacebook(appId, lng) {
  window.fbAsyncInit = function() {
    FB.init({
      appId: appId,
      cookie: true,
      status: true,
      xfbml: true,
      version: "v7.0"
    });
  };
  loadJavascript("facebook-jssdk", "//connect.facebook.net/" + (lng == "th" ? "th_TH" : "en_US") + "/sdk.js");
}

function fbLoginSubmit(xhr) {
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
