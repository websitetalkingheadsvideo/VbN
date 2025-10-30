document.addEventListener('DOMContentLoaded', function () {
	var btn = document.getElementById('exitEditorBtn');
	if (!btn) return;
	btn.addEventListener('click', function (e) {
		e.preventDefault();
		console.log('Exit Editor button pressed');
		if (history.length > 1) {
			history.back();
		} else {
			window.location.href = 'dashboard.php';
		}
	});
});


