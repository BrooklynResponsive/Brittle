
function brittleUser()
{
    this.attrs = {};

    this.loadUser = function() {
        var c_value = document.cookie;
        var c_start = c_value.indexOf("brittleUserContents=");

        if (c_start == -1)
        {
            return
        }
        else
        {
            c_start = c_value.indexOf("=", c_start) + 1;
            var c_end = c_value.indexOf(";", c_start);

            if (c_end == -1)
            {
                c_end = c_value.length;
            }

            var attrString = getCookie("brittleUserContents");
            this.attrs = JSON.parse(attrString);

       }
    }

    this.saveUser = function() {
        var seconds = (60*6*6); //6 hours
        var c_value="brittleUserContents=" + escape(JSON.stringify(this.attrs)) + "; max-age=" + String(seconds) +  ";";
        document.cookie = c_value;
    }

   function getCookie(c_name) {
        var i, x, y, ARRcookies = document.cookie.split(";");
        for (i = 0; i < ARRcookies.length; i++) {
            x = ARRcookies[i].substr(0, ARRcookies[i].indexOf("="));
            y = ARRcookies[i].substr(ARRcookies[i].indexOf("=") + 1);
            x = x.replace(/^\s+|\s+$/g, "");
            if (x == c_name) {
                return unescape(y);
            }
        }
    }

    this.loadUser();
}

