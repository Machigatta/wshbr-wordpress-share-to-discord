jQuery(window).load(function () {
    $("button[name=dc_share]").removeAttr("disabled");
});

function shareDiscord(){
    $("button[name=dc_share]").attr("disabled","disabled");
    
    var http = new XMLHttpRequest();
    var url = "/wp-content/plugins/wshbr-wordpress-share-to-discord/SHARE/bot.php";
    var params = "title="+encodeURIComponent(postData.title)+"&url="+encodeURIComponent(postData.url)+"&short="+encodeURIComponent(postData.short)+"&thumbnailurl="+encodeURIComponent(postData.thumbnailurl)+"&webhooks="+encodeURIComponent(wshbr_hookurls);
    http.open("POST", url, true);

    //Send the proper header information along with the request
    http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

    http.onreadystatechange = function() {//Call a function when the state changes.
        if(http.readyState == 4 && http.status == 200) {
            var ret = JSON.parse(http.responseText)
            if(ret.retNumber != 0){
                alert(ret.retMessage);
            }
        }
    }
    http.send(params);


}