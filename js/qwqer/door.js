qwqerdoor_controller = document.location.origin+'/qwqer/ajax/';
qwqerdoor = {}; // object
qwqerdoor.vars = []; // global
qwqerdoor.update = {};
qwqerdoor.required = {};
qwqerdoor.required.disable = {};
qwqerdoor.dropdown = {};
Translator = new Translate('en_US');

Validation.add('validate-phone-lv', 'You must enter correct phone', function(v) {
	var phone = v.replace(/\D/g, "");
	console.log(phone, phone.length);
	if(phone.length >= 10) {
		return Validation.get('IsEmpty').test(v) || /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/.test(v);
	}
	return false;
});

qwqerdoor.helper = {
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

qwqerdoor.dropdown.address = qwqerdoor.helper.constructor.createElement(
	true,
	Translator.translate('QWQER piegāde Address'),
	"input",
	{
		id: "qwqerdoor_address",
		type: "text"
	},
	['input-text','address-input'],
	{"width": "100%"},
	{"width": '49%'}
).hide();

qwqerdoor.required.disable.address = function(){
	let element = document.getElementById("qwqerdoor_address");
	element.classList.remove("required-entry");
}

qwqerdoor.required.address = function(){
	qwqerdoor.required.disable.address;
	let element = document.getElementById("qwqerdoor_address");
	element.classList.add("required-entry");
}

qwqerdoor.required.handler = function(el){
	switch(el.getAttribute('id')) {
		case 's_method_qwqer_door_express':
			qwqerdoor.dropdown.address.show();
			qwqerdoor.required.address();
		break;

		default:
			qwqerdoor.dropdown.address.hide();
			qwqerdoor.required.disable.address();
		break;
	}
}
qwqerdoor.init = function(){
	if(document.getElementById("s_method_qwqer_door_express") == undefined) {
		return false;
	} else {
		if(document.getElementById("qwqerdoor_address") == undefined) {
			const element = document.querySelector('input#s_method_qwqer_door_express');
			element.closest('li').insert(qwqerdoor.dropdown.address);

			if(document.getElementById("autocomplete_door_choices") == undefined && typeof ShippingMethod != "undefined") {
				const elementAutocomplete = document.createElement('div');
				elementAutocomplete.setAttribute("id", "autocomplete_door_choices");
				document.getElementById('qwqerdoor_address').after(elementAutocomplete);
				document.getElementById("autocomplete_door_choices").classList.add("search-autocomplete");

				ShippingMethod.prototype.validate = ShippingMethod.prototype.validate.wrap(function(parentMethod){
					var methods = document.getElementsByName('shipping_method');
					for (var i=0; i<methods.length; i++) {
						if (methods[i].checked && methods[i].value == 'qwqer_door_express') {
							if(document.getElementById('qwqerdoor_address').value != ''
								&& document.getElementById("autocomplete_door_choices") !== undefined
								&& document.getElementById("autocomplete_door_choices").getElementsByClassName('selected').length > 0
								&& document.getElementById('qwqerdoor_address').value != document.getElementById("autocomplete_door_choices").getElementsByClassName('selected')['0'].innerText
							) {
								alert(Translator.translate('QWQER Delivery option not available'));
								return false;
							}

							if(
								document.getElementById('qwqerdoor_address').value != ''
								&& document.getElementById("autocomplete_door_choices") !== undefined
								&& document.getElementById("autocomplete_door_choices").getElementsByClassName('selected').length == 0
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

			new Ajax.Autocompleter("qwqerdoor_address", "autocomplete_door_choices", qwqerdoor_controller+'address?method=qwqer_door_express', {
				paramName: "value",
				minChars: 2,
				afterUpdateElement : getSelectionId,
			});

			function getSelectionId(text, li) {
				var methods = document.getElementsByName('shipping_method');
				var shippingMethod = false
				for (var i=0; i<methods.length; i++) {
					if (methods[i].checked && methods[i].value == 'qwqer_door_express') {
						shippingMethod = methods[i].value;
					}
				}
				new Ajax.Request(qwqerdoor_controller+'cost', {
					parameters: {location: li.innerText, method: shippingMethod},
					method: 'GET',
					asynchronous: true,
					onComplete: function (resp) {
						let priceCollected = resp.responseJSON.price;
						const elementPrice = document.querySelector('input#s_method_qwqer_door_express');
						elementPrice.closest('li').getElementsByClassName('price')[0].innerHTML = priceCollected;
						if(!resp.responseJSON.status){
							document.getElementById('qwqerdoor_address').value = '';
							alert(resp.responseJSON.message);
						} else {
							document.getElementById('advice-required-entry-qwqerdoor_address').hide();
							let element = document.getElementById("qwqerdoor_address");
							element.classList.remove("validation-failed");
							element.classList.add("validation-passed");
						}
					}
				});
			}

			function getSavedValue() {
				new Ajax.Request(qwqerdoor_controller+'savedAddress', {
					parameters: {},
					method: 'POST',
					asynchronous: false,
					onComplete: function (resp) {
						document.getElementById('qwqerdoor_address').value = resp.responseJSON.address;
					}
				});
			}

			// Switch "Required"
			var nameOfRadioGroup = $('s_method_qwqer_door_express').getAttribute('name');
			var radioGroup = $$('[name="'+nameOfRadioGroup+'"]');
			// Switch event
			radioGroup.invoke('observe', 'change', function(el) {
				qwqerdoor.required.handler(el.target);
			});

			// And required by default
			radioGroup.each(function(el){
				if(el.checked){
					qwqerdoor.required.handler(el);
				}
			});
		}
	}
	return true;
}