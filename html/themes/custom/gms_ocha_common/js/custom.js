(function ($) {
	$("#field_page_type_page").change(function(){
		$('#edit-field-cerf-menu').val('All');
		$('#edit-field-cbpf-menu').val('All');
	});
	const chatbox = jQuery.noConflict();
	chatbox(() => {
		chatbox(".chatbox-open").click(() =>
		chatbox(".chatbox-popup, .chatbox-close").fadeIn()
		);
		chatbox(".chatbox-close").click(() =>
		chatbox(".chatbox-popup, .chatbox-close").fadeOut()
		);
	});
	const styleOptions = {
		botAvatarInitials: 'BT',
		botAvatarImage: 'https://bot-framework.azureedge.net/bot-icons-v1/6bfe444d-6c2a-4571-809e-67ca9f886ccc_4ws6nG5VCAjj65d33E13fEhG7Y12iO3zaAEdy74AE9Ek2qW.png',
		userAvatarInitials: 'BT',
		userAvatarImage: 'https://content.powerapps.com/resource/makerx/static/media/user.417aa99d.svg',
		hideUploadButton: true	
	};
	var theURL = "https://default0f9e35db544f4f60bdcc5ea416e6dc.70.environment.api.powerplatform.com/powervirtualagents/botsbyschema/new_bot_6bfe444d6c2a4571809e67ca9f886ccc/directline/token?api-version=2022-03-01-preview";
	var environmentEndPoint = theURL.slice(0,theURL.indexOf('/powervirtualagents'));
	var apiVersion = theURL.slice(theURL.indexOf('api-version')).split('=')[1];
	var regionalChannelSettingsURL = `${environmentEndPoint}/powervirtualagents/regionalchannelsettings?api-version=${apiVersion}`;
	var directline;
	fetch(regionalChannelSettingsURL)
	.then((response) => {
		return response.json();
	})
	.then((data) => {
		directline = data.channelUrlsById.directline;
	})
	.catch(err => console.error("An error occurred: " + err));
	fetch(theURL)
	.then(response => response.json())
	.then(conversationInfo => {
		window.WebChat.renderWebChat({
			directLine: window.WebChat.createDirectLine({
				domain: `${directline}v3/directline`,
				token: conversationInfo.token,
			}),
				styleOptions
			},
			document.getElementById('webchat')
		);
	})
	.catch(err => console.error("An error occurred: " + err));
}(jQuery));
