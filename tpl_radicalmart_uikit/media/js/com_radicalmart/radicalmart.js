/*
 * @package     RadicalMart Uikit Package
 * @subpackage  tpl_radicalmart_uikit
 * @version     __DEPLOY_VERSION__
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2022 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

document.addEventListener('onRadicalMartCartAfterUpdateDisplayData', function (event) {
	let hasProducts = (event.detail && event.detail.total.quantity && event.detail.total.quantity > 0);

	document.querySelectorAll('.radicalmart-icon .quantity').forEach(function (badge) {
		if (hasProducts) {
			badge.style.display = '';
		} else {
			badge.style.display = 'none';
		}
	});

	document.querySelectorAll('.radicalmart-icon[uk-tooltip]').forEach(function (tooltip) {
		if (hasProducts) {
			UIkit.tooltip(tooltip, {title: event.detail.total.sum_seo});
		} else {
			UIkit.tooltip(tooltip, {title: ''});
		}
	});

	let cartPage = document.querySelector('#RadicalMart.cart');
	if (cartPage) {
		let errorLines = cartPage.querySelectorAll('tr[radicalmart-cart="product"].uk-alert.uk-alert-danger');
		errorLines.forEach(function (line) {
			let quantity = line.querySelector('input[name="quantity"]');
			if (quantity) {
				let max = quantity.getAttribute('max');
				if (max) {
					max = parseFloat(max);
					if (max > 0 && parseFloat(quantity.value) <= max) {
						line.classList.remove('uk-alert', 'uk-alert-danger');

						let error = line.querySelector('.error-message');
						if (error) {
							error.remove();
						}
					}
				}
			}
		});

		document.querySelectorAll('[radicalmart-cart="discount-block"], [radicalmart-cart="discount-block"]')
			.forEach(function (block) {
				block.style.display = (event.detail.total.discount > 0) ? '' : 'none';
			});
	}
});

document.addEventListener('onRadicalMartCartBeforeAddProduct', function (event) {
	document.querySelectorAll('[radicalmart-cart="add"], [data-radicalmart-cart="add"]')
		.forEach(function (button) {
			button.setAttribute('disabled', '');
			button.classList.add('uk-disabled');
		});
});

document.addEventListener('onRadicalMartCartAfterAddProduct', function (event) {
	if (!event.detail.error) {
		if (window.RadicalMartTrigger) {
			window.RadicalMartTrigger({
				action: 'add_to_cart',
				product_id: event.detail.entry.product_id,
				quantity: event.detail.entry.quantity,
			})
		}
	}
	document.querySelectorAll('[radicalmart-cart="add"], [data-radicalmart-cart="add"]')
		.forEach(function (button) {
			button.removeAttribute('disabled');
			button.classList.remove('uk-disabled');
		});
});

document.addEventListener('onRadicalMartCartAfterRemoveProduct', function (event) {
	if (!event.detail.error) {
		if (window.RadicalMartTrigger) {
			window.RadicalMartTrigger({
				action: 'remove_from_cart',
				product_id: event.detail.entry.product_id,
			})
		}
		if (event.detail.cart === false) {
			let module = document.querySelector('#radicalmartCartModule');
			if (module) {
				UIkit.offcanvas(document.querySelector('#radicalmartCartModule')).hide();
			}

			let cartView = (Joomla.getOptions('radicalmart_cart') && Joomla.getOptions('radicalmart_cart').cartView);
			if (cartView) {
				window.location.reload();
			}
		}
	}
});

document.addEventListener('onRadicalMartCartRenderLayout', function (event) {
	if (event.detail.name === 'notification_add') {
		UIkit.modal(document.querySelector('#radicalmartCartNotificationAdd')).show();
	} else if (event.detail.name === 'module') {
		UIkit.offcanvas(document.querySelector('#radicalmartCartModule')).show();
	}
});

document.addEventListener('onRadicalMartCartError', function (event) {
	if (event.detail !== 'Request aborted') {
		UIkit.notification(event.detail, {status: 'danger'})
	}

	document.querySelectorAll('[radicalmart-cart="add"], [data-radicalmart-cart="add"]')
		.forEach(function (button) {
			button.removeAttribute('disabled');
			button.classList.remove('uk-disabled');
		});
});


document.addEventListener('onRadicalMartCheckoutBeforeUpdateDisplayData', function (event) {
	if (!window.RadicalMartCheckout().source_data) {
		document.querySelectorAll('[radicalmart-checkout="submit-button"], [data-radicalmart-checkout="submit-button"]')
			.forEach(function (button) {
				button.setAttribute('disabled', '');
				button.classList.add('uk-disabled');
			});
	}
});

document.addEventListener('onRadicalMartCheckoutAfterUpdateDisplayData', function (event) {
	document.querySelectorAll('[radicalmart-checkout="submit-button"], [data-radicalmart-checkout="submit-button"]')
		.forEach(function (button) {
			button.removeAttribute('disabled');
			button.classList.remove('uk-disabled');
		});

	document.querySelectorAll('[radicalmart-checkout="discount-block"], [data-radicalmart-checkout="discount-block"]')
		.forEach(function (block) {
			block.style.display = (event.detail && event.detail.total && event.detail.total.discount > 0) ? '' : 'none';
		});
});


document.addEventListener('onRadicalMartCheckoutAfterCheckData', function (event) {
	if (event.detail) {
		document.querySelectorAll('[radicalmart-checkout="check-error"], [data-radicalmart-checkout="check-error"]')
			.forEach(function (block) {
				block.style.display = (event.detail.success) ? 'none' : '';
			});

		if (!event.detail.success) {
			document.querySelectorAll('[radicalmart-checkout="submit-button"], [data-radicalmart-checkout="submit-button"]')
				.forEach(function (button) {
					button.setAttribute('disabled', '');
					button.classList.add('uk-disabled');
				});
		}
	}
});

document.addEventListener('onRadicalMartCheckoutBeforeReloadForm', function (event) {
	if (event.detail.step === 'shipping') {
		let loading = document.querySelector('#checkout_shipping_loading');
		if (loading) {
			loading.style.display = '';
		}
	} else if (event.detail.step === 'payment') {
		let loading = document.querySelector('#checkout_payment_loading');
		if (loading) {
			loading.style.display = '';
		}
	}
});

document.addEventListener('onRadicalMartCheckoutRenderLayout', function (event) {
	if (event.detail.name === 'login') {
		UIkit.modal(document.querySelector('#radicalmartCheckoutLogin')).show();
	}
});

document.addEventListener('onRadicalMartCheckoutError', function (event) {
	if (event.detail !== 'Request aborted') {
		UIkit.notification(event.detail, {status: 'danger'})
	}

	document.querySelectorAll('[radicalmart-checkout="submit-button"], [data-radicalmart-checkout="submit-button"]')
		.forEach(function (button) {
			button.removeAttribute('disabled');
			button.classList.remove('uk-disabled');
		});
});

document.addEventListener('DOMContentLoaded', function () {
	let productLightbox = document.querySelector('#RadicalMart.product .product-gallery .uk-slideshow-items');
	if (productLightbox) {
		UIkit.lightbox(productLightbox, {
			container: '#RadicalMart.product'
		});
	}
});