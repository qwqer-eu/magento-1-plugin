qwqerparcel_controller = document.location.origin+'/qwqer/ajax/';
qwqerparcel = {}; // object
qwqerparcel.vars = []; // global
qwqerparcel.update = {};
qwqerparcel.required = {};
qwqerparcel.required.disable = {};
qwqerparcel.dropdown = {};
qwqerparcel.parcels = [];
Translator = new Translate('en_US');

Validation.add('validate-phone-lv', 'You must enter correct phone', function(v) {
	var phone = v.replace(/\D/g, "");
	console.log(phone, phone.length);
	if(phone.length >= 10) {
		return Validation.get('IsEmpty').test(v) || /^[\+]?[(]?[0-9]{3}[)]?[-\s\.]?[0-9]{3}[-\s\.]?[0-9]{4,6}$/.test(v);
	}
	return false;
});

Autocompleter.LocalExtend = Class.create(Autocompleter.Local, {
	initialize: function(element, update, array, options) {
		this.baseInitialize(element, update, options);
		this.options.array = array;
		Event.observe(this.element, 'onfocusin', this.activate());
	}
});

qwqerparcel.helper = {
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

qwqerparcel.dropdown.address = qwqerparcel.helper.constructor.createElement(
	true,
	Translator.translate('QWQER Parcel Machines'),
	"input",
	{
		id: "qwqerparcel_address",
		type: "text"
	},
	['input-text','address-input'],
	{"width": "100%"},
	{"width": '49%'}
).hide();

qwqerparcel.required.disable.address = function(){
	let element = document.getElementById("qwqerparcel_address");
	element.classList.remove("required-entry");
}

qwqerparcel.required.address = function(){
	qwqerparcel.required.disable.address;
	let element = document.getElementById("qwqerparcel_address");
	element.classList.add("required-entry");
}

qwqerparcel.required.handler = function(el){
	switch(el.getAttribute('id')) {
		case 's_method_qwqer_parcel_express':
			qwqerparcel.dropdown.address.show();
			qwqerparcel.required.address();
		break;

		default:
			qwqerparcel.dropdown.address.hide();
			qwqerparcel.required.disable.address();
		break;
	}
}

qwqerparcel.init = function(){
	if(document.getElementById("s_method_qwqer_parcel_express") == undefined) {
		return false;
	} else {
		if(document.getElementById("qwqerparcel_address") == undefined && typeof ShippingMethod != "undefined") {
			const element = document.querySelector('input#s_method_qwqer_parcel_express');
			element.closest('li').insert(qwqerparcel.dropdown.address);

			if(document.getElementById("autocomplete_parcel_choices") == undefined) {
								const elementAutocomplete = document.createElement('div');
				elementAutocomplete.setAttribute("id", "autocomplete_parcel_choices");
				document.getElementById('qwqerparcel_address').after(elementAutocomplete);
				document.getElementById("autocomplete_parcel_choices").classList.add("search-autocomplete");
				document.getElementById("autocomplete_parcel_choices").style.display = 'none';
				document.getElementById('qwqerparcel_address').onfocus = function() {
					onShowAutocomplete();
				};

				ShippingMethod.prototype.validate = ShippingMethod.prototype.validate.wrap(function(parentMethod){
					var methods = document.getElementsByName('shipping_method');
					for (var i=0; i<methods.length; i++) {
						if (methods[i].checked && methods[i].value == 'qwqer_parcel_express') {
							if(document.getElementById('qwqerparcel_address').value != ''
								&& document.getElementById("autocomplete_parcel_choices") !== undefined
								&& document.getElementById("autocomplete_parcel_choices").getElementsByClassName('selected').length > 0
								&& document.getElementById('qwqerparcel_address').value != document.getElementById("autocomplete_parcel_choices").getElementsByClassName('selected')['0'].innerText
							) {
								alert(Translator.translate('QWQER Delivery option not available'));
								return false;
							}

							if(
								document.getElementById('qwqerparcel_address').value != ''
								&& document.getElementById("autocomplete_parcel_choices") !== undefined
								&& document.getElementById("autocomplete_parcel_choices").getElementsByClassName('selected').length == 0
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
			function onShowAutocomplete()
			{
				if (qwqerparcel.parcels.length > 0) {
					autocompleteShow(qwqerparcel.parcels);
					return true;
				}

				new Ajax.Request(qwqerparcel_controller+'address?method=qwqer_parcel_express', {
					parameters: {parcels: true},
					method: 'GET',
					asynchronous: true,
					onComplete: function (resp) {
						qwqerparcel.parcels = JSON.parse(resp.responseText);
						autocompleteShow(qwqerparcel.parcels);
					}
				});
			}
			function autocompleteShow(parcelsData)
			{
				new Autocompleter.LocalExtend(
					"qwqerparcel_address",
					"autocomplete_parcel_choices",
					parcelsData,
					{
						choices: parcelsData.length,
						partialSearch: true,
						partialChars: 1,
						ignoreCase: true,
						fullSearch: true,
						minChars: 0,
						afterUpdateElement: getSelectionId,
						selector: function(instance) {
							var ret       = []; // Beginning matches
							var partial   = []; // Inside matches
							var entry     = instance.getToken();
							var count     = 0;
							for (var i = 0; i < instance.options.array.length &&
							ret.length < instance.options.choices ; i++) {
								var elem = instance.options.array[i];
								var foundPos = instance.options.ignoreCase ?
									elem.toLowerCase().indexOf(entry.toLowerCase()) :
									elem.indexOf(entry);
								while (foundPos != -1) {
									if (foundPos == 0 && elem.length != entry.length) {
										ret.push("<li><strong>" + elem.substr(0, entry.length) + "</strong>" +
											elem.substr(entry.length) + "</li>");
										break;
									} else if (entry.length >= instance.options.partialChars &&
										instance.options.partialSearch && foundPos != -1) {
										if (instance.options.fullSearch || /\s/.test(elem.substr(foundPos-1,1))) {
											partial.push("<li>" + elem.substr(0, foundPos) + "<strong>" +
												elem.substr(foundPos, entry.length) + "</strong>" + elem.substr(
													foundPos + entry.length) + "</li>");
											break;
										}
									}

									foundPos = instance.options.ignoreCase ?
										elem.toLowerCase().indexOf(entry.toLowerCase(), foundPos + 1) :
										elem.indexOf(entry, foundPos + 1);

								}
							}
							if (partial.length)
								ret = ret.concat(partial.slice(0, instance.options.choices - ret.length));
							return "<ul style='max-height: 200px; overflow: scroll;'>" + ret.join('') + "</ul>";
						}
					}
				);
			}
			function getSelectionId(text, li) {
				var methods = document.getElementsByName('shipping_method');
				var shippingMethod = false
				for (var i=0; i<methods.length; i++) {
					if (methods[i].checked && methods[i].value == 'qwqer_parcel_express') {
						shippingMethod = methods[i].value;
					}
				}
				new Ajax.Request(qwqerparcel_controller+'cost', {
					parameters: {location: li.innerText, method: shippingMethod},
					method: 'GET',
					asynchronous: true,
					onComplete: function (resp) {
						let priceCollected = resp.responseJSON.price;
						const elementPrice = document.querySelector('input#s_method_qwqer_parcel_express');
						elementPrice.closest('li').getElementsByClassName('price')[0].innerHTML = priceCollected;
						if(!resp.responseJSON.status){
							document.getElementById('qwqerparcel_address').value = '';
							alert(resp.responseJSON.message);
						} else {
							document.getElementById('advice-required-entry-qwqerparcel_address').hide();
							let element = document.getElementById("qwqerparcel_address");
							element.classList.remove("validation-failed");
							element.classList.add("validation-passed");
						}
					}
				});
			}
			function getSavedValue() {
				new Ajax.Request(qwqerparcel_controller+'savedAddress', {
					parameters: {},
					method: 'POST',
					asynchronous: false,
					onComplete: function (resp) {
						document.getElementById('qwqerparcel_address').value = resp.responseJSON.address;
					}
				});
			}

			// Switch "Required"
			var nameOfRadioGroup = $('s_method_qwqer_parcel_express').getAttribute('name');
			var radioGroup = $$('[name="'+nameOfRadioGroup+'"]');
			// Switch event
			radioGroup.invoke('observe', 'change', function(el) {
				qwqerparcel.required.handler(el.target);
			});

			// And required by default
			radioGroup.each(function(el){
				if(el.checked){
					qwqerparcel.required.handler(el);
				}
			});
		}
	}
	return true;
}