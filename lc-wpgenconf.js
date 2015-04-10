function lcGenconfCheckboxes() {
	var checkboxes = document.querySelectorAll(".lc_genconf_wrap input[type=checkbox]");

	for(var i = 0; i < checkboxes.length; i++) {
		var checkbox = checkboxes[i];
		var id = checkbox.id;
		var hidden_id = "lc_genconf_hidden_" + id;

		if(document.getElementById(id).checked == true) {
			document.getElementById(hidden_id).value = "true";
		} else {
			document.getElementById(hidden_id).value = "false";
		}
	}

	return true;
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
    //Récupération de la valeur de l'élément conditionnant
    var conditionValue = elem.value;

    //Récupération du div de section correspondant
    var sectionDiv = getParentByClassName(elem, "lc_genconf_section");    
    var sectionDivId = sectionDiv.id;

    //Récupération des <tr> de la section
    var tr = document.querySelectorAll('#' + sectionDivId + ' tr');

    //Pour chacun, affichage ou masquage en fonction de la valeur
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
