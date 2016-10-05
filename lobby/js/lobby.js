/**
 * Created by abrainerd on 2/24/2016.
 */

var queryID = 0;          // IE caching fix
var environment = "";
var header;
var inputs;
var form;
var showReport;
var viewVisitors;
var visitorRows;
var closeReport;
var branch;
var vid;
var fname;
var lname;
var reason;
var addInfo;
var submit;
var cancel;
var page;

$(document).ready(function () {
    checkRedirect();
    var logo = $("header > img");
    header = $("header");
    inputs = $("input");
    form = $("#initialForm");
    viewVisitors = $("#viewVisitors");
    visitorRows = $("#viewVisitors .row");
    showReport = $("#report");
    closeReport = $("#closeReport");
    branch = $("#branch");
    vid = $("#vid").val();
    fname = $("#fname");
    lname = $("#lname");
    reason = $("#reason");
    addInfo = $("#addInfo");
    submit = $("#submitForm");
    cancel = $("#cancel");
    page = $("body");

    page.find("#closingNote").remove();
    viewVisitors.hide();
    closeReport.hide();
    addInfo.hide();
    fetchReasons();
    clearForm();
    checkEnvironment();

    $(document).bind('keypress.key13', function (e) {
        if (e.which == 13) {
            e.preventDefault();
            submit.click();
        }
    });

    page.fadeIn(function () {
        $("body").scroll();
        //fname.focus();
    });
    page.click(function () {
        //$("#viewVisitors .row").removeClass("selected");
        //page.find("#closingNote").remove();
    });
    logo.click(function () {
        var loc = location.href;
        if (loc.indexOf("goto") >= 0) {
            window.location.href = "index.php";
        }
        else {
            location.reload();
        }
    });
    inputs.focus(function () {
        var inputLabel = $(this).prev("label");
        inputLabel.css("right", "80px");
        if (inputLabel.text().indexOf(" ") > 0)
            inputLabel.text(inputLabel.text().substring(0, inputLabel.text().indexOf(" ")) + ":");
        //showReport.hide();
    });
    inputs.blur(function () {
        $(this).val(capitalize($.trim($(this).val())));
        var elementType = $(this).prev().prop("nodeName");
        if (elementType == "LABEL" && $(this).val() == "") {
            $(this).prev().css("right", "0");
        }
        showReport.show();
    });
    reason.focus(function () {
        var label = $(this).prev("label");
        $("#report").hide();
    });
    reason.blur(function () {
        showReport.show();
        $("html, body").animate({
            scrollTop: 0
        }, 0);
    });
    reason.change(function () {
        var r = reason.val();
        if (r == 0) {
            addInfo.attr("placeholder", "Other Reason").show();
        }
        else if (r == "Appointment") {
            addInfo.attr("placeholder", "Meeting With").show();
        }
        else {
            addInfo.hide();
        }
    });
    cancel.click(function () {
        clearForm();
    });
    submit.click(function () {
        if (!submit.hasClass("disabled")) {
            var r = reason.val() == 0 ? addInfo.val() : reason.val();
            if (reason.val() == "Appointment") {
                r = "Appt w/" + addInfo.val();
            }
            if (validateCheckIn()) {
                $.ajax({
                    type: "POST",
                    url: "util/setVisitor.php",
                    data: "fname=" + fname.val() +
                    "&lname=" + lname.val() +
                    "&reason=" + r +
                    "&branch=" + $("#branch").val(),
                    success: function () {
                        showPopupMessage();
                    }
                });
            }
        }
    });
    showReport.click(fetchVisitors);
    closeReport.click(function () {
        page.find("#closingNote").remove();
        viewVisitors.fadeOut(function () {
            closeReport.fadeOut(function () {
                header.css({"position": "static", "border-bottom": "none"});
                form.fadeIn();
                showReport.fadeIn();
            });
        });
    });
});

