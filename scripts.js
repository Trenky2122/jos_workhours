function recalculateHours(element_id) {
    let begin_time = document.getElementById(element_id + "_begin_time").value;
    let end_time = document.getElementById(element_id + "_end_time").value;
    if (begin_time && end_time) {
        let time = calculateTimeDifference(begin_time, end_time);
        let break_begin = document.getElementById(element_id + "_break_begin").value;
        let break_end = document.getElementById(element_id + "_break_end").value;
        if (break_begin && break_end) {
            time = calculateTimeDifference(calculateTimeDifference(break_begin, break_end), time);
        }
        document.getElementById(element_id).innerHTML = time;
    } else {
        document.getElementById(element_id).innerHTML = "0:00";
    }
    console.log(element_id);
}

function calculateTimeDifference(time_begin, time_end) {
    time_begin = time_begin.substring(0, 5);
    time_end = time_end.substring(0, 5);
    console.log(arguments);
    let time_begin_min = time_begin.substring(3);
    let time_end_min = time_end.substring(3);
    let minutes = time_end_min - time_begin_min >= 0 ? time_end_min - time_begin_min : 60 + (time_end_min - time_begin_min);
    let hours_begin = time_begin.substring(0, 2);
    let hours_end = time_end.substring(0, 2);
    let hours = hours_end - hours_begin - (time_end_min - time_begin_min >= 0 ? 0 : 1);
    if (hours < 0)
        return "00:00";
    return (hours < 10 ? "0" : "") + hours.toString() + ":" + (minutes < 10 ? "0" : "") + minutes.toString();
}

function reloadFilter() {
    let allRows = document.getElementsByClassName("table-row");
    [].forEach.call(allRows, (element) => element.style.display = "none");
    let className = "";
    if (document.getElementById("worker_select").value != "0") {
        className += document.getElementById("worker_select").value;

        let elsToShow = document.getElementsByClassName("worker_name "+ className);
        [].forEach.call(elsToShow, (el) => el.style.display = "table-row");
    }
    else {
        let elsToShow = document.getElementsByClassName("worker_name");
        [].forEach.call(elsToShow, (el) => el.style.display = "table-row");
    }
    if (document.getElementById("day_select").value != "0") {
        className += " " + document.getElementById("day_select").value;
    }
    if (className == "") {
        [].forEach.call(allRows, (element) => element.style.display = "table-row");
        return;
    }
    if(className.charAt(0)==" ")
        className=className.substring(1);
    let elsToShow = document.getElementsByClassName(className);
    [].forEach.call(elsToShow, (el) => el.style.display = "table-row");
}

function generate(table_id) {
    var doc = new jsPDF('p', 'pt', 'letter');
    var htmlstring = '';
    var tempVarToCheckPageHeight = 0;
    var pageHeight = 0;
    pageHeight = doc.internal.pageSize.height;
    specialElementHandlers = {
        // element with id of "bypass" - jQuery style selector
        '#bypassme': function(element, renderer) {
            // true = "handled elsewhere, bypass text extraction"
            return true
        }
    };
    margins = {
        top: 150,
        bottom: 60,
        left: 40,
        right: 40,
        width: 600
    };
    var y = 20;
    doc.setLineWidth(2);
    doc.text(200, y = y + 30, table_id);
    doc.autoTable({
        html: '#'+table_id,
        startY: 70,
        theme: 'grid',
        columnStyles: {
            0: {
                cellWidth: 180,
            },
            1: {
                cellWidth: 180,
            },
            2: {
                cellWidth: 180,
            }
        },
        styles: {
            minCellHeight: 40
        }
    })
    caption = table_id + '.pdf'
    doc.save(caption);
}