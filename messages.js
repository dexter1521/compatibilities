
function myMessages(icon, title, text, timer = 3000) {
	Swal.fire({
		icon,
		title,
		text,
		timer,
		showConfirmButton: false,
		timerProgressBar: true,
	});
}

function emptyMessage(timeout = 5000) {
	setTimeout(() => {
		$("#messages").hide("slow");
	}, timeout);
}

function emptyText() {
	const delays = {
		info: 500,
		warning: 2000,
		danger: 500,
		success: 500,
	};

	const durations = {
		info: 3000,
		warning: 6000,
		danger: 3000,
		success: 3000,
	};

	['info', 'warning', 'danger', 'success'].forEach(type => {
		$(`.text-${type}`).delay(delays[type]).show(10, function() {
			$(this).delay(durations[type]).hide(10, function() {
				$(this).remove();
			});
		});
	});
}

function showMessage(type, message, timeout = 5000) {
	const alertClass = {
		success: 'alert-success',
		warning: 'alert-warning',
		danger: 'alert-danger',
	} [type] || 'alert-danger';

	$("#messages").empty().html(`
		<div class="alert ${alertClass} alert-dismissible fade show" role="alert">
			${message}
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	`).show();

	if (timeout > 0) {
		setTimeout(() => {
			$("#messages .alert").alert('close');
		}, timeout);
	}
}

function clearFieldErrors(formSelector) {
	if (formSelector) {
		const $form = $(formSelector);
		$form.find('.is-invalid').removeClass('is-invalid');
		$form.find('.invalid-feedback').remove();
	}
	$('.text-danger').remove();
}

function handleValidationErrors(messages, timeout = 5000, formSelector = null) {
	// Limpia errores previos
	clearFieldErrors(formSelector);
	$("#messages").empty();

	if (typeof messages === 'string') {
		showMessage('danger', messages);
		return;
	}

	if (typeof messages === 'object') {
		for (var key in messages) {
			if (!messages.hasOwnProperty(key)) continue;
			var message = messages[key];
			if (!message) continue;

			var $field = $('#' + key);
			if (formSelector && $field.length) {
				// Estilo Bootstrap
				$field.addClass('is-invalid');
				// Evitar duplicados
				if (!$field.next('.invalid-feedback').length) {
					$field.after('<div class="invalid-feedback">' + message + '</div>');
				}
			} else {
				// Fallback a texto simple si no se encuentra el campo
				$('#' + key).after('<span class="text-danger">' + message + '</span>');
			}
		}
	} else {
		console.error("Formato de error desconocido:", messages);
	}

	if (timeout > 0) {
		setTimeout(() => {
			if (formSelector) {
				const $form = $(formSelector);
				$form.find('.is-invalid').removeClass('is-invalid');
				$form.find('.invalid-feedback').fadeOut('slow', function(){ $(this).remove(); });
			}
			$('.text-danger').fadeOut('slow', function() { $(this).remove(); });
		}, timeout);
	}
}

function handleSuccess(successMessage, messages) {
	const combinedMessage = messages && typeof messages === 'object' ?
		Object.values(messages).join('<br/>') :
		messages;

	showMessage('success', `${successMessage}<br/>${combinedMessage || ''}`);
}

function handleAjaxError(xhr) {
	$("#messages").empty();

	const errorMessage = xhr.responseJSON?.message ||
		xhr.statusText ||
		`Error ${xhr.status}` ||
		"Ocurrió un error.";

	showMessage('danger', errorMessage);
}

function mayusculas(e) {
	e.value = e.value.toUpperCase();
}

function minusculas(e) {
	e.value = e.value.toLowerCase();
}

function validarNumerosDecimales(input) {
	input.value = input.value.replace(/[^0-9.]/g, '');
}

function previewImage(input, imgId) {
	var imgElement = document.getElementById(imgId);
	if (input.files && input.files[0]) {
		var reader = new FileReader();
		reader.onload = function (e) {
			imgElement.src = e.target.result;
			imgElement.style.display = 'block';
		};
		reader.readAsDataURL(input.files[0]);
	} else {
		imgElement.style.display = 'none';
	}
}