function showPopupMessage() {
    var popup = $("#thankYou");
    popup.fadeIn("slow", function () {
        clearForm();
    });
    setTimeout(function () {
        popup.fadeOut("slow", function () {
            $("#report").fadeIn();
        });
    }, 3000);
}
function fetchVisitors() {
    var sd = "";
    console.log("searchDate=" + sd + "&queryID=" + queryID + "&branch=" + $("#branch").val());
    $.ajax({
        type: "POST",
        url: "util/viewVisitors.php",
        data: "searchDate=" + sd +
        "&queryID=" + queryID +
        "&branch=" + branch.val(),
        success: function (msg) {
            viewVisitors.html(msg);
            form.fadeOut();
            showReport.fadeOut(function () {
                header.css({"position": "fixed", "border-bottom": "3px solid white"});
                $(".table").droppable();
                $(".row").draggable({
                    helper: "clone"
                }).click(function () {
                    if ($(this).hasClass(("ui-draggable-draggin"))) return;
                    console.log("I was clicked on");
                });
                viewVisitors.fadeIn();
                closeReport.fadeIn();
            });
            queryID++;
        }
    });
}
function helpMember(vid) {
    if (vid != -1) {
        if (confirm("Assist This Member?")) {
            $.ajax({
                type: "POST",
                url: "util/updateStatus.php",
                data: "vid=" + vid + "&status=1",
                success: function (msg) {
                    fetchVisitors();
                }
            });
        }
    }
}
function finalNote(vid, row) {
    if (vid != -1) {
        console.log("Class: " + row.attr("class"));
        page.find("#closingNote").remove();
        viewVisitors.find(".row").removeClass("selected");
        row.addClass("selected");
        console.log("Row onClick: " + row.attr("onclick"));
        row.on("click.selected", function () {
            row.removeClass("selected");
            page.find("#closingNote").remove();
            row.off("click.selected");
        });
        var finalNote = $("<div id='closingNote'></div>");
        finalNote.append("<label for='note'>How did we aMAIZE this member today?</label>");
        finalNote.append("<textarea id='note'></textarea>");
        finalNote.css({
            "width": row.width(),
            "height": row.height(),
            "left": row.offset().left,
            "top": row.offset().top + row.height()
        });
        var dasButton = $("<input id='addNote' type='button' value='Done' onclick='checkout(" + vid + ")'/>");
        dasButton.css({
            "height": finalNote.height(),
            "left": finalNote.width(),
            "top": -20 //finalNote.offset().top
        });
        finalNote.append(dasButton);
        page.append(finalNote);
    }
    else alert("Visitor already checked out");
}
function checkout(vid) {
    if (vid != -1) {
        var noteText = $("#note").val();
        $.ajax({
            type: "POST",
            url: "util/updateStatus.php",
            data: "vid=" + vid +
                "&noteText=" + noteText +
                "&status=2",
            success: function (msg) {
                page.find("#closingNote").remove();
                fetchVisitors();
            }
        });
    }
}
function clearForm() {
    fname.val("");
    fname.prev("label").css("right", "0").text("First Name");
    lname.val("");
    lname.prev("label").css("right", "0").text("Last Name");
    reason.val("-1");
    addInfo.hide();
    addInfo.val("");
    submit.removeClass("disabled");
}
function fetchReasons() {
    $.ajax({
        type: "POST",
        url: "util/getReasons.php",
        success: function (data) {
            reason.html(data);
        }
    });
}
function validateCheckIn() {
    if (fname.val() == "") {
        alert("Please enter your first name");
        return false;
    }
    if (lname.val() == "") {
        alert("Please enter your last name");
        return false;
    }
    if (reason.val() == -1) {
        alert("Please provide a reason for your visit");
        return false;
    }
    else if (reason.val() == 0) {
        if ($.trim(addInfo.val()) == "") {
            alert("Please provide a reason for you visit");
            return false;
        }
    }
    submit.addClass("disabled");
    return true;
}
function checkEnvironment() {
    var loc = window.location.toString();
    if (loc.indexOf("notmuchhappening") > 0) {
        $("#timer").html("Dev Server");
        environment = "DEV";
    }
    else {
        environment = "LIVE";
    }
}
function checkRedirect() {
    branch = $("#branch");
    if (branch.val() == "") location.href = "https://umculobby.com?d=instant";
    //else if (branch.val() == "William") location.href = "https://umculobby.com/parking/?branch=William";
    else if (branch.val() == "Huron") location.href = "https://umculobby.com/parking/?branch=Huron";
    var g = $("#goTo").val();
    if (g != "") {
        console.log("There is a goTo value");
        if (g == "view") {
            fetchVisitors();
        }
        else {
            console.log("Invalid redirect");
        }
    }
}
function capitalize(s) {
    return s.charAt(0).toUpperCase() + s.slice(1);
}