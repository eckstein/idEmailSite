jQuery(document).ready(function ($) {

	//Show the full template code 
	$('#showFullCode').on('click', function () {
		$('#fullScreenCode').show();
		$('#iDoverlay').show();
	});
	$('#hideFullCode').on('click', function () {
		$('#fullScreenCode').hide();
		$('#iDoverlay').hide();
	});

	//Show mobile preview
	$('#showMobile').click(function () {
		$.ajax({
			url: idAjax.ajaxurl,
			type: 'POST',
			data: {
				action: 'load_mobile_css',
				css_file: '/styles/inlineStyles-mobile.css',
			},
			success: function (response) {
				$('#inline-styles').html(response);
				$('#templatePreview').addClass('mobileMode');
				$('#showMobile').addClass('active');
				$('#showDesktop').removeClass('active');
			},
			error: function (xhr, status, error) {
				console.log('Error: ' + error);
			}
		});
	});
	//Show desktop preview
	$('#showDesktop').click(function () {
		$.ajax({
			url: idAjax.ajaxurl,
			type: 'POST',
			data: {
				action: 'load_mobile_css',
				css_file: '/styles/inlineStyles.css',
			},
			success: function (response) {
				$('#inline-styles').html(response);
				$('#templatePreview').removeClass('mobileMode');
				$('#showDesktop').addClass('active');
				$('#showMobile').removeClass('active');
			},
			error: function (xhr, status, error) {
				console.log('Error: ' + error);
			}
		});
	});

	//Save ACF form on click outside of form
	$('#saveTemplate').on('click', function () {
		$('.acf-form-submit input').click();
	});
	

	$('#copyCode').on('click', function () {
		var html = $('#generatedCode code').text();
		var tempInput = document.createElement("textarea");
		tempInput.style = "position: absolute; left: -1000px; top: -1000px";
		tempInput.innerHTML = html;
		document.body.appendChild(tempInput);
		tempInput.select();
		document.execCommand("copy");
		document.body.removeChild(tempInput);
		$('.copyConfirm').fadeIn(1000, function () {
			setTimeout(function () {
				$('.copyConfirm').fadeOut(1000);
			}, 5000);
		});
	});


	$('#copyCodeSnippet').click(function () {
		var html = $('.codeChunk:visible code').text();
		var tempInput = document.createElement("textarea");
		tempInput.style = "position: absolute; left: -1000px; top: -1000px";
		tempInput.innerHTML = html;
		document.body.appendChild(tempInput);
		tempInput.select();
		document.execCommand("copy");
		document.body.removeChild(tempInput);
		$('.copySnippetConfirm').fadeIn(1000, function () {
			setTimeout(function () {
				$('.copySnippetConfirm').fadeOut(1000);
			}, 5000);
		});
	});


	// Show block code in pop-up
	$('.showChunkCode').on('click', function (e) {

		//var blockKey = $(this).attr('data-id');
		// var codeBlock = $('#'+blockKey);
		var codeBlock = $(this).siblings('.hiddenCodeChunk:first').html();
		console.log('code block: ' + codeBlock);

		Swal.fire({
			title: 'Chunk Code: ' + $(this).closest('.chunkWrap').attr('data-chunk-layout'),
			html: codeBlock,
			customClass: {
				container: 'code-popup',
			},
			heightAuto: false, //height set with css
			width: '800px',
			showCancelButton: true,
			cancelButtonText: 'Close',
			confirmButtonText: 'Copy Code',
		}).then((copyCode) => {
			if (copyCode.isConfirmed) {
				var html = $('.swal2-html-container code').text();
				var tempInput = document.createElement("textarea");
				tempInput.style = "position: absolute; left: -1000px; top: -1000px";
				tempInput.innerHTML = html;
				document.body.appendChild(tempInput);
				tempInput.select();
				document.execCommand("copy");
				document.body.removeChild(tempInput);
				Swal.fire({
					title: "Code copied!",
					icon: "success",
				}).then(() => {
					$("#iDoverlay").hide();
				});
			} else {
				//nada
			}
		});


	});

	//Activate the ACF chunk when a preview chunk is clicked
	function chunkWrapActivate(chunk) {
		var editorID = $(chunk).attr('data-id');
		var attachedEditor = $('.layout[data-id="' + editorID + '"');
		$('.layout').addClass('-collapsed');
		$('.chunkWrap').removeClass('active');
		$(chunk).addClass('active');
		attachedEditor.removeClass('-collapsed').trigger('editorActivated');

		// Switch to the main editor tab if we're on the settings tab
		$('a[data-key="field_63e3d761ed5b4"]').click();
	}


	//collapse all acf groups on page load
	$('.layout').addClass('-collapsed');

	$('.acf-fc-layout-handle').on('click', function () {
		var chunkID = $(this).parent('.layout').attr('data-id');
		$(this).parent('.layout')
			.siblings('.layout')
			.addClass('-collapsed')
			.children('.chunkWrap')
			.removeClass('active');
		$('.chunkWrap[data-id="' + chunkID + '"]')
			.addClass('active');
		if ($(this).parent('.layout').hasClass('-collapsed')) {
			$([document.documentElement, document.body])
				.animate({
					scrollTop: $('.chunkWrap[data-id="' + chunkID + '"]').offset().top - 100
				}, 500);
		} else {
			$('.chunkWrap[data-id="' + chunkID + '"]').removeClass('active');
		}
	});


	//When a chunk of preview is clicked, we open the chunk editor
	$('.chunkWrap').on('click', function (e) {
		e.preventDefault();
		chunkWrapActivate(this);
		//$([document.documentElement, document.body]).animate({
		//scrollTop: $(this).offset().top - 100
		//}, 500);
	});

$('#templateTable').on('click', '.folderMenu i', function() {
	$('.archive.category .aboveTemplateTable .folderMenu ul').slideToggle();
});

});