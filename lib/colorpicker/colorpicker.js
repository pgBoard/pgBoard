/*
Copyright (c) 2007 John Dyer (http://johndyer.name)
MIT style license
*/

if (!window.Refresh) Refresh = {};
if (!Refresh.Web) Refresh.Web = {};

Refresh.Web.DefaultColorPickerSettings = {
	startMode:'h',
	startHex:'ff0000',
	clientFilesPath: '/lib/colorpicker/'
};

Refresh.Web.ColorPicker = Class.create();
Refresh.Web.ColorPicker.prototype = {

	initialize: function(id, settings) {
		this.id = id;
		this.settings = Object.extend(Object.extend({},Refresh.Web.DefaultColorPickerSettings), settings || {});

		// attach radio & check boxes
		this._hueRadio = $(this.id + '_HueRadio');
		this._saturationRadio = $(this.id + '_SaturationRadio');
		this._valueRadio = $(this.id + '_BrightnessRadio');
		
		this._redRadio = $(this.id + '_RedRadio');
		this._greenRadio = $(this.id + '_GreenRadio');
		this._blueRadio = $(this.id + '_BlueRadio');
		//this._webSafeCheck = $(this.id + '_WebSafeCheck');

		this._hueRadio.value = 'h';
		this._saturationRadio.value = 's';
		this._valueRadio.value = 'v';
		
		this._redRadio.value = 'r';
		this._greenRadio.value = 'g';
		this._blueRadio.value = 'b';

		// attach events to radio & checks

		this._event_onRadioClicked = this._onRadioClicked.bindAsEventListener(this);

		Event.observe( this._hueRadio,'click', this._event_onRadioClicked);
		Event.observe( this._saturationRadio,'click', this._event_onRadioClicked);
		Event.observe( this._valueRadio,'click', this._event_onRadioClicked);
		
		Event.observe( this._redRadio,'click', this._event_onRadioClicked);
		Event.observe( this._greenRadio,'click', this._event_onRadioClicked);
		Event.observe( this._blueRadio,'click', this._event_onRadioClicked);

		//this._event_webSafeClicked = this._onWebSafeClicked.bindAsEventListener(this);
		//Event.observe( this._webSafeCheck, 'click', this._event_webSafeClicked);


		// attach simple properties
		this._preview = $(this.id + '_Preview');
		
		
		// MAP
		this._mapBase = $(this.id + '_ColorMap');
		this._mapBase.style.width = '256px';
		this._mapBase.style.height = '256px';
		this._mapBase.style.padding = 0;
		this._mapBase.style.margin = 0;
		this._mapBase.style.border = 'solid 1px #000';
		
		
		this._mapL1 = new Element('img',{src:this.settings.clientFilesPath + 'blank.gif', width:256, height:256} ); //'blank.gif'});
		this._mapL1.style.margin = '0px';
		this._mapL1.style.display = 'block';
		this._mapBase.appendChild(this._mapL1);				
	
		this._mapL2 = new Element('img',{src:this.settings.clientFilesPath + 'blank.gif', width:256, height:256} ); //'blank.gif'});
		this._mapBase.appendChild(this._mapL2);
		this._mapL2.style.clear = 'both';
		this._mapL2.style.margin = '-256px 0px 0px 0px';
		this._mapL2.setOpacity(.5);
		this._mapL2.style.display = 'block';
		
		// BAR
		this._bar = $(this.id + '_ColorBar');
		this._bar.style.width = '20px';
		this._bar.style.height = '256px';
		this._bar.style.padding = 0;
		this._bar.style.margin = '0px 10px';
		this._bar.style.border = 'solid 1px #000';	
		
		this._barL1 = new Element('img',{src:this.settings.clientFilesPath + 'blank.gif', width:20, height:256});
		this._barL1.style.margin = '0px';
		this._barL1.style.display = 'block';	
		this._bar.appendChild(this._barL1);			

		this._barL2 = new Element('img',{src:this.settings.clientFilesPath + 'blank.gif', width:20, height:256} );
		this._barL2.style.margin = '-256px 0px 0px 0px';
		this._barL2.style.display = 'block';	
		this._bar.appendChild(this._barL2);
		
		this._barL3 = new Element('img',{src:this.settings.clientFilesPath + 'blank.gif', width:20, height:256} );
		this._barL3.style.margin = '-256px 0px 0px 0px';
		this._barL3.style.backgroundColor = '#ff0000';
		this._barL3.style.display = 'block';	
		this._bar.appendChild(this._barL3);
		
		this._barL4 = new Element('img',{src:this.settings.clientFilesPath + 'bar-brightness.png', width:20, height:256} );
		this._barL4.style.margin = '-256px 0px 0px 0px';
		this._barL4.style.display = 'block';	
		this._bar.appendChild(this._barL4);				
		
		// attach map slider
		this._map = new Refresh.Web.Slider(this._mapL2, {xMaxValue: 255, yMinValue: 255, arrowImage: this.settings.clientFilesPath + 'mappoint.gif'});

		// attach color slider
		this._slider = new Refresh.Web.Slider(this._barL4, {xMinValue: 1,xMaxValue: 1, yMinValue: 255, arrowImage: this.settings.clientFilesPath + 'rangearrows.gif'});;

		// attach color values
		this._cvp = new Refresh.Web.ColorValuePicker(this.id);

		// link up events
		var cp = this;
		
		this._slider.onValuesChanged = function() { cp.sliderValueChanged() };
		this._map.onValuesChanged = function() { cp.mapValueChanged(); }
		this._cvp.onValuesChanged = function() { cp.textValuesChanged(); }

		// browser!
		this.isLessThanIE7 = false;
		var version = parseFloat(navigator.appVersion.split("MSIE")[1]);
		if ((version < 7) && (document.body.filters))
			this.isLessThanIE7 = true;
		

		// initialize values
		this.setColorMode(this.settings.startMode);
		if (this.settings.startHex)
			this._cvp._hexInput.value = this.settings.startHex;
		this._cvp.setValuesFromHex();
		this.positionMapAndSliderArrows();
		this.updateVisuals();
		
		this.color = null;
	},
	show: function() {
		this._map.Arrow.style.display = '';
		this._slider.Arrow.style.display = '';
		this._map.setPositioningVariables();
		this._slider.setPositioningVariables();
		this.positionMapAndSliderArrows();
	},
	hide: function() {
		this._map.Arrow.style.display = 'none';
		this._slider.Arrow.style.display = 'none';
	},
	_onRadioClicked: function(e) {
		this.setColorMode(e.target.value);
	},
	_onWebSafeClicked: function(e) {
		// reset
		this.setColorMode(this.ColorMode);
	},
	textValuesChanged: function() {
		this.positionMapAndSliderArrows();
		this.updateVisuals();
	},
	setColorMode: function(colorMode) {

		this.color = this._cvp.color;
		
		// reset all images		
		function resetImage(cp, img) {
			cp.setAlpha(img, 100);	
			img.style.backgroundColor = '';
			img.src = cp.settings.clientFilesPath + 'blank.gif';
			img.style.filter = '';
		}
		resetImage(this, this._mapL1);
		resetImage(this, this._mapL2);
		resetImage(this, this._barL1);
		resetImage(this, this._barL2);
		resetImage(this, this._barL3);
		resetImage(this, this._barL4);

		this._hueRadio.checked = false;
		this._saturationRadio.checked = false;
		this._valueRadio.checked = false;
		this._redRadio.checked = false;
		this._greenRadio.checked = false;
		this._blueRadio.checked = false;
	

		switch (colorMode) {
			case 'h':
				this._hueRadio.checked = true;

				// MAP
				// put a color layer on the bottom
				this._mapL1.style.backgroundColor = '#' + this.color.hex;				

				// add a hue map on the top
				this._mapL2.style.backgroundColor = 'transparent';
				this.setImg(this._mapL2, this.settings.clientFilesPath + 'map-hue.png');
				this.setAlpha(this._mapL2, 100);

				// SLIDER
				// simple hue map
				this.setImg(this._barL4,this.settings.clientFilesPath + 'bar-hue.png');

				this._map.settings.xMaxValue = 100;
				this._map.settings.yMaxValue = 100;
				this._slider.settings.yMaxValue = 359;

				break;
				
			case 's':
				this._saturationRadio.checked = true;			

				// MAP
				// bottom has saturation map
				this.setImg(this._mapL1, this.settings.clientFilesPath + 'map-saturation.png');

				// top has overlay
				this.setImg(this._mapL2, this.settings.clientFilesPath + 'map-saturation-overlay.png');
				this.setAlpha(this._mapL2,0);

				// SLIDER
				// bottom: color
				this.setBG(this._barL3, this.color.hex);
				
				// top: graduated overlay
				this.setImg(this._barL4, this.settings.clientFilesPath + 'bar-saturation.png');
				

				this._map.settings.xMaxValue = 359;
				this._map.settings.yMaxValue = 100;
				this._slider.settings.yMaxValue = 100;

				break;
				
			case 'v':
				this._valueRadio.checked = true;			

				// MAP
				// bottom: nothing
				
				// top
				this.setBG(this._mapL1,'000');
				this.setImg(this._mapL2, this.settings.clientFilesPath + 'map-brightness.png');				
				
				// SLIDER
				// bottom
				this._barL3.style.backgroundColor = '#' + this.color.hex;
				
				// top				
				this.setImg(this._barL4, this.settings.clientFilesPath + 'bar-brightness.png');
				

				this._map.settings.xMaxValue = 359;
				this._map.settings.yMaxValue = 100;
				this._slider.settings.yMaxValue = 100;
				break;
				
			case 'r':
				this._redRadio.checked = true;
				this.setImg(this._mapL2, this.settings.clientFilesPath + 'map-red-max.png');
				this.setImg(this._mapL1, this.settings.clientFilesPath + 'map-red-min.png');
				
				this.setImg(this._barL4, this.settings.clientFilesPath + 'bar-red-tl.png');
				this.setImg(this._barL3, this.settings.clientFilesPath + 'bar-red-tr.png');
				this.setImg(this._barL2, this.settings.clientFilesPath + 'bar-red-br.png');
				this.setImg(this._barL1, this.settings.clientFilesPath + 'bar-red-bl.png');				
				
				break;

			case 'g':
				this._greenRadio.checked = true;
				this.setImg(this._mapL2, this.settings.clientFilesPath + 'map-green-max.png');
				this.setImg(this._mapL1, this.settings.clientFilesPath + 'map-green-min.png');
				
				this.setImg(this._barL4, this.settings.clientFilesPath + 'bar-green-tl.png');
				this.setImg(this._barL3, this.settings.clientFilesPath + 'bar-green-tr.png');
				this.setImg(this._barL2, this.settings.clientFilesPath + 'bar-green-br.png');
				this.setImg(this._barL1, this.settings.clientFilesPath + 'bar-green-bl.png');				
				
				break;
				
			case 'b':
				this._blueRadio.checked = true;
				this.setImg(this._mapL2, this.settings.clientFilesPath + 'map-blue-max.png');
				this.setImg(this._mapL1, this.settings.clientFilesPath + 'map-blue-min.png');
				
				this.setImg(this._barL4, this.settings.clientFilesPath + 'bar-blue-tl.png');
				this.setImg(this._barL3, this.settings.clientFilesPath + 'bar-blue-tr.png');
				this.setImg(this._barL2, this.settings.clientFilesPath + 'bar-blue-br.png');
				this.setImg(this._barL1, this.settings.clientFilesPath + 'bar-blue-bl.png');
				
				//this.setImg(this._barL4, this.settings.clientFilesPath + 'bar-hue.png');			
				
				break;
				
			default:
				alert('invalid mode');
				break;
		}
		
		switch (colorMode) {
			case 'h':
			case 's':
			case 'v':
			
				this._map.settings.xMinValue = 1;
				this._map.settings.yMinValue = 1;				
				this._slider.settings.yMinValue = 1;
				break;
				
			case 'r':
			case 'g':
			case 'b':
			
				this._map.settings.xMinValue = 0;
				this._map.settings.yMinValue = 0;				
				this._slider.settings.yMinValue = 0;					
				
				this._map.settings.xMaxValue = 255;
				this._map.settings.yMaxValue = 255;				
				this._slider.settings.yMaxValue = 255;	
				break;
		}
				
		this.ColorMode = colorMode;

		this.positionMapAndSliderArrows();
		
		this.updateMapVisuals();
		this.updateSliderVisuals();
	},
	mapValueChanged: function() {
		// update values

		switch(this.ColorMode) {
			case 'h':
				this._cvp._saturationInput.value = this._map.xValue;
				this._cvp._valueInput.value = 100 - this._map.yValue;
				break;
				
			case 's':
				this._cvp._hueInput.value = this._map.xValue;
				this._cvp._valueInput.value = 100 - this._map.yValue;
				break;
				
			case 'v':
				this._cvp._hueInput.value = this._map.xValue;
				this._cvp._saturationInput.value = 100 - this._map.yValue;
				break;
								
			case 'r':
				this._cvp._blueInput.value = this._map.xValue;
				this._cvp._greenInput.value = 256 - this._map.yValue;
				break;
				
			case 'g':
				this._cvp._blueInput.value = this._map.xValue;
				this._cvp._redInput.value = 256 - this._map.yValue;
				break;
				
			case 'b':
				this._cvp._redInput.value = this._map.xValue;
				this._cvp._greenInput.value = 256 - this._map.yValue;
				break;				
		}
		
		switch(this.ColorMode) {
			case 'h':
			case 's':
			case 'v':
				this._cvp.setValuesFromHsv();
				break;
				
			case 'r':
			case 'g':
			case 'b':
				this._cvp.setValuesFromRgb();
				break;				
		}		

		
		this.updateVisuals();
	},
	sliderValueChanged: function() {
		
		switch(this.ColorMode) {
			case 'h':
				this._cvp._hueInput.value = 360 - this._slider.yValue;
				break;
			case 's':
				this._cvp._saturationInput.value = 100 - this._slider.yValue;
				break;
			case 'v':
				this._cvp._valueInput.value = 100 - this._slider.yValue;
				break;
				
			case 'r':
				this._cvp._redInput.value = 255 - this._slider.yValue;
				break;
			case 'g':
				this._cvp._greenInput.value = 255 - this._slider.yValue;
				break;
			case 'b':
				this._cvp._blueInput.value = 255 - this._slider.yValue;
				break;				
		}
		
		switch(this.ColorMode) {
			case 'h':
			case 's':
			case 'v':
				this._cvp.setValuesFromHsv();
				break;
				
			case 'r':
			case 'g':
			case 'b':
				this._cvp.setValuesFromRgb();
				break;				
		}		

		this.updateVisuals();
	},
	positionMapAndSliderArrows: function() {
		this.color = this._cvp.color;
		
		// Slider
		var sliderValue = 0;
		switch(this.ColorMode) {
			case 'h':
				sliderValue = 360 - this.color.h;
				break;
			
			case 's':
				sliderValue = 100 - this.color.s;
				break;
				
			case 'v':
				sliderValue = 100 - this.color.v;
				break;
				
			case 'r':
				sliderValue = 255- this.color.r;
				break;
			
			case 'g':
				sliderValue = 255- this.color.g;
				break;
				
			case 'b':
				sliderValue = 255- this.color.b;
				break;				
		}	
		
		this._slider.yValue = sliderValue;
		this._slider.setArrowPositionFromValues();

		// color map
		var mapXValue = 0;
		var mapYValue = 0;
		switch(this.ColorMode) {
			case 'h':
				mapXValue = this.color.s;
				mapYValue = 100 - this.color.v;
				break;
				
			case 's':
				mapXValue = this.color.h;
				mapYValue = 100 - this.color.v;
				break;
				
			case 'v':
				mapXValue = this.color.h;
				mapYValue = 100 - this.color.s;
				break;
				
			case 'r':
				mapXValue = this.color.b;
				mapYValue = 256 - this.color.g;
				break;
				
			case 'g':
				mapXValue = this.color.b;
				mapYValue = 256 - this.color.r;
				break;
				
			case 'b':
				mapXValue = this.color.r;
				mapYValue = 256 - this.color.g;
				break;				
		}
		this._map.xValue = mapXValue;
		this._map.yValue = mapYValue;
		this._map.setArrowPositionFromValues();
	},
	updateVisuals: function() {
		this.updatePreview();
		this.updateMapVisuals();
		this.updateSliderVisuals();
	},
	updatePreview: function() {
		try {
			this._preview.style.backgroundColor = '#' + this._cvp.color.hex;
		} catch (e) {}
	},
	updateMapVisuals: function() {
		
		this.color = this._cvp.color;
		
		switch(this.ColorMode) {
			case 'h':
				// fake color with only hue
				var color = new Refresh.Web.Color({h:this.color.h, s:100, v:100});					
				this.setBG(this._mapL1, color.hex);
				break;
				
			case 's':
				this.setAlpha(this._mapL2, 100 - this.color.s);
				break;
				
			case 'v':
				this.setAlpha(this._mapL2, this.color.v);
				break;
				
			case 'r':								
				this.setAlpha(this._mapL2, this.color.r/256*100);
				break;
				
			case 'g':
				this.setAlpha(this._mapL2, this.color.g/256*100);
				break;
				
			case 'b':
				this.setAlpha(this._mapL2, this.color.b/256*100);
				break;				
		}
	},
	updateSliderVisuals: function() {
	
		this.color = this._cvp.color;
		
		switch(this.ColorMode) {
			case 'h':
				break;
				
			case 's':
				var saturatedColor = new Refresh.Web.Color({h:this.color.h, s:100, v:this.color.v});
				this.setBG(this._barL3, saturatedColor.hex);
				break;
				
			case 'v':
				var valueColor = new Refresh.Web.Color({h:this.color.h, s:this.color.s, v:100});
				this.setBG(this._barL3, valueColor.hex);
				break;
			case 'r':
			case 'g':				
			case 'b':
			
				var hValue = 0;
				var vValue = 0;
				
				if (this.ColorMode == 'r') {
					hValue = this._cvp._blueInput.value;
					vValue = this._cvp._greenInput.value;
				} else if (this.ColorMode == 'g') {
					hValue = this._cvp._blueInput.value;
					vValue = this._cvp._redInput.value;
				} else if (this.ColorMode == 'b') {
					hValue = this._cvp._redInput.value;
					vValue = this._cvp._greenInput.value;
				}
			
				var horzPer = (hValue /256)*100;
				var vertPer = ( vValue/256)*100;
				
				var horzPerRev = ( (256-hValue)/256)*100;
				var vertPerRev = ( (256-vValue)/256)*100;
										
				this.setAlpha(this._barL4, (vertPer>horzPerRev) ? horzPerRev : vertPer);
				this.setAlpha(this._barL3, (vertPer>horzPer) ? horzPer : vertPer); 
				this.setAlpha(this._barL2, (vertPerRev>horzPer) ? horzPer : vertPerRev);
				this.setAlpha(this._barL1, (vertPerRev>horzPerRev) ? horzPerRev : vertPerRev);
			
				break;
							
			
		}
	},
	setBG: function(el, c) {
		try {
			el.style.backgroundColor = '#' + c;
		} catch (e) {}
	},
	setImg: function(img, src) {
	
		if (src.indexOf('png') && this.isLessThanIE7) {
			img.pngSrc = src;
			img.src = this.settings.clientFilesPath + 'blank.gif';
			img.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + src + '\');';	
		
		} else {
			img.src = src;
		}
	},
	setAlpha: function(obj, alpha) {
		if (this.isLessThanIE7) {			
			var src = obj.pngSrc;
			// exception for the hue map
			if (src != null && src.indexOf('map-hue') == -1)
				obj.style.filter = 'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'' + src + '\') progid:DXImageTransform.Microsoft.Alpha(opacity=' + alpha + ')';	
		} else {
			obj.setOpacity(alpha/100);		
		}
	}
};
