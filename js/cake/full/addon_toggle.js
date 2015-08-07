/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined)
{
	if (typeof Cake === "undefined") Cake = {};

	Cake.AddOnToggle = function($form)
	{
		$input = $form.find('input[type=checkbox]');
		
		function update()
		{
			var cakeDisabled = false;
			
			$input.each(function()
			{
				if ($(this).attr('name').substring(2, 8) == '[Cake]') {
					if (!$(this).attr('checked')) {
						cakeDisabled = true;
					}
				}
			});
			
			$input.each(function()
			{
				if ($(this).attr('name').substring(2, 8) == '[Cake_') {
					if (cakeDisabled) {
						$(this).attr('disabled','disabled').addClass('disabled');
						$(this).attr('checked', false);
					} else {
						$(this).removeAttr('disabled').removeClass('disabled');
					}
				}
			});
		}
		
		var $checkboxes = $input.click(update);
	};

    XenForo.register('.CakeAddOnToggle', 'Cake.AddOnToggle');
}
(jQuery, this, document);