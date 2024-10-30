jQuery(document).ready(function ($) {
	// Handle generating the BigPurple AI Writer button
	const newLink = $('<li>', {
		id: 'bigpurple-ai-writer-link',
		html: $('<a>', {
			href: '#',
			class: 'ab-item',
			html: $('<i>', {
				class: 'ab-icon'
			}),
			append: $('<span>', {
				class: 'ab-label',
				text: 'BigPurple AI Writer'
			})
		})
	});

	const modal = $('<div>', {
		id: 'bigpurple-ai-writer-modal',
		style: 'display: none;',
		html: document.getElementById('bigpurple-ai-writer-modal-body').innerHTML
	});

	// Create an overlay div
	const overlay = $('<div>', {
		id: 'bigpurple-ai-writer-overlay',
		style: 'display: none;'
	});

	$('#wp-admin-bar-root-default').append(newLink);
	$('body').append(modal, overlay); // append overlay to the body
	
	// Open and close the modal
	$('#bigpurple-ai-writer-link').click(function (event) {
		event.preventDefault();
		$('#bigpurple-ai-writer-modal, #bigpurple-ai-writer-overlay').show();
	});

	$('#bigpurple-ai-writer-close').click(function (event) {
		event.preventDefault();
		$('#bigpurple-ai-writer-modal, #bigpurple-ai-writer-overlay').hide();
	});

	$('#bigpurple-ai-writer-overlay').click(function (event) {
		event.preventDefault();
		$('#bigpurple-ai-writer-modal, #bigpurple-ai-writer-overlay').hide();
	});

	// Shortcuts
	$(document).keyup(function (event) {
		// Close the modal if the Escape key is pressed
		if (event.key === "Escape") {
			$('#bigpurple-ai-writer-modal, #bigpurple-ai-writer-overlay').hide();
		}
	});

	$('#bigpurple-ai-writer-input').keyup(function (event) {
		// Send chat message if Enter key is pressed and input has text
		if (event.key === "Enter" && $(this).val().trim() !== "") {
			$('#bigpurple-ai-writer-send').click();
		}
	});

	// Initialize chat history
	const chatHistory = [];

	// Handle sending the chat message
	$('#bigpurple-ai-writer-send').click(function (event) {
		event.preventDefault();

		const input = $('#bigpurple-ai-writer-input');
		const message = input.val().trim();
		const messages = $('#bigpurple-ai-writer-messages');
		const loading = $('#bigpurple-ai-writer-loading');
		const intro = $('#bigpurple-ai-writer-intro');

		messages.append($('<div>', {
			class: 'bigpurple-ai-writer-message',
			text: message
		}));

		chatHistory.push({
			role: 'user',
			content: message
		});

		loading.show();

		window.SoflyyAiToolkit.chat('bigpurple-ai-writer', chatHistory)
			.then(response => {
				const responseContainer = $('<div>', {
					class: 'bigpurple-ai-writer-response-container'
				});

				const responseMessage = $('<div>', {
					class: 'bigpurple-ai-writer-response',
					text: response
				});

				const copyButton = $('<button>', {
					class: 'bigpurple-ai-writer-copy-button',
					text: 'Copy',
					click: function () {
						const button = $(this);
						const tempElement = $('<textarea>').val(response).appendTo('body').select();
						document.execCommand('copy');
						tempElement.remove();
						button.text('Copied!');
						button.prop('disabled', true);
					}
				});

				responseContainer.append(responseMessage);
				responseContainer.append(copyButton);
				messages.append(responseContainer);

				chatHistory.push({
					role: 'assistant',
					content: response
				});

				// Check if there are any messages in the chat history
				intro.toggle(chatHistory.length === 0);

				// Scroll to the bottom of the messages
				messages.scrollTop(messages[0].scrollHeight);
			})
			.catch(error => {
				// Handle error
				console.error(error);
			})
			.finally(() => {
				loading.hide();
				input.val('');
			});
	});
});
