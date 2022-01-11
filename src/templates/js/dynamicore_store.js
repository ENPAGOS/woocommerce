function setPeriod(period, e) {
	var elem = document.getElementById("dynamicore_periodos");
	elem.value = period;

	var selected = document.getElementsByClassName("card-selected");
	for (i = 0; i < selected.length; i++) {
    	selected[i].classList.remove("card-selected");
	}

	e.classList.add("card-selected");
}
