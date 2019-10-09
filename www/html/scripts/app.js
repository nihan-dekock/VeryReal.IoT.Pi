var cntr = 0;
var jsonUrlLocal = "http://10.0.0.30:9999/beamjson.php";
var jsonUrlRemote = "http://ni.kguard.org:9999/beamjson.php";
var jsonUrl = jsonUrlLocal;
var audioInited = false;
var retry = 0;
var snd;
var maudio;
var alarmAudio;
var end = 0;
var soundSprite = [
    { start: 0, end: 0.5 },
    { start: 3500, end: 6789 }
];
var alarmPlaying = false;

//Used to play sound
function initAudio() {
    if (!audioInited) {
        audioInited = true;
        var audio = document.getElementById('alarmAudio');
        audio.play();
        audio.pause();
        audio = document.getElementById('myaudio');
        audio.play();
        audio.pause();
    }
}

//Play a sound file
function playSoundFile(idx) {
    maudio.currentTime = soundSprite[idx].start;
    end = soundSprite[idx].end;
    maudio.play();
}

//Play alarm sound
function playAlarm() {
    if (alarmPlaying == false) {
        alarmPlaying = true;
        //alert(alarmAudio.duration);
        alarmAudio.pause();
        alarmAudio.currentTime = 0;
        alarmAudio.play();
    }
}

//Stop playing alarm sound
function stopAlarmPlay() {
    alarmPlaying = false;
    alarmAudio.pause();
}

//Ajax call every one second to get status of GPIO pins in JSON format
function ajaxCall() {
    if (document.hidden) {
        $("#alarmStatusText").html("INACTIVE");
        setTimeout(ajaxCall, 1000);
    }
    else {
        $.getJSON(jsonUrl, function (alarmStatus) {
            updateStatus(alarmStatus);
        })
            .done(function () {
                $("#conn").html(jsonUrl == jsonUrlLocal ? "Local" : "Remote");
                setTimeout(ajaxCall, 1000);
            })
            .fail(function (jqXHR, textStatus, errorThrown) {
                $("#alarmScreamPulse").attr('class', "pulse-search");
                retry++;
                cntr++;
                var server = jsonUrl == jsonUrlLocal ? "Local" : "Remote";
                $("#conn").html("Err: " + server + ". " + textStatus + ". " + retry);
                if (retry >= 1) {
                    retry = 0;
                    //Switch between remote and local to resolve issue when on local Wi-Fi
                    jsonUrl = jsonUrl == jsonUrlLocal ? jsonUrlRemote : jsonUrlLocal;
                }

                setTimeout(ajaxCall, 1000);
            });
    }

}

//Update status, text and CSS based on info received from Pi
function updateStatus(alarmStatus) {
    $("#responseTime").html(alarmStatus.responseTime);
    $("#alarmStatus").attr('class', alarmStatus.class);
    $("#alarmStatusText").attr('class', alarmStatus.statusClass);
    $("#alarmStatusText").html(alarmStatus.status);

    $("#alarmStatusLastDate").html(alarmStatus.alarmStatusLastDate);
    $("#alarmScreamStatus").html(alarmStatus.alarmScreamStatus);
    $("#alarmScreamText").html(alarmStatus.alarmScreamText);
    $("#alarmScreamPulse").attr('class', alarmStatus.alarmScreamPulse);
    $("#alarmScreamStatus").attr('class', alarmStatus.alarmScreamStatusClass);

    if (alarmStatus.alarmScreamStatus == "SIREN ON!") {
        //Play alarm sound
        playAlarm();
    }
    else {
        //Stop playing alarm sound
        stopAlarmPlay();
    }
    for (i in alarmStatus.zones) {
        //Set alarm zones text and CSS
        $("#zone" + alarmStatus.zones[i].name + "Status").html(alarmStatus.zones[i].status);
        $("#zone" + alarmStatus.zones[i].name + "StatusText").html(alarmStatus.zones[i].status);
        $("#zone" + alarmStatus.zones[i].name).attr('class', 'ZoneStatus ' + alarmStatus.zones[i].class);
    }
    for (i in alarmStatus.lights) {
        //Set lights/electronics text and CSS
        $("#light" + alarmStatus.lights[i].name + "Status").html(alarmStatus.lights[i].status == "1" ? "OFF" : "ON");
        $("#light" + alarmStatus.lights[i].name).attr('class', alarmStatus.lights[i].status == "1" ? "ZoneStatus LightsOff" : "ZoneStatus LightsOn");
    }
}

//Wire up when DOM is ready
$(document).ready(function () {
    var theElement = document.getElementById("main");
    theElement.addEventListener("touchstart", handlerFunction, false);

    $.ajaxSetup({
        timeout: 4000 //Time in milliseconds
    });

    $.support.cors = true;

    maudio = document.getElementById('myaudio');
    alarmAudio = document.getElementById('alarmAudio');

    maudio.addEventListener('timeupdate', function (ev) {
        if (maudio.currentTime > end) { maudio.pause(); }
    }, false);

    alarmAudio.addEventListener('timeupdate', function (ev) {
        if (alarmAudio.currentTime >= 6.7) { alarmAudio.currentTime = 0; alarmAudio.play(); }
    }, false);

    //make the first ajax call to start monitoring
    setTimeout(ajaxCall, 1000);
});

function handlerFunction(event) {
    initAudio();
}

//Switch light/electronics on/off by calling API with light number querystring
function switchLight(lightNo) {
    //jwplayer('myElement').play();
    playSoundFile(0);
    //alert(getCacheStatus());
    $.getJSON(jsonUrl + '?light=' + lightNo, function (alarmStatus) {
        //maudio.play();

        updateStatus(alarmStatus);

    }).fail(function () { });

}

//Reset alarm zone by calling API with zone number querystring
function resetZone(lightNo) {
    playSoundFile(0);
    //alert(getCacheStatus());
    $.getJSON(jsonUrl + '?zone=' + lightNo, function (alarmStatus) {

        updateStatus(alarmStatus);

    }).fail(function () { });

}


//Arm/disarm alarm by calling API with arm querystring
function arm(armType) {
    playSoundFile(0);
    $.getJSON(jsonUrl + '?arm=' + armType, function (alarmStatus) {
        updateStatus(alarmStatus);

    }).fail(function () { jsonUrl = jsonUrlRemote; });

}

//Add event listener to detect tripple click for panic
window.addEventListener('click', function (evt) {
    if (evt.detail === 3) {
        //panic
        playSoundFile(0);
        arm(2);
    }
});

//Refresh withouit cache
function refreshNoCache() {
    var appCache = window.applicationCache;
    appCache.update();
    location.reload();
}

//Get cache status, works with manifest file
function getCacheStatus() {
    //alert('a');
    var appCache = window.applicationCache;

    switch (appCache.status) {
        case appCache.UNCACHED: // UNCACHED == 0
            return 'UNCACHED';
            break;
        case appCache.IDLE: // IDLE == 1
            return 'IDLE';
            break;
        case appCache.CHECKING: // CHECKING == 2
            return 'CHECKING';
            break;
        case appCache.DOWNLOADING: // DOWNLOADING == 3
            return 'DOWNLOADING';
            break;
        case appCache.UPDATEREADY:  // UPDATEREADY == 4
            return 'UPDATEREADY';
            break;
        case appCache.OBSOLETE: // OBSOLETE == 5
            return 'OBSOLETE';
            break;
        default:
            return 'UKNOWN CACHE STATUS';
            break;
    }
}