
function lcSendForm() {

    //Get the form
    var form = document.getElementById("lc_genconf_form");

    //Initialize the data
    var data = new Object();
    data['action'] = 'lc_genconf_submit_form';
    data['conf_key'] = document.getElementById('lc_genconf_special_conf_key').value;


    //Iterate over repeater
    var repeaters = form.getElementsByClassName("lc_genconf_repeater");    
    for(var rIdx = 0; rIdx < repeaters.length; rIdx++) {
	//Initialize repeater data
	data[rIdx] = new Object();
	
	//Iterate over sections
	var sections = repeaters[rIdx].getElementsByClassName("lc_genconf_section");
	for(var sIdx = 0; sIdx < sections.length; sIdx++) {
	    //Get the section id
	    var sectionId = sections[sIdx].getAttribute("data-sectionid");

	    //Initialize section data
	    data[rIdx][sectionId] = new Object();
	    
	    //Iterate over inputs
	    var inputs = sections[sIdx].getElementsByTagName("input");
	    for(var iIdx = 0; iIdx < inputs.length; iIdx++) {
		//Handle checkboxes separately
		if(inputs[iIdx].attributes['type'].value != 'checkbox') {
		    //Get its name and value
		    var inputName = inputs[iIdx].name;
		    var inputValue = inputs[iIdx].value;
		    
		    //Store its value
		    data[rIdx][sectionId][inputName] = inputValue;
		} else {
		    var inputName = inputs[iIdx].name;
		    if(inputs[iIdx].checked)
			data[rIdx][sectionId][inputName] = 1;
		    else
			data[rIdx][sectionId][inputName] = 0;
		}

	    }
	    
	    //Iterate over selects
	    var selects = sections[sIdx].getElementsByTagName("select");
	    for(var slIdx = 0; slIdx < selects.length; slIdx++) {
		//Get its name and value
		var selectName = selects[slIdx].name;
		var selectValue = selects[slIdx].value;
		//Store its value
		data[rIdx][sectionId][selectName] = selectValue;
	    }
	}
    }

    //Send the data
    jQuery.post(ajaxurl, data);
}

function lcAddRepeaterSection() {
    
    var nextid = document.getElementById("lc_genconf_add_repeater_nextid").value;
    var confKey = document.getElementById("lc_genconf_special_conf_key").value;
    var params = "action=lc_genconf_add_repeater&lc_genconf_special_repeater_nextid=" + nextid + "&lc_genconf_special_conf_key=" + confKey;
    
    var request = new XMLHttpRequest();
    request.open('POST', ajaxurl, true);
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    request.onreadystatechange = function() {
	if (this.readyState === 4) {
	    if (this.status >= 200 && this.status < 400) {
		// Success!
		lcAppendRepeaterSection(this.responseText);
	    } else {
		// Error :(
	    }
	}
    };
    
    request.send(params);
    request = null;
    
}

function lcGenconfCheckCondition(elem) {
    //Get the value of the conditionning element
    var conditionValue = elem.value;
    
    //Get the corresponding section div
    var sectionDiv = getParentByClassName(elem, "lc_genconf_section");    
    var sectionDivId = sectionDiv.id;
    
    //Get the <tr>s in the section
    var tr = document.querySelectorAll('#' + sectionDivId + ' tr');
    
    //For each <tr> display or not the line
    for(var i = 0; i < tr.length; i++) {
	var conditionCheckValue = tr[i].getAttribute("data-condition");
	if(conditionCheckValue != undefined && conditionCheckValue != "") {
	    if(conditionValue == conditionCheckValue) {
		removeClass(tr[i], "lc_genconf_hidden");
	    } else {
		addClass(tr[i], "lc_genconf_hidden");
	    }
	}
    }    
}

function lcAppendRepeaterSection(html) {
    document.getElementById('lc_genconf_content').insertAdjacentHTML('beforeend', html);
    var nextid = lcFirstFreeId();
    document.getElementById("lc_genconf_add_repeater_nextid").value = nextid;
}

function lcDeleteRepeaterSection(aTag) {
    repeaterSection = aTag.parentNode;
    content = repeaterSection.parentNode;
    
    content.removeChild(repeaterSection);
    
    var nextid = lcFirstFreeId();
    document.getElementById("lc_genconf_add_repeater_nextid").value = nextid;
    
}


function lcFirstFreeId() {
    var firstFreeId = 0;
    var repeaters = document.getElementsByClassName("lc_genconf_repeater");
    
    while(isUsedId(firstFreeId, repeaters)) {
	firstFreeId++;
    }
    
    return firstFreeId;
}

function isUsedId(id, repeaters) {
    for(var i = 0; i < repeaters.length; i++) {
	
	var repeaterId = getRepeaterId(repeaters[i]);
	if(repeaterId === id) {
	    return true;
	}
    }
    return false;
}

function getRepeaterId(repeater) {
    //get the hidden input
    var inputs = repeater.getElementsByTagName("input");
    var idInput = null;
    for(i = 0; i < inputs.length; i++) {
	if(inputs[i].getAttribute("id") == "lc_genconf_special_id") {
	    idInput = inputs[i];
	    break;
	}
    }
    
    if(idInput != null) {
	return parseInt(idInput.getAttribute("value"));
    } else {
	return -1;
    }
    
}

function getParentByClassName(elem, className) {
    var parentNode = elem.parentNode;
    if(parentNode == undefined || hasClass(parentNode, className)) {
	return parentNode;
    } else {
	return getParentByClassName(parentNode, className);
    }
}


function hasClass(ele,cls) {
    return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
}
function addClass(ele,cls) {
    if (!this.hasClass(ele,cls)) ele.className += " "+cls;
}
function removeClass(ele,cls) {
    if (hasClass(ele,cls)) {
        var reg = new RegExp('(\\s|^)'+cls+'(\\s|$)');
        ele.className=ele.className.replace(reg,' ');
    }
}
