jQuery(document).ready ($) ->

	$('iframe[src*="youtube"]').each ->
		iframe = $ this
		div = $ '<div />'
		div.insertBefore iframe
		iframe.detach().appendTo div
		p = 100
		w = parseInt iframe.attr('width')
		h = parseInt iframe.attr('height')
		p = h/w
		div.css
			height: '0'
			paddingBottom: "#{p*100.0}%"
			position: 'relative'
		iframe.css
			position: 'absolute'
			top: 0
			left: 0
			width: '100%'
			height: '100%'
			display: 'block'

	$('.collapse-small-screens').each ->
		div = $ this
		div.parent().addClass('row-collapse-small-screens')