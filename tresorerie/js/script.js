$(document).on("dblclick", ".prev:not(:has('input')[type='text'])", function() {
	var le_html = $(this).html();
	var valeur = "";
	valeur = $(this).text();
	$(this).html(le_html+'<input type="text" value="'+valeur+'">');
	$(this).children().focus();
});/*
$(document).on("blur", ".prev", function(){
	var valeur = $(this).find('input[type="text"]').val();
	var input = $(this).find(":hidden").attr('name');
	$(this).html('<input type="hidden" name="'+input+'" value="'+valeur+';'+$(this).data('id')+'">'+valeur);
});*/
$(document).ready(function(){
	$('.prev').keypress(function(e){
		if (e.keyCode == 13) {
			var valeur = $(this).find('input[type="text"]').val();
			var input = $(this).find(":hidden").attr('name');
			$(this).html('<input type="hidden" name="'+input+'" value="'+valeur+';'+$(this).data('id')+'">'+valeur);
		};
	});
});