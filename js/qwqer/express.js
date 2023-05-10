qwqer_controller = document.location.origin+'/qwqer/ajax/';
qwqer = {}; // object
qwqer.vars = []; // global
qwqer.update = {};
qwqer.required = {};
qwqer.required.disable = {};
qwqer.dropdown = {};
Translator = new Translate('en_US');

Validation.add('validate-phone-lv', 'You must enter correct phone', function(v) {
	var phone = v.replace(/\D/g, "");
	console.log(phone, phone.length);
	if(phone.length >= 10) {
		return Validation.get('IsEmpty').test(v) || /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/.test(v);
	}
	return false;
});

qwqer.helper = {
	constructor: {
		createElement : function(container,label,type,attributes,classes,elementStyle,containerStyle){
			var el = new Element(type);
			var keys = Object.keys(attributes);
			var values = Object.values(attributes);
			if(keys && keys.length>0){
				keys.each(function(key, index){
					el.setAttribute(key,values[index]);
				});
			}
			if(classes && classes.length>0){
				classes.each(function(className){
					el.addClassName(className);
				});
			}
			if(elementStyle){
				el.setStyle(elementStyle);
			}
			if(container == true){
				var parentEl = new Element('div');
				parentEl.setStyle({'margin': '5px 0'});
				parentEl.update(el);
				parentEl.prepend(new Element('div').update(label));
				el = parentEl;
				if(containerStyle){
					el.setStyle(containerStyle);
				}
			}
			return el;
		}
	}
}

qwqer.dropdown.address = qwqer.helper.constructor.createElement(
	true,
	Translator.translate('QWQER Express Address'),
	"input",
	{
		id: "qwqer_address",
		type: "text"
	},
	['input-text','address-input'],
	{"width": "100%"},
	{"width": '49%'}
).hide();

qwqer.required.disable.address = function(){
	let element = document.getElementById("qwqer_address");
	element.classList.remove("required-entry");
}

qwqer.required.address = function(){
	qwqer.required.disable.address;
	let element = document.getElementById("qwqer_address");
	element.classList.add("required-entry");
}

qwqer.required.handler = function(el){
	switch(el.getAttribute('id')) {
		case 's_method_qwqer_express_express':
			qwqer.dropdown.address.show();
			qwqer.required.address();
		break;

		default:
			qwqer.dropdown.address.hide();
			qwqer.required.disable.address();
		break;
	}
}
qwqer.init = function(){
	if(document.getElementById("s_method_qwqer_express_express") == undefined) {
		return false;
	} else {
		if(document.getElementById("qwqer_address") == undefined) {
			const element = document.querySelector('input#s_method_qwqer_express_express');
			element.closest('li').insert(qwqer.dropdown.address);

			if(document.getElementById("autocomplete_choices") == undefined) {
				const elementAutocomplete = document.createElement('div');
				elementAutocomplete.setAttribute("id", "autocomplete_choices");
				document.getElementById('qwqer_address').after(elementAutocomplete);
				document.getElementById("autocomplete_choices").classList.add("search-autocomplete");

				ShippingMethod.prototype.validate = ShippingMethod.prototype.validate.wrap(function(parentMethod){
					var methods = document.getElementsByName('shipping_method');
					for (var i=0; i<methods.length; i++) {
						if (methods[i].checked && methods[i].value == 'qwqer_express_express') {
							if(document.getElementById('qwqer_address').value != ''
								&& document.getElementById("autocomplete_choices") !== undefined
								&& document.getElementById("autocomplete_choices").getElementsByClassName('selected').length > 0
								&& document.getElementById('qwqer_address').value != document.getElementById("autocomplete_choices").getElementsByClassName('selected')['0'].innerText
							) {
								alert(Translator.translate('QWQER Delivery option not available'));
								return false;
							}

							if(
								document.getElementById('qwqer_address').value != ''
								&& document.getElementById("autocomplete_choices") !== undefined
								&& document.getElementById("autocomplete_choices").getElementsByClassName('selected').length == 0
							) {
								alert(Translator.translate('QWQER Delivery option not available'));
								return false;
							}
							break;
						}
					}
					return parentMethod();
				});
			}

			new Ajax.Autocompleter("qwqer_address", "autocomplete_choices", qwqer_controller+'address', {
				paramName: "value",
				minChars: 2,
				afterUpdateElement : getSelectionId,
			});

			function getSelectionId(text, li) {
				new Ajax.Request(qwqer_controller+'cost', {
					parameters: {location: li.innerText},
					method: 'GET',
					asynchronous: true,
					onComplete: function (resp) {
						let priceCollected = resp.responseJSON.price;
						const elementPrice = document.querySelector('input#s_method_qwqer_express_express');
						elementPrice.closest('li').getElementsByClassName('price')[0].innerHTML = priceCollected;
						if(!resp.responseJSON.status){
							document.getElementById('qwqer_address').value = '';
							alert(resp.responseJSON.message);
						} else {
							document.getElementById('advice-required-entry-qwqer_address').hide();
							let element = document.getElementById("qwqer_address");
							element.classList.remove("validation-failed");
							element.classList.add("validation-passed");
						}
					}
				});
			}

			function getSavedValue() {
				new Ajax.Request(qwqer_controller+'savedAddress', {
					parameters: {},
					method: 'POST',
					asynchronous: false,
					onComplete: function (resp) {
						document.getElementById('qwqer_address').value = resp.responseJSON.address;
					}
				});
			}

			// Switch "Required"
			var nameOfRadioGroup = $('s_method_qwqer_express_express').getAttribute('name');
			var radioGroup = $$('[name="'+nameOfRadioGroup+'"]');
			// Switch event
			radioGroup.invoke('observe', 'change', function(el) {
				qwqer.required.handler(el.target);
			});

			// And required by default
			radioGroup.each(function(el){
				if(el.checked){
					qwqer.required.handler(el);
				}
			});
		}
	}
	return true;
}