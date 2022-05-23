function recalculateHours(element_id) {
    let begin_time = document.getElementById(element_id + "_begin_time").value;
    let end_time = document.getElementById(element_id + "_end_time").value;
    let breakOk = true;
    let break_begin = '';
    let break_end = '';
    if (begin_time && end_time) {
        let time = calculateTimeDifference(begin_time, end_time);
        break_begin = document.getElementById(element_id + "_break_begin").value;
        break_end = document.getElementById(element_id + "_break_end").value;
        breakOk = (break_begin && break_end) || (!break_begin && !break_end);
        if (break_begin && break_end) {
            time = calculateTimeDifference(calculateTimeDifference(break_begin, break_end), time);
            breakOk = break_end>=break_begin;
        }
        document.getElementById(element_id).innerHTML = time;
    } else {
        document.getElementById(element_id).innerHTML = "0:00";
    }
    return breakOk && begin_time<=end_time && (break_begin == '' || (begin_time<=break_begin && end_time>=break_end));
}

function calculateTimeDifference(time_begin, time_end) {
    time_begin = time_begin.substring(0, 5);
    time_end = time_end.substring(0, 5);
    let time_begin_min = time_begin.substring(3);
    let time_end_min = time_end.substring(3);
    let minutes = time_end_min - time_begin_min >= 0 ? time_end_min - time_begin_min : 60 + (time_end_min - time_begin_min);
    let hours_begin = time_begin.substring(0, 2);
    let hours_end = time_end.substring(0, 2);
    let hours = hours_end - hours_begin - (time_end_min - time_begin_min >= 0 ? 0 : 1);
    if (hours < 0)
        return "Negative time";
    return (hours < 10 ? "0" : "") + hours.toString() + ":" + (minutes < 10 ? "0" : "") + minutes.toString();
}

function reloadFilter() {
    let allRows = document.getElementsByClassName("table-row");
    [].forEach.call(allRows, (element) => element.style.display = "none");
    let className = "";
    if (document.getElementById("worker_select").value != "0") {
        className += document.getElementById("worker_select").value;

        let elsToShow = document.getElementsByClassName("worker_name "+ className);
        [].forEach.call(elsToShow, (el) => el.style.display = "flex");
    }
    else {
        let elsToShow = document.getElementsByClassName("worker_name");
        [].forEach.call(elsToShow, (el) => el.style.display = "flex");
    }
    if (document.getElementById("day_select").value != "0") {
        className += " " + document.getElementById("day_select").value;
    }
    if (className == "") {
        [].forEach.call(allRows, (element) => element.style.display = "flex");
        return;
    }
    if(className.charAt(0)==" ")
        className=className.substring(1);
    let elsToShow = document.getElementsByClassName(className);
    [].forEach.call(elsToShow, (el) => el.style.display = "flex");
}

function verifyProjectInputs(classnameProjects, valueId, collapse, done){
    if(!document.getElementById(done).checked)
        return true;
    let allProjectTimes = document.getElementsByClassName(classnameProjects);
    let originalTime = document.getElementById(valueId).innerHTML;
    [].forEach.call(allProjectTimes, (element) => {
        originalTime = calculateTimeDifference(element.value, originalTime);
    });
    if(originalTime != "00:00"){
        $("#"+collapse).collapse('show');
        return false;
    }
    return true;
}