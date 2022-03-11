function setPeriod(period, e) {
	var elem = document.getElementById("dynamicore_periodos");
	elem.value = period;

	var selected = document.getElementsByClassName("card-selected");
	for (i = 0; i < selected.length; i++) {
    	selected[i].classList.remove("card-selected");
	}

    var icons = document.getElementsByClassName("dashicons-saved");
	for (i = 0; i < icons.length; i++) {
        icons[i].style.display = "none";
	}

	e.classList.add("card-selected");

    document.getElementById("period__" + period).style.display = "block";
}
