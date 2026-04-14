document.addEventListener("DOMContentLoaded", function(){
	document.querySelectorAll("input[type='checkbox'][value='fx-builder-content']").forEach( checkbox => {
		checkbox.disabled = true
		checkbox.checked = true
	});
});
