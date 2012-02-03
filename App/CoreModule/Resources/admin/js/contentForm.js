$(document).ready(function () {

	var nodiac = { 'á':'a', 'č':'c', 'ď':'d', 'é':'e', 'ě':'e', 'í':'i', 'ň':'n', 'ó':'o', 'ř':'r', 'š':'s', 'ť':'t', 'ú':'u', 'ů':'u', 'ý':'y', 'ž':'z' };

	/** Vytvoření přátelského URL
	 * @param string řetězec, ze kterého se má vytvořit URL
	 * @return string řetězec obsahující pouze čísla, znaky bez diakritiky, podtržítko a pomlčku
	 * @copyright Jakub Vrána, http://php.vrana.cz/
	 */
	function make_url(s) {
		s = s.toLowerCase();
		var s2 = '';
		for (var i = 0; i < s.length; i++) {
			s2 += (typeof nodiac[s.charAt(i)] != 'undefined' ? nodiac[s.charAt(i)] : s.charAt(i));
		}
		return s2.replace(/[^a-z0-9_]+/g, '-').replace(/^-|-$/g, '');
	}

	function makeUrl() {
		if ($(".venne-form input[name='mainPage']").is(':checked')) {
			$(".venne-form input[name='localUrl']").val("");
			$(".venne-form input[name='localUrl']").attr("disabled", true);
			$(".venne-form select[name='parent']").attr("disabled", true);
		} else {
			$(".venne-form input[name='localUrl']").val(make_url($(".venne-form input[name='title']").val()));
			$(".venne-form input[name='localUrl']").attr("disabled", false);
			$(".venne-form select[name='parent']").attr("disabled", false);
		}
	}

	$(".venne-form input[name='title']").live("change", function () {
		makeUrl();
	});

	$(".venne-form select[name='parent']").live("change", function () {
		makeUrl();
	});

	$(".venne-form input[name='mainPage']").live("change", function () {
		makeUrl();
	});
});
